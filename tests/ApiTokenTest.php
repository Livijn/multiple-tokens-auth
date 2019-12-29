<?php
namespace Livijn\MultipleTokensAuth\Test;

use Livijn\MultipleTokensAuth\Models\ApiToken;

class ApiTokenTest extends TestCase
{
    /** @test It can determine if it has expired */
    public function it_can_determine_if_it_has_expired()
    {
        $token = factory(ApiToken::class)->create();

        $this->assertFalse($token->hasExpired());

        $token->update([
            'expired_at' => now()->subDay(),
        ]);

        $this->assertTrue($token->hasExpired());
    }

    /** @test It can determine if it should extend its life */
    public function it_can_determine_if_it_should_extend_its_life()
    {
        $token = factory(ApiToken::class)->create([
            'expired_at' => now()->addDays(config('multiple-tokens-auth.token.extend_life_at') + 1),
        ]);

        $this->assertFalse($token->shouldExtendLife());

        $token->update([
            'expired_at' => now()->addDays(config('multiple-tokens-auth.token.extend_life_at') - 1),
        ]);

        $this->assertTrue($token->shouldExtendLife());

        $token->update([
            'expired_at' => now()->subDay(),
        ]);

        $this->assertFalse($token->shouldExtendLife());
    }

    /** @test It can be scoped by whereHasExpired */
    public function it_can_be_scoped_by_whereHasExpired()
    {
        $token = factory(ApiToken::class)->create();

        $this->assertEquals(0, ApiToken::whereHasExpired()->count());

        $token->update([
            'expired_at' => now()->subDay(),
        ]);

        $this->assertEquals(1, ApiToken::whereHasExpired()->count());
    }

    /** @test It can be scoped by whereHasNotExpired */
    public function it_can_be_scoped_by_whereHasNotExpired()
    {
        $token = factory(ApiToken::class)->create();

        $this->assertEquals(1, ApiToken::whereHasNotExpired()->count());

        $token->update([
            'expired_at' => now()->subDay(),
        ]);

        $this->assertEquals(0, ApiToken::whereHasNotExpired()->count());
    }
}
