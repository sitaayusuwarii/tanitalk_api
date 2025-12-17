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
    Schema::table('posts', function (Blueprint $table) {
        // hapus column lama
        $table->dropColumn('category');

        // tambah foreign key baru
        $table->unsignedBigInteger('category_id')->nullable()->after('user_id');
        $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');
    });
}

public function down()
{
    Schema::table('posts', function (Blueprint $table) {
        $table->string('category')->nullable();

        $table->dropForeign(['category_id']);
        $table->dropColumn('category_id');
    });
}

};
