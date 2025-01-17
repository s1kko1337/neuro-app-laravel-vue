<?php

namespace App\Services;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;


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

    public function getModels (Request $request){
        try {

            $responseModelsInfo = Ollama::models();
            $modelNames = [];
            if (isset($responseModelsInfo['models']) && !empty($responseModelsInfo['models'])) {
                foreach ($responseModelsInfo['models'] as $model) {
                    $modelNames[] = $model['name']; // Извлекаем имя модели
                }
            } else {
                // Обработка случая, когда моделей нет
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

    public function chat (Request $request){

    }

    public function chatMessage (Request $request){

    }

}
