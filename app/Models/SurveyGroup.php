<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SurveyGroup extends Model
{
    protected $fillable = ['name','parameters'];

    public function userMatches(): HasMany
    {
        return $this->hasMany(UserMatch::class, 'group_id', 'id');
    }
}
