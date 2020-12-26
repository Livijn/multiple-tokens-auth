<?php
namespace Livijn\MultipleTokensAuth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class MultipleTokensAuthServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/multiple-tokens-auth.php' => config_path('multiple-tokens-auth.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations')
        ], 'migrations');

        Auth::extend('multiple-tokens', function ($app, $name, array $config) {
            return new MultipleTokensGuard(
                Auth::createUserProvider($config['provider']),
                $app['request'],
                config()->get('multiple-tokens-auth.hash') ?? $config['hash'] ?? false
            );
        });
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/multiple-tokens-auth.php',
            'multiple-tokens-auth'
        );
    }
}
