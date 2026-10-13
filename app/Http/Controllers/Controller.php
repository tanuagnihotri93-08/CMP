<?php

namespace App\Http\Controllers;

use Illuminate\Database\QueryException;

abstract class Controller
{
    protected function isEmailUniqueViolation(QueryException $exception, string $table): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $message = strtolower($exception->getMessage());

        return in_array($sqlState, ['23000', '23505'], true)
            && (
                str_contains($message, "{$table}_email_unique")
                || str_contains($message, "{$table}.email")
            );
    }
}
