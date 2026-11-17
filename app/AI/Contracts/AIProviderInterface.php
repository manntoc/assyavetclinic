<?php

namespace App\AI\Contracts;

interface AIProviderInterface
{
    /**
     * Check provider health/connectivity.
     */
    public function checkHealth(): bool;

    /**
     * Send chat completion with support for tool/function calling schemas.
     *
     * @param array $messages
     * @param array $tools
     * @param array $options
     * @return array
     */
    public function chatCompletion(array $messages, array $tools = [], array $options = []): array;
}
