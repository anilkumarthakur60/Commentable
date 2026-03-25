<?php

namespace Anil\Comments\Tests;

use Anil\Comments\ServiceProvider;
use Anil\Comments\Tests\Support\Models\UserModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName): string => 'Anil\\Comments\\Tests\\Support\\Factories\\'.class_basename($modelName).'Factory'
        );

        /** @var Application $app */
        $app = $this->app;

        $app['config']->set('auth.guards.web', [
            'driver'   => 'session',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model'  => UserModel::class,
        ]);

        $app['config']->set('auth.defaults.guard', 'web');

        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();

        if (!$schema->hasTable('users')) {
            $this->createUsersTable();
        }

        if (!$schema->hasTable('posts')) {
            $this->createPostsTable();
        }
    }

    protected function createUsersTable(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->string('password');
                $table->boolean('is_admin')->default(false);
                $table->timestamps();
            });
    }

    protected function createPostsTable(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('posts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
            });
    }

    /**
     * @param Application $app
     *
     * @return list<class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
