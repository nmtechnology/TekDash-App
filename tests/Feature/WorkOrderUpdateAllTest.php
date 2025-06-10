<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\WorkOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderUpdateAllTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_update_all_method_calculates_grand_total_correctly()
    {
        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create a customer
        $customer = \App\Models\Customer::create([
            'business_name' => 'Test Customer',
            'address' => '123 Test St',
            'poc_name' => 'John Doe',
            'poc_email' => 'john@example.com',
            'phone' => '555-123-4567'
        ]);
        
        // Create a work order
        $workOrder = WorkOrder::create([
            'title' => 'Test Work Order',
            'description' => 'Description',
            'date_time' => now(),
            'status' => 'Scheduled',
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'hours' => 5,
            'hourly_rate' => 100,
            'travel_cost' => 50,
            'has_travel' => false,
            'grand_total' => 500  // Initially 5 hours * $100 = $500
        ]);
        
        // Update the work order with travel included
        $response = $this->post('/work-orders/'.$workOrder->id.'/update-all', [
            'title' => 'Updated Work Order',
            'description' => 'Updated Description',
            'hours' => 6,  // Changed from 5 to 6
            'hourly_rate' => 100,
            'travel_cost' => 50,
            'has_travel' => true,  // Changed from false to true
            'status' => 'In Progress'
        ]);
        
        // Verify response has success status
        $response->assertStatus(200);
        
        // Reload the work order from database
        $updatedWorkOrder = WorkOrder::find($workOrder->id);
        
        // Assert the grand_total is calculated correctly
        // Should be: 6 hours * $100 + $50 travel = $650
        $this->assertEquals(650, $updatedWorkOrder->grand_total);
        
        // Verify activities were created
        $this->assertDatabaseHas('work_order_activities', [
            'work_order_id' => $workOrder->id,
            'action' => 'update',
            'description' => 'Updated title'
        ]);
        
        $this->assertDatabaseHas('work_order_activities', [
            'work_order_id' => $workOrder->id,
            'action' => 'update',
            'description' => 'Updated hours'
        ]);
        
        $this->assertDatabaseHas('work_order_activities', [
            'work_order_id' => $workOrder->id,
            'action' => 'update',
            'description' => 'Updated has_travel'
        ]);
    }
    
    public function test_revenue_calculation_uses_grand_total()
    {
        // Create a user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Create a customer
        $customer = \App\Models\Customer::create([
            'business_name' => 'Test Customer',
            'address' => '123 Test St',
            'poc_name' => 'John Doe',
            'poc_email' => 'john@example.com',
            'phone' => '555-123-4567'
        ]);
        
        // Create two completed work orders
        WorkOrder::create([
            'title' => 'Completed Order 1',
            'description' => 'Description 1',
            'date_time' => now()->subDays(5),
            'status' => 'Complete',
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'hours' => 3,
            'hourly_rate' => 100,
            'travel_cost' => 75,
            'has_travel' => true,
            'grand_total' => 375  // 3 hours * $100 + $75 travel
        ]);
        
        WorkOrder::create([
            'title' => 'Completed Order 2',
            'description' => 'Description 2',
            'date_time' => now()->subDays(2),
            'status' => 'Complete',
            'user_id' => $user->id,
            'customer_id' => $customer->id,
            'hours' => 5,
            'hourly_rate' => 120,
            'travel_cost' => 0,
            'has_travel' => false,
            'grand_total' => 600  // 5 hours * $120
        ]);
        
        // Get the revenue stats
        $response = $this->get('/revenue-stats');
        
        // Verify the response
        $response->assertStatus(200);
        
        $responseData = $response->json();
        
        // The total revenue should be the sum of grand_total values (375 + 600 = 975)
        $this->assertEquals(975, $responseData['totalRevenue']);
        
        // Since both orders are within the last 7 days, last7DaysRevenue should also be 975
        $this->assertEquals(975, $responseData['last7DaysRevenue']);
    }
}
