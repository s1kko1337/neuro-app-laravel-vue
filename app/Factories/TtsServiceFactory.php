<?php

namespace App\Factories;

use App\Contracts\Tts\YandexTtsService;
use App\Contracts\Tts\ESpeakService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

class TtsServiceFactory
{
    public function make(string $provider): \App\Contracts\Tts\TtsService
    {
        return match ($provider) {
            'yandex' => new YandexTtsService(new Client()),
            'espeak' => new ESpeakService(),
            default => throw new \InvalidArgumentException("Unknown provider: $provider"),
        };
    }
}
