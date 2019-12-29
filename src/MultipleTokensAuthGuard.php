<?php
namespace Livijn\MultipleTokensAuth;

use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Http\Request;
use Livijn\MultipleTokensAuth\Models\ApiToken;

class MultipleTokensAuthGuard implements Guard
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

        $user = null;

        $token = $this->getTokenForRequest();

        try {
            $apiToken = ApiToken::where('token', $token)->firstOrFail();

            $user = $this->provider->retrieveById($apiToken->user_id);
        } catch (\Exception $e) {
            $user = null;
        }

        return $this->user = $user;
    }

    public function validate(array $credentials = [])
    {
        return ApiToken::where('token', $this->token($credentials))->exists();
    }

    public function logout()
    {
        if ($this->guest() || ! $token = $this->getTokenForRequest()) {
            return;
        }

        ApiToken::where('token', $token)->delete();
        $this->user = null;
    }

    private function getTokenForRequest()
    {
        $token = $this->request->query('token');

        if (empty($token)) {
            $token = $this->request->input('token');
        }

        if (empty($token)) {
            $token = $this->request->bearerToken();
        }

        if (empty($token)) {
            $token = $this->request->getPassword();
        }

        return $token;
    }

    private function token($token)
    {
        $token = is_array($token) ? $token['token'] : $token;

        return $this->hash ? hash('sha256', $token) : $token;
    }
}
