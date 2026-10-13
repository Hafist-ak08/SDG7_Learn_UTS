<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hasil_kuis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('kuis_id')->constrained('kuis')->cascadeOnDelete();
            $table->string('kuis_judul');           // snapshot judul saat pengerjaan
            $table->unsignedInteger('skor')->default(0);
            $table->unsignedInteger('skor_maks')->default(0);
            $table->decimal('persentase', 5, 2)->default(0);
            $table->unsignedInteger('jumlah_benar')->default(0);
            $table->unsignedInteger('jumlah_pertanyaan')->default(0);
            $table->json('tinjauan');               // snapshot per-pertanyaan
            $table->timestamps();

            $table->index(['user_id', 'kuis_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hasil_kuis');
    }
};
