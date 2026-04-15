<?php
use App\Models\Kelas;
use App\Models\GradingComponent;

$kelas = Kelas::where('nama_kelas', 'like', '%Mekanika%')->first();
if ($kelas) {
    $classId = $kelas->kelas_id;
    
    // 1. Tambah/Update Presensi ke 10%
    $presensi = GradingComponent::where('kelas_id', $classId)->where('mapping_type', 'attendance')->first();
    if (!$presensi) {
        GradingComponent::create([
            'kelas_id' => $classId,
            'name' => 'Presensi',
            'category' => 'presensi',
            'weight' => 10,
            'is_default' => true,
            'mapping_type' => 'attendance'
        ]);
    } else {
        $presensi->update(['weight' => 10]);
    }

    // 2. Sesuaikan UAS ke 30%
    $uas = GradingComponent::where('kelas_id', $classId)->where('name', 'like', '%UAS%')->first();
    if ($uas) {
        $uas->update(['weight' => 30]);
    }
    
    echo "SUCCESS: Presensi ditambahkan dan UAS disesuaikan.";
} else {
    echo "ERROR: Kelas Mekanika tidak ditemukan.";
}
