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
    Schema::table('users', function (Blueprint $table) {
        // Tambahkan 'super_admin' ke dalam array list
        // Pastikan default-nya tetap 'petani' atau sesuai kebutuhan
        $table->enum('role', ['admin', 'petani', 'super_admin'])->default('petani')->after('email');
    });
}

public function down()
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}

};
