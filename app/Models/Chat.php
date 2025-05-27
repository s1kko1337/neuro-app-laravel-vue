<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $table = 'chats';
    protected $fillable = ['user_id'];

    public function user(): BelongsTo{
        return $this->belongsTo(User::class, 'user_id','id');
    }
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(UploadedFile::class , 'chat_id','id');
    }
}
