<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserMatch extends Model
{
    protected $fillable = ['user_id', 'survey_group_id'];

    public function group(): BelongsTo
    {
        return $this->belongsTo(SurveyGroup::class, 'group_id','id');
    }
}
