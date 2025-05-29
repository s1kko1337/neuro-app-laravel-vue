<?php

namespace App\Http\Controllers\Voice;

use App\Http\Controllers\ChatController;
use App\Http\Controllers\Controller;
use App\Models\AudioMessage;
use App\Models\Chat;
use App\Models\Message;
use App\Services\AudioService;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSurveyChatMessagesController extends Controller
{
    public function __construct(
        private AudioService   $audioService,
        private OllamaService $ollamaService,
        private ChatController $chatController,
    )
    {
    }

    /**Отображение страницы опроса
     *
     * @return boolean
     */
    public function survey()
    {
        $chatId = Chat::where('user_id', auth()->id())->where('is_system', true)->first()->id;
        $lastSystemMessage = Message::where('chat_id', $chatId)->latest()->first()->content;
        $isFinal = str_contains($lastSystemMessage, 'IS_FINAL');
        if ($isFinal) {
            return false;
        }
        return true;
    }

    /**Добавление сообщения пользователем
     */
    public function userStore(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'required|string',
            'language' => 'required|string|in:en-US,ru-RU,de-DE',
            'tts_provider' => 'sometimes|string|in:espeak,yandex',
        ]);

        try {
            [$userMessage, $botResponse] = DB::transaction(function () use ($validatedData) {
                // Сохраняем сообщение пользователя
                $chat = $this->getOrCreateSystemChat();

                $userMessage = Message::create([
                    'chat_id' => $chat->id,
                    'role' => 'user',
                    'content' => $validatedData['content'],
                ]);

                // Получаем историю диалога для контекста
                $chatHistory = Message::where('chat_id', $chat->id)
                    ->orderBy('created_at', 'asc')
                    ->get()
                    ->map(function ($message) {
                        return [
                            'content' => $message->content,
                            'role' => $message->is_bot ? 'assistant' : 'user'
                        ];
                    })
                    ->toArray();

                $data = [
                    'messages' => $chatHistory,
                    'chatId' => $chat->id,
                    'model' => config('services.survey.model'),
                ];
                //dd($data['messages']);

                $lastUserMessage = $this->chatController->getLastUserMessage($data['messages']);
                \Log::info('Last User Message:', ['message' => $lastUserMessage]);

                // Отправка POST-запроса на FastAPI
                $pythonHost = config('services.python_api.host');
                $pythonPort = config('services.python_api.port');
                $url = "http://{$pythonHost}:{$pythonPort}/chats/{$chat->id}/survey_messages";
                $response = Http::post($url, [
                    'messages' => $data['messages'],
                    'system_prompt' => "
                You are conducting a survey of the student about his hobbies.
                Ask one question in each message.
                Each new question should be on a new topic in order to cover as many of the student's interests as possible.
                Ask questions from different fields, cover all his interests.
                Ask exactly 5 questions in total, each in a separate message.
                After the user answers the fifth question, send a sixth message containing ONLY the text 'IS_FINAL'.
                Do NOT write any additional text or questions in the sixth message.
                Always answer in Russian.
                Make sure that your answer is as accurate and complete as possible.
            ",
                ]);
                // Логируем ответ от FastAPI
                \Log::info('Response from FastAPI:', ['response' => $response->json()]);
                // Возврат ответа от FastAPI
                $responseContent = $response['message'];

                $isFinal = str_contains($responseContent, 'IS_FINAL');
                if ($isFinal) {
                    $response = $this->ollamaService->gentrateUserSurvey($chatHistory);

                    $jsonString = trim(str_replace(['```json', '```'], '', $response));
                    $hobbies = json_decode($jsonString, true);

                    \Log::info('Response from FastAPI:', ['response' => $response]);

                    $user = Auth::user();
                    $userSurvey = $user->parameters()->updateOrCreate([
                            'user_id'=>$user->id,
                            'parameters' =>implode(',',$hobbies)
                    ])->toArray();
                }

                $botResponse = $this->audioService->synthesizeAndSave($chat, 'assistant', $responseContent, $validatedData['language'], $validatedData['tts_provider']);

                if (!$botResponse || !isset($botResponse['audio_path'])) {
                    throw new \RuntimeException('Failed to generate audio response');
                }

                return [$userMessage, $botResponse];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => $userMessage,
                    'bot_response' => $botResponse,
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error("Message processing error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'error' => 'Message processing failed',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**Получение истории сообщений
     */
    public function history()
    {
        $chat = Chat::where('user_id', auth()->id())->where('is_system', true)->first();
        if (!$chat) {
            Chat::create([
                'user_id' => auth()->id(),
                'is_system' => true,
            ]);
        }
        $messages = Message::where('chat_id', $chat->id)
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($message) {
                if ($message->role === 'assistant') {
                    $audioContent = AudioMessage::where('message_id', $message->id)->first();
                    $message->audio_url = $audioContent ? $audioContent->audio_url : null;
                    $message->audio_path = $audioContent ? $audioContent->audio_path : null;
                }
                return $message;
            })
            ->toArray();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**Получение чата с is_system флагом или его создание
     *
     * @return Chat|bool
     */
    public function getOrCreateSystemChat(): Chat|bool
    {
        if (!auth()->check()) {
            return false;
        }
        $userId = auth()->id();

        $chat = Chat::where('user_id', $userId)->where('is_system', true)->first();
        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $userId,
                'is_system' => true,
            ]);

            $userMessage = Message::create([
                'chat_id' => $chat->id,
                'role' => 'user',
                'content' => 'Привет. Начинай опрос!',
            ]);

            // Отправка POST-запроса на FastAPI
            $pythonHost = config('services.python_api.host');
            $pythonPort = config('services.python_api.port');
            $url = "http://{$pythonHost}:{$pythonPort}/chats/{$chat->id}/survey_messages";
            $response = Http::post($url, [
                'messages' => array($userMessage),
                'system_prompt' => "
                You are conducting a survey of the student about his hobbies.
                Ask one question in each message.
                Each new question should be on a new topic in order to cover as many of the student's interests as possible.
                Ask questions from different fields, cover all his interests.
                Ask exactly 5 questions in total, each in a separate message.
                After the user answers the fifth question, send a sixth message containing ONLY the text 'IS_FINAL'.
                Do NOT write any additional text or questions in the sixth message.
                Always answer in Russian.
                Make sure that your answer is as accurate and complete as possible.
            ",
            ]);
            // Логируем ответ от FastAPI
            \Log::info('Response from FastAPI:', ['response' => $response->json()]);
            // Возврат ответа от FastAPI
            $responseContent = $response['message'];

            $botResponse = $this->audioService->synthesizeAndSave($chat, 'assistant', $responseContent, 'ru-RU', 'yandex');

            if (!$botResponse || !isset($botResponse['audio_path'])) {
                throw new \RuntimeException('Failed to generate audio response');
            }

        }
        return $chat;
    }

}
