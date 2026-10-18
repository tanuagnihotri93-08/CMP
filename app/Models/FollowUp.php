<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FollowUp extends Model
{
    protected $fillable = [
        'lead_id',
        'date',
        'reminder_sent_at',
        'type',
        'remark',
        'status',
        'assigned_to_user_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
