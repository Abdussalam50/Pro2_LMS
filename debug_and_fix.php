<?php
// Dump columns for Kelas and GradingComponent to debug
try {
    echo "Columns for 'kelas':\n";
    print_r(\Illuminate\Support\Facades\Schema::getColumnListing('kelas'));
    
    echo "\nColumns for 'grading_components':\n";
    print_r(\Illuminate\Support\Facades\Schema::getColumnListing('grading_components'));

    // Try finding the class again with a broader search if nama_kelas failed
    $allKelas = \App\Models\Kelas::all();
    foreach($allKelas as $k) {
        // We know from screenshot it contains "Mekanika"
        $data = $k->toArray();
        $found = false;
        foreach($data as $val) {
            if (is_string($val) && strpos($val, 'Mekanika') !== false) {
                $found = true;
                break;
            }
        }
        
        if ($found) {
            echo "\nFOUND KELAS: " . $k->kelas_id . "\n";
            $classId = $k->kelas_id;
            
            // Apply Fix
            \App\Models\GradingComponent::updateOrCreate(
                ['kelas_id' => $classId, 'mapping_type' => 'attendance'],
                ['name' => 'Presensi', 'category' => 'presensi', 'weight' => 10, 'is_default' => true]
            );
            
            // Adjust UAS
            $uas = \App\Models\GradingComponent::where('kelas_id', $classId)
                ->where('name', 'like', '%UAS%')->first();
            if ($uas) { $uas->update(['weight' => 30]); }
            
            echo "SUCCESS\n";
            break;
        }
    }
} catch (\Exception $e) {
    echo "FATAL ERROR: " . $e->getMessage();
}
