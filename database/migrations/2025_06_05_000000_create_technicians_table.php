<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('technicians', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone_number');
            $table->string('address')->nullable();
            $table->string('employee_id')->unique();
            $table->date('hire_date');
            $table->string('certifications')->nullable(); // JSON or comma-separated list of certifications
            $table->string('specializations')->nullable(); // JSON or comma-separated list of specializations
            $table->boolean('is_active')->default(true);
            $table->string('profile_picture')->nullable(); // Path to the profile picture
            $table->json('attachments')->nullable(); // JSON field for storing signed hiring documents
            $table->decimal('pay_rate', 8, 2)->nullable(); // Hourly or salary pay rate
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('technicians');
    }
};
