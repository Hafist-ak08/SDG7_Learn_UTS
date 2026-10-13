<?php

namespace App\Http\Controllers;

use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * ArtikelController  (sisi publik)
 *
 * Endpoint:
 *  GET /artikel      (publik) — §7.1  KF-01
 *  GET /artikel/{id} (publik) — §7.2  KF-01
 */
class ArtikelController extends Controller
{
    // ─────────────────────────────────────────────
    // §7.1  GET /artikel
    // q mencari: judul
    // sort: tanggal (default -tanggal)
    // ─────────────────────────────────────────────
    public function index(Request $request): JsonResponse
    {
        // Validasi parameter paginasi
        $params = $this->paginasiParams($request);
        if ($params === null) {
            return $this->errorValidasi([
                'page'  => ['Nilai page harus >= 1'],
                'limit' => ['Nilai limit harus antara 1 dan 50'],
            ]);
        }

        // Validasi q dan sort
        $validator = Validator::make($request->query(), [
            'q'    => 'nullable|string|max:100',
            'sort' => 'nullable|string|in:tanggal,-tanggal',
        ]);
        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $query = Artikel::query();

        // Pencarian
        if ($request->filled('q')) {
            $query->where('judul', 'like', '%' . $request->q . '%');
        }

        // Pengurutan (default: -tanggal = terbaru dulu)
        $sort = $request->query('sort', '-tanggal');
        $query->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');

        $paginator = $query->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(fn ($a) => $this->formatRingkas($a));

        return $this->suksesDaftar('Daftar artikel berhasil diambil', $data, $this->buildMeta($paginator));
    }

    // ─────────────────────────────────────────────
    // §7.2  GET /artikel/{id}
    // ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $artikel = Artikel::find($id);

        if (! $artikel) {
            return $this->error('Artikel tidak ditemukan', 'NOT_FOUND', 404);
        }

        return $this->sukses('Detail artikel berhasil diambil', [
            'id'             => $artikel->id,
            'judul'          => $artikel->judul,
            'isi'            => $artikel->isi,
            'sumber'         => $artikel->sumber,
            'gambar_url'     => $artikel->gambar_url,
            'tanggal'        => $artikel->created_at->toIso8601ZuluString(),
            'diperbarui_pada' => $artikel->updated_at->toIso8601ZuluString(),
        ]);
    }

    // ─────────────────────────────────────────────
    // Helper: format artikel ringkas (§4.3)
    // ringkasan = 150 karakter pertama isi, diakhiri … bila terpotong
    // ─────────────────────────────────────────────
    private function formatRingkas(Artikel $artikel): array
    {
        $isi       = $artikel->isi ?? '';
        $panjang   = mb_strlen($isi);
        $ringkasan = $panjang > 150
            ? mb_substr($isi, 0, 150) . '…'
            : $isi;

        return [
            'id'         => $artikel->id,
            'judul'      => $artikel->judul,
            'ringkasan'  => $ringkasan,
            'sumber'     => $artikel->sumber,
            'gambar_url' => $artikel->gambar_url,
            'tanggal'    => $artikel->created_at->toIso8601ZuluString(),
        ];
    }
}
