<?php
namespace Livijn\MultipleTokensAuth\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Livijn\MultipleTokensAuth\Models\ApiToken;

class PurgeExpiredApiTokensJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        ApiToken::whereHasExpired()->delete();
    }
}
