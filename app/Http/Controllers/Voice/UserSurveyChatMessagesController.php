<?php

namespace App\Http\Controllers\Voice;

use App\Http\Controllers\Controller;
use App\Models\AudioMessage;
use App\Models\Chat;
use App\Models\Message;
use App\Services\AudioService;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSurveyChatMessagesController extends Controller
{
    public function __construct(
        private AudioService $audioService,
        private OllamaService $ollamaService,
    ) {}

    /**Отображение страницы опроса
     *
     * @return boolean
     */
    public function survey()
    {
        $chatId = Chat::where('user_id', auth()->id())->where('is_system', true)->first()->id;
        $lastSystemMessage = Message::where('chat_id', $chatId)->latest()->first()->content;
        $isFinal = str_contains($lastSystemMessage, 'IS_FINAL');
        if($isFinal) {
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
            // УКАЗАТЬ МОДЕЛЬ
            'model' => 'string|in:gemma3:1b'
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

                // Формируем промпт для Ollama
//                $prompt = $this->ollamaService->buildPrompt($chatHistory, $validatedData['content']);

                // Отправляем запрос к Ollama
//                $ollamaResponse = $this->ollamaService->callOllama($prompt, $validatedData['model']); // или сделать config('survey.model');
//                $ollamaResponse = $this->ollamaService->callOllama($prompt, "qwen2.5:3b");

                // Парсим ответ Ollama
//                $responseContent = $ollamaResponse['message']['content'];
//                $isFinal = $ollamaResponse['is_final'] ?? false;

//                if ($isFinal)
//                {
//                    $this->ollamaService->generateParameters($chatHistory, "qwen2.5:3b");
//                }

                // Генерируем аудио ответ согласно content
//                $botResponse = $this->audioService->synthesizeAndSave($chat, 'assistant', $responseContent, $validatedData['language'], $validatedData['provider']);
                $responseContent = "hello";
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
        if (!$chat)
        {
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
    private function getOrCreateSystemChat(): Chat|bool
    {
        if(!auth()->check())
        {
            return false;
        }
        $userId = auth()->id();

        $chat = Chat::where('user_id', $userId)->where('is_system', true)->first();
        if (!$chat) {
            $chat = Chat::create([
                'user_id' => $userId,
                'is_system' => true,
            ]);
        }
        return $chat;
    }
}
