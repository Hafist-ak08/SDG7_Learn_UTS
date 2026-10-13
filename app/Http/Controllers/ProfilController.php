<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * ProfilController
 *
 * Endpoint:
 *  GET /profil           (user, admin) — §6.1
 *  PUT /profil           (user, admin) — §6.2
 *  PUT /profil/password  (user, admin) — §6.3
 */
class ProfilController extends Controller
{
    // ─────────────────────────────────────────────
    // §6.1  GET /profil
    // ─────────────────────────────────────────────
    public function show(Request $request): JsonResponse
    {
        $user = $request->user();

        return $this->sukses('Profil berhasil diambil', [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'pekerjaan'   => $user->pekerjaan,
            'usia'        => $user->usia,
            'role'        => $user->role,
            'dibuat_pada' => $user->created_at->toIso8601ZuluString(),
        ]);
    }

    // ─────────────────────────────────────────────
    // §6.2  PUT /profil
    // ─────────────────────────────────────────────
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama'      => 'required|string|max:255',
            'pekerjaan' => 'nullable|string|max:255',
            'usia'      => 'nullable|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $user = $request->user();
        $user->update([
            'nama'      => $request->nama,
            'pekerjaan' => $request->pekerjaan,
            'usia'      => $request->usia,
        ]);

        return $this->sukses('Profil berhasil diperbarui', [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'pekerjaan'   => $user->pekerjaan,
            'usia'        => $user->usia,
            'role'        => $user->role,
            'dibuat_pada' => $user->created_at->toIso8601ZuluString(),
        ]);
    }

    // ─────────────────────────────────────────────
    // §6.3  PUT /profil/password
    // ─────────────────────────────────────────────
    public function ubahPassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'password_lama'          => 'required|string',
            'password_baru'          => 'required|string|min:8|confirmed',
            'password_baru_confirmation' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $user = $request->user();

        if (! Hash::check($request->password_lama, $user->password)) {
            return $this->errorValidasi([
                'password_lama' => ['Password lama tidak sesuai'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->password_baru),
        ]);

        return $this->suksesKosong('Password berhasil diganti');
    }
}
