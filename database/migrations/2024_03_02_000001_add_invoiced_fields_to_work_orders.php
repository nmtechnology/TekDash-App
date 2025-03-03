<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Check if work_orders table exists first
        if (!Schema::hasTable('work_orders')) {
            // Create the table if it doesn't exist
            Schema::create('work_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('customer_id');
                $table->string('title');
                $table->text('description');
                $table->dateTime('date_time');
                $table->dateTime('scheduled_at')->nullable();
                $table->decimal('price', 8, 2);
                $table->enum('status', ['Scheduled', 'In Progress', 'Part Needed', 'Complete', 'Cancelled', 'Archived']);
                $table->json('file_attachments')->nullable();
                $table->json('images')->nullable();
                $table->timestamps();
            });
        }
        
        // Now add the invoice fields
        Schema::table('work_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('work_orders', 'invoiced')) {
                $table->boolean('invoiced')->default(false);
            }
            if (!Schema::hasColumn('work_orders', 'invoiced_at')) {
                $table->timestamp('invoiced_at')->nullable();
            }
            if (!Schema::hasColumn('work_orders', 'archived')) {
                $table->boolean('archived')->default(false);
            }
            if (!Schema::hasColumn('work_orders', 'archived_at')) {
                $table->timestamp('archived_at')->nullable();
            }
        });
    }

    public function down()
    {
        // If we created the table in this migration, drop it entirely
        // Otherwise just drop the added columns
        if (Schema::getConnection()->getSchemaManager()->listTableDetails('work_orders')->getColumn('id')->getAutoincrement()) {
            Schema::dropIfExists('work_orders');
        } else {
            Schema::table('work_orders', function (Blueprint $table) {
                $table->dropColumn(['invoiced', 'invoiced_at', 'archived', 'archived_at']);
            });
        }
    }
};
