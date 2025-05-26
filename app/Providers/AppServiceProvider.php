<?php

namespace App\Providers;

use App\Models\CustomToken;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Sanctum::usePersonalAccessTokenModel(CustomToken::class);

        VerifyEmail::createUrlUsing(function ($notifiable) {
            $frontend_url = 'http://localhost'; //В проде убрать

            $api_url = URL::temporarySignedRoute(
                'verification.verify',
                Carbon::now()->addMinutes(Config::get('auth.verification.expire', 60)),
                [
                    'id' => $notifiable->getKey(),
                    'hash' => sha1($notifiable->getEmailForVerification()),
                ]
            );

            $url_parts = parse_url($api_url);
            parse_str($url_parts['query'] ?? '', $query_params);


            return rtrim($frontend_url, '/') .
                '/email/verify/' . $notifiable->getKey() . '/' . sha1($notifiable->getEmailForVerification()) .
                '?expires=' . ($query_params['expires'] ?? '') .
                '&signature=' . ($query_params['signature'] ?? '');
        });

        VerifyEmail::toMailUsing(static function ($notifiable, $url) {
            return (new MailMessage)
                ->subject('Подтверждение адреса электронной почты')
                ->greeting('Здравствуйте!')
                ->line('Пожалуйста, нажмите кнопку ниже, чтобы подтвердить Ваш адрес электронной почты.')
                ->action('Подтвердить Email', $url)
                ->line('Если Вы не создавали учетную запись, никаких дальнейших действий не требуется.')
                ->salutation('С уважением, команда SupportApp');
        });

        // Configure password reset URL
        ResetPassword::createUrlUsing(function ($user, string $token) {
            $frontend_url = 'http://localhost'; // В проде убрать
            return rtrim($frontend_url, '/') . '/reset-password/' . $token . '?email=' . urlencode($user->email);
        });

        // Configure password reset mail message
        ResetPassword::toMailUsing(function ($notifiable, string $token) {
            $url = 'http://localhost' . '/reset-password/' . $token . '?email=' . urlencode($notifiable->getEmailForPasswordReset());

            return (new MailMessage)
                ->subject('Сброс пароля')
                ->greeting('Здравствуйте!')
                ->line('Вы получили это письмо, потому что мы получили запрос на сброс пароля для вашей учетной записи.')
                ->action('Сбросить пароль', $url)
                ->line('Срок действия ссылки для сброса пароля истечет через ' . config('auth.passwords.users.expire') . ' минут.')
                ->line('Если вы не запрашивали сброс пароля, никаких дальнейших действий не требуется.')
                ->salutation('С уважением, команда SupportApp');
        });

        RateLimiter::for('api', static function (Request $request) {
            return Limit::perMinute(2000)->
            by($request->user()?->id ?: $request->
            ip())->response(
                function (Request $request, array $headers = []) {
                    return response('Too Many Attempts.', 429);
                }
            );
        });
    }
}
