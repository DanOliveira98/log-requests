<?php

namespace Samfelgar\LogRequests\Providers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Samfelgar\LogRequests\Http\Middleware\LogRequest;

class LogRequestServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/log-requests.php', 'log-requests');
    }

    public function boot(): void
    {
        $this->publishConfig();

        $this->registerMiddleware();
    }

    private function publishConfig(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../../config/log-requests.php' => $this->app->configPath('log-requests.php'),
        ]);
    }

    private function registerMiddleware(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('log-requests', LogRequest::class);
    }
}
