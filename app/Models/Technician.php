<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Technician extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone_number',
        'address',
        'employee_id',
        'hire_date',
        'certifications',
        'specializations',
        'is_active',
        'profile_picture',
        'attachments',
        'pay_rate',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'certifications' => 'array',
        'specializations' => 'array',
        'is_active' => 'boolean',
        'attachments' => 'array',
        'pay_rate' => 'decimal:2',
    ];

    /**
     * Define a relationship to work orders.
     */
    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
