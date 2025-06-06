<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'business_name',
        'address',
        'poc_name',
        'poc_email',
        'fax',
        'net_terms',
        'attachable_files',
        'is_active',
    ];

    protected $casts = [
        'attachable_files' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the work orders associated with the customer.
     */
    public function workOrders(): HasMany
    {
        return $this->hasMany(WorkOrder::class);
    }
}
