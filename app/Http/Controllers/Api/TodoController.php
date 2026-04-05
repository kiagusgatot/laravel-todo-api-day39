<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TodoController extends Controller
{
    // Mengambil semua Todo milik user dengan fitur filter status
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->todos();

        // Implementasi Filter berdasarkan Query Parameter (?status=completed/pending)
        if ($request->has('status')) {
            if ($request->status === 'completed') {
                $query->completed(); // Memanggil local scope di Model
            } elseif ($request->status === 'pending') {
                $query->pending(); // Memanggil local scope di Model
            }
        }

        // Pengurutan: yang tenggat waktunya paling dekat, lalu yang terbaru dibuat
        $query->orderBy('due_date', 'asc')->orderBy('created_at', 'desc');

        $todos = $query->get();

        return response()->json([
            'success' => true,
            'message' => 'List todos',
            'data' => $todos
        ]);
    }

    // Membuat Todo baru
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date|after_or_equal:today', // Tidak bisa input tanggal masa lalu
        ]);

        $todo = $request->user()->todos()->create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Todo berhasil ditambahkan',
            'data' => $todo
        ], 201);
    }

    // Menampilkan detail satu Todo
    public function show(Request $request, $id): JsonResponse
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'success' => false,
                'message' => 'Todo tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail todo',
            'data' => $todo
        ]);
    }

    // Mengubah data Todo
    public function update(Request $request, $id): JsonResponse
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'success' => false,
                'message' => 'Todo tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'is_completed' => 'sometimes|boolean',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
        ]);

        $todo->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Todo berhasil diperbarui',
            'data' => $todo
        ]);
    }

    // Menghapus Todo
    public function destroy(Request $request, $id): JsonResponse
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'success' => false,
                'message' => 'Todo tidak ditemukan'
            ], 404);
        }

        $todo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Todo berhasil dihapus'
        ]);
    }

    // Mengubah status is_completed secara cepat (Toggle)
    public function toggle(Request $request, $id): JsonResponse
    {
        $todo = $request->user()->todos()->find($id);

        if (!$todo) {
            return response()->json([
                'success' => false,
                'message' => 'Todo tidak ditemukan'
            ], 404);
        }

        // Membalikkan nilai boolean (true jadi false, false jadi true)
        $todo->update([
            'is_completed' => !$todo->is_completed
        ]);

        $status = $todo->is_completed ? 'selesai' : 'belum selesai';

        return response()->json([
            'success' => true,
            'message' => "Todo status diubah menjadi {$status}",
            'data' => $todo
        ]);
    }
}