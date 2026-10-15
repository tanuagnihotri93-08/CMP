<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Performance extends Model
{
    protected $fillable = [

        'user_id',

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


    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}