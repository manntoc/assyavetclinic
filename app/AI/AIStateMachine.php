<?php

namespace App\AI;

class AIStateMachine
{
    public const IDLE                 = 'IDLE';
    public const UNDERSTANDING        = 'UNDERSTANDING';
    public const COLLECTING_DATA      = 'COLLECTING_DATA';
    public const VALIDATING           = 'VALIDATING';
    public const WAITING_CONFIRMATION = 'WAITING_CONFIRMATION';
    public const EXECUTING            = 'EXECUTING';
    public const COMPLETED            = 'COMPLETED';
    public const FAILED               = 'FAILED';

    public static function isValidState(string $state): bool
    {
        return in_array($state, [
            self::IDLE,
            self::UNDERSTANDING,
            self::COLLECTING_DATA,
            self::VALIDATING,
            self::WAITING_CONFIRMATION,
            self::EXECUTING,
            self::COMPLETED,
            self::FAILED,
        ]);
    }
}
