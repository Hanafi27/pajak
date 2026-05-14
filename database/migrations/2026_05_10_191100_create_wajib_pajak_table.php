<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wajib_pajak', function (Blueprint $table) {
            $table->increments('id_wp');
            $table->string('nama_wp', 100);
            $table->text('alamat');
            $table->string('no_ktp', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wajib_pajak');
    }
};
