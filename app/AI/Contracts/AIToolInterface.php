<?php

namespace App\AI\Contracts;

use App\Models\User;

interface AIToolInterface
{
    /**
     * Get unique tool name identifier.
     */
    public function getName(): string;

    /**
     * Get tool description for LLM system prompt.
     */
    public function getDescription(): string;

    /**
     * Get parameter schema definition (JSON schema array).
     */
    public function getParameters(): array;

    /**
     * Check if authenticated user has permission to execute this tool.
     */
    public function authorize(User $user): bool;

    /**
     * Determine if tool mutates data and requires explicit confirmation.
     */
    public function requiresConfirmation(): bool;

    /**
     * Execute tool logic via Laravel services/models.
     *
     * @param array $parameters
     * @param User $user
     * @return array
     */
    public function execute(array $parameters, User $user): array;
}
