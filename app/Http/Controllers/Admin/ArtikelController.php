<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Artikel;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * AdminArtikelController
 *
 * Endpoint (Admin Only):
 *  POST   /admin/artikel       — §11.1  KF-04
 *  PUT    /admin/artikel/{id}  — §11.2  KF-04
 *  DELETE /admin/artikel/{id}  — §11.3  KF-04
 */
class ArtikelController extends Controller
{
    // ─────────────────────────────────────────────
    // §11.1  POST /admin/artikel
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul'      => 'required|string|max:255',
            'isi'        => 'required|string',
            'sumber'     => 'required|string|max:255',
            'gambar_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $artikel = Artikel::create([
            'judul'      => $request->judul,
            'isi'        => $request->isi,
            'sumber'     => $request->sumber,
            'gambar_url' => $request->gambar_url,
        ]);

        return $this->sukses('Artikel berhasil dibuat', [
            'id'             => $artikel->id,
            'judul'          => $artikel->judul,
            'isi'            => $artikel->isi,
            'sumber'         => $artikel->sumber,
            'gambar_url'     => $artikel->gambar_url,
            'tanggal'        => $artikel->created_at->toIso8601ZuluString(),
            'diperbarui_pada' => $artikel->updated_at->toIso8601ZuluString(),
        ], 201);
    }

    // ─────────────────────────────────────────────
    // §11.2  PUT /admin/artikel/{id}
    // ─────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $artikel = Artikel::find($id);

        if (! $artikel) {
            return $this->error('Artikel tidak ditemukan', 'NOT_FOUND', 404);
        }

        $validator = Validator::make($request->all(), [
            'judul'      => 'required|string|max:255',
            'isi'        => 'required|string',
            'sumber'     => 'required|string|max:255',
            'gambar_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $artikel->update([
            'judul'      => $request->judul,
            'isi'        => $request->isi,
            'sumber'     => $request->sumber,
            'gambar_url' => $request->gambar_url,
        ]);

        return $this->sukses('Artikel berhasil diperbarui', [
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
    // §11.3  DELETE /admin/artikel/{id}
    // ─────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $artikel = Artikel::find($id);

        if (! $artikel) {
            return $this->error('Artikel tidak ditemukan', 'NOT_FOUND', 404);
        }

        $artikel->delete();

        return $this->suksesKosong('Artikel berhasil dihapus');
    }
}
