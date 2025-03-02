<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
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
        Schema::table('work_orders', function (Blueprint $table) {
            $table->dropColumn(['invoiced', 'invoiced_at', 'archived', 'archived_at']);
        });
    }
};
