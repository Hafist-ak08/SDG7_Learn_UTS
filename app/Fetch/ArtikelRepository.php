<?php

namespace App\Repositories;

use App\Models\Artikel;
use Illuminate\Pagination\LengthAwarePaginator;

/** Fetching artikel (KF-01). Hanya membaca data. */
class ArtikelRepository
{
    /**
     * Daftar artikel berpaginasi.
     * Query: when() + where like, latest(), paginate(), withQueryString()
     *
     * @param  string|null $q      kata kunci judul (opsional)
     * @param  string      $sort   'terbaru' | 'terlama' | 'judul'
     */
    public function daftar(?string $q = null, int $limit = 10, string $sort = 'terbaru'): LengthAwarePaginator
    {
        return Artikel::query()
            ->when($q, fn ($query) => $query->where('judul', 'like', "%{$q}%"))
            ->when($sort === 'terlama', fn ($query) => $query->oldest(),
                fn ($query) => $sort === 'judul' ? $query->orderBy('judul') : $query->latest())
            ->paginate($limit)
            ->withQueryString();
    }

    /** Detail satu artikel. Query: findOrFail() -> 404 bila tidak ada. */
    public function detail(int $id): Artikel
    {
        return Artikel::findOrFail($id);
    }
}
