<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class UserSurveyChatMessages extends Model
{
    protected $table = "user_survey_chat_messages";

    protected $fillable = [
        "user_id",
        "is_bot",
        "is_final",
        "content",
        "audio_path",
    ];

    protected $casts = [
        'is_bot' => 'boolean',
        'is_final' => 'boolean',
    ];

    protected $appends = ['audio_url'];

    public function getAudioUrlAttribute()
    {
        return $this->audio_path
            ? Storage::disk('public')->url($this->audio_path)
            : null;
    }
}
