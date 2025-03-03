<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GenerateContextController extends Controller
{
    public function create(Request $request)
    {
        try {
            $validated_data = $request->validate([
                'chat_id' => 'required|integer',
            ]);

            // Отправка POST-запроса на FastAPI с query-параметром
            $response = Http::post(
                "http://python:8000/global/collection?chat_id=" . $validated_data['chat_id']
            );

            // Логируем ответ от FastAPI
            Log::info('Response from FastAPI:', ['response' => $response->json()]);

            // Возврат ответа от FastAPI
            if ($response->successful()) {
                return $response->json();
            } else {
                Log::error('FastAPI error: ' . $response->body());
                return response()->json([
                    'error' => 'Error from FastAPI: ' . $response->body()
                ], $response->status());
            }
        } catch (\Exception $e) {
            Log::error('Generate context error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function delete($chat_id) {
        $coll_name = 'collection-' . $chat_id;
        $response = Http::delete("http://python:8000/chroma/collections/" . $coll_name);

        return $response;
    }
}