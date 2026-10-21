<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UjianCountdown;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UjianCountdownController extends Controller
{
    public function index()
    {
        $countdowns = UjianCountdown::orderBy('start_date', 'desc')->get();
        
        // Update status untuk setiap countdown
        foreach ($countdowns as $countdown) {
            $countdown->updateStatus();
        }
        
        return view('admin.ujian-countdown.index', compact('countdowns'));
    }

    public function create()
    {
        return view('admin.ujian-countdown.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'ujian_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'nullable|boolean'
        ]);

        DB::transaction(function () use ($request) {
            // Jika ada countdown aktif, nonaktifkan
            if ($request->has('is_active') && $request->is_active) {
                UjianCountdown::where('is_active', true)->update(['is_active' => false]);
            }

            $countdown = UjianCountdown::create([
                'ujian_name' => $request->ujian_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active ?? false,
                'status' => 'upcoming'
            ]);

            $countdown->updateStatus();
        });

        return redirect()->route('admin.ujian-countdown.index')
            ->with('success', 'Countdown ujian berhasil dibuat.');
    }

    public function show(UjianCountdown $ujianCountdown)
    {
        $ujianCountdown->updateStatus();
        return view('admin.ujian-countdown.show', compact('ujianCountdown'));
    }

    public function edit(UjianCountdown $ujianCountdown)
    {
        return view('admin.ujian-countdown.edit', compact('ujianCountdown'));
    }

    public function update(Request $request, UjianCountdown $ujianCountdown)
    {
        $request->validate([
            'ujian_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'nullable|boolean'
        ]);

        DB::transaction(function () use ($request, $ujianCountdown) {
            // Jika mengaktifkan countdown ini, nonaktifkan yang lain
            if ($request->has('is_active') && $request->is_active && !$ujianCountdown->is_active) {
                UjianCountdown::where('is_active', true)
                    ->where('id', '!=', $ujianCountdown->id)
                    ->update(['is_active' => false]);
            }

            $ujianCountdown->update([
                'ujian_name' => $request->ujian_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'is_active' => $request->is_active ?? $ujianCountdown->is_active
            ]);

            $ujianCountdown->updateStatus();
        });

        return redirect()->route('admin.ujian-countdown.index')
            ->with('success', 'Countdown ujian berhasil diperbarui.');
    }

    public function destroy(UjianCountdown $ujianCountdown)
    {
        $ujianCountdown->delete();
        
        return redirect()->route('admin.ujian-countdown.index')
            ->with('success', 'Countdown ujian berhasil dihapus.');
    }

    public function toggleActive(UjianCountdown $ujianCountdown)
    {
        DB::transaction(function () use ($ujianCountdown) {
            if ($ujianCountdown->is_active) {
                $ujianCountdown->update(['is_active' => false]);
            } else {
                // Nonaktifkan semua countdown terlebih dahulu
                UjianCountdown::where('is_active', true)->update(['is_active' => false]);
                $ujianCountdown->update(['is_active' => true]);
            }
        });

        return response()->json(['success' => true]);
    }
    
    // Method tambahan jika diperlukan
    public function activate(UjianCountdown $ujianCountdown)
    {
        DB::transaction(function () use ($ujianCountdown) {
            UjianCountdown::where('is_active', true)->update(['is_active' => false]);
            $ujianCountdown->update(['is_active' => true]);
        });
        
        return redirect()->back()->with('success', 'Countdown berhasil diaktifkan.');
    }
    
    public function deactivate(UjianCountdown $ujianCountdown)
    {
        $ujianCountdown->update(['is_active' => false]);
        return redirect()->back()->with('success', 'Countdown berhasil dinonaktifkan.');
    }
}