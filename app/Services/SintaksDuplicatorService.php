<?php

namespace App\Services;

use App\Models\SintaksBelajar;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SintaksDuplicatorService
{
    /**
     * Duplicate an entire learning syntax (deep copy) from one meeting to another.
     * 
     * @param string $sourceSintaksId ID of the SintaksBelajar to clone
     * @param string $targetPertemuanId ID of the Pertemuan where the clone will be attached
     * @return SintaksBelajar|false Returns the newly cloned SintaksBelajar, or false on failure
     */
    public function duplicateSintaks($sourceSintaksId, $targetPertemuanId)
    {
        try {
            DB::beginTransaction();

            // 1. Get the original SintaksBelajar
            $originalSintaks = SintaksBelajar::with([
                'tahapanSintaks.kegiatan',
                'tahapanSintaks.materis',
                'tahapanSintaks.masterSoal.mainSoal.soal.pilihanGanda',
                'tahapanSintaks.masterSoal.mainSoal.soal.kunciJawaban'
            ])->findOrFail($sourceSintaksId);

            // 2. Clone the main SintaksBelajar
            $newSintaks = $originalSintaks->replicate();
            $newSintaks->pertemuan_id = $targetPertemuanId;
            $newSintaks->save();

            // 3. Loop through Tahapan Sintaks
            foreach ($originalSintaks->tahapanSintaks as $tahapan) {
                $newTahapan = $tahapan->replicate();
                $newTahapan->sintaks_belajar_id = $newSintaks->sintaks_belajar_id;
                $newTahapan->save();

                // 3a. Clone Kegiatan
                foreach ($tahapan->kegiatan as $kegiatan) {
                    $newKegiatan = $kegiatan->replicate();
                    $newKegiatan->tahapan_sintaks_id = $newTahapan->tahapan_sintaks_id;
                    $newKegiatan->save();
                }

                // 3b. Clone Materi
                foreach ($tahapan->materis as $materi) {
                    $newMateri = $materi->replicate();
                    $newMateri->tahapan_sintaks_id = $newTahapan->tahapan_sintaks_id;
                    $newMateri->save();
                }

                // 3c. Clone Master Soal (and all its nested relations down to the answer keys)
                foreach ($tahapan->masterSoal as $masterSoal) {
                    $newMasterSoal = $masterSoal->replicate();
                    $newMasterSoal->tahapan_sintaks_id = $newTahapan->tahapan_sintaks_id;
                    $newMasterSoal->save();

                    // Clone Main Soal
                    foreach ($masterSoal->mainSoal as $mainSoal) {
                        $newMainSoal = $mainSoal->replicate();
                        $newMainSoal->master_soal_id = $newMasterSoal->master_soal_id;
                        $newMainSoal->save();

                        // Clone Soal
                        foreach ($mainSoal->soal as $soal) {
                            $newSoal = $soal->replicate();
                            $newSoal->main_soal_id = $newMainSoal->main_soal_id;
                            $newSoal->save();

                            // Clone Pilihan Ganda
                            foreach ($soal->pilihanGanda as $pg) {
                                $newPg = $pg->replicate();
                                $newPg->soal_id = $newSoal->soal_id;
                                $newPg->save();
                            }

                            // Clone Kunci Jawaban
                            if ($soal->kunciJawaban) {
                                $newKunci = $soal->kunciJawaban->replicate();
                                $newKunci->soal_id = $newSoal->soal_id;
                                $newKunci->save();
                            }
                        }
                    }
                }
            }

            DB::commit();
            return $newSintaks;

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Sintaks Duplicator Failed: ' . $e->getMessage());
            return false;
        }
    }
}
