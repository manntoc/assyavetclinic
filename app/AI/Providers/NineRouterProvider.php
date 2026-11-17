<?php

namespace App\AI\Providers;

use App\AI\Contracts\AIProviderInterface;
use App\Services\NineRouterService;
use Exception;

class NineRouterProvider implements AIProviderInterface
{
    protected NineRouterService $nineRouter;

    public function __construct(NineRouterService $nineRouter)
    {
        $this->nineRouter = $nineRouter;
    }

    public function checkHealth(): bool
    {
        return $this->nineRouter->checkHealth();
    }

    public function chatCompletion(array $messages, array $tools = [], array $options = []): array
    {
        $model = $options['model'] ?? config('ai.default_model', 'gpt-4o');
        $payloadOptions = array_merge([
            'temperature' => 0.2,
        ], $options);

        if (!empty($tools)) {
            $payloadOptions['tools'] = $tools;
        }

        return $this->nineRouter->chatCompletion($messages, $model, $payloadOptions);
    }
}
