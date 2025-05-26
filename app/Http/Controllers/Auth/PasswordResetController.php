<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\PasswordResetRequest;
use App\Http\Requests\PasswordRevokeRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function revoke(PasswordRevokeRequest $request): JsonResponse
    {
        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::ResetLinkSent
            ? response()->json([
                'message' => 'Ссылка для смены пароля отправлена',
                'status' => $status,
            ], 200)
            : response()->json([
                'message' => 'Произошла ошибка при отправке ссылки на сброс пароля.',
                'error' => $status,
            ], 500);
    }

    public function update(PasswordResetRequest $request) : JsonResponse{
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            static function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json([
            'message' => 'Пароль успешно изменен',
            'status' => $status,
        ], 200)
            : response()->json([
            'message' => 'Произошла ошибка при сбросе пароля.',
            'error' => $status,
        ], 500);
    }

    /**
     * Change password for authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function change(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'password_confirmation' => ['required', 'string'],
        ]);

        $user = Auth::user();

        // Проверяем, что текущий пароль соответствует тому, что есть в базе
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Текущий пароль указан неверно.',
                'errors' => [
                    'current_password' => ['Текущий пароль указан неверно.']
                ]
            ], 422);
        }

        // Обновляем пароль
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'message' => 'Пароль успешно изменен',
        ], 200);
    }
}
