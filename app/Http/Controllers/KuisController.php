<?php

namespace App\Http\Controllers;

use App\Models\HasilKuis;
use App\Models\Kuis;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

/**
 * KuisController  (sisi user)
 *
 * Endpoint:
 *  GET  /kuis                  (user, admin) — §8.1  KF-02
 *  GET  /kuis/{id}             (user, admin) — §8.2  KF-02
 *  POST /kuis/{id}/percobaan   (user)        — §8.3  KF-02, KF-07
 */
class KuisController extends Controller
{
    // ─────────────────────────────────────────────
    // §8.1  GET /kuis
    // q mencari: judul
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

        $userId = $request->user()->id;

        $query = Kuis::withCount('pertanyaan as jumlah_pertanyaan')
            ->withSum('pertanyaan as total_poin', 'poin');

        if ($request->filled('q')) {
            $query->where('judul', 'like', '%' . $request->q . '%');
        }

        $sort = $request->query('sort', '-dibuat_pada');
        $query->orderBy('created_at', str_starts_with($sort, '-') ? 'desc' : 'asc');

        $paginator = $query->paginate($params['limit'], ['*'], 'page', $params['page']);

        $data = $paginator->getCollection()->map(function (Kuis $kuis) use ($userId) {
            // Cek apakah user pernah mengerjakan kuis ini
            $hasilTerbaik = HasilKuis::where('kuis_id', $kuis->id)
                ->where('user_id', $userId)
                ->orderByDesc('skor')
                ->first();

            return [
                'id'                => $kuis->id,
                'judul'             => $kuis->judul,
                'deskripsi'         => $kuis->deskripsi,
                'jumlah_pertanyaan' => $kuis->jumlah_pertanyaan ?? 0,
                'total_poin'        => $kuis->total_poin ?? 0,
                'sudah_dikerjakan'  => $hasilTerbaik !== null,
                'skor_terbaik'      => $hasilTerbaik?->skor,
                'dibuat_pada'       => $kuis->created_at->toIso8601ZuluString(),
            ];
        });

