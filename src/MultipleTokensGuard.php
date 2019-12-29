<?php
namespace Livijn\MultipleTokensAuth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Livijn\MultipleTokensAuth\Models\ApiToken;

class MultipleTokensGuard implements Guard
{
    use GuardHelpers;

    /**
     * The request instance.
     *
     * @var \Illuminate\Http\Request
     */
    protected $request;

    /**
     * Indicates if the API token is hashed in storage.
     *
     * @var bool
     */
    protected $hash = false;

    public function __construct(UserProvider $provider, Request $request, $hash = false)
    {
        $this->hash = $hash;
        $this->request = $request;
        $this->provider = $provider;
    }

    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $token = $this->getTokenForRequest();

        $apiToken = ApiToken::where('token', $this->hashedToken($token))
            ->whereHasNotExpired()
            ->first();

        if (is_null($apiToken)) {
            return $this->user = null;
        }

        if ($apiToken->shouldExtendLife()) {
            $apiToken->update([
                'expired_at' => now()->addDays(config('multiple-tokens-auth.token.life_length')),
            ]);
        }

        return $this->user = $this->provider->retrieveById($apiToken->user_id);
    }

    public function validate(array $credentials = [])
    {
        return ApiToken::where('token', $this->hashedToken($credentials))
            ->whereHasNotExpired()
            ->exists();
    }

    public function logout()
    {
        if ($this->guest() || ! $token = $this->getTokenForRequest()) {
            return;
        }

        ApiToken::where('token', $this->hashedToken($token))->delete();
        $this->user = null;
    }

    private function getTokenForRequest()
    {
        $token = $this->request->query('api_token');

        if (empty($token)) {
            $token = $this->request->input('api_token');
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        if (empty($token)) {
            $token = $this->request->getPassword();
        }

        return $token;
    }

    private function hashedToken($token)
    {
        $token = is_array($token) ? $token['token'] : $token;

        return $this->hash ? hash('sha256', $token) : $token;
    }
}
