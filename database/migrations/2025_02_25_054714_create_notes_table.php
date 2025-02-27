<?php
// filepath: database/migrations/xxxx_xx_xx_create_notes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateNotesTable extends Migration
{
    public function up()
    {
        
        // In your migrations folder, check that your notes table has this structure
        Schema::create('notes', function (Blueprint $table) {
        $table->id();
        $table->text('text');
        $table->foreignId('user_id')->constrained();
        $table->foreignId('work_order_id')->constrained(); // This is crucial
        $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('notes');
    }
}