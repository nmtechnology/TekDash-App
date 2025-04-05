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
            // Add invoice_id column if it doesn't exist
            if (!Schema::hasColumn('work_orders', 'invoice_id')) {
                $table->string('invoice_id')->nullable()->after('archived');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Drop the column if it exists
            if (Schema::hasColumn('work_orders', 'invoice_id')) {
                $table->dropColumn('invoice_id');
            }
        });
    }
};
