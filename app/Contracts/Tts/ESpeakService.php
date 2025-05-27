<?php

namespace App\Contracts\Tts;

use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Log;

class ESpeakService implements TtsService
{
    protected array $voices = [
        'ru-RU' => 'russian',
        'en-US' => 'en-us'
    ];

    public function synthesize(string $text, string $language): string
    {
        $this->validateLanguage($language);

        $tempDir = sys_get_temp_dir();
        $tempInput = tempnam($tempDir, 'espeak_') . '.txt';
        $tempOutput = tempnam($tempDir, 'espeak_') . '.wav';

        try {
            // Запись текста в файл с явным указанием кодировки
            file_put_contents($tempInput, mb_convert_encoding($text, 'UTF-8'));

            $process = new Process([
                config('services.espeak.path'),
                '-f', $tempInput,
                '-w', $tempOutput,
                '-v', $this->voices[$language],
                '-s', '120',    // Уменьшаем скорость (было 170)
                '-p', '65',     // Повышаем тон для лучшей артикуляции
                '-g', '8',      // Паузы между фразами (мс)
                '-a', '150',    // Увеличиваем громкость
                '-k', '20',     // Ударение заглавных слов
                '-l', '0'       // Убираем лишние паузы в словах
            ]);

            $process->setTimeout(30);
            $process->run();

            Log::debug('eSpeak Process Output', [
                'exit_code' => $process->getExitCode(),
                'output' => $process->getOutput(),
                'error' => $process->getErrorOutput()
            ]);

            if (!$process->isSuccessful()) {
                throw new \Exception('eSpeak error: '.$process->getErrorOutput());
            }

            if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
                throw new \Exception('eSpeak generated empty audio file');
            }

            $audio = file_get_contents($tempOutput);

            // Обязательная очистка временных файлов
            unlink($tempInput);
            unlink($tempOutput);

            return $audio;

        } catch (\Throwable $e) {
            // Гарантированная очистка при ошибке
            if (file_exists($tempInput)) @unlink($tempInput);
            if (file_exists($tempOutput)) @unlink($tempOutput);

            Log::error('eSpeak Synthesis Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw new \Exception('eSpeak synthesis failed: '. $e->getMessage());
        }
    }

    private function validateLanguage(string $language): void
    {
        if (!array_key_exists($language, $this->voices)) {
            throw new \Exception("Unsupported eSpeak language: $language");
        }
    }
}
