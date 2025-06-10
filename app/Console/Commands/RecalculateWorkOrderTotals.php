<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Log;

class RecalculateWorkOrderTotals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'workorders:recalculate-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the grand_total field for all work orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting recalculation of work order totals...');
        
        $workOrders = WorkOrder::all();
        $count = 0;
        
        foreach ($workOrders as $workOrder) {
            $oldTotal = $workOrder->grand_total;
            
            // Calculate grand total (labor + travel if applicable)
            $laborTotal = $workOrder->hourly_rate * $workOrder->hours;
            $travelCost = $workOrder->has_travel ? $workOrder->travel_cost : 0;
            $newTotal = $laborTotal + $travelCost;
            
            // Only update if different
            if ($oldTotal != $newTotal) {
                $workOrder->grand_total = $newTotal;
                $workOrder->save();
                
                $this->info(sprintf('Updated Work Order #%d: %s → $%.2f (was $%.2f)', 
                    $workOrder->id, 
                    $workOrder->title ?? 'Untitled', 
                    $newTotal, 
                    $oldTotal
                ));
                
                $count++;
            }
        }
        
        $this->info("Completed! Updated {$count} work order(s).");
        
        return Command::SUCCESS;
    }
}
