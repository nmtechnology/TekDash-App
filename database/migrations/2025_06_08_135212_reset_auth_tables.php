<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Truncate all auth-related tables
        DB::statement('DELETE FROM users');
        DB::statement('DELETE FROM sessions');
        DB::statement('DELETE FROM personal_access_tokens');
        
        // Reset auto-increment counters
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::statement('DELETE FROM sqlite_sequence WHERE name = "users"');
            DB::statement('DELETE FROM sqlite_sequence WHERE name = "sessions"');
            DB::statement('DELETE FROM sqlite_sequence WHERE name = "personal_access_tokens"');
        } else {
            // MySQL, PostgreSQL, etc.
            DB::statement('ALTER TABLE users AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE sessions AUTO_INCREMENT = 1');
            DB::statement('ALTER TABLE personal_access_tokens AUTO_INCREMENT = 1');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to do here since we're just clearing data
    }
};
