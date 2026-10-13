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
        Schema::create('pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->unsignedInteger('urutan');
            $table->text('teks');
            $table->unsignedInteger('poin')->default(1);
            $table->text('penjelasan')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pertanyaan');
    }
};
