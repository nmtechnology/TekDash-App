<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImagesColumnToWorkOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Add images column if it doesn't exist
            if (!Schema::hasColumn('work_orders', 'images')) {
                $table->json('images')->nullable()->after('file_attachments');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Drop the column if it exists
            if (Schema::hasColumn('work_orders', 'images')) {
                $table->dropColumn('images');
            }
        });
    }
}
