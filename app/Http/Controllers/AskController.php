<?php

namespace App\Http\Controllers;

use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class AskController extends Controller
{
    private OllamaService $ollamaService;

    public function __construct(OllamaService $ollamaService)
    {
        $this->ollamaService = $ollamaService;
    }

    public function __invoke(Request $request)
    {
        Log::info($request->all());

        $request->validate([
            'role_discription' => 'required|string|min:3|max:500',
            'question' => 'required|string|min:3|max:500',
        ]);
        $response = $this->ollamaService->ask($request);
        Log::info($response);

        return $response;
    }
}
