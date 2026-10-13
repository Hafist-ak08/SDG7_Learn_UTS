<?php

namespace App\Repositories;

use App\Models\HasilKuis;
use App\Models\Kuis;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/** Fetching hasil/skor kuis (KF-07). Hanya membaca data. */
class HasilKuisRepository
{
    /**
     * Riwayat skor milik satu user.
     * Query: where(), latest(), paginate()
     */
    public function riwayat(int $penggunaId, int $limit = 10): LengthAwarePaginator
    {
        return HasilKuis::query()
            ->where('pengguna_id', $penggunaId)
            ->latest()
            ->paginate($limit);
    }

    /**
     * Detail satu hasil (berisi `tinjauan` snapshot, otomatis jadi array lewat cast model).
     *
     * @param int|null $penggunaId  isi dengan id user agar hanya bisa membuka miliknya
     *                              (404 bila bukan miliknya). Biarkan null untuk admin.
     * Query: when() + where(), findOrFail()
     */
    public function detail(int $id, ?int $penggunaId = null): HasilKuis
    {
        return HasilKuis::query()
            ->when($penggunaId, fn ($query) => $query->where('pengguna_id', $penggunaId))
            ->findOrFail($id);
    }

    /**
     * Rekap hasil satu kuis dari semua user (admin).
     * Query: where(), with() pemilihan kolom, latest()/orderByDesc(), paginate()
     *
     * @param string $sort 'terbaru' | 'skor'
     */
    public function rekapPerKuis(int $kuisId, int $limit = 20, string $sort = 'terbaru'): LengthAwarePaginator
    {
        Kuis::findOrFail($kuisId); // 404 bila kuis tidak ada

        return HasilKuis::query()
            ->with('pengguna:id,nama,pekerjaan,usia')
            ->where('kuis_id', $kuisId)
            ->when($sort === 'skor', fn ($q) => $q->orderByDesc('skor')->latest(),
                fn ($q) => $q->latest())
            ->paginate($limit);
    }

    /**
     * Skor terbaik user per kuis -> [kuis_id => skor_terbaik].
     * Query: selectRaw() + groupBy() + pluck()
     */
    public function skorTerbaikPerKuis(int $penggunaId): Collection
    {
        return HasilKuis::query()
            ->where('pengguna_id', $penggunaId)
            ->selectRaw('kuis_id, max(skor) as terbaik')
            ->groupBy('kuis_id')
            ->pluck('terbaik', 'kuis_id');
    }
}
