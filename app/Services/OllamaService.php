<?php

namespace App\Services;
use App\Models\Chat;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\Embedding;
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
                    if ($model['name'] !== 'nomic-embed-text:latest'){
                        $modelNames[] = $model['name'];
                    }
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

//    public function chat(Request $request)
//    {
//        $messages = $request->input('messages'); // Это массив сообщений
//        $model = $request->input('model');
//        $chatId = $request->input('chatId');
//
//        $lastUserMessage = null;
//        foreach (array_reverse($messages) as $message) {
//            if ($message['role'] === 'user') {
//                $lastUserMessage = $message;
//                break;
//            }
//        }
//
//        if (!$lastUserMessage) {
//            return response()->json([
//                'error' => 'No user message found',
//            ], 400);
//        }
//
//
//        Log::info('Request data:', [
//            'messages' => $messages,
//            'model' => $model,
//            'chatId' => $chatId
//        ]);
//
////        $relevantMessages = $this->getRelevantMessages($chatId, $lastUserMessage['content']);
//
//        foreach ($message as $message) {
//            $messages[] = [
//                'role' => $message->role,
//                'content' => $message->content,
//            ];
//        }
//
//        $response = Ollama::agent('You know all as well!')
//            ->model($model)
//            ->chat($messages);
//
//        Log::info('Ollama response:', $response);
//
//        if (!isset($response['message'])) {
//            Log::error('Ollama response does not contain "message" key:', $response);
//            return response()->json([
//                'error' => 'Ollama response is invalid',
//            ], 500);
//        }
//
//        $assistantMessage = $response['message']['content'] ?? $response['message'];
//
//        DB::transaction(function () use ($lastUserMessage, $assistantMessage, $model, $chatId) {
//            $chat = Chat::findOrFail($chatId);
//
//            $lastUserMessageModel = $chat->messages()->create([
//                'role' => $lastUserMessage['role'],
//                'content' => $lastUserMessage['content'],
//            ]);
//
////            $userEmbedding = $this->generateEmbedding($lastUserMessage['content']);
////            $lastUserMessageModel->embedding()->create([
////                'embedding' => $userEmbedding,
////            ]);
//
//            $assistantMessageModel = $chat->messages()->create([
//                'role' => 'assistant',
//                'content' => $assistantMessage,
//            ]);
//
//            $assistantEmbedding = $this->generateEmbedding($assistantMessage);
//            $assistantMessageModel->embedding()->create([
//                'embedding' => $assistantEmbedding,
//            ]);
//        });
//
//        return response()->json([
//            'message' => $assistantMessage,
//        ], 200);
//    }

    public function chat(Request $request)
    {
        $messages = $request->input('messages');
        $model = $request->input('model');
        $chatId = $request->input('chatId');

        //Поиск контекстного сообщения с текстом файла
        $chat = Chat::findOrFail($chatId);
        $fileContextMessage = $chat->messages()
            ->where('role', 'user')
            ->whereNotNull('content')
            ->first();

        if ($fileContextMessage) {
            array_unshift($messages, [
                'role' => $fileContextMessage->role,
                'content' => $fileContextMessage->content,
            ]);
        }
        //Конец

        $lastUserMessage = null;
        foreach (array_reverse($messages) as $message) {
            if ($message['role'] === 'user') {
                $lastUserMessage = $message;
                break;
            }
        }

        if (!$lastUserMessage) {
            return response()->json([
                'error' => 'No user message found',
            ], 400);
        }

        Log::info('Request data:', [
            'messages' => $messages,
            'model' => $model,
            'chatId' => $chatId
        ]);

        $response = Ollama::agent('You know all as well!')
            ->model($model)
            ->chat($messages);

        Log::info('Ollama response:', $response);

        if (!isset($response['message'])) {
            Log::error('Ollama response does not contain "message" key:', $response);
            return response()->json([
                'error' => 'Ollama response is invalid',
            ], 500);
        }

        $assistantMessage = $response['message']['content'] ?? $response['message'];

        DB::transaction(function () use ($lastUserMessage, $assistantMessage, $model, $chatId) {
            $chat = Chat::findOrFail($chatId);

            $lastUserMessageModel = $chat->messages()->create([
                'role' => $lastUserMessage['role'],
                'content' => $lastUserMessage['content'],
            ]);

            $assistantMessageModel = $chat->messages()->create([
                'role' => 'assistant',
                'content' => $assistantMessage,
            ]);
        });

        return response()->json([
            'message' => $assistantMessage,
        ], 200);
    }

    public function generateEmbedding(string $text): array
    {
        $embeddings = Ollama::model('nomic-embed-text')->embeddings($text);

        if (empty($embeddings['embedding'])) {
            Log::error('Failed to generate embedding for text:', ['text' => $text]);
            throw new \RuntimeException('Failed to generate embedding for the given text.');
        }

        return $embeddings['embedding'];
    }
}
