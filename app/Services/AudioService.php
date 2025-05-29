<?php

namespace App\Services;

use App\Factories\TtsServiceFactory;
use App\Models\AudioMessage;
use App\Models\Chat;
use App\Models\Message;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioService
{
    public function __construct(
        private readonly TtsServiceFactory $ttsFactory
    ) {}

    //$botResponse = $this->audioService->synthesizeAndSave([
    //                        'chat_id' => $chat->id,
    //                        'role' => 'assistant',
    //                        'content' => $responseContent,
    //                        'language' => $validatedData['language'],
    //                        'tts_provider' => $validatedData['tts_provider'] ?? 'espeak',
    //                    ]);
    public function synthesizeAndSave(Chat $chat, string $role, string $content, string $language, string $provider): array
    {
        try
        {
            $cleanContent = $this->validateInput($content, $language, $provider);

            // Аудио файл
            $audioContent = ($provider === 'yandex')
                ? $this->processYandexRequest($cleanContent, $language)
                : $this->processDefaultRequest($cleanContent, $language, $provider);

            if (empty($audioContent)) {
                throw new \RuntimeException("Audio content is empty");
            }

            $audioPath = $this->storeAudioFile($audioContent, $provider);

            return $this->createChatMessage($chat, $role, $cleanContent, $audioPath);
        }
        catch (\Throwable $e)
        {
            $this->handleServiceError($e);
            throw $e;
        }
    }

    private function validateInput(string $content, string $language, string $provider): string
    {
        $cleanContent = mb_convert_encoding($content, 'UTF-8', 'UTF-8');

        if (mb_strlen($cleanContent) > 500) {
            throw new \InvalidArgumentException('Text exceeds maximum length');
        }

        if (!in_array($language, ['en-US', 'ru-RU', 'de-DE'])) {
            throw new \InvalidArgumentException('Unsupported language');
        }

        if (!in_array($provider, ['espeak', 'yandex'])) {
            throw new \InvalidArgumentException('Invalid TTS provider');
        }

        return $cleanContent;
    }

    private function processYandexRequest(string $content, string $language): string
    {
        $ttsService = $this->ttsFactory->make('yandex');
        $lpcmAudio = $ttsService->synthesize($content, $language);
        return $this->convertToWav($lpcmAudio);
    }

    private function processDefaultRequest(string $content, string $language, string $provider): string
    {
        $ttsService = $this->ttsFactory->make($provider);
        return $ttsService->synthesize($content, $language);
    }

    private function convertToWav(string $lpcmData): string
    {
        $tempDir = sys_get_temp_dir();
        $rawFile = tempnam($tempDir, 'yandex_raw_') . '.raw';
        $wavFile = tempnam($tempDir, 'yandex_wav_') . '.wav';

        try {
            if (file_put_contents($rawFile, $lpcmData) === false) {
                throw new \RuntimeException("Failed to write RAW file");
            }

            $command = "sox -r 48000 -b 16 -e signed-integer -c 1 {$rawFile} {$wavFile} 2>&1";
            exec($command, $output, $returnCode);

            if ($returnCode !== 0 || !file_exists($wavFile)) {
                throw new \RuntimeException("Audio conversion failed");
            }

            return file_get_contents($wavFile);
        } finally {
            array_map(fn($f) => @unlink($f), [$rawFile, $wavFile]);
        }
    }

    private function storeAudioFile(string $audioContent, string $provider): string
    {
        $extension = $provider === 'edge' ? 'mp3' : 'wav';
        $path = 'public/chat_audio/user_' . auth()->id() . '/' . Str::uuid() . '.' . $extension;

        // Сохраняем в storage/app/public/chat_audio/...
        Storage::disk('public')->put($path, $audioContent);

        // Возвращаем путь, который будет доступен через симлинк
        return $path;
    }

    /**Сохранение сообщения и аудиофайла в таблице БД
     * @param int $userId
     * @param string $content
     * @param string $audioPath
     * @return array
     */
    private function createChatMessage(Chat $chat, string $role, string $content, string $audioPath): array
    {
        DB::transaction(function () use ($chat, $role, $content, $audioPath)
        {
            $message = Message::create([
                'chat_id' => $chat->id,
                'role' => $role,
                'content' => $content,
            ]);
            AudioMessage::create([
                'message_id' => $message->id,
                'audio_path' => $audioPath,
            ]);
        });
        return [
            'user_id' => auth()->id(),
            'content' => $content,
            'audio_path' => $audioPath,
            'audio_url' => Storage::disk('public')->url($audioPath),
            'role' => $role,
        ];
    }

    private function handleServiceError(\Throwable $e): void
    {
        logger()->error('AudioService Error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
    }
}
