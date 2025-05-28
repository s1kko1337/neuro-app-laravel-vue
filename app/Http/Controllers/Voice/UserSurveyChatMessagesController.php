<?php

namespace App\Http\Controllers\Voice;

use App\Http\Controllers\Controller;
use App\Models\UserSurveyChatMessages;
use App\Services\AudioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserSurveyChatMessagesController extends Controller
{
    public function __construct(
        private AudioService $audioService
    ) {}

    /**Отображение страницы опроса
     *
     * @return boolean
     */
    public function survey()
    {
        return UserSurveyChatMessages::find('user_id', auth()->id())->is_final;
    }

    /**Добавление сообщения пользователем
     */
    public function userStore(Request $request)
    {
        $validatedData = $request->validate([
            'content' => 'required|string',
            'language' => 'required|string|in:en-US,ru-RU,de-DE',
            'tts_provider' => 'sometimes|string|in:espeak,yandex'
        ]);

        $message = null;

        try {
            DB::transaction(function () use ($validatedData, &$userMessage, &$botResponse) {
                // Сохраняем сообщение пользователя
                $userMessage = UserSurveyChatMessages::create([
                    'user_id' => auth()->id(),
                    'content' => $validatedData['content'],
                    'is_bot' => false,
                    'message_type' => 'user'
                ]);

                dd("Необходимо продумать ответ (response) модели. UserSurveyChatMessagesController");

                // Генерируем ответ бота
                $response = "Ответ бота";
                // Генерируем аудио ответ согласно content
                $botResponse = $this->botStore([
                    'content' => $response,
                    'language' => $validatedData['language'],
                    'tts_provider' => $validatedData['tts_provider']
                ]);
                //$botResponse = UserSurveyChatMessages::where('is_bot', true)->latest()->first();

                if (!$botResponse || !$botResponse->audio_path) {
                    throw new \RuntimeException('Failed to generate audio response');
                }
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
                'details' => $e->getMessage()
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
