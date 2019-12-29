<?php
namespace Livijn\MultipleTokensAuth\Test;

use Livijn\MultipleTokensAuth\Jobs\PurgeExpiredApiTokensJob;
use Livijn\MultipleTokensAuth\Models\ApiToken;

class PurgeExpiredApiTokensJobTest extends TestCase
{
    /** @test It purges expired tokens */
    public function it_purges_expired_tokens()
    {
        factory(ApiToken::class)->create(['expired_at' => now()->subDay()]);
        $token = factory(ApiToken::class)->create(['expired_at' => now()->addDay()]);

        $this->assertEquals(2, ApiToken::count());

        (new PurgeExpiredApiTokensJob())->handle();

        $this->assertEquals(1, ApiToken::count());
        $this->assertTrue(ApiToken::first()->is($token));
    }
}
