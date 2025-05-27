<?php

namespace App\Services;

use App\Factories\TtsServiceFactory;
use App\Models\UserSurveyChatMessages;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AudioService
{
    public function __construct(
        private readonly TtsServiceFactory $ttsFactory
    ) {}

    public function synthesizeAndSave(string $text, string $language, string $provider, int $userId): UserSurveyChatMessages
    {
        try {
            $this->validateInput($text, $language, $provider);

            if ($provider === 'yandex') {
                $audioContent = $this->processYandexRequest($text, $language);
            } else {
                $audioContent = $this->processDefaultRequest($text, $language, $provider);
            }

            $audioPath = $this->storeAudioFile($audioContent, $provider);

            $newChatMessage = $this->createChatMessage($userId, $text, $audioPath);

            return $newChatMessage;

        } catch (\Throwable $e) {
            $this->handleServiceError($e);
            throw $e;
        }
    }

    private function validateInput(string $text, string $language, string $provider): void
    {
        if (mb_strlen($text) > 500) {
            throw new \InvalidArgumentException('Text exceeds maximum length');
        }

        if (!in_array($language, ['en-US', 'ru-RU', 'de-DE'])) {
            throw new \InvalidArgumentException('Unsupported language');
        }

        if (!in_array($provider, ['espeak', 'yandex'])) {
            throw new \InvalidArgumentException('Invalid TTS provider');
        }
    }

    private function processYandexRequest(string $text, string $language): string
    {
        $ttsService = $this->ttsFactory->make('yandex');
        $lpcmAudio = $ttsService->synthesize($text, $language);
        return $this->convertToWav($lpcmAudio);
    }

    private function processDefaultRequest(string $text, string $language, string $provider): string
    {
        $ttsService = $this->ttsFactory->make($provider);
        return $ttsService->synthesize($text, $language);
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
        $path = 'chat_audio/' . Str::uuid() . '.' . $extension;

        Storage::disk('public')->put($path, $audioContent);

        return $path;
    }

    private function createChatMessage(int $userId, string $text, string $audioPath): UserSurveyChatMessages
    {
        return UserSurveyChatMessages::create([
            'user_id' => $userId,
            'content' => $text,
            'audio_path' => $audioPath,
            'is_bot' => true,
        ]);
    }

    private function handleServiceError(\Throwable $e): void
    {
        logger()->error('AudioService Error: ' . $e->getMessage(), [
            'exception' => $e,
            'trace' => $e->getTraceAsString()
        ]);
    }
}
