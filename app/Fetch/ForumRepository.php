<?php

namespace App\Fetch;

use App\Models\Balasan;
use App\Models\Postingan;
use Illuminate\Pagination\LengthAwarePaginator;

/** Fetching forum diskusi (KF-03, KF-09). Hanya membaca data. */
class ForumRepository
{
    /**
     * Daftar postingan berpaginasi.
     * Query: with() pemilihan kolom, withCount(), when() + where like, latest(), paginate()
     * Atribut tambahan: balasan_count
     */
    public function daftar(?string $q = null, int $limit = 10): LengthAwarePaginator
    {
        return Postingan::query()
            ->with('penulis:id,nama,role')
            ->withCount('balasan')
            ->when($q, fn ($query) => $query->where('judul', 'like', "%{$q}%"))
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }

    /** Detail satu postingan + penulis + jumlah balasan. Query: with(), withCount(), findOrFail() */
    public function detail(int $id): Postingan
    {
        return Postingan::query()
            ->with('penulis:id,nama,role')
            ->withCount('balasan')
            ->findOrFail($id);
    }

    /**
     * Balasan sebuah postingan (urut dari yang terlama).
     * Query: whereKey()->firstOrFail() (cek postingan), with(), where(), oldest(), paginate()
     */
    public function balasan(int $postinganId, int $limit = 20): LengthAwarePaginator
    {
        Postingan::whereKey($postinganId)->firstOrFail(); // 404 bila postingan tidak ada

        return Balasan::query()
            ->with('penulis:id,nama,role')
            ->where('postingan_id', $postinganId)
            ->oldest()
            ->paginate($limit);
    }

    /**
     * Rekap isi forum untuk ADMIN, dengan info penulis diperluas (pekerjaan, usia).
     * Query: with() kolom lebih lengkap, withCount(), when() + where like, latest(), paginate()
     */
    public function rekapAdmin(?string $q = null, int $limit = 10): LengthAwarePaginator
    {
        return Postingan::query()
            ->with('penulis:id,nama,role,pekerjaan,usia')
            ->withCount('balasan')
            ->when($q, fn ($query) => $query->where('judul', 'like', "%{$q}%"))
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }
}
