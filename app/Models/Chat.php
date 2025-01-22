<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $table = 'chats';
    protected $fillable = [''];

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
