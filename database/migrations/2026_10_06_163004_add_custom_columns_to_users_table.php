<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom-kolom custom ke tabel users yang sudah ada.
     * (users dibuat oleh migration bawaan Laravel, kita extend di sini)
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambah setelah kolom id agar urutan rapi
            $table->string('nama')->after('id');
            $table->string('pekerjaan')->nullable()->after('email');
            $table->unsignedTinyInteger('usia')->nullable()->after('pekerjaan');
            $table->enum('role', ['user', 'admin'])->default('user')->after('usia');

            // Hapus kolom 'name' bawaan Laravel karena diganti 'nama'
            $table->dropColumn('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->dropColumn(['nama', 'pekerjaan', 'usia', 'role']);
        });
    }
};
