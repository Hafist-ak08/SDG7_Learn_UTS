<?php

namespace App\Http\Controllers;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

/**
 * AuthController
 *
 * Endpoint:
 *  POST /auth/register  (publik)   — §5.1
 *  POST /auth/login     (publik)   — §5.2
 *  POST /auth/refresh   (publik*)  — §5.3
 *  POST /auth/logout    (user/admin) — §5.4
 */
class AuthController extends Controller
{
    // ─────────────────────────────────────────────
    // §5.1  POST /auth/register
    // ─────────────────────────────────────────────
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama'      => 'required|string|max:255',
            'email'     => 'required|email|max:255|unique:users,email',
            'password'  => 'required|string|min:8|confirmed',
            'pekerjaan' => 'nullable|string|max:255',
            'usia'      => 'nullable|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        // Cek duplikat email secara eksplisit (409)
        if (User::where('email', strtolower($request->email))->exists()) {
            return $this->error(
                'Email sudah terdaftar',
                'CONFLICT',
                409
            );
        }

        $user = User::create([
            'nama'      => $request->nama,
            'email'     => strtolower($request->email),
            'password'  => Hash::make($request->password),
            'pekerjaan' => $request->pekerjaan,
            'usia'      => $request->usia,
            'role'      => 'user',
        ]);

        [$accessToken, $refreshToken] = $this->terbitkanToken($user);

        return $this->sukses('Registrasi berhasil', [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'pengguna'      => $this->formatPengguna($user),
        ], 201);
    }

    // ─────────────────────────────────────────────
    // §5.2  POST /auth/login
    // ─────────────────────────────────────────────
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        $user = User::where('email', strtolower($request->email))->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            return $this->error(
                'Email atau password salah',
                'UNAUTHENTICATED',
                401
            );
        }

        [$accessToken, $refreshToken] = $this->terbitkanToken($user);

        return $this->sukses('Login berhasil', [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
            'pengguna'      => $this->formatPengguna($user),
        ]);
    }

    // ─────────────────────────────────────────────
    // §5.3  POST /auth/refresh
    // Tidak memakai header Authorization — yang divalidasi adalah refresh_token di body
    // ─────────────────────────────────────────────
    public function refresh(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        // Cari refresh token yang masih valid (belum kedaluwarsa, belum dipakai)
        $tokenRecord = RefreshToken::where('token', hash('sha256', $request->refresh_token))
            ->where('expired_at', '>', now())
            ->whereNull('dipakai_pada')
            ->with('user')
            ->first();

        if (! $tokenRecord) {
            return $this->error(
                'Refresh token tidak valid atau sudah kedaluwarsa',
                'INVALID_REFRESH_TOKEN',
                401
            );
        }

        // Tandai token lama sebagai sudah dipakai (sekali pakai)
        $tokenRecord->update(['dipakai_pada' => now()]);

        // Terbitkan pasangan token baru
        [$accessToken, $refreshToken] = $this->terbitkanToken($tokenRecord->user);

        return $this->sukses('Token berhasil diperbarui', [
            'access_token'  => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
    }

    // ─────────────────────────────────────────────
    // §5.4  POST /auth/logout
    // ─────────────────────────────────────────────
    public function logout(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'refresh_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->errorValidasi($validator->errors()->toArray());
        }

        // Cabut refresh token yang sesuai milik user ini
        RefreshToken::where('token', hash('sha256', $request->refresh_token))
            ->where('user_id', $request->user()->id)
            ->delete();

        // Cabut access token Sanctum yang sedang dipakai
        $request->user()->currentAccessToken()->delete();

        return $this->suksesKosong('Logout berhasil');
    }

    // ─────────────────────────────────────────────
    // Helper: Terbitkan access token (Sanctum) + refresh token (opaque)
    // Access token berlaku 3600 detik (1 jam), refresh token 7 hari
    // ─────────────────────────────────────────────
    private function terbitkanToken(User $user): array
    {
        // Access token via Laravel Sanctum
        $accessToken = $user->createToken('access', ['*'], now()->addSeconds(3600))
            ->plainTextToken;

        // Refresh token: string acak, disimpan sebagai hash SHA-256
        $plainRefreshToken = Str::random(64);
        RefreshToken::create([
            'user_id'    => $user->id,
            'token'      => hash('sha256', $plainRefreshToken),
            'expired_at' => now()->addDays(7),
        ]);

        return [$accessToken, $plainRefreshToken];
    }

    // ─────────────────────────────────────────────
    // Helper: Format data pengguna sesuai skema §4.1
    // ─────────────────────────────────────────────
    private function formatPengguna(User $user): array
    {
        return [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'pekerjaan'   => $user->pekerjaan,
            'usia'        => $user->usia,
            'role'        => $user->role,
            'dibuat_pada' => $user->created_at->toIso8601ZuluString(),
        ];
    }
}
