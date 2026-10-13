<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kuis', function (Blueprint $table) {
            $table->id();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('pertanyaan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan')->default(1);
            $table->text('teks');
            $table->unsignedInteger('poin')->default(1);
            $table->text('penjelasan')->nullable();
            $table->timestamps();

            $table->index(['kuis_id', 'urutan']);
        });

        Schema::create('opsi', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pertanyaan_id')->constrained('pertanyaan')->cascadeOnDelete();
            $table->text('teks');
            $table->boolean('benar')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opsi');
        Schema::dropIfExists('pertanyaan');
        Schema::dropIfExists('kuis');
    }
};
