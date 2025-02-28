<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuickBooksToken extends Model
{
    //
    public function up()
{
    Schema::create('quick_books_tokens', function (Blueprint $table) {
        $table->id();
        $table->text('access_token');
        $table->text('refresh_token');
        $table->string('realm_id');
        $table->timestamp('expires_at');
        $table->timestamps();
    });
}
}
