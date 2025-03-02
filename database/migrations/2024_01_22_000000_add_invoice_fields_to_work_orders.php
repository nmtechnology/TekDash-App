<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInvoiceFieldsToWorkOrders extends Migration
{
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->boolean('invoiced')->default(false);
            $table->string('invoice_number')->nullable();
            $table->timestamp('invoiced_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['invoiced', 'invoice_number', 'invoiced_at']);
        });
    }
}
