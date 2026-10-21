<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request; // <-- Pastikan ini ada
use App\Models\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon; // <-- [BARU] Tambahkan ini

class EventController extends Controller
{
    /**
     * [MODIFIKASI TOTAL] Ambil event berdasarkan rentang tanggal dari FullCalendar.
     */
    public function index(Request $request)
    {
        // 1. Validasi input dari FullCalendar
        $request->validate([
            'start' => 'required|date',
            'end'   => 'required|date|after:start',
        ]);

        try {
            // 2. Parse tanggal start dan end menggunakan Carbon
            $startDate = Carbon::parse($request->input('start'));
            $endDate   = Carbon::parse($request->input('end'));

            // 3. Ambil event dari DB yang berada di rentang tanggal
            // Logika: Event yang 'mulai' SEBELUM 'end' view
            // DAN 'selesai' SETELAH 'start' view
            $userEvents = Event::where('start', '<', $endDate)
                                ->where('end', '>', $startDate)
                                ->get();

            // 4. Siapkan collection untuk hari libur
            $holidayEvents = collect();
            
            // 5. Ambil data API untuk tahun/tahun-tahun yang relevan
            // Ambil tahun unik dari rentang tanggal (menangani kasus ganti tahun Des-Jan)
            $startYear = $startDate->year;
            $endYear   = $endDate->year;
            
            $yearsToFetch = [$startYear];
            if ($startYear !== $endYear) {
                $yearsToFetch[] = $endYear;
            }

            foreach ($yearsToFetch as $year) {
                $response = Http::get("https://api-harilibur.vercel.app/api?year={$year}");

                if ($response->successful()) {
                    $apiData = $response->json();

                    // Cek keamanan jika API return 'null'
                    if (is_array($apiData)) {
                        foreach ($apiData as $day) {
                            if (!isset($day['holiday_name']) || !isset($day['holiday_date'])) {
                                continue;
                            }
                            
                            $eventColor = '#ef4444'; 
                            $eventType  = 'observance';
                            if (isset($day['is_national_holiday']) && $day['is_national_holiday']) {
                                $eventColor = '#ef4444'; 
                                $eventType  = 'holiday';
                            }

                            $holidayEvents->push([
                                'title'     => $day['holiday_name'],
                                'start'     => $day['holiday_date'],
                                'allDay'    => true,
                                'color'     => $eventColor,
                                'textColor' => '#ffffff',
                                'editable'  => false,
                                'type'      => $eventType
                            ]);
                        }
                    }
                }
            } 
            $allEvents = $userEvents->toBase()->merge($holidayEvents);

            return response()->json($allEvents);

        } catch (\Exception $e) {
            Log::error('Error fetching calendar events: ' . $e->getMessage());
            return response()->json([
                'message' => 'Gagal memuat data event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Simpan event baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $event = Event::create($validated);

        return response()->json([
            'message' => 'Event berhasil disimpan!',
            'event' => $event
        ]);
    }

    // Update event yang sudah ada
    public function update(Request $request, $id)
    {
        $event = Event::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start' => 'required|date',
            'end'   => 'required|date|after_or_equal:start',
        ]);

        $event->update($validated);

        return response()->json([
            'message' => 'Event berhasil diperbarui!',
            'event' => $event
        ]);
    }

    // Hapus event
    public function destroy($id)
    {
        $event = Event::findOrFail($id);
        $event->delete();

        return response()->json(['success' => true]);
    }
}