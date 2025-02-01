<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $table = 'messages';
    protected $fillable = ['chat_id', 'role', 'content'];

    public function embedding(): HasMany
    {
        return $this->hasMany(Embedding::class);
    }

    public function chat(): BelongsTo
    {
        return $this->belongsTo(Chat::class);
    }
}
