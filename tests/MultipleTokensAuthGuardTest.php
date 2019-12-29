<?php
namespace Livijn\MultipleTokensAuth\Test;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livijn\MultipleTokensAuth\MultipleTokensAuthGuard;
use Livijn\MultipleTokensAuth\Models\ApiToken;
use Illuminate\Support\Facades\Route;

class MultipleTokensAuthGuardTest extends TestCase
{
    private function createGuard($hash = false, string $token = null)
    {
        $request = $token
            ? new Request(['token' => $token])
            : new Request;

        return new MultipleTokensAuthGuard(
            $this->app['auth']->createUserProvider('users'),
            $request,
            $hash
        );
    }

    /** @test It can validate credentials without hash */
    public function it_can_validate_credentials_without_hash()
    {
        $token = factory(ApiToken::class)->create();
        $guard = $this->createGuard();

        $this->assertFalse($guard->validate(['token' => 'some-random-token']));
        $this->assertTrue($guard->validate(['token' => $token->token]));
    }

    /** @test It can validate credentials with hash */
    public function it_can_validate_credentials_with_hash()
    {
        $token = Str::random(64);
        factory(ApiToken::class)->create(['token' => hash('sha256', $token)]);
        $guard = $this->createGuard(true);

        $this->assertFalse($guard->validate(['token' => 'some-random-token']));
        $this->assertTrue($guard->validate(['token' => $token]));
    }

    /** @test It can get a user from a valid token */
    public function it_can_get_a_user_from_a_valid_token()
    {
        $user = factory(User::class)->create();
        $token = factory(ApiToken::class)->create(['user_id' => $user->id]);
        $guard = $this->createGuard(false, $token->token);

        $this->assertNotNull($guard->user());
        $this->assertTrue($user->is($guard->user()));
    }

    /** @test It returns 401 if the token is invalid */
    public function it_returns_401_if_the_token_is_invalid()
    {
        Route::get('multiple-tokens-auth/test-invalid-token', function () {
            return true;
        })->middleware('auth:api');

        $request = $this->getJson('multiple-tokens-auth/test-invalid-token');
        $request->assertStatus(401);

        $request = $this->getJson('multiple-tokens-auth/test-invalid-token', ['Authorization' => 'Bearer abc123']);
        $request->assertStatus(401);
    }

    /** @test It can get the user from a request */
    public function it_can_get_the_user_from_a_request()
    {
        Route::get('multiple-tokens-auth/test-guard', function () {
            return ['user' => auth()->user()];
        })->middleware('auth:api');

        $user = factory(User::class)->create();
        $token = $user->generateApiToken();

        $request = $this->getJson('multiple-tokens-auth/test-guard', ['Authorization' => 'Bearer ' . $token]);
        $request->assertJson(['user' => [
            'id' => $user->id,
        ]]);
    }

    /** @test It can logout without hash */
    public function it_can_logout_without_hash()
    {
        Route::get('multiple-tokens-auth/test-logout', function () {
            auth()->logout();
            return null;
        })->middleware('auth:api');

        factory(ApiToken::class)->create();
        $user = factory(User::class)->create();
        $tokenOne = $user->generateApiToken();
        $tokenTwo = $user->generateApiToken();

        $this->assertEquals(3, ApiToken::count());
        $this->assertEquals(2, $user->apiTokens()->count());

        $request = $this->getJson('multiple-tokens-auth/test-logout', ['Authorization' => 'Bearer ' . $tokenTwo]);
        $request->assertSuccessful();

        $this->assertEquals(2, ApiToken::count());
        $this->assertEquals(1, $user->apiTokens()->count());
        $this->assertEquals($tokenOne, $user->apiTokens()->first()->token);

        $request = $this->getJson('multiple-tokens-auth/test-logout', ['Authorization' => 'Bearer ' . $tokenTwo]);
        $request->assertUnauthorized();
    }

    /** @test It can logout with hash */
    public function it_can_logout_with_hash()
    {
        config()->set('auth.guards.api.hash', true);

        Route::get('multiple-tokens-auth/test-logout', function () {
            auth()->logout();
            return null;
        })->middleware('auth:api');

        $user = factory(User::class)->create();
        $token = $user->generateApiToken();

        $this->assertEquals(1, ApiToken::count());
        $this->assertEquals(1, $user->apiTokens()->count());

        $request = $this->getJson('multiple-tokens-auth/test-logout', ['Authorization' => 'Bearer ' . $token]);
        $request->assertSuccessful();

        $this->assertEquals(0, ApiToken::count());
        $this->assertEquals(0, $user->apiTokens()->count());
    }

    /** @test Logging out without a token doesnt delete any token */
    public function logging_out_without_a_token_doesnt_delete_any_token()
    {
        $user = factory(User::class)->create();
        $user->generateApiToken();

        $this->assertEquals(1, ApiToken::count());

        auth()->guard('api')->logout();

        $this->assertEquals(1, ApiToken::count());
    }
}
