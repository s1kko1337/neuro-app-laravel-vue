<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Services\OllamaService;
use Illuminate\Http\Request;
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

    public function send(Request $request)
    {
        $request->validate([
            'messages' => 'required|array',
        ]);

        return $this->ollamaService->chat($request);
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
}
