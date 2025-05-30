<?php

namespace App\Services;

use App\Models\Chat;
use App\Models\Message;
use App\Models\SurveyGroup;
use App\Models\UserMatch;
use App\Models\UserParameters;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\Translation\t;

class MatchService
{
//if(!empty($userSurveyArray)){
//$response = $this->ollamaService->gentrateMatch($userSurveyArray);
//
//$jsonString = trim(str_replace(['```json', '```'], '', $response));
//$hobbies = json_decode($jsonString, true);
//
//\Log::info('Response from FastAPI:', ['response' => $response]);
//
//$user = Auth::user();
//$userSurvey = $user->parameters()->updateOrCreate([
//'user_id'=>$user->id,
//'parameters' =>implode(',',$hobbies)
//])->toArray();
//}
    public function gentrateMatch()
    {
        $chat = $this->getOrCreateMatchChat();
        $user = Auth::user();
        $userParams = UserParameters::where('user_id', $user->id)->first()->parameters;

        $userMessage = Message::create([
            'chat_id' => $chat->id,
            'role' => 'user',
            'content' => 'В соответствии с заданием.
             Подбери пользователю группы на основе его интересов. В ответе напиши group_id = [].
             ###ИНТЕРЕСЫ Пользователя###' . $userParams,
        ]);
        // Отправка POST-запроса на FastAPI
        $pythonHost = config('services.python_api.host');
        $pythonPort = config('services.python_api.port');

        $url = "http://{$pythonHost}:{$pythonPort}/chats/{$chat->id}/survey_messages";

        $surveyGroups = SurveyGroup::query()
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($group) {
                return [
                    'survey_group_id' => $group->id,
                    'parameters' => $group->parameters,
                ];
            })
            ->toArray();

        $response = Http::post($url, [
            'messages' => array($userMessage),
            'system_prompt' => "
                ### INSTRUCTIONS ###
        You are an analyzer of students' interests.
        Select groups for the user based on their interests and return ONLY a JSON array of group_id in descending order of relevance (most relevant first).

        LIST OF GROUPS:
        " . json_encode($surveyGroups, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE) . "

        ### STRICT RESPONSE RULES ###
        1. Output MUST be a pure JSON array (e.g., [3, 1, 5]).
        2. Do NOT include any explanations, Markdown, text, or keys like \"group_id\".
        3. Do NOT add system messages (e.g., \"Here are the groups:\").
        4. If no groups match, return an empty array ([]).
            ",
        ]);
        // Логируем ответ от FastAPI
        \Log::info('Response from FastAPI:', ['response' => $response->json()]);
        // Возврат ответа от FastAPI
        $groupsArr = json_decode($response['message'],true);

        $dataToDrop = UserMatch::all();
        foreach ($dataToDrop as $item) {
            $item->delete();
        }

        foreach ($groupsArr as $group) {
            UserMatch::create(
                [
                    'survey_group_id' => $group,
                    'user_id' =>$user->id
                ]
            );
        }

        return $response['message'];
    }

    private function getOrCreateMatchChat(): Chat|bool
    {
        if (!auth()->check()) {
            return false;
        }
        $userId = auth()->id();

        $systemChatCount = Chat::where('user_id', $userId)->where('is_system', true)->count();
        $systemChat = Chat::where('user_id', $userId)->where('is_system', true)->first();

        if ($systemChat && $systemChatCount < 3) {
            $chat = Chat::create([
                'user_id' => $userId,
                'is_system' => true,
            ]);
        }
        else {
            $chat = Chat::where('user_id', $userId)->where('is_system', true)->latest()->first();
        }
        return $chat;
    }
}
