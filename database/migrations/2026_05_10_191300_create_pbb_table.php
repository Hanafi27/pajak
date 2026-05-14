<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pbb', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_objek')->constrained('objek_pajak')->cascadeOnDelete();
            $table->double('njop');
            $table->double('tarif');
            $table->double('total_pajak');
            $table->year('tahun');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pbb');
    }
};
