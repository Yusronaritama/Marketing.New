<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\MasterEreg;
use App\Models\MasterMoodleLulus;
use App\Models\MasterUser;

class SyncMoodleLulusCommand extends Command
{
    protected $signature = 'pmb:sync-moodle-lulus';
    protected $description = 'Sinkron kelulusan, handle swap jurusan1 & 2 saat duplikat, dan sinkron mutlak jurusan_lulusan ke jurusan1';

    public function handle()
    {
        $this->info('Memulai proses sinkronisasi kelulusan...');
        
        try {
            // Ambil semua data Moodle dan kelompokkan berdasarkan user
            $moodleData = MasterMoodleLulus::all();
            $moodleGroups = $moodleData->groupBy('master_user_id');
            
            $updatedCount = 0;
            $this->info("Ditemukan " . $moodleData->count() . " baris data dari " . $moodleGroups->count() . " user di Moodle Lulus.");

            foreach ($moodleGroups as $masterUserId => $records) {
                if (!$masterUserId) continue;

                $masterUser = MasterUser::find($masterUserId);
                if (!$masterUser) continue;

                $realUserId = $masterUser->user_id;

                $ereg = MasterEreg::where('user_id', $realUserId)->first();
                if (!$ereg) continue;

                // Urutkan Moodle untuk mendapatkan prodi kelulusan terbaru
                $latestMoodle = $records->sortByDesc('updated_at')->first();
                $latestProdiMoodle = $latestMoodle->prodi_lulus;
                
                $isDuplicateCase = $records->count() > 1;
                $updateData = [];
                $logMessage = "";

                // ========================================================
                // 1. LOGIC TUKAR POSISI JIKA DUPLIKAT (LULUS JURUSAN 2)
                // ========================================================
                // Cek: Jika duplikat, dan jurusan2 saat ini = prodi lulusan Moodle.
                // (Ini mencegah script menukar berkali-kali setiap kali dijalankan)
                if ($isDuplicateCase && !empty($latestProdiMoodle)) {
                    if ($ereg->jurusan2 === $latestProdiMoodle && $ereg->jurusan1 !== $latestProdiMoodle) {
                        
                        // Siapkan data tukar posisi
                        $updateData['jurusan1'] = $ereg->jurusan2;
                        $updateData['jurusan2'] = $ereg->jurusan1;
                        
                        // Update variabel Ereg di memori agar logic di bawahnya pakai data yang baru ditukar
                        $ereg->jurusan1 = $updateData['jurusan1'];
                        $ereg->jurusan2 = $updateData['jurusan2'];
                        
                        $logMessage .= "<fg=cyan>Swap Duplikat: User ID {$realUserId} ditukar (Jurusan 1 naik jadi {$ereg->jurusan1}).</>";
                    }
                }

                // ========================================================
                // 2. LOGIC MUTLAK: JURUSAN LULUSAN = JURUSAN 1
                // ========================================================
                // Berlaku untuk: Sinkron awal, update setelah di-swap, ATAU ganti prodi manual pasca ujian
                if (empty($ereg->jurusan_lulusan) || $ereg->jurusan_lulusan !== $ereg->jurusan1) {
                    if (!empty($ereg->jurusan1)) {
                        
                        $updateData['jurusan_lulusan'] = $ereg->jurusan1;
                        $updateData['status_lulus'] = 'LULUS';
                        
                        // Rapihin text log di terminal
                        if ($logMessage === "") {
                            $logMessage = "<fg=green>Sync Prodi: User ID {$realUserId} jurusan_lulusan disinkron ke {$ereg->jurusan1}.</>";
                        } else {
                            $logMessage .= " <fg=green>Dan jurusan_lulusan disinkron mengikuti jurusan1.</>";
                        }
                    }
                }

                // ========================================================
                // EKSEKUSI UPDATE DATABASE (SEKALI JALAN SAJA PER USER)
                // ========================================================
                if (!empty($updateData)) {
                    // Pakai query update agar langsung tembak ke DB
                    MasterEreg::where('id', $ereg->id)->update($updateData);
                    $updatedCount++;
                    $this->line($logMessage);
                }
            }

            $this->info("Sinkronisasi selesai! Berhasil memperbarui {$updatedCount} data maba.");

        } catch (\Exception $e) {
            $this->error('Terjadi kesalahan saat sinkronisasi: ' . $e->getMessage());
        }

        return Command::SUCCESS;
    }
}