<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;

abstract class Controller
{
    /**
     * Response sukses — satu objek.
     */
    protected function sukses(string $message, mixed $data, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }

    /**
     * Response sukses — daftar berpaginasi.
     */
    protected function suksesDaftar(string $message, mixed $data, array $meta): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
            'meta'    => $meta,
        ], 200);
    }

    /**
     * Response sukses tanpa data (DELETE, logout, dsb.).
     */
    protected function suksesKosong(string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => null,
        ], 200);
    }

    /**
     * Response error generik.
     */
    protected function error(string $message, string $code, int $status, array $details = []): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error'   => [
                'code'    => $code,
                'details' => $details,
            ],
        ], $status);
    }

    /**
     * Response error validasi (422) dengan format details [{ field, message }].
     */
    protected function errorValidasi(array $errors): JsonResponse
    {
        $details = [];
        foreach ($errors as $field => $messages) {
            foreach ((array) $messages as $msg) {
                $details[] = ['field' => $field, 'message' => $msg];
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Data yang dikirim tidak valid',
            'error'   => [
                'code'    => 'VALIDATION_ERROR',
                'details' => $details,
            ],
        ], 422);
    }

    /**
     * Bangun meta paginasi dari LengthAwarePaginator.
     */
    protected function buildMeta(\Illuminate\Contracts\Pagination\LengthAwarePaginator $paginator): array
    {
        return [
            'page'        => $paginator->currentPage(),
            'limit'       => $paginator->perPage(),
            'total_items' => $paginator->total(),
            'total_pages' => $paginator->lastPage(),
        ];
    }

    /**
     * Validasi dan normalisasi parameter paginasi dari request.
     * Mengembalikan ['page' => int, 'limit' => int] atau null jika tidak valid.
     */
    protected function paginasiParams(\Illuminate\Http\Request $request): array|null
    {
        $page  = (int) $request->query('page', 1);
        $limit = (int) $request->query('limit', 10);

        if ($page < 1 || $limit < 1 || $limit > 50) {
            return null;
        }

        return ['page' => $page, 'limit' => $limit];
    }
}
