<?php

namespace Spatie\Enum\Laravel\Tests;

use Illuminate\Http\Request;
use Illuminate\Routing\Route;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\Enum\Laravel\EnumServiceProvider;
use Spatie\Enum\Laravel\Tests\Extra\InvalidNullablePost;
use Spatie\Enum\Laravel\Tests\Extra\Post;

abstract class TestCase extends OrchestraTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
    }

    protected function setUpDatabase()
    {
        Post::migrate();
        InvalidNullablePost::migrate();
    }

    protected function getPackageProviders($app)
    {
        return [
            EnumServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');

        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    public function getStub(string $file): string
    {
        return file_get_contents(__DIR__.'/stubs/'.$file);
    }

    protected function createRequest(
        array $query = [],
        string $method = Request::METHOD_GET,
        array $request = []
    ): Request {
        $uri = 'http://localhost/en/test';
        $server = [
            'REQUEST_URI' => $uri,
            'CONTENT_TYPE' => 'application/json',
        ];

        $request = new Request(
            $query,
            $request,
            [],
            [],
            [],
            $server,
            json_encode($request)
        );
        $request->setMethod($method);

        $route = (new Route($method, '{locale}/test', [
            'uses' => 'Controller@index',
        ]))->bind($request);
        $request->setRouteResolver(function () use ($route) {
            return $route;
        });

        return $request;
    }
}
