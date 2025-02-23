<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkOrder extends Model
{
    use HasFactory;

    // Define the table associated with the model
    protected $table = 'work_orders';

    // Define the attributes that are mass assignable
    protected $fillable = [
        'title',
        'description',
        'scheduled_at',
        'status',
        'user_id',
        'images',
        'notes',
        'price',
        'customer_id',
    ];

    // Define the attributes that should be cast to native types
    protected $casts = [
        'scheduled_at' => 'datetime',
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    // Define the possible statuses
    const STATUSES = [
        'Scheduled',
        'In Progress',
        'Part Needed',
        'Complete',
        'Cancelled',
    ];

    // Define the NM Technology Customers Options
    const CUSTOMERS = [
        'APS',
        'Barrister Global',
        'DarAlIslam',
        'Field Nation',
        'Navco',
        'NEW CUSTOMER',
        'NuTech National',
        'Telaid',
        'TKH Security',
        'Work Market',
    ];

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the Customer model
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Check if the work order has a specific status
    public function hasStatus($status)
    {
        return $this->status === $status;
    }
}