<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('postingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->longText('isi');
            $table->timestamps();
        });

        Schema::create('balasan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('postingan_id')->constrained('postingan')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->longText('isi');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('balasan');
        Schema::dropIfExists('postingan');
    }
};
