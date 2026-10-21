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
    Schema::create('glosarium', function (Blueprint $table) {
        $table->id();
        $table->string('istilah', 100)->unique();
        $table->text('definisi');
        $table->enum('kategori', ['surya', 'angin', 'air', 'biomassa', 'umum'])->default('umum');
        $table->enum('status', ['draft', 'terbit'])->default('draft');
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('glosarium');
}
};
