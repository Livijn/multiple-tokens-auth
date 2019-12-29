<?php
namespace Livijn\MultipleTokensAuth\Test;

use Livijn\MultipleTokensAuth\MultipleTokensAuthServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        config()->set('auth.guards.api.driver', 'multiple-tokens');
        config()->set('auth.providers.users.model', User::class);

        $this->loadLaravelMigrations();
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->artisan('migrate');

        $this->withFactories(__DIR__ . '/../database/factories');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [
            MultipleTokensAuthServiceProvider::class,
        ];
    }
}
