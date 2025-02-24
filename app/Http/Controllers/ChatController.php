<?php

namespace App\Http\Controllers;
use App\Models\Chat;
use App\Models\Message;
use App\Services\OllamaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
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


    /**
     * Отправляет сообщения в чат.
     *
     * Этот метод принимает запрос с массивом сообщений и идентификатором чата,
     * проверяет существование чата, извлекает последнее сообщение от пользователя
     * и отправляет его на внешний сервис.
     *
     * @param Request $request Входящий HTTP-запрос.
     *
     * @return JsonResponse Ответ от внешнего сервиса в формате JSON.
     *
     * @throws ValidationException Если валидация запроса не проходит.
     * @throws ModelNotFoundException Если чат с указанным идентификатором не найден.
     */
    public function send_message(Request $request)
    {
        // Валидация входящих данных
        $validatedData = $request->validate([
            'messages' => 'required|array',
            'chatId' => 'required|integer', // Добавляем валидацию для chatId
            'model' => 'required|string', // Добавляем валидацию для model
        ]);

        $chatId = $validatedData['chatId'];

        // Попытка найти чат по идентификатору
        try {
            $chat = Chat::findOrFail($chatId);
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Chat not found.'], 404);
        }

        // Получение последнего сообщения от пользователя
        $lastUserMessage = $this->getLastUserMessage($validatedData['messages']);
        \Log::info('Last User Message:', ['message' => $lastUserMessage]); // Логируем последнее сообщение

        // Добавляем поле model к последнему сообщению
        if (!empty($validatedData['messages'])) {
            $lastIndex = count($validatedData['messages']) - 1;
            $validatedData['messages'][$lastIndex]['model'] = $request->model; // Добавляем поле model
        }

        // Отправка POST-запроса на FastAPI
        $response = Http::post("http://python:8000/chats/{$chatId}/messages", [
            'messages' => $validatedData['messages'], // Отправляем обновленный массив messages
            'system_prompt' => "
                Always answer in Russian.
                Make sure your answer is as accurate and complete as possible.
                Use the provided context to improve the quality of your answer.
                If the question is asked in another language, translate it into Russian before answering.
                "
        ]);
        // Логируем ответ от FastAPI
        \Log::info('Response from FastAPI:', ['response' => $response->json()]); // Передаем массив в качестве контекста
        // Возврат ответа от FastAPI
        return $response;
    }

    public function getChats()
    {
        $chats = Chat::with('messages')->get();
        return response()->json($chats);
    }

    public function addChat()
    {
        $chat = Chat::create();
        return response()->json($chat);
    }

    public function getChatMessages($chatId)
    {
        $messages = Message::where('chat_id', $chatId)->get();
        return response()->json($messages);
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
