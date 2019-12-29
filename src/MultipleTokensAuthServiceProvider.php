<?php
namespace Livijn\MultipleTokensAuth;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class MultipleTokensAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');

        Auth::extend('multiple-tokens', function ($app, $name, array $config) {
            return new MultipleTokensAuthGuard(
                $app['auth']->createUserProvider('users'),
                $app['request'],
                $config['hash'] ?? false
            );
        });
    }
}
