<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class AudioMessage extends Model
{
    protected $table = "audio_messages";

    protected $fillable = [
        "message_id",
        "audio_path",
    ];

    protected $appends = ['audio_url'];

    public function getAudioUrlAttribute()
    {
        return $this->audio_path
            ? Storage::disk('public')->url($this->audio_path)
            : null;
    }
}
