<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Balasan;
use App\Models\Postingan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * AdminForumController
 *
 * Endpoint (Admin Only):
 *  GET    /admin/forum/postingan       — §13.1  KF-09
 *  DELETE /admin/forum/postingan/{id}  — §13.2  ER Mengolah
 *  DELETE /admin/forum/balasan/{id}    — §13.3  ER Mengolah
 */
class ForumController extends Controller
{
    // ─────────────────────────────────────────────
    // §13.1  GET /admin/forum/postingan
    // Rekap isi forum + info penulis
    // q mencari: judul
    // sort: tanggal (default -tanggal)
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
            'sort' => 'nullable|string|in:tanggal,-tanggal',
        ]);
        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $sort  = $request->query('sort', '-tanggal');
        $query = Postingan::with('penulis')->withCount('balasan as jumlah_balasan');

        if ($request->filled('q')) {
            $query->where('judul', 'like', '%' . $request->q . '%');
        }

        $query->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');

        $paginator = $query->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(function ($p) {
            $isi = $p->isi ?? '';
            $ringkasan = mb_strlen($isi) > 150 ? mb_substr($isi, 0, 150) . '…' : $isi;

            return [
                'id'             => $p->id,
                'judul'          => $p->judul,
                'ringkasan'      => $ringkasan,
                'penulis'        => [
                    'id'        => $p->penulis->id,
                    'nama'      => $p->penulis->nama,
                    'role'      => $p->penulis->role,
                    'pekerjaan' => $p->penulis->pekerjaan, // Tambahan admin
                    'usia'      => $p->penulis->usia,      // Tambahan admin
                ],
                'jumlah_balasan' => $p->jumlah_balasan ?? 0,
                'tanggal'        => $p->created_at->toIso8601ZuluString(),
            ];
        });

        return $this->suksesDaftar('Rekap forum berhasil diambil', $data, $this->buildMeta($paginator));
    }

    // ─────────────────────────────────────────────
    // §13.2  DELETE /admin/forum/postingan/{id}
    // ─────────────────────────────────────────────
    public function hapusPostingan(int $id): JsonResponse
    {
        $postingan = Postingan::find($id);

        if (! $postingan) {
            return $this->error('Postingan tidak ditemukan', 'NOT_FOUND', 404);
        }

        $postingan->delete(); // Pastikan ada cascade hapus balasan di DB/Model

        return $this->suksesKosong('Postingan berhasil dihapus');
    }

    // ─────────────────────────────────────────────
    // §13.3  DELETE /admin/forum/balasan/{id}
    // ─────────────────────────────────────────────
    public function hapusBalasan(int $id): JsonResponse
    {
        $balasan = Balasan::find($id);

        if (! $balasan) {
            return $this->error('Balasan tidak ditemukan', 'NOT_FOUND', 404);
        }

        $balasan->delete();

        return $this->suksesKosong('Balasan berhasil dihapus');
    }
}
