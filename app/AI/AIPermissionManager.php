<?php

namespace App\AI;

use App\AI\Contracts\AIToolInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class AIPermissionManager
{
    /**
     * Check if user is authorized to execute specified tool.
     */
    public function authorize(AIToolInterface $tool, ?User $user): bool
    {
        // Public/guest tools can be executed if tool allows null user
        if ($user === null) {
            // For safety, require auth for mutating operations
            if ($tool->requiresConfirmation()) {
                Log::warning("Unauthorized tool attempt (Guest user tried mutating tool: {$tool->getName()})");
                return false;
            }
            return true;
        }

        // Delegate to tool's native authorization method
        $isAuthorized = $tool->authorize($user);

        if (!$isAuthorized) {
            Log::warning("Tool Authorization Denied", [
                'user_id' => $user->id,
                'user_type' => $user->type ?? 'unknown',
                'tool_name' => $tool->getName()
            ]);
        }

        return $isAuthorized;
    }
}
