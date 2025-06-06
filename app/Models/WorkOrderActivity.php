<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\WorkOrder;
use App\Models\User;

class WorkOrderActivity extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'user_id',
        'field_name',
        'old_value',
        'new_value',
        'action_type',
        'description'
    ];

    /**
     * Get the work order that owns this activity
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    /**
     * Get the user who performed this activity
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
