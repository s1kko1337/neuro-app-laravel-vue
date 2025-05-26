<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailVerificationController extends Controller
{
    /**
     * Показать статус верификации email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function notice(Request $request): JsonResponse
    {
        $isVerified = $request->user()->hasVerifiedEmail();

        return response()->json([
            'message' => $isVerified
                ? 'Email уже подтвержден.'
                : 'Email требует подтверждения.',
            'verified' => $isVerified,
            'email' => $request->user()->email,
        ]);
    }

    /**
     * Подтвердить email пользователя.
     *
     * @param EmailVerificationRequest $request
     * @return JsonResponse
     */
    public function verify(EmailVerificationRequest $request): JsonResponse
    {
        try {
            if ($request->user()->hasVerifiedEmail()) {
                return response()->json([
                    'message' => 'Email уже подтвержден.',
                    'verified' => true,
                    'user_id' => $request->user()->id,
                    'email' => $request->user()->email,
                ]);
            }

            $request->fulfill();

            return response()->json([
                'message' => 'Email успешно подтвержден.',
                'verified' => true,
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Ошибка при верификации email: ' . $e->getMessage(), [
                'exception' => $e,
                'user_id' => $request->user()->id,
                'email' => $request->user()->email,
            ]);

            return response()->json([
                'message' => 'Произошла ошибка при подтверждении email.',
                'verified' => false,
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(), // В production убрать
            ], 500);
        }
    }

    /**
     * Повторно отправить письмо для подтверждения email.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resend(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email уже подтвержден.',
                'verified' => true,
            ]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Ссылка для подтверждения отправлена.',
            'email' => $request->user()->email,
        ]);
    }
}
