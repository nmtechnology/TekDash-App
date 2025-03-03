<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->id();
        $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
        $table->string('invoice_number')->nullable();
        $table->string('customer_id');
        $table->decimal('amount', 10, 2);
        $table->string('status')->default('pending');
        $table->string('quickbooks_id')->nullable();
        $table->timestamp('issued_at')->nullable();
        $table->timestamp('due_at')->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
