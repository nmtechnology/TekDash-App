<?php
// filepath: /Users/nmtechnology/Herd/nmtis-dash/app/Models/Note.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'text',
        'user_id',
        'work_order_id'
    ];

    /**
     * Get the work order that owns the note.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the user that owns the note.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}