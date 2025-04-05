<?php
// filepath: app/Models/WorkOrder.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Schema;
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
        'file_attachments',
        'archived',
        'archived_at',
        'address',
        'hours',
    ];

    protected $with = ['notes'];


    protected $casts = [
        'scheduled_at' => 'datetime',
        'images' => 'array',
        'price' => 'decimal:2',
        'file_attachments' => 'array',
        'archived_at' => 'datetime',
        'status' => 'string',
        'hours' => 'decimal:2',
    ];

    const STATUSES = [
        'Scheduled',
        'In Progress',
        'Part Needed',
        'Complete',
        'Cancelled',
        'Archived'
    ];

    const CUSTOMERS = [
        'APS',
        'Barrister Global',
        'Field Nation',
        'Navco',
        'NEW CUSTOMER',
        'NuTech National',
        'Telaid',
        'TKH Security',
        'Work Market',
    ];

    // Make sure to add notes to visible attributes if using API resources
    protected $appends = ['notes', 'all_attachments'];


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

    /**
     * Get all attachments, combining file_attachments and images
     */
    public function getAllAttachmentsAttribute()
    {
        $allAttachments = [];
        
        // Add file_attachments if they exist
        if (!empty($this->file_attachments)) {
            if (is_string($this->file_attachments)) {
                $decoded = json_decode($this->file_attachments, true);
                if (is_array($decoded)) {
                    $allAttachments = array_merge($allAttachments, $decoded);
                }
            } elseif (is_array($this->file_attachments)) {
                $allAttachments = array_merge($allAttachments, $this->file_attachments);
            }
        }
        
        // Add images if the column exists and they're set
        if (Schema::hasColumn('work_orders', 'images') && !empty($this->images)) {
            if (is_string($this->images)) {
                $decoded = json_decode($this->images, true);
                if (is_array($decoded)) {
                    $allAttachments = array_merge($allAttachments, $decoded);
                }
            } elseif (is_array($this->images)) {
                $allAttachments = array_merge($allAttachments, $this->images);
            }
        }
        
        return $allAttachments;
    }
}