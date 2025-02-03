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
    public function splitTextIntoChunks(string $text, int $chunkSize = 50): array
    {
        $words = preg_split('/\s+/', $text);

        $chunks = [];
        $currentChunk = '';
        foreach ($words as $word) {
            if (strlen($currentChunk . ' ' . $word) <= $chunkSize) {
                $currentChunk .= ($currentChunk ? ' ' : '') . $word;
            } else {
                $chunks[] = $currentChunk;
                $currentChunk = $word;
            }
        }
        if ($currentChunk) {
            $chunks[] = $currentChunk;
        }

        return $chunks;
    }

    public function chat(Request $request)
    {
        $messages = $request->input('messages');
        $model = $request->input('model');
        $chatId = $request->input('chatId');
        $chat = Chat::findOrFail($chatId);

        $lastUserMessage = $this->getLastUserMessage($messages);

        if (!$lastUserMessage) {
            return response()->json([
                'error' => 'No user message found',
            ], 400);
        }

        $userMessageChunks = $this->splitTextIntoChunks($lastUserMessage['content']);

        $userMessageEmbeddings = [];
        foreach ($userMessageChunks as $chunk) {
            $userMessageEmbeddings[] = $this->generateEmbedding($chunk);
        }

        $context = $this->getRelevantContext($chatId, $userMessageEmbeddings);
        Log::info('Context:', [
            json_encode($context)
        ]);

        $systemMessage = [
            'role' => 'system',
            'content' => "Context:\n" . $context
        ];

        array_unshift($messages, $systemMessage);

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

        $assistantMessageChunks = $this->splitTextIntoChunks($assistantMessage);

        $assistantMessageEmbeddings = [];
        foreach ($assistantMessageChunks as $chunk) {
            $assistantMessageEmbeddings[] = $this->generateEmbedding($chunk);
        }

        DB::transaction(function () use ($lastUserMessage, $assistantMessage, $model, $chatId, $userMessageEmbeddings, $assistantMessageEmbeddings, $systemMessage) {
            $chat = Chat::findOrFail($chatId);

            $lastUserMessageModel = $chat->messages()->create([
                'role' => $lastUserMessage['role'],
                'content' => $lastUserMessage['content'],
            ]);

            foreach ($userMessageEmbeddings as $embedding) {
                $lastUserMessageModel->embedding()->create([
                    'embedding' => $embedding,
                ]);
            }

            $systemMessageModel = $chat->messages()->create([
                'role' => $systemMessage['role'],
                'content' => $systemMessage['content'],
            ]);

            $assistantMessageModel = $chat->messages()->create([
                'role' => 'assistant',
                'content' => $assistantMessage,
            ]);

            foreach ($assistantMessageEmbeddings as $embedding) {
                $assistantMessageModel->embedding()->create([
                    'embedding' => $embedding,
                ]);
            }
        });

        return response()->json([
            'message' => $assistantMessage,
        ], 200);
    }

    public function generateEmbedding(string $text): array
    {
        try {
            $embeddings = Ollama::model('nomic-embed-text')
                ->embeddings($text);

            if (empty($embeddings['embedding'])) {
                throw new \RuntimeException('Empty embedding received');
            }

            return $embeddings['embedding'];
        } catch (\Exception $e) {
            Log::error('Embedding generation failed', [
                'error' => $e->getMessage(),
                'text' => substr($text, 0, 100)
            ]);
            throw new \RuntimeException('Failed to generate embedding: ' . $e->getMessage());
        }
    }

    private function getRelevantContext($chatId, array $embeddings, $limit = 25): string
    {
        try {
            $relevantMessages = collect();

            foreach ($embeddings as $embedding) {
                $results = Embedding::query()
                    ->select('messages.content')
                    ->join('messages', 'embeddings.message_id', '=', 'messages.id')
                    ->where('messages.chat_id', $chatId)
                    ->where('messages.role', 'system') // Ищем только системные сообщения (контекст)
                    ->orderByRaw('embeddings.embedding <=> ?', [json_encode($embedding)])
                    ->limit($limit)
                    ->get();

                $relevantMessages = $relevantMessages->merge($results);
            }

            $uniqueMessages = $relevantMessages->unique('content');

            Log::info('Relevant context found:', [
                'count' => $uniqueMessages->count(),
                'chat_id' => $chatId,
                'embedding_sample' => array_slice($embeddings[0], 0, 5) // Пример первого эмбеддинга для логов
            ]);

            return $uniqueMessages->pluck('content')->implode("\n\n");
        } catch (\Exception $e) {
            Log::error('Error getting relevant context:', [
                'error' => $e->getMessage(),
                'chat_id' => $chatId
            ]);
            return '';
        }
    }

    private function getLastUserMessage(array $messages): ?array
    {
        foreach (array_reverse($messages) as $message) {
            if ($message['role'] === 'user') {
                return $message;
            }
        }
        return null;
    }
}
