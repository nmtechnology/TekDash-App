<?php

namespace App\Observers;

use App\Models\WorkOrder;
use App\Models\WorkOrderActivity;

class WorkOrderObserver
{
    /**
     * Handle the WorkOrder "saving" event.
     * This happens before both creating and updating events
     */
    public function saving(WorkOrder $workOrder)
    {
        // If price-affecting fields are changed, recalculate grand_total
        if ($workOrder->isDirty('hours') || 
            $workOrder->isDirty('hourly_rate') || 
            $workOrder->isDirty('travel_cost') || 
            $workOrder->isDirty('has_travel')) {
            
            $laborTotal = $workOrder->hourly_rate * $workOrder->hours;
            $travelCost = $workOrder->has_travel ? $workOrder->travel_cost : 0;
            $workOrder->grand_total = $laborTotal + $travelCost;
        }
    }

    public function created(WorkOrder $workOrder)
    {
        WorkOrderActivity::create([
            'work_order_id' => $workOrder->id,
            'user_id' => auth()->id(),
            'field_name' => 'work_order',
            'old_value' => null,
            'new_value' => 'Created',
            'action_type' => 'create',
            'description' => 'Work order created',
        ]);
    }

    public function updated(WorkOrder $workOrder)
    {
        $dirty = $workOrder->getDirty();
        $original = $workOrder->getOriginal();

        foreach ($dirty as $field => $newValue) {
            if ($field === 'updated_at') continue;

            $oldValue = $original[$field] ?? null;

            WorkOrderActivity::create([
                'work_order_id' => $workOrder->id,
                'user_id' => auth()->id(),
                'field_name' => $field,
                'old_value' => $oldValue,
                'new_value' => $newValue,
                'action_type' => 'update',
                'description' => "Updated {$field} from '{$oldValue}' to '{$newValue}'",
            ]);
        }
    }

    public function deleted(WorkOrder $workOrder)
    {
        // The activity log for deletion is now handled directly in the controller
        // to avoid foreign key constraint violations
        // We leave this method for compatibility but it shouldn't be called
        try {
            // Only try to create an activity if the work order still exists in database
            if (WorkOrder::find($workOrder->id)) {
                WorkOrderActivity::create([
                    'work_order_id' => $workOrder->id,
                    'user_id' => auth()->id(),
                    'field_name' => 'work_order',
                    'old_value' => 'Active',
                    'new_value' => 'Deleted',
                    'action_type' => 'delete',
                    'description' => 'Work order deleted',
                ]);
            }
        } catch (\Exception $e) {
            // Silently fail if we can't create the activity record
            // This prevents errors during normal deletion
            \Log::error('Failed to create deletion activity: ' . $e->getMessage());
        }
    }
}