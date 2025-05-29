<?php

namespace App\Http\Controllers\Voice;

use App\Http\Controllers\Controller;
use App\Models\UserSurveyChatMessages;
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
        $isFinal = UserSurveyChatMessages::where('user_id', auth()->id())->value('is_final');
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
                $userMessage = UserSurveyChatMessages::create([
                    'user_id' => auth()->id(),
                    'content' => $validatedData['content'],
                    'is_bot' => false,
                    'message_type' => 'user'
                ]);

                $chatHistory = UserSurveyChatMessages::where('user_id', auth()->id())
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
                $prompt = $this->ollamaService->buildPrompt($chatHistory, $validatedData['content']);

                // Отправляем запрос к Ollama
//                $ollamaResponse = $this->ollamaService->callOllama($prompt, $validatedData['model']); // или сделать config('survey.model');
                $ollamaResponse = $this->ollamaService->callOllama($prompt, "gemma3:1b");

                // Парсим ответ Ollama
                $responseContent = $ollamaResponse['message']['content'];
                $isFinal = $ollamaResponse['is_final'] ?? false;

                if ($isFinal)
                {
                    $this->ollamaService->generateParameters($chatHistory, "gemma3:1b");
                }

                // Генерируем аудио ответ согласно content
                $botResponse = $this->botStore([
                    'content' => $responseContent,
                    'language' => $validatedData['language'],
                    'tts_provider' => $validatedData['tts_provider'] ?? 'espeak',
                    'is_final' => $isFinal,
                    'message_type' => 'bot'
                ]);

                if (!$botResponse || !isset($botResponse->audio_path)) {
                    throw new \RuntimeException('Failed to generate audio response');
                }

                return [$userMessage, $botResponse];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'user_message' => $userMessage,
                    'bot_response' => $botResponse
                ]
            ]);

        } catch (\Throwable $e) {
            Log::error("Message processing error: {$e->getMessage()}");
            return response()->json([
                'success' => false,
                'error' => 'Message processing failed',
                'details' => $e->getMessage() . PHP_EOL . print_r($e->getTraceAsString())
            ], 500);
        }
    }
    /**Получение истории сообщений
     */
    public function history()
    {
        $messages = UserSurveyChatMessages::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $messages
        ]);
    }

    /**Добавление сообщения большой языковой моделью
     */
    private function botStore(array $validatedData): UserSurveyChatMessages|bool
    {
        try {
            return $this->audioService->synthesizeAndSave(
                $validatedData['content'],
                $validatedData['language'],
                $validatedData['tts_provider'],
                auth()->id()
            );
        } catch (\Throwable $e) {
            Log::error('Bot response error: ' . $e->getMessage());
            return false;
        }
    }
}
