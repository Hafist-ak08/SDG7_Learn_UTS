<?php

namespace App\Repositories;

use App\Models\Balasan;
use App\Models\Pengguna;
use Illuminate\Pagination\LengthAwarePaginator;

/** Fetching data pengguna (profil & pengelolaan oleh admin). Hanya membaca data. */
class PenggunaRepository
{
    /**
     * Daftar pengguna (admin).
     * Query: withCount() dua relasi, when() + where grup (nama OR email), when() role, latest(), paginate()
     *
     * @param string|null $role 'user' | 'admin' (opsional)
     */
    public function daftar(?string $q = null, ?string $role = null, int $limit = 20): LengthAwarePaginator
    {
        return Pengguna::query()
            ->withCount(['hasilKuis', 'postingan'])
            ->when($q, fn ($query) => $query->where(
                fn ($w) => $w->where('nama', 'like', "%{$q}%")->orWhere('email', 'like', "%{$q}%")
            ))
            ->when($role, fn ($query) => $query->where('role', $role))
            ->latest()
            ->paginate($limit)
            ->withQueryString();
    }

    /**
     * Detail satu pengguna + ringkasan aktivitas (admin).
     * Query: findOrFail(), count(), avg(), latest()->limit()->get()
     *
     * @return array{pengguna: Pengguna, ringkasan: array, hasil_terakhir: \Illuminate\Support\Collection}
     */
    public function detail(int $id): array
    {
        $pengguna = Pengguna::findOrFail($id);

        return [
            'pengguna'  => $pengguna,
            'ringkasan' => [
                'jumlah_kuis_dikerjakan' => $pengguna->hasilKuis()->count(),
                'rata_rata_persentase'   => round((float) $pengguna->hasilKuis()->avg('persentase'), 2),
                'jumlah_postingan'       => $pengguna->postingan()->count(),
                'jumlah_balasan'         => Balasan::where('pengguna_id', $pengguna->id)->count(),
            ],
            'hasil_terakhir' => $pengguna->hasilKuis()->latest()->limit(5)->get(),
        ];
    }

    /** Profil sendiri. Controller memanggil dengan auth()->id(). Query: findOrFail() */
    public function profil(int $id): Pengguna
    {
        return Pengguna::findOrFail($id);
    }
}
