<?php

namespace App\Services;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;


class OllamaService
{
    public function ask (Request $request){
        $response = Ollama::agent($request->role_discription)
            ->prompt($request->question.' '.'Respond in Json')
            ->model(config('ollama-laravel.model'))
            ->options(['temperature',config(floatval('ollama-laravel.temperature'))])
            ->format('json')
            ->stream(false)
            ->ask();

        return $response()->json($response,200);

    }
}
