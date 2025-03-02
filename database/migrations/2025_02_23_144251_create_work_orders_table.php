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
            $table->boolean('archived')->default(false);
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};