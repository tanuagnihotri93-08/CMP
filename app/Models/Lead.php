<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'phone',
        'source',
        'status',
        'notes',
        'assigned_to_user_id',
    ];

    public function customer()
    {
        return $this->hasOne(Customer::class);
    }

    public function followUps()
    {
        return $this->hasMany(FollowUp::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to_user_id');
    }
}
