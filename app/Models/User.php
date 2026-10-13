<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;


    protected $fillable = [
        'name',
        'email',
        'password',
    ];


    protected $hidden = [
        'password',
        'remember_token',
    ];


    protected static function booted(): void
    {
        static::creating(function (User $user): void {

            if (!$user->role_id) {

                $user->role_id = Role::query()
                    ->where('name', 'employee')
                    ->value('id');

            }

        });
    }


    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }


    public function auditLogs(): HasMany
    {
        return $this->hasMany(
            AuditLog::class
        );
    }


    public function role(): BelongsTo
    {
        return $this->belongsTo(
            Role::class
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Task Relations
    |--------------------------------------------------------------------------
    */


    public function createdTasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'created_by'
        );
    }


    public function assignedTasks(): HasMany
    {
        return $this->hasMany(
            Task::class,
            'assigned_to'
        );
    }


    public function hasPermission(string $permission): bool
    {
        if (!$this->is_active) {
            return false;
        }


        $this->loadMissing(
            'role.permissions'
        );


        return $this->role?->permissions
            ->contains('name', $permission) ?? false;
    }


    public function canAccessAllRecords(): bool
    {
        return $this->hasPermission(
            'records.view_all'
        );
    }


    public function canAccessRecord(Model $record): bool
    {
        if ($this->canAccessAllRecords()) {

            return true;

        }


        return (int) $record->assigned_to_user_id
            === (int) $this->getKey();
    }


    public function constrainToAssignedRecords(
        Builder $query,
        string $column = 'assigned_to_user_id'
    ): Builder {

        if ($this->canAccessAllRecords()) {

            return $query;

        }


        return $query->where(
            $column,
            $this->getKey()
        );
    }
}