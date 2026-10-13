<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use LogicException;

class AuditLog extends Model
{
    protected $guarded = ['*'];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit logs are append-only.'));
        static::deleting(fn () => throw new LogicException('Audit logs are append-only.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(
        string $action,
        string $module,
        string $recordType,
        ?int $recordId,
        string $description,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $userName = null,
        ?int $userId = null
    ): self {
        $actor = Auth::user();
        $log = new self;
        $log->forceFill([
            'user_id' => $userId ?? $actor?->getAuthIdentifier(),
            'user_name' => $userName ?? $actor?->name ?? 'Unauthenticated',
            'action' => $action,
            'module' => $module,
            'record_type' => $recordType,
            'record_id' => $recordId,
            'description' => $description,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => Request::ip(),
        ]);
        $log->save();

        return $log;
    }
}
