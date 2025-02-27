<?php
// filepath: app/Models/WorkOrder.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Models\Customer;
use App\Models\Note;


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
        'price',
        'customer_id',
    ];

    protected $with = ['notes'];


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

    // Make sure to add notes to visible attributes if using API resources
    protected $appends = ['notes'];


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

    // Add a custom accessor to always return notes
    public function notesWithUsers()
    {
        return $this->notes()->with('user')->get();
    }

    public function getNotesAttribute()
    {
        // This is what will be returned when accessing $workOrder->notes
        return $this->notes()->with('user')->get();
    }
    
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}