<?php

namespace Anil\Comments\Tests;

use Anil\Comments\ServiceProvider;
use Anil\Comments\Tests\TestSetup\Models\UserModel;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    // use DatabaseMigrations;
    use RefreshDatabase;

    /**
     * @throws BindingResolutionException
     */
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            function (string $modelName): string {
                return 'Anil\\Comments\Tests\\TestSetup\\Factories\\'.class_basename($modelName).'Factory';
            }
        );
        /** @var Application $app */
        $app = $this->app;
        $app['config']->set('auth.guards.web', [
            'driver' => 'session',
            'provider' => 'users',
        ]);

        $app['config']->set('auth.providers.users', [
            'driver' => 'eloquent',
            'model' => UserModel::class,
        ]);

        $app['config']->set('auth.defaults.guard', 'web');
        $this->setUpDatabase();
    }

    protected function setUpDatabase(): void
    {
        $schema = $this->app['db']->connection()->getSchemaBuilder();
        if (! $schema->hasTable('users')) {
            $this->userMigration();
        }
        if (! $schema->hasTable('tags')) {
            $this->tagMigration();
        }
        if (! $schema->hasTable('posts')) {
            $this->postMigration();
        }
    }

    protected function userMigration(): void
    {

        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('users', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->string(column: 'email');
                $table->string(column: 'password');
                $table->timestamps();
            });
    }

    protected function tagMigration(): void
    {

        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('tags', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->timestamps();
            });
    }

    protected function postMigration(): void
    {
        /** @var Application $app */
        $app = $this->app;
        $app['db']->connection()
            ->getSchemaBuilder()
            ->create('posts', function (Blueprint $table) {
                $table->id();
                $table->string(column: 'name');
                $table->timestamps();
            });
    }

    protected function getPackageProviders($app): array
    {
        return [
            ServiceProvider::class,
        ];
    }
}
