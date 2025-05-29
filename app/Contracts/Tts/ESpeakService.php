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
            // Запись текста в UTF-8
            file_put_contents($tempInput, mb_convert_encoding($text, 'UTF-8', 'auto'));

            $process = new Process([
                config('services.espeak.path'),
                '-f', $tempInput,
                '-w', $tempOutput,
                '-v', $this->voices[$language],
                '-s', '120',
                '-p', '65',
                '-g', '8',
                '-a', '150',
                '-k', '20',
                '-l', '0'
            ]);
            $process->setTimeout(30);
            $process->run();

            // Convert error output to UTF-8 (Windows CP866 → UTF-8)
            if (!$process->isSuccessful()) {
                $rawError = $process->getErrorOutput();
                $utf8Error = mb_convert_encoding($rawError, 'UTF-8', 'CP866');
                throw new \Exception('eSpeak error: ' . trim($utf8Error));
            }

            // verify output file
            if (!file_exists($tempOutput) || filesize($tempOutput) === 0) {
                throw new \Exception('eSpeak generated empty audio file');
            }

            $audio = file_get_contents($tempOutput);

            return $audio !== false
                ? $audio
                : throw new \Exception('Failed to read eSpeak output file');
        } catch (\Throwable $e) {
            // clean up temp files
            @unlink($tempInput);
            @unlink($tempOutput);

            // ensure exception messages are UTF-8
            $msg = mb_convert_encoding($e->getMessage(), 'UTF-8', 'CP866');
            Log::error('eSpeak Synthesis Failed', [
                'error' => trim($msg),
                'trace' => $e->getTraceAsString()
            ]);
            throw new \Exception('eSpeak synthesis failed: ' . trim($msg));
        }
    }

    private function validateLanguage(string $language): void
    {
        if (!array_key_exists($language, $this->voices)) {
            throw new \Exception("Unsupported eSpeak language: $language");
        }
    }
}
