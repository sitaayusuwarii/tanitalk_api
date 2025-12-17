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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); 
            // pemilik notifikasi (yang postingannya di-like / dikomentari)

            $table->foreignId('from_user_id')->constrained('users')->cascadeOnDelete();
            // yang melakukan aksi

            $table->foreignId('post_id')->constrained()->cascadeOnDelete();

            $table->enum('type', ['like', 'comment']);
            $table->text('comment')->nullable();

            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
