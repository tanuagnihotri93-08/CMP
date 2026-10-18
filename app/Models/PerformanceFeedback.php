<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformanceFeedback extends Model
{
    protected $table = 'performance_feedbacks';

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'performance_history_id',
        'message',
        'rating',
        'status',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function performanceHistory()
    {
        return $this->belongsTo(PerformanceHistory::class);
    }
}
