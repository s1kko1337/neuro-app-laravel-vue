<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use function Laravel\Prompts\text;

class SetupYandexCredentials extends Command
{
    protected $signature = 'yandex:setup';
    protected $description = 'Setup Yandex Cloud credentials for SpeechKit';

    public function handle()
    {
        // Запросить у пользователя OAuth-токен
        $oauthToken = text(
            label: 'Введите OAuth-токен Яндекс (https://cloud.yandex.ru/docs/iam/operations/iam-token/create)',
            required: true
        );

        $this->info('Получение IAM токена...');

        $response = Http::withOptions(['verify' => false])
            ->withHeaders(['Content-Type' => 'application/json'])
            ->post('https://iam.api.cloud.yandex.net/iam/v1/tokens', [
                'yandexPassportOauthToken' => $oauthToken,
            ]);

        if (!$response->ok()) {
            $this->error('Ошибка при получении IAM токена: ' . $response->body());
            return Command::FAILURE;
        }

        $iamToken = $response->json('iamToken');
        if (!$iamToken) {
            $this->error('IAM токен не получен. Ответ: ' . $response->body());
            return Command::FAILURE;
        }

        $this->info('IAM токен успешно получен.');

        // Сохраняем токен в .env
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        $envKey = 'YANDEX_IAM_TOKEN';
        $newLine = $envKey . '="' . $iamToken . '"';

        if (preg_match("/^{$envKey}=.*$/m", $envContent)) {
            $envContent = preg_replace("/^{$envKey}=.*$/m", $newLine, $envContent);
        } else {
            $envContent .= PHP_EOL . $newLine . PHP_EOL;
        }

        File::put($envPath, $envContent);

        $this->info("IAM токен сохранён в .env как {$envKey}.");
        return Command::SUCCESS;
    }
}
