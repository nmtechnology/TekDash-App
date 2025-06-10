<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->decimal('travel_cost', 10, 2)->nullable()->after('hours')->default(0);
            $table->decimal('grand_total', 10, 2)->nullable()->after('travel_cost')->default(0);
            $table->boolean('has_travel')->default(false)->after('travel_cost');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['travel_cost', 'has_travel', 'grand_total']);
        });
    }
};