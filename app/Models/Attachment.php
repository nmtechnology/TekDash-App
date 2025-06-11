<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attachment extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'work_order_id',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'url',
    ];

    /**
     * Get the work order that owns the attachment.
     */
    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }
}
