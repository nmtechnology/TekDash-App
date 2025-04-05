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
            
            // Check if the price column exists
            $hasPriceColumn = in_array('price', $columns);
            if (!$hasPriceColumn) {
                Log::error('Work orders table does not have a price column!');
                return $this->errorResponse('Work orders table does not have a price column');
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
            
            // Create an array of statuses to include in revenue calculations
            $revenueStatuses = [$completedStatus];
            if ($archivedStatus) {
                $revenueStatuses[] = $archivedStatus;
            }
            
            Log::info('Including statuses in revenue calculations: ' . json_encode($revenueStatuses));
            
            // Calculate revenue for the last 7 days
            $last7Days = Carbon::now()->subDays(7);
            $last7DaysRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->where($completedDateColumn, '>=', $last7Days)
                ->sum('price');
            
            // Calculate revenue for the last 30 days
            $last30Days = Carbon::now()->subDays(30);
            $last30DaysRevenue = WorkOrder::whereIn('status', $revenueStatuses)
                ->where($completedDateColumn, '>=', $last30Days)
                ->sum('price');
            
            // Calculate total revenue
            $totalRevenue = WorkOrder::whereIn('status', $revenueStatuses)->sum('price');
            
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
        
        $monthlyQuery = WorkOrder::whereIn('status', $revenueStatuses)
            ->where($completedDateColumn, '>=', $yearStart)
            ->select(DB::raw("MONTH($completedDateColumn) as month"), DB::raw('SUM(price) as revenue'))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $monthlyRevenue = $monthlyQuery->map(function ($item) {
            return [
                'month' => Carbon::create()->month($item->month)->format('M'),
                'revenue' => (float) $item->revenue
            ];
        });
        
        // Fill in missing months
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
            ->whereMonth($completedDateColumn, $currentMonth)
            ->sum('price');
            
        $lastMonthRevenue = WorkOrder::whereIn('status', $revenueStatuses)
            ->whereMonth($completedDateColumn, $lastMonth)
            ->sum('price');
            
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
            ->whereYear($completedDateColumn, $thisYear)
            ->sum('price');
            
        $lastYearRevenue = WorkOrder::whereIn('status', $revenueStatuses)
            ->whereYear($completedDateColumn, $lastYear)
            ->sum('price');
            
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
