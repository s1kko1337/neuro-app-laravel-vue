<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Pgvector\Laravel\Vector;

class Embedding extends Model
{
    protected $table = 'embeddings';
    protected $fillable = ['message_id', 'embedding'];
    protected $casts = ['embedding' => Vector::class];

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }
}
