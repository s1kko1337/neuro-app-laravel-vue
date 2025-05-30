<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\SurveyGroup;
use App\Models\UserMatch;
use App\Services\MatchService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use App\Models\UploadedFile;

class MatchController extends Controller
{
    public function __construct(
        private MatchService $matchService,
    )
    {
    }
    public function match(Request $request)
    {
        $this->matchService->gentrateMatch();
    }

    public function groups(Request $request)
    {
        $groups = UserMatch::query()->where('user_id', Auth::user()->id)->get()->map(
            function (UserMatch $userMatch) {
                return [
                    "id" => $userMatch->id,
                    "user_id" => $userMatch->user_id,
                    "survey_group_id" => $userMatch->survey_group_id,
                    "name" => SurveyGroup::where('id',$userMatch->survey_group_id)->first()->name,
                    "parameters" => SurveyGroup::where('id',$userMatch->survey_group_id)->first()->parameters,
                ];
            }
        )->toArray();

        return response()->json([
            'success' => true,
            'data' => $groups
        ]);
    }
}
