<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{

    private OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function __invoke()
    {
        $response = $this->ollamaService->getModels();
        Log::info($response);

        return $response;
    }

    public function send(Request $request)
    {
        $request->validate([
            'messages' => 'required|array', // Массив сообщений
        ]);

        return $this->ollamaService->chat($request);
    }
}
