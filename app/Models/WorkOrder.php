<?php
// filepath: app/Models/WorkOrder.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class WorkOrder extends Model
{
    use HasFactory;

    protected $table = 'work_orders';

    protected $fillable = [
        'title',
        'description',
        'date_time',
        'status',
        'user_id',
        'images',
        'notes',
        'price',
        'customer_id',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'images' => 'array',
        'price' => 'decimal:2',
    ];

    const STATUSES = [
        'Scheduled',
        'In Progress',
        'Part Needed',
        'Complete',
        'Cancelled',
    ];

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function hasStatus($status)
    {
        return $this->status === $status;
    }

    public function notes()
    {
        return $this->hasMany(Note::class);
    }
}