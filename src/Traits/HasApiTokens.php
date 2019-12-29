<?php
namespace Livijn\MultipleTokensAuth\Traits;

use Illuminate\Support\Str;
use Livijn\MultipleTokensAuth\Models\ApiToken;

trait HasApiTokens
{
    public function apiTokens()
    {
        return $this->hasMany(ApiToken::class);
    }

    public function generateApiToken()
    {
        $useHash = config('auth.guards.api.hash', false);
        $unique = false;
        $token = null;
        $hashedToken = null;

        while (! $unique) {
            $token = Str::random(80);
            $hashedToken = $useHash
                ? hash('sha256', $token)
                : $token;

            $unique = ApiToken::where('token', $hashedToken)->exists() == false;
        }

        ApiToken::create([
            'user_id' => $this->id,
            'token' => $hashedToken,
        ]);

        return $token;
    }

    public function purgeApiTokens()
    {
        $this->apiTokens()->delete();
    }
}
