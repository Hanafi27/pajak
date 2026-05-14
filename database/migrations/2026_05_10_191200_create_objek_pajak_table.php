<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('objek_pajak', function (Blueprint $table) {
            $table->increments('id_objek');
            $table->unsignedInteger('id_wp');
            $table->foreign('id_wp')->references('id_wp')->on('wajib_pajak')->cascadeOnDelete();
            $table->string('nop', 30);
            $table->string('lokasi', 150);
            $table->double('luas_tanah');
            $table->double('luas_bangunan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('objek_pajak');
    }
};
