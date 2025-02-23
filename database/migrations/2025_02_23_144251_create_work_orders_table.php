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
        $table->decimal('price', 8, 2);
        $table->enum('status', ['Scheduled', 'In Progress', 'Part/Return', 'Complete', 'Cancelled']);
        $table->json('file_attachments')->nullable(); // Add this line to store file paths for .pdf and .jpg files
        $table->text('notes')->nullable();
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