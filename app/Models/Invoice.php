<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_order_id',
        'invoice_number',
        'customer_id',
        'amount',
        'status',
        'quickbooks_id',
        'issued_at',
        'due_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'due_at' => 'datetime',
    ];

    // Relationship with WorkOrder
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}