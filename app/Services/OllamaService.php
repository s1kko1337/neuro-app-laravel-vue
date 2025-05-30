<?php

namespace App\Services;
use App\Models\Chat;
use Cloudstudio\Ollama\Facades\Ollama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Message;
use App\Models\Embedding;

class OllamaService
{
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
        $temperature = $request->input('temperature');
        $chatId = $request->input('chatId');
        $chat = Chat::findOrFail($chatId);
        $response = 0;

        $lastUserMessage = $this->getLastUserMessage($messages);
        $lastUserMessageText = $lastUserMessage['content'];

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

        if (preg_match('/^deepseek-r1/', $model)) {
            $systemMessage = [
                'role' => 'system',
                'content' => "Всегда отвечай на русском языке, вот необходимый контекст для твоего ответа. Контекст:\n" . $context .".\n. Если ты не умеешь думать на русском языке, думай на другом языке но отвечай на русском.Свой ответ давай на русском языке! Свой ответ переведи на русский."
            ];

            array_unshift($messages, $systemMessage);

            Log::info('Request data:', [
                'messages' => $messages,
                'model' => $model,
                'chatId' => $chatId,
                'temperature' => $temperature
            ]);

            $response = Ollama::agent('Ты отвечаешь только по-русски! Если вопрос задан не на русском языке, все равно отвечай на русском!')
                ->model($model)
                ->chat($messages);

            Log::info('Ollama response:', $response);
        } else {
            $helperMessage = Ollama::agent('Ты эксперт в русском языке.')
            ->prompt("Всегда отвечай на русском языке, вот необходимый контекст для твоего ответа. Вопрос: $lastUserMessageText \n
            Контекст:\n" . $context .".\n Задай 5 вопросов на русском языке, уточняющих вопрос пользователя и
            учитывающих переданный контекст, на базе этих вопросов строй свой ответ.
            В ответ давай только уточняющие вопросы на русском языке!")
            ->model($model)
            ->stream(false)
            ->ask();

            $helperResponse =  $helperMessage['response'];

            $systemMessage = [
                'role' => 'system',
                'content' => "Всегда отвечай на русском языке.
                На базе этих вопросов и уточнений:(\n" . $helperResponse ."),\n составь свой точный ответ на заданный вопрос пользователя, в формате связанного текста, а не прямых ответов на вопросы. Вопрос пользователя: $lastUserMessageText.
                Свой ответ давай на русском языке! Свой ответ переведи на русский."
            ];

            array_unshift($messages, $systemMessage);

            Log::info('Request data:', [
                'messages' => $messages,
                'model' => $model,
                'chatId' => $chatId
            ]);

            $response = Ollama::agent('На базе вопросов подсказок и контекста ты отвечаешь на вопрос пользователя на русском языке!')
                ->model($model)
                ->options(['temperature' => $temperature])
                ->chat($messages);

            Log::info('Ollama response:', $response);
        }


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

    /**Запрос к LLM для опроса пользователя
     *
     */
    public function buildPrompt(array $chatHistory, string $newMessage): string
    {
        $systemPrompt = <<<PROMPT
            You are conducting a friendly survey to learn about a person's hobbies and interests.
            Your goal is to identify at least 3 main hobbies or interests of the user.
            Ask questions one at a time and analyze the responses.
            When you have enough information, summarize the hobbies and mark the conversation as complete.

            Rules:
            1. Be friendly and conversational
            2. Ask open-ended questions
            3. Don't ask more than 5 questions total
            4. When you have identified 3 hobbies or when the user seems disinterested, end the conversation
            5. In your final message, summarize the hobbies and add "IS_FINAL: true"

            Current conversation:
        PROMPT;

        $conversation = '';
        foreach ($chatHistory as $message) {
            $conversation .= "{$message['role']}: {$message['content']}\n";
        }

        return $systemPrompt . $conversation . "user: {$newMessage}\nassistant:";
    }

    /**Ведение опроса пользователя
     *
     */
    public function callOllama(string $prompt, string $model): array
    {
        $client = new \GuzzleHttp\Client();
        $pythonHost = config("services.python_api.host");
        $pythonPort = config("services.python_api.port");

        dd("Определить маршрут для чата без chatId");

        $url = "http://{$pythonHost}:{$pythonPort}/chat";
        $response = Http::post($url, [
            'json' => [
                'model' => $model,
                'messages' => [
                    ['role' => 'system', 'content' => $prompt]
                ],
                'temperature' => 0.7
            ]
        ]);

        $responseData = json_decode($response->getBody(), true);

        // Парсим ответ на предмет флага завершения
        $isFinal = str_contains($responseData['message']['content'], 'IS_FINAL: true');
        $content = str_replace('IS_FINAL: true', '', $responseData['message']['content']);

        return [
            'message' => ['content' => trim($content)],
            'is_final' => $isFinal
        ];
    }

    public function generateParameters(string $chatHistory, string $model)
    {
        dd ("Определить маршрут для генерации параметров пользователя: post /user-params");
        $url = "http://{$pythonHost}:{$pythonPort}/user-params";
        $response = Http::post($url, [
            'model' => $model,
            'messages' => $chatHistory,
            'temperature' => 0.7
        ]);
    }
}
