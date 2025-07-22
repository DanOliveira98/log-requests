<?php

namespace Samfelgar\LogRequests\Http\Middleware;

use Closure;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LogRequest
{
    private Config $config;

    public function __construct(Config $config)
    {
        $this->config = $config;
    }

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        Log::channel($this->channel())
            ->debug(
                \sprintf("%s\n%s\n\n%s", $this->message(), (string)$request, (string)$response),
                $this->context($request),
            );

        return $response;
    }

    private function context(Request $request): array
    {
        $context = [
            'agent' => $request->header('User-Agent'),
            'ip' => $request->getClientIp(),
        ];

        $authenticatedUser = $request->user();

        if ($authenticatedUser !== null) {
            $context['user'] = $authenticatedUser->getKey();
        }

        return \array_filter($context, static fn(mixed $value): bool => $value !== null);
    }

    private function channel(): string
    {
        if (!$this->config->has('log-requests.log-channel')) {
            return $this->config->get('logging.default', 'stack');
        }

        return $this->config->get('log-requests.log-channel');
    }

    private function message(): string
    {
        return $this->config->get('log-requests.message', '');
    }
}
