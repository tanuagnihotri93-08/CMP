<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $fillable = [

        'project_id',
        'assigned_to',
        'created_by',

        'title',
        'description',

        'priority',
        'status',

        'due_date',

    ];


    public function project()
    {
        return $this->belongsTo(Project::class);
    }


    public function assignedUser()
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }


    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
}