<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HasilKuis;
use App\Models\Kuis;
use App\Models\Opsi;
use App\Models\Pertanyaan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * AdminKuisController
 *
 * Endpoint (Admin Only):
 *  GET    /admin/kuis                                  — §12.1  KF-06
 *  GET    /admin/kuis/{id}                             — §12.2  KF-06
 *  POST   /admin/kuis                                  — §12.3  KF-06
 *  PUT    /admin/kuis/{id}                             — §12.4  KF-06
 *  DELETE /admin/kuis/{id}                             — §12.5  KF-06
 *  POST   /admin/kuis/{id}/pertanyaan                  — §12.6  KF-06
 *  PUT    /admin/kuis/{id}/pertanyaan/{pertanyaan_id}  — §12.7  KF-06
 *  DELETE /admin/kuis/{id}/pertanyaan/{pertanyaan_id}  — §12.8  KF-06
 *  GET    /admin/kuis/{id}/hasil                       — §12.9  DFD 1.4.5
 */
class KuisController extends Controller
{
    // ─────────────────────────────────────────────
    // §12.1  GET /admin/kuis
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

        $query = Kuis::withCount('pertanyaan as jumlah_pertanyaan')
            ->withSum('pertanyaan as total_poin', 'poin');

        if ($request->filled('q')) {
            $query->where('judul', 'like', '%' . $request->q . '%');
        }

        $sort = $request->query('sort', '-dibuat_pada');
        $query->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');

        $paginator = $query->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(fn ($kuis) => [
            'id'                => $kuis->id,
            'judul'             => $kuis->judul,
            'deskripsi'         => $kuis->deskripsi,
            'jumlah_pertanyaan' => $kuis->jumlah_pertanyaan ?? 0,
            'total_poin'        => $kuis->total_poin ?? 0,
            'dibuat_pada'       => $kuis->created_at->toIso8601ZuluString(),
        ]);

