<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Npc;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class NpcController extends Controller
{
    /**
     * Menampilkan daftar NPC dengan fitur Pencarian dan Pagination
     * Endpoint: GET /api/npcs?search=...&page=1
     */
    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        // Query pencarian berdasarkan nama atau peran (role)
        $npcs = Npc::query()
            ->when($search, function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('role', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(5) // Pagination 5 data per halaman
            ->withQueryString();

        return response()->json($npcs);
    }

    /**
     * Menyimpan data NPC baru beserta upload file gambar
     * Endpoint: POST /api/npcs
     */
    public function store(Request $request): JsonResponse
    {
        // Validasi input
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'role'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048', // maks 2MB
        ]);

        // Proses simpan file gambar ke storage/app/public/npcs
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('npcs', 'public');
        }

        // Simpan ke database
        $npc = Npc::create([
            'name'        => $validated['name'],
            'role'        => $validated['role'],
            'description' => $validated['description'] ?? null,
            'image'       => $imagePath,
        ]);

        return response()->json([
            'message' => 'NPC berhasil ditambahkan!',
            'data'    => $npc,
        ], 201);
    }

    /**
     * Menampilkan detail satu data NPC
     * Endpoint: GET /api/npcs/{id}
     */
    public function show(Npc $npc): JsonResponse
    {
        return response()->json([
            'data' => $npc,
        ]);
    }

    /**
     * Memperbarui data NPC dan mengganti gambar lama jika ada gambar baru
     * Endpoint: POST /api/npcs/{id} atau PUT /api/npcs/{id}
     */
    public function update(Request $request, Npc $npc): JsonResponse
    {
        // Validasi input
        $validated = $request->validate([
            'name'        => 'required|string|max:255',
            'role'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Jika ada unggahan gambar baru
        if ($request->hasFile('image')) {
            // Hapus file gambar lama dari disk jika ada
            if ($npc->image && Storage::disk('public')->exists($npc->image)) {
                Storage::disk('public')->delete($npc->image);
            }
            // Simpan gambar baru
            $npc->image = $request->file('image')->store('npcs', 'public');
        }

        // Update data teks
        $npc->name = $validated['name'];
        $npc->role = $validated['role'];
        $npc->description = $validated['description'] ?? null;
        $npc->save();

        return response()->json([
            'message' => 'NPC berhasil diperbarui!',
            'data'    => $npc,
        ]);
    }

    /**
     * Menghapus data NPC dari database dan menghapus file gambar dari storage
     * Endpoint: DELETE /api/npcs/{id}
     */
    public function destroy(Npc $npc): JsonResponse
    {
        // Hapus file gambar jika tersimpan
        if ($npc->image && Storage::disk('public')->exists($npc->image)) {
            Storage::disk('public')->delete($npc->image);
        }

        // Hapus data dari database
        $npc->delete();

        return response()->json([
            'message' => 'NPC berhasil dihapus!',
        ]);
    }
}
