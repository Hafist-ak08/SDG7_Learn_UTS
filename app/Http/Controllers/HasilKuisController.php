<?php

namespace App\Http\Controllers;

use App\Models\HasilKuis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * HasilKuisController
 *
 * Endpoint:
 *  GET /hasil-kuis      (user)                  — §9.1  KF-07
 *  GET /hasil-kuis/{id} (user milik sendiri, admin) — §9.2  KF-07
 */
class HasilKuisController extends Controller
{
    // ─────────────────────────────────────────────
    // §9.1  GET /hasil-kuis
    // Riwayat skor kuis milik user yang sedang login
    // sort: dikerjakan_pada (default -dikerjakan_pada)
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
            'sort' => 'nullable|string|in:dikerjakan_pada,-dikerjakan_pada',
        ]);
        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $sort = $request->query('sort', '-dikerjakan_pada');

        $paginator = HasilKuis::where('user_id', $request->user()->id)
            ->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc')
            ->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(fn ($h) => $this->formatRingkas($h));

        return $this->suksesDaftar('Riwayat hasil kuis berhasil diambil', $data, $this->buildMeta($paginator));
    }

    // ─────────────────────────────────────────────
    // §9.2  GET /hasil-kuis/{id}
    // Detail satu hasil kuis + tinjauan per pertanyaan
    // User hanya bisa lihat milik sendiri; admin bisa lihat semua
    // ─────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
    {
        $hasil = HasilKuis::find($id);

        if (! $hasil) {
            return $this->error('Hasil kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        $user = $request->user();

        // User biasa hanya boleh melihat miliknya sendiri
        if ($user->role === 'user' && $hasil->user_id !== $user->id) {
            return $this->error('Anda tidak berhak mengakses hasil kuis ini', 'FORBIDDEN', 403);
        }

        return $this->sukses('Detail hasil kuis berhasil diambil', [
            'id'                => $hasil->id,
            'kuis_id'           => $hasil->kuis_id,
            'kuis_judul'        => $hasil->kuis_judul,
            'skor'              => $hasil->skor,
            'skor_maks'         => $hasil->skor_maks,
            'persentase'        => $hasil->persentase,
            'jumlah_benar'      => $hasil->jumlah_benar,
            'jumlah_pertanyaan' => $hasil->jumlah_pertanyaan,
            'dikerjakan_pada'   => $hasil->created_at->toIso8601ZuluString(),
            'tinjauan'          => json_decode($hasil->tinjauan, true),
        ]);
    }

    // ─────────────────────────────────────────────
    // Helper: format ringkas HasilKuis (§4.5)
    // ─────────────────────────────────────────────
    private function formatRingkas(HasilKuis $hasil): array
    {
        return [
            'id'                => $hasil->id,
            'kuis_id'           => $hasil->kuis_id,
            'kuis_judul'        => $hasil->kuis_judul,
            'skor'              => $hasil->skor,
            'skor_maks'         => $hasil->skor_maks,
            'persentase'        => $hasil->persentase,
            'jumlah_benar'      => $hasil->jumlah_benar,
            'jumlah_pertanyaan' => $hasil->jumlah_pertanyaan,
            'dikerjakan_pada'   => $hasil->created_at->toIso8601ZuluString(),
        ];
    }
}