        return $this->suksesDaftar('Daftar kuis (admin) berhasil diambil', $data, $this->buildMeta($paginator));
    }

    // ─────────────────────────────────────────────
    // §12.2  GET /admin/kuis/{id}
    // Detail kuis + pertanyaan + kunci jawaban
    // ─────────────────────────────────────────────
    public function show(int $id): JsonResponse
    {
        $kuis = Kuis::with([
            'pertanyaan' => fn ($q) => $q->orderBy('urutan'),
            'pertanyaan.opsi',
        ])
            ->withCount('pertanyaan as jumlah_pertanyaan')
            ->withSum('pertanyaan as total_poin', 'poin')
            ->find($id);

        if (! $kuis) {
            return $this->error('Kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        $pertanyaan = $kuis->pertanyaan->map(fn ($p) => [
            'id'         => $p->id,
            'urutan'     => $p->urutan,
            'teks'       => $p->teks,
            'poin'       => $p->poin,
            'penjelasan' => $p->penjelasan, // khusus admin
            'opsi'       => $p->opsi->map(fn ($o) => [
                'id'    => $o->id,
                'teks'  => $o->teks,
                'benar' => (bool)$o->benar, // khusus admin
            ])->values(),
        ])->values();

        return $this->sukses('Detail kuis berhasil diambil', [
            'id'                => $kuis->id,
            'judul'             => $kuis->judul,
            'deskripsi'         => $kuis->deskripsi,
            'jumlah_pertanyaan' => $kuis->jumlah_pertanyaan ?? 0,
            'total_poin'        => $kuis->total_poin ?? 0,
            'dibuat_pada'       => $kuis->created_at->toIso8601ZuluString(),
            'diperbarui_pada'   => $kuis->updated_at->toIso8601ZuluString(), // khusus admin
            'pertanyaan'        => $pertanyaan,
        ]);
    }

    // ─────────────────────────────────────────────
    // §12.3  POST /admin/kuis
    // Buat kuis beserta pertanyaannya
    // ─────────────────────────────────────────────
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'judul'                         => 'required|string|max:255',
            'deskripsi'                     => 'nullable|string',
            'pertanyaan'                    => 'required|array|min:1',
            'pertanyaan.*.teks'             => 'required|string',
            'pertanyaan.*.poin'             => 'required|integer|min:1',
            'pertanyaan.*.penjelasan'       => 'nullable|string',
            'pertanyaan.*.opsi'             => 'required|array|min:2',
            'pertanyaan.*.opsi.*.teks'      => 'required|string',
            'pertanyaan.*.opsi.*.benar'     => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        // Validasi: pastikan tiap pertanyaan punya tepat 1 opsi benar
        foreach ($request->pertanyaan as $i => $p) {
            $jumlahBenar = collect($p['opsi'])->where('benar', true)->count();
            if ($jumlahBenar !== 1) {
                return $this->errorValidasi([
                    "pertanyaan.{$i}.opsi" => ["Tiap pertanyaan harus memiliki tepat 1 opsi yang benar (benar = true)"],
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $kuis = Kuis::create([
                'judul'     => $request->judul,
                'deskripsi' => $request->deskripsi,
            ]);

            $urutan = 1;
            foreach ($request->pertanyaan as $p) {
                $pertanyaan = Pertanyaan::create([
                    'kuis_id'    => $kuis->id,
                    'urutan'     => $urutan++,
                    'teks'       => $p['teks'],
                    'poin'       => $p['poin'],
                    'penjelasan' => $p['penjelasan'] ?? null,
                ]);

                foreach ($p['opsi'] as $o) {
                    Opsi::create([
                        'pertanyaan_id' => $pertanyaan->id,
                        'teks'          => $o['teks'],
                        'benar'         => $o['benar'],
                    ]);
                }
            }
            DB::commit();

            // Load kuis secara manual dengan relasi
            return $this->show($kuis->id)->setStatusCode(201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Terjadi kesalahan pada server', 'INTERNAL_SERVER_ERROR', 500);
        }
    }

    // ─────────────────────────────────────────────
    // §12.4  PUT /admin/kuis/{id}
    // Hanya mengubah judul/deskripsi
    // ─────────────────────────────────────────────
    public function update(Request $request, int $id): JsonResponse
    {
        $kuis = Kuis::find($id);

        if (! $kuis) {
            return $this->error('Kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        $validator = Validator::make($request->all(), [
            'judul'     => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $kuis->update([
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
        ]);

        return $this->sukses('Kuis berhasil diperbarui', [
            'id'              => $kuis->id,
            'judul'           => $kuis->judul,
            'deskripsi'       => $kuis->deskripsi,
            'dibuat_pada'     => $kuis->created_at->toIso8601ZuluString(),
            'diperbarui_pada' => $kuis->updated_at->toIso8601ZuluString(),
        ]);
    }

    // ─────────────────────────────────────────────
    // §12.5  DELETE /admin/kuis/{id}
    // ─────────────────────────────────────────────
    public function destroy(int $id): JsonResponse
    {
        $kuis = Kuis::find($id);

        if (! $kuis) {
            return $this->error('Kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        $kuis->delete(); // Pastikan cascade hapus pertanyaan & opsi di DB

        return $this->suksesKosong('Kuis berhasil dihapus');
    }

    // ─────────────────────────────────────────────
    // §12.6  POST /admin/kuis/{id}/pertanyaan
    // ─────────────────────────────────────────────
    public function tambahPertanyaan(Request $request, int $id): JsonResponse
    {
        $kuis = Kuis::find($id);
        if (! $kuis) {
            return $this->error('Kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        $validator = Validator::make($request->all(), [
            'urutan'           => 'required|integer|min:1',
            'teks'             => 'required|string',
            'poin'             => 'required|integer|min:1',
            'penjelasan'       => 'nullable|string',
            'opsi'             => 'required|array|min:2',
            'opsi.*.teks'      => 'required|string',
            'opsi.*.benar'     => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $jumlahBenar = collect($request->opsi)->where('benar', true)->count();
        if ($jumlahBenar !== 1) {
            return $this->errorValidasi([
                "opsi" => ["Tiap pertanyaan harus memiliki tepat 1 opsi yang benar"],
            ]);
        }

        DB::beginTransaction();
        try {
            $pertanyaan = Pertanyaan::create([
                'kuis_id'    => $id,
                'urutan'     => $request->urutan,
                'teks'       => $request->teks,
                'poin'       => $request->poin,
                'penjelasan' => $request->penjelasan,
            ]);

            foreach ($request->opsi as $o) {
                Opsi::create([
                    'pertanyaan_id' => $pertanyaan->id,
                    'teks'          => $o['teks'],
                    'benar'         => $o['benar'],
                ]);
            }
            DB::commit();

            return $this->sukses('Pertanyaan berhasil ditambahkan', $this->formatPertanyaanAdmin($pertanyaan), 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Terjadi kesalahan pada server', 'INTERNAL_SERVER_ERROR', 500);
        }
    }

    // ─────────────────────────────────────────────
    // §12.7  PUT /admin/kuis/{id}/pertanyaan/{pertanyaan_id}
    // ─────────────────────────────────────────────
    public function ubahPertanyaan(Request $request, int $id, int $pertanyaan_id): JsonResponse
    {
        $pertanyaan = Pertanyaan::where('kuis_id', $id)->find($pertanyaan_id);
        if (! $pertanyaan) {
            return $this->error('Pertanyaan tidak ditemukan', 'NOT_FOUND', 404);
        }

        $validator = Validator::make($request->all(), [
            'urutan'           => 'required|integer|min:1',
            'teks'             => 'required|string',
            'poin'             => 'required|integer|min:1',
            'penjelasan'       => 'nullable|string',
            'opsi'             => 'required|array|min:2',
            'opsi.*.teks'      => 'required|string',
            'opsi.*.benar'     => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $jumlahBenar = collect($request->opsi)->where('benar', true)->count();
        if ($jumlahBenar !== 1) {
            return $this->errorValidasi([
                "opsi" => ["Tiap pertanyaan harus memiliki tepat 1 opsi yang benar"],
            ]);
        }

        DB::beginTransaction();
        try {
            $pertanyaan->update([
                'urutan'     => $request->urutan,
                'teks'       => $request->teks,
                'poin'       => $request->poin,
                'penjelasan' => $request->penjelasan,
            ]);

            // Hapus opsi lama
            Opsi::where('pertanyaan_id', $pertanyaan->id)->delete();

            // Masukkan opsi baru
            foreach ($request->opsi as $o) {
                Opsi::create([
                    'pertanyaan_id' => $pertanyaan->id,
                    'teks'          => $o['teks'],
                    'benar'         => $o['benar'],
                ]);
            }
            DB::commit();

            return $this->sukses('Pertanyaan berhasil diperbarui', $this->formatPertanyaanAdmin($pertanyaan));
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Terjadi kesalahan pada server', 'INTERNAL_SERVER_ERROR', 500);
        }
    }

    // ─────────────────────────────────────────────
    // §12.8  DELETE /admin/kuis/{id}/pertanyaan/{pertanyaan_id}
    // ─────────────────────────────────────────────
    public function hapusPertanyaan(int $id, int $pertanyaan_id): JsonResponse
    {
        $pertanyaan = Pertanyaan::where('kuis_id', $id)->find($pertanyaan_id);
        if (! $pertanyaan) {
            return $this->error('Pertanyaan tidak ditemukan', 'NOT_FOUND', 404);
        }

        $pertanyaan->delete();

        return $this->suksesKosong('Pertanyaan berhasil dihapus');
    }

    // ─────────────────────────────────────────────
    // §12.9  GET /admin/kuis/{id}/hasil
    // sort: dikerjakan_pada (default -dikerjakan_pada), atau skor, -skor
    // ─────────────────────────────────────────────
    public function hasil(Request $request, int $id): JsonResponse
    {
        $kuis = Kuis::find($id);
        if (! $kuis) {
            return $this->error('Kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        $params = $this->paginasiParams($request);
        if ($params === null) {
            return $this->errorValidasi([
                'page'  => ['Nilai page harus >= 1'],
                'limit' => ['Nilai limit harus antara 1 dan 50'],
            ]);
        }

        $validator = Validator::make($request->query(), [
            'sort' => 'nullable|string|in:dikerjakan_pada,-dikerjakan_pada,skor,-skor',
        ]);
        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $sort = $request->query('sort', '-dikerjakan_pada');
        
        $query = HasilKuis::with('user')->where('kuis_id', $id);

        if (str_contains($sort, 'skor')) {
            $query->orderBy('skor', str_starts_with($sort, '-') ? 'desc' : 'asc');
        } else {
            $query->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');
        }

        $paginator = $query->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(fn ($h) => [
            'id'                => $h->id,
            'kuis_id'           => $h->kuis_id,
            'kuis_judul'        => $h->kuis_judul,
            'skor'              => $h->skor,
            'skor_maks'         => $h->skor_maks,
            'persentase'        => $h->persentase,
            'jumlah_benar'      => $h->jumlah_benar,
            'jumlah_pertanyaan' => $h->jumlah_pertanyaan,
            'dikerjakan_pada'   => $h->created_at->toIso8601ZuluString(),
            'pengguna'          => [
                'id'        => $h->user->id,
                'nama'      => $h->user->nama,
                'pekerjaan' => $h->user->pekerjaan,
                'usia'      => $h->user->usia,
            ],
        ]);

        return $this->suksesDaftar('Rekap hasil kuis berhasil diambil', $data, $this->buildMeta($paginator));
    }


    // ─────────────────────────────────────────────
    // Helper format
    // ─────────────────────────────────────────────
    private function formatPertanyaanAdmin(Pertanyaan $pertanyaan): array
    {
        $pertanyaan->load('opsi');
        return [
            'id'         => $pertanyaan->id,
            'urutan'     => $pertanyaan->urutan,
            'teks'       => $pertanyaan->teks,
            'poin'       => $pertanyaan->poin,
            'penjelasan' => $pertanyaan->penjelasan,
            'opsi'       => $pertanyaan->opsi->map(fn ($o) => [
                'id'    => $o->id,
                'teks'  => $o->teks,
                'benar' => (bool)$o->benar,
            ])->values(),
        ];
    }
}
