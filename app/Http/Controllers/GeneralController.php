<?php

namespace App\Http\Controllers;


use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeneralController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Hello from Laravel API!',
            'status' => 200,
        ]);
    }

    public function testOllama(): \Illuminate\Http\JsonResponse
    {
        try {
            Log::info('Starting Ollama request...');

            $response = Ollama::agent('You are a weather expert...')
                ->prompt('Why is the sky blue?')
                ->options(['temperature' => 0.8])
                ->stream(false)
                ->ask();


            Log::info('LOG', ['response' => $response]);

            return response()->json([
                'response' => $response,
            ]);
        } catch (\Exception $e) {
            Log::error('Ошибка API оламы: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Ошибка с запросом.',
                'details' => $e->getMessage(),
            ], 500);
        }
    }
}
