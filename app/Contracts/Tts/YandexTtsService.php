<?php

namespace App\Contracts\Tts;

use Illuminate\Support\Facades\Http;
use Exception;

class YandexTtsService implements TtsService
{
    protected array $voiceMap = [
        'ru-RU' => 'alena',
        'en-US' => 'john',
        'de-DE' => 'omazh'
    ];

    protected array $speedMap = [
        'ru-RU' => 1.0,
        'en-US' => 1.1,
        'de-DE' => 1.0
    ];

    public function synthesize(string $text, string $language): string
    {
        $this->validateLanguage($language);

        $params = [
            'text' => $this->sanitizeText($text),
            'lang' => $language,
            'voice' => $this->voiceMap[$language],
            'speed' => $this->speedMap[$language],
            'format' => 'lpcm',
            'sampleRateHertz' => 48000
        ];

        // Добавляем folderId только если он указан
        if ($folderId = config('services.yandex.folder_id')) {
            $params['folderId'] = $folderId;
        }

        try {
            $response = Http::withOptions(['verify' => false])
                ->asForm()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . config('services.yandex.iam_token'),
                ])
                ->post('https://tts.api.cloud.yandex.net/speech/v1/tts:synthesize', $params);

            if ($response->failed()) {
                $error = $response->json();
                throw new Exception("Yandex TTS error [{$error['error_code']}]: {$error['error_message']}");
            }

            return $response->body();

        } catch (\Throwable $e) {
            throw new Exception("Yandex TTS request failed: {$e->getMessage()}");
        }
    }

    private function validateLanguage(string $language): void
    {
        if (!array_key_exists($language, $this->voiceMap)) {
            throw new \InvalidArgumentException("Unsupported language: $language");
        }
    }

    private function sanitizeText(string $text): string
    {
        return preg_replace('/[^\p{L}\p{N}\s.,!?\-]/u', '', $text);
    }
}