        return $this->suksesDaftar('Daftar kuis berhasil diambil', $data, $this->buildMeta($paginator));
    }

    // ─────────────────────────────────────────────
    // §8.2  GET /kuis/{id}
    // Detail kuis + pertanyaan tanpa kunci jawaban (field 'benar' tidak ditampilkan)
    // ─────────────────────────────────────────────
    public function show(Request $request, int $id): JsonResponse
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

        $userId       = $request->user()->id;
        $hasilTerbaik = HasilKuis::where('kuis_id', $id)
            ->where('user_id', $userId)
            ->orderByDesc('skor')
            ->first();

        $pertanyaan = $kuis->pertanyaan->map(fn ($p) => [
            'id'     => $p->id,
            'urutan' => $p->urutan,
            'teks'   => $p->teks,
            'poin'   => $p->poin,
            // Opsi tanpa field 'benar' (sisi user)
            'opsi'   => $p->opsi->map(fn ($o) => [
                'id'   => $o->id,
                'teks' => $o->teks,
            ])->values(),
        ])->values();

        return $this->sukses('Detail kuis berhasil diambil', [
            'id'                => $kuis->id,
            'judul'             => $kuis->judul,
            'deskripsi'         => $kuis->deskripsi,
            'jumlah_pertanyaan' => $kuis->jumlah_pertanyaan ?? 0,
            'total_poin'        => $kuis->total_poin ?? 0,
            'sudah_dikerjakan'  => $hasilTerbaik !== null,
            'skor_terbaik'      => $hasilTerbaik?->skor,
            'dibuat_pada'       => $kuis->created_at->toIso8601ZuluString(),
            'pertanyaan'        => $pertanyaan,
        ]);
    }

    // ─────────────────────────────────────────────
    // §8.3  POST /kuis/{id}/percobaan
    // Kirim jawaban, hitung skor, simpan HasilKuis + Tinjauan (snapshot)
    // ─────────────────────────────────────────────
    public function kerjakan(Request $request, int $id): JsonResponse
    {
        $kuis = Kuis::with(['pertanyaan.opsi'])->find($id);

        if (! $kuis) {
            return $this->error('Kuis tidak ditemukan', 'NOT_FOUND', 404);
        }

        // Validasi: jawaban harus berupa array dengan panjang >= jumlah pertanyaan
        $validator = Validator::make($request->all(), [
            'jawaban'                  => 'required|array',
            'jawaban.*.pertanyaan_id'  => 'required|integer',
            'jawaban.*.opsi_id'        => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        // Susun peta jawaban: pertanyaan_id => opsi_id
        $jawabanMap = collect($request->jawaban)
            ->keyBy('pertanyaan_id')
            ->map(fn ($j) => $j['opsi_id'] ?? null);

        DB::beginTransaction();
        try {
            $skor            = 0;
            $jumlahBenar     = 0;
            $totalPoin       = 0;
            $tinjauan        = [];

            foreach ($kuis->pertanyaan->sortBy('urutan') as $pertanyaan) {
                $opsiBenar  = $pertanyaan->opsi->firstWhere('benar', true);
                $opsiDipilihId = $jawabanMap->get($pertanyaan->id);
                $opsiDipilih = $opsiDipilihId !== null ? $pertanyaan->opsi->firstWhere('id', $opsiDipilihId) : null;
                $benar      = $opsiDipilihId !== null && $opsiDipilihId === $opsiBenar?->id;
                $poinDiperoleh = $benar ? $pertanyaan->poin : 0;

                $skor        += $poinDiperoleh;
                $totalPoin   += $pertanyaan->poin;
                if ($benar) {
                    $jumlahBenar++;
                }

                $tinjauan[] = [
                    'pertanyaan_id'  => $pertanyaan->id,
                    'urutan'         => $pertanyaan->urutan,
                    'teks'           => $pertanyaan->teks,
                    'poin'           => $pertanyaan->poin,
                    'opsi'           => $pertanyaan->opsi->map(fn ($o) => ['id' => $o->id, 'teks' => $o->teks])->values()->toArray(),
                    'opsi_dipilih_id' => $opsiDipilihId,
                    'opsi_dipilih'   => $opsiDipilih?->teks,
                    'opsi_benar_id'  => $opsiBenar?->id,
                    'opsi_benar'     => $opsiBenar?->teks,
                    'benar'          => $benar,
                    'poin_diperoleh' => $poinDiperoleh,
                    'penjelasan'     => $pertanyaan->penjelasan,
                ];
            }

            $persentase = $totalPoin > 0
                ? round(($skor / $totalPoin) * 100, 2)
                : 0;

            $hasil = HasilKuis::create([
                'user_id'           => $request->user()->id,
                'kuis_id'           => $kuis->id,
                'kuis_judul'        => $kuis->judul,       // snapshot judul
                'skor'              => $skor,
                'skor_maks'         => $totalPoin,
                'persentase'        => $persentase,
                'jumlah_benar'      => $jumlahBenar,
                'jumlah_pertanyaan' => $kuis->pertanyaan->count(),
                'tinjauan'          => $tinjauan,
            ]);

            DB::commit();

            return $this->sukses('Jawaban kuis berhasil dikirim', [
                'id'                => $hasil->id,
                'kuis_id'           => $hasil->kuis_id,
                'kuis_judul'        => $hasil->kuis_judul,
                'skor'              => $hasil->skor,
                'skor_maks'         => $hasil->skor_maks,
                'persentase'        => $hasil->persentase,
                'jumlah_benar'      => $hasil->jumlah_benar,
                'jumlah_pertanyaan' => $hasil->jumlah_pertanyaan,
                'dikerjakan_pada'   => $hasil->created_at->toIso8601ZuluString(),
                'tinjauan'          => $tinjauan,
            ], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            return $this->error('Terjadi kesalahan pada server', 'INTERNAL_SERVER_ERROR', 500);
        }
    }
}
