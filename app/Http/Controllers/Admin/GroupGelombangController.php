<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\GroupGelombang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupGelombangController extends Controller
{
    public function index()
    {
        $gelombangs = GroupGelombang::orderBy('urutan')->orderBy('gelombang')->get();
        
        // Data untuk chart/stats
        $stats = [
            'total' => $gelombangs->count(),
            'active' => $gelombangs->where('is_active', true)->count(),
            'whatsapp' => $gelombangs->where('tipe_group', 'whatsapp')->count(),
            'telegram' => $gelombangs->where('tipe_group', 'telegram')->count(),
        ];
        
        return view('admin.group.index', compact('gelombangs', 'stats'));
    }

    public function create()
    {
        // Generate gelombang yang belum ada
        $existingGelombangs = GroupGelombang::pluck('gelombang')->toArray();
        $availableGelombangs = array_diff(range(1, 10), $existingGelombangs);
        
        return view('admin.group.create', compact('availableGelombangs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'gelombang' => 'required|integer|between:1,10|unique:group_gelombang,gelombang',
            'nama_gelombang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_group' => 'required|url|max:500',
            'tipe_group' => 'required|in:whatsapp,telegram,discord,zoom,meet',
            'is_active' => 'nullable|boolean',
            'urutan' => 'required|integer|min:1'
        ]);

        GroupGelombang::create([
            'gelombang' => $request->gelombang,
            'nama_gelombang' => $request->nama_gelombang,
            'deskripsi' => $request->deskripsi,
            'link_group' => $request->link_group,
            'tipe_group' => $request->tipe_group,
            'is_active' => $request->is_active ?? true,
            'urutan' => $request->urutan
        ]);

        return redirect()->route('admin.group-gelombang.index')
            ->with('success', 'Group gelombang berhasil ditambahkan.');
    }

    public function show(GroupGelombang $groupGelombang)
    {
        return view('admin.group.show', compact('groupGelombang'));
    }

    public function edit(GroupGelombang $groupGelombang)
    {
        return view('admin.group.edit', compact('groupGelombang'));
    }

    public function update(Request $request, GroupGelombang $groupGelombang)
    {
        $request->validate([
            'gelombang' => 'required|integer|between:1,10|unique:group_gelombang,gelombang,' . $groupGelombang->id,
            'nama_gelombang' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'link_group' => 'required|url|max:500',
            'tipe_group' => 'required|in:whatsapp,telegram,discord,zoom,meet',
            'is_active' => 'nullable|boolean',
            'urutan' => 'required|integer|min:1'
        ]);

        $groupGelombang->update([
            'gelombang' => $request->gelombang,
            'nama_gelombang' => $request->nama_gelombang,
            'deskripsi' => $request->deskripsi,
            'link_group' => $request->link_group,
            'tipe_group' => $request->tipe_group,
            'is_active' => $request->is_active ?? $groupGelombang->is_active,
            'urutan' => $request->urutan
        ]);

        return redirect()->route('admin.group-gelombang.index')
            ->with('success', 'Group gelombang berhasil diperbarui.');
    }

    public function destroy(GroupGelombang $groupGelombang)
    {
        $groupGelombang->delete();
        
        return redirect()->route('admin.group-gelombang.index')
            ->with('success', 'Group gelombang berhasil dihapus.');
    }

    public function toggleActive(GroupGelombang $groupGelombang)
    {
        $groupGelombang->update([
            'is_active' => !$groupGelombang->is_active
        ]);

        return response()->json([
            'success' => true,
            'is_active' => $groupGelombang->is_active
        ]);
    }

    public function updateOrder(Request $request)
    {
        $request->validate([
            'order' => 'required|array'
        ]);

        DB::transaction(function () use ($request) {
            foreach ($request->order as $index => $id) {
                GroupGelombang::where('id', $id)->update(['urutan' => $index + 1]);
            }
        });

        return response()->json(['success' => true]);
    }

    // Bulk create gelombang 1-10
    public function bulkCreate()
    {
        $existingGelombangs = GroupGelombang::pluck('gelombang')->toArray();
        
        $defaultData = [
            ['gelombang' => 1, 'nama' => 'Gelombang 1 - Early Bird'],
            ['gelombang' => 2, 'nama' => 'Gelombang 2 - Regular'],
            ['gelombang' => 3, 'nama' => 'Gelombang 3 - Regular'],
            ['gelombang' => 4, 'nama' => 'Gelombang 4 - Regular'],
            ['gelombang' => 5, 'nama' => 'Gelombang 5 - Regular'],
            ['gelombang' => 6, 'nama' => 'Gelombang 6 - Akhir'],
            ['gelombang' => 7, 'nama' => 'Gelombang 7 - Akhir'],
            ['gelombang' => 8, 'nama' => 'Gelombang 8 - Khusus'],
            ['gelombang' => 9, 'nama' => 'Gelombang 9 - Khusus'],
            ['gelombang' => 10, 'nama' => 'Gelombang 10 - Final'],
        ];

        $created = 0;
        foreach ($defaultData as $data) {
            if (!in_array($data['gelombang'], $existingGelombangs)) {
                GroupGelombang::create([
                    'gelombang' => $data['gelombang'],
                    'nama_gelombang' => $data['nama'],
                    'deskripsi' => 'Group untuk peserta gelombang ' . $data['gelombang'],
                    'link_group' => '#',
                    'tipe_group' => 'whatsapp',
                    'is_active' => true,
                    'urutan' => $data['gelombang']
                ]);
                $created++;
            }
        }

        return redirect()->route('admin.group-gelombang.index')
            ->with('success', "Berhasil membuat {$created} gelombang default.");
    }
}