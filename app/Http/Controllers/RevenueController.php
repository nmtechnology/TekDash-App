<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class RevenueController extends Controller
{
    public function getStats()
    {
        try {
            // First, let's log table structure and some sample data
            $workOrdersTable = (new WorkOrder())->getTable();
            $columns = Schema::getColumnListing($workOrdersTable);
            Log::info('Work Orders table columns: ' . json_encode($columns));
            
            $sampleData = WorkOrder::take(3)->get();
            Log::info('Sample work order data: ' . json_encode($sampleData));
            
            // Check if the grand_total column exists
            $hasGrandTotalColumn = in_array('grand_total', $columns);
            if (!$hasGrandTotalColumn) {
                Log::error('Work orders table does not have a grand_total column!');
                return $this->errorResponse('Work orders table does not have a grand_total column');
            }
            
            // Check if we have a completed_at column, otherwise use updated_at
            $hasCompletedAt = in_array('completed_at', $columns);
            $completedDateColumn = $hasCompletedAt ? 'completed_at' : 'updated_at';
            
            // Find out what values are used for completed status
            $possibleCompletedStatuses = ['completed', 'Completed', 'COMPLETED', 'complete', 'Complete', 'done', 'Done', 'finished', 'Finished'];
            $statusField = in_array('status', $columns) ? 'status' : null;
            
            if (!$statusField) {
                Log::error('Work orders table does not have a status column!');
                return $this->errorResponse('Work orders table does not have a status column');
            }
            
            // Check which status values actually exist in the database
            $availableStatuses = DB::table($workOrdersTable)
                ->select('status')
                ->distinct()
                ->whereNotNull('status')
                ->pluck('status')
                ->toArray();
            
            Log::info('Available statuses in the database: ' . json_encode($availableStatuses));
            
            // Determine which status to use for completed work orders
            $completedStatus = null;
            foreach ($possibleCompletedStatuses as $status) {
                if (in_array($status, $availableStatuses)) {
                    $completedStatus = $status;
                    break;
                }
            }
            
            if (!$completedStatus) {
                $completedStatus = $availableStatuses[0] ?? 'completed';
                Log::warning('Could not determine completed status, using: ' . $completedStatus);
            }
            
            // Look for archived status
            $archivedStatus = null;
            $possibleArchivedStatuses = ['archived', 'Archived', 'ARCHIVED', 'archive', 'Archive'];
            foreach ($possibleArchivedStatuses as $status) {
                if (in_array($status, $availableStatuses)) {
                    $archivedStatus = $status;
                    break;
                }
            }
            
            // Look for In Progress status
            $inProgressStatus = null;
            $possibleInProgressStatuses = ['in progress', 'In Progress', 'IN PROGRESS', 'inprogress', 'InProgress'];
            foreach ($possibleInProgressStatuses as $status) {
                if (in_array($status, $availableStatuses)) {
                    $inProgressStatus = $status;
                    break;
                }
            }
            
            // Create an array of statuses to include in revenue calculations
            $revenueStatuses = [];
            if ($completedStatus) {
                $revenueStatuses[] = $completedStatus;
            }
            if ($archivedStatus) {
                $revenueStatuses[] = $archivedStatus;
            }
            if ($inProgressStatus) {
                $revenueStatuses[] = $inProgressStatus;
            }
            
            // If no statuses found, use all available statuses
            if (empty($revenueStatuses) && !empty($availableStatuses)) {
                $revenueStatuses = $availableStatuses;
                Log::warning('No specific status match found, using all available statuses');
            }
            
            Log::info('Including statuses in revenue calculations: ' . json_encode($revenueStatuses));
            
            // Calculate revenue for the last 7 days
            $last7Days = Carbon::now()->subDays(7);
            $last7DaysRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->where($completedDateColumn, '>=', $last7Days)
                ->get()
                ->sum('grand_total');
            
            // Calculate revenue for the last 30 days
            $last30Days = Carbon::now()->subDays(30);
            $last30DaysRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->where($completedDateColumn, '>=', $last30Days)
                ->get()
                ->sum('grand_total');
            
            // Calculate total revenue
            $totalRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->get()
                ->sum('grand_total');
            Log::info('All work orders: ' . json_encode($allWorkOrders));
            
            // Get monthly revenue data
            $monthlyRevenue = $this->getMonthlyRevenue($revenueStatuses, $completedDateColumn);
            
            // Calculate month-over-month growth
            list($currentMonthRevenue, $lastMonthRevenue, $comparedToLastMonth) = 
                $this->calculateMonthOverMonthGrowth($revenueStatuses, $completedDateColumn);
            
            // Calculate year-over-year growth
            $comparedToLastYear = $this->calculateYearOverYearGrowth($revenueStatuses, $completedDateColumn);
            
            // Calculate forecast
            $forecastNextMonth = $this->calculateForecastNextMonth($currentMonthRevenue, $lastMonthRevenue);
            
            // Log the results
            Log::info('Revenue stats calculated successfully', [
                'totalRevenue' => $totalRevenue,
                'last7DaysRevenue' => $last7DaysRevenue,
                'last30DaysRevenue' => $last30DaysRevenue,
                'monthlyRevenueCount' => count($monthlyRevenue)
            ]);
            
            return [
                'monthly' => $monthlyRevenue,
                'totalRevenue' => $totalRevenue,
                'comparedToLastMonth' => $comparedToLastMonth,
                'comparedToLastYear' => $comparedToLastYear,
                'forecastNextMonth' => $forecastNextMonth,
                'last7DaysRevenue' => $last7DaysRevenue,
                'last30DaysRevenue' => $last30DaysRevenue,
                '_debug' => [
                    'completedStatus' => $completedStatus,
                    'archivedStatus' => $archivedStatus,
                    'revenueStatuses' => $revenueStatuses,
                    'availableStatuses' => $availableStatuses,
                    'completedDateColumn' => $completedDateColumn,
                    'currentMonthRevenue' => $currentMonthRevenue,
                    'lastMonthRevenue' => $lastMonthRevenue,
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Error calculating revenue stats: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return $this->errorResponse('Failed to calculate revenue stats: ' . $e->getMessage());
        }
    }
    
    private function getMonthlyRevenue($revenueStatuses, $completedDateColumn)
    {
        $yearStart = Carbon::now()->startOfYear();
        
        $workOrders = WorkOrder::whereIn('status', $revenueStatuses)
            ->where($completedDateColumn, '>=', $yearStart)
            ->get(['id', 'status', 'grand_total', $completedDateColumn]);
        
        $monthlyRevenue = $workOrders
            ->groupBy(function($order) use ($completedDateColumn) {
                return Carbon::parse($order->{$completedDateColumn})->format('m');
            })
            ->map(function($orders) use ($completedDateColumn) {
                return [
                    'month' => Carbon::create()->month((int)$orders->first()->{$completedDateColumn}->format('m'))->format('M'),
                    'revenue' => $orders->sum('grand_total')
                ];
            })
            ->values();
        
        // Fill in missing months up to current month
        $allMonths = [];
        for ($i = 1; $i <= Carbon::now()->month; $i++) {
            $monthName = Carbon::create()->month($i)->format('M');
            $existingMonth = $monthlyRevenue->firstWhere('month', $monthName);
            $allMonths[] = $existingMonth ?? ['month' => $monthName, 'revenue' => 0];
        }
        
        return collect($allMonths)->values()->all();
    }
    
    private function calculateMonthOverMonthGrowth($revenueStatuses, $completedDateColumn)
    {
        $currentMonth = Carbon::now()->month;
        $lastMonth = Carbon::now()->subMonth()->month;
        
        $currentMonthRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->whereRaw("strftime('%m', $completedDateColumn) = ?", [sprintf("%02d", $currentMonth)])
                ->get()
                ->sum('grand_total');
            
        $lastMonthRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->whereRaw("strftime('%m', $completedDateColumn) = ?", [sprintf("%02d", $lastMonth)])
                ->get()
                ->sum('grand_total');
            
        $comparedToLastMonth = $lastMonthRevenue > 0
            ? round((($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100, 1)
            : 0;
            
        return [$currentMonthRevenue, $lastMonthRevenue, $comparedToLastMonth];
    }
    
    private function calculateYearOverYearGrowth($revenueStatuses, $completedDateColumn)
    {
        $thisYear = Carbon::now()->year;
        $lastYear = Carbon::now()->subYear()->year;
        
        $thisYearRevenue = WorkOrder::whereIn('status', $revenueStatuses)
            ->whereRaw("strftime('%Y', $completedDateColumn) = ?", [$thisYear])
            ->sum('grand_total');
            
        $lastYearRevenue = WorkOrder::whereIn('status', $revenueStatuses)
            ->whereRaw("strftime('%Y', $completedDateColumn) = ?", [$lastYear])
            ->sum('grand_total');
            
        return $lastYearRevenue > 0
            ? round((($thisYearRevenue - $lastYearRevenue) / $lastYearRevenue) * 100, 1)
            : 0;
    }
    
    private function calculateForecastNextMonth($currentMonthRevenue, $lastMonthRevenue)
    {
        $forecastNextMonth = $currentMonthRevenue;
        if ($lastMonthRevenue > 0 && $currentMonthRevenue > 0) {
            $growthRate = ($currentMonthRevenue - $lastMonthRevenue) / $lastMonthRevenue;
            $forecastNextMonth = $currentMonthRevenue * (1 + $growthRate);
        }
        return $forecastNextMonth;
    }
    
    private function errorResponse($message)
    {
        return [
            'error' => $message,
            'monthly' => [],
            'totalRevenue' => 0,
            'comparedToLastMonth' => 0,
            'comparedToLastYear' => 0,
            'forecastNextMonth' => 0,
            'last7DaysRevenue' => 0,
            'last30DaysRevenue' => 0,
        ];
    }
}
