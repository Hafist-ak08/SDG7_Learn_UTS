<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * AdminPenggunaController
 *
 * Endpoint (Admin Only):
 *  GET /admin/pengguna      — §14.1  (§2.2, DFD Data User)
 *  GET /admin/pengguna/{id} — §14.2  (§2.2, DFD Data User)
 */
class PenggunaController extends Controller
{
    // ─────────────────────────────────────────────
    // §14.1  GET /admin/pengguna
    // q mencari: nama atau email
    // sort: dibuat_pada (default -dibuat_pada)
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        $params = $this->paginasiParams($request);
        if ($params === null) {
            return $this->errorValidasi([
                'page'  => ['Nilai page harus >= 1'],
                'limit' => ['Nilai limit harus antara 1 dan 50'],
            ]);
        }

        $validator = Validator::make($request->query(), [
            'q'    => 'nullable|string|max:100',
            'sort' => 'nullable|string|in:dibuat_pada,-dibuat_pada',
        ]);
        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $query = User::query();

        // Pencarian (q)
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('nama', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
            });
        }

        $sort = $request->query('sort', '-dibuat_pada');
        $query->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');

        $paginator = $query->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(fn ($user) => [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'pekerjaan'   => $user->pekerjaan,
            'usia'        => $user->usia,
            'role'        => $user->role,
            'dibuat_pada' => $user->created_at->toIso8601ZuluString(),
        ]);

        return $this->suksesDaftar('Daftar pengguna berhasil diambil', $data, $this->buildMeta($paginator));
    }

    // ─────────────────────────────────────────────
    // §14.2  GET /admin/pengguna/{id}
    // Detail pengguna + ringkasan aktivitas (postingan, balasan, kuis)
    // ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        // Load user dengan count aktivitas
        $user = User::withCount([
            'postingan',
            'balasan',
            'hasilKuis',
        ])->find($id);

        if (! $user) {
            return $this->error('Pengguna tidak ditemukan', 'NOT_FOUND', 404);
        }

        return $this->sukses('Detail pengguna berhasil diambil', [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'pekerjaan'   => $user->pekerjaan,
            'usia'        => $user->usia,
            'role'        => $user->role,
            'dibuat_pada' => $user->created_at->toIso8601ZuluString(),
            'aktivitas'   => [
                'jumlah_postingan'  => $user->postingan_count ?? 0,
                'jumlah_balasan'    => $user->balasan_count ?? 0,
                'jumlah_kuis_diikuti' => $user->hasil_kuis_count ?? 0,
            ],
        ]);
    }
}
