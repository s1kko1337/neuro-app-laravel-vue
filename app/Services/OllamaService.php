<?php

namespace App\Services;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class OllamaService
{
    public function ask (Request $request){
        $options = [
            'temperature' => floatval(config('ollama-laravel.temperature'))
        ];

        $response = Ollama::agent($request->role_discription)
            ->prompt($request->question.' '.'Respond in Json')
            ->model(config('ollama-laravel.model'))
            ->options($options)
            ->format('json')
            ->stream(false)
            ->ask();

        return response()->json($response,200);

    }

    public function getModels (){
        try {
            $responseModelsInfo = Ollama::models();
            $modelNames = [];
            if (isset($responseModelsInfo['models']) && !empty($responseModelsInfo['models'])) {
                foreach ($responseModelsInfo['models'] as $model) {
                    $modelNames[] = $model['name'];
                }
            } else {
                echo "Модели не найдены.";
            }

            $modelNames = array_map(function($name) {
                return ['name' => $name];
            }, $modelNames);

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => "LLM models get successfully",
                    'models' => $modelNames
                ]);
            } else {
                return view('app', ['message' => "LLM models get successfully", 'models' => $modelNames]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error getting models', 'errors' => $e->getMessage()], 500);
        }
    }

    public function chat(Request $request)
    {
        $messages = $request->input('messages');
        $model = $request->input('model');

        // Логгируем входные данные
        Log::info('Request data:', [
            'messages' => $messages,
            'model' => $model,
        ]);

        $response = Ollama::agent('You know me really well!')
            ->model($model)
//            ->embendings()
            ->chat($messages);

        Log::info('Ollama response:', $response);

        if (!isset($response['message'])) {
            Log::error('Ollama response does not contain "message" key:', $response);
            return response()->json([
                'error' => 'Ollama response is invalid',
            ], 500);
        }

        $assistantMessage = $response['message']['content'] ?? $response['message'];

        return response()->json([
            'message' => $assistantMessage,
        ], 200);
    }

    public function chatMessage (Request $request){

    }

    public function setModel (Request $request){
        try {
            $modelName = $request->modelName;

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => "LLM models set successfully",
                ]);
            } else {
                return view('app', ['message' => "LLM models get successfully", 'models' => $modelName]);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error set model', 'errors' => $e->getMessage()], 500);
        }
    }
}
