<?php

namespace App\Fetch;

use App\Models\HasilKuis;
use App\Models\Kuis;
use Illuminate\Pagination\LengthAwarePaginator;

/** Fetching kuis (KF-02, KF-06). Hanya membaca data. */
class KuisRepository
{
    /**
     * Daftar kuis untuk user/admin.
     * Query: withCount(), withSum(), addSelect() subquery, when(), latest(), paginate()
     *
     * Atribut tambahan pada tiap item:
     *   pertanyaan_count -> jumlah pertanyaan
     *   total_poin       -> jumlah poin semua pertanyaan
     *   skor_terbaik     -> skor tertinggi user (null bila belum mengerjakan / $penggunaId kosong)
     */
    public function daftar(?int $penggunaId = null, ?string $q = null, int $limit = 10): LengthAwarePaginator
    {
        return Kuis::query()
            ->withCount('pertanyaan')
            ->withSum('pertanyaan as total_poin', 'poin')
            ->when($penggunaId, fn ($query) => $query->addSelect([
                'skor_terbaik' => HasilKuis::query()
                    ->selectRaw('max(skor)')
                    ->whereColumn('hasil_kuis.kuis_id', 'kuis.id')
                    ->where('user_id', $penggunaId),
            ]))
            ->when($q, fn ($query) => $query->where('judul', 'like', "%{$q}%"))
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     * Detail kuis sisi USER: pertanyaan + opsi TANPA kolom `benar` (kunci jawaban tidak bocor).
     * Query: eager loading with() dengan pemilihan kolom, withCount(), withSum(), findOrFail()
     */
    public function detail(int $id): Kuis
    {
        return Kuis::query()
            ->withCount('pertanyaan')
            ->withSum('pertanyaan as total_poin', 'poin')
            ->with([
                'pertanyaan:id,kuis_id,urutan,teks,poin',   // tanpa 'penjelasan'
                'pertanyaan.opsi:id,pertanyaan_id,teks',     // tanpa 'benar'
            ])
            ->findOrFail($id);
    }

    /** Daftar kuis sisi ADMIN (tanpa skor pribadi). */
    public function daftarAdmin(?string $q = null, int $limit = 10): LengthAwarePaginator
    {
        return $this->daftar(null, $q, $limit);
    }

    /**
     * Detail kuis sisi ADMIN: lengkap dengan kunci jawaban (`opsi.benar`) dan penjelasan.
     * Query: with() relasi bersarang, withSum(), findOrFail()
     */
    public function detailAdmin(int $id): Kuis
    {
        return Kuis::query()
            ->withCount('pertanyaan')
            ->withSum('pertanyaan as total_poin', 'poin')
            ->with('pertanyaan.opsi')
            ->findOrFail($id);
    }
}
