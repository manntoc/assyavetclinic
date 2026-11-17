<?php

namespace App\AI;

use App\AI\Contracts\AIToolInterface;
use App\Models\AIToolLog;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class AIToolExecutor
{
    protected AIPermissionManager $permissionManager;

    public function __construct(AIPermissionManager $permissionManager)
    {
        $this->permissionManager = $permissionManager;
    }

    /**
     * Safely execute an AI Tool and log audit trail to database.
     */
    public function execute(AIToolInterface $tool, array $parameters, ?User $user, ?int $conversationId = null): array
    {
        $startTime = microtime(true);
        $toolName = $tool->getName();
        $userId = $user ? $user->id : null;

        // 1. Otorisasi Permission Check via Laravel Backend
        if (!$this->permissionManager->authorize($tool, $user)) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            AIToolLog::create([
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'tool_name' => $toolName,
                'parameters' => $parameters,
                'result' => ['error' => 'Permission Denied'],
                'status' => 'denied',
                'execution_time' => $executionTime
            ]);

            return [
                'status' => 'error',
                'error_code' => 'PERMISSION_DENIED',
                'message' => 'Anda tidak memiliki izin untuk menggunakan fitur ini.'
            ];
        }

        // 2. Eksekusi Business Logic Tool
        try {
            // Guarantee User is passed if required
            $result = $tool->execute($parameters, $user ?? new User());
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            AIToolLog::create([
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'tool_name' => $toolName,
                'parameters' => $parameters,
                'result' => $result,
                'status' => 'success',
                'execution_time' => $executionTime
            ]);

            return [
                'status' => 'success',
                'data' => $result
            ];

        } catch (Exception $e) {
            $executionTime = round((microtime(true) - $startTime) * 1000, 2);

            Log::error("AI Tool Execution Failed [{$toolName}]: " . $e->getMessage(), [
                'parameters' => $parameters,
                'trace' => $e->getTraceAsString()
            ]);

            AIToolLog::create([
                'conversation_id' => $conversationId,
                'user_id' => $userId,
                'tool_name' => $toolName,
                'parameters' => $parameters,
                'result' => ['error' => $e->getMessage()],
                'status' => 'failed',
                'execution_time' => $executionTime
            ]);

            return [
                'status' => 'error',
                'error_code' => 'EXECUTION_FAILED',
                'message' => 'Terjadi kendala teknis saat memproses permintaan data klinik.'
            ];
        }
    }
}
