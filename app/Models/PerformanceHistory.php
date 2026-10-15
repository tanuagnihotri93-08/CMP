<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceHistory extends Model
{
    protected $fillable = [

        'user_id',
        'snapshot_date',
        'lead_score',
        'followup_score',
        'activity_score',
        'conversion_score',
        'total_score',
        'leads_count',
        'customers_count',
        'activities_count',
        'pending_followups_count',

    ];


    protected $casts = [

        'snapshot_date' => 'date',

    ];


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}