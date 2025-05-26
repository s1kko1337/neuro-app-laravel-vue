<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken as SanctumPersonalAccessToken;

class CustomToken extends SanctumPersonalAccessToken
{
    public $table = 'personal_access_tokens';

    public function can($ability): bool
    {
        // if the user can't do it, our token can't either.
        if (!$this->tokenable->can($ability)) {
            return false;
        }

        // no wildcards. sorry Charlie.
        $abilities = collect($this->abilities)->filter(function ($ability) {
            return $ability !== '*';
        })->toArray();

        // we have explicit permissions passed to `createAccessToken`
        if (count($abilities) > 0) {
            return $this->canDb($abilities);
        }

        return true;

    }

    protected function canDb($ability): bool
    {
        return array_key_exists($ability, array_flip($this->abilities));
    }
}
