<?php

namespace App\AI;

use App\AI\Contracts\AIToolInterface;
use Exception;

class AIToolRegistry
{
    /** @var array<string, AIToolInterface> */
    protected array $tools = [];

    /**
     * Register a new AI Tool.
     */
    public function register(AIToolInterface $tool): void
    {
        $this->tools[$tool->getName()] = $tool;
    }

    /**
     * Get tool by name identifier.
     */
    public function getTool(string $name): ?AIToolInterface
    {
        return $this->tools[$name] ?? null;
    }

    /**
     * Get all registered tools.
     *
     * @return array<string, AIToolInterface>
     */
    public function getAllTools(): array
    {
        return $this->tools;
    }

    /**
     * Export all tools formatted as OpenAI/9Router function tools schemas.
     */
    public function exportFunctionSchemas(): array
    {
        $schemas = [];
        foreach ($this->tools as $tool) {
            $schemas[] = [
                'type' => 'function',
                'function' => [
                    'name' => $tool->getName(),
                    'description' => $tool->getDescription(),
                    'parameters' => $tool->getParameters(),
                ]
            ];
        }
        return $schemas;
    }
}
