<?php

namespace App\Services\Ujian;

use App\Models\Ujian;

/**
 * Sample Custom Handler: High Security Exam
 * This handler overrides default settings to be even stricter.
 */
class CustomHighSecurityHandler extends BaseUjianHandler
{
    public function getSecuritySettings(Ujian $ujian): array
    {
        $settings = parent::getSecuritySettings($ujian);
        
        // Override for higher security
        $settings['maxWarnings'] = 1; // Only 1 chance!
        $settings['overlayMessage'] = 'MODE SUPER KETAT: Sekali Anda keluar layar penuh, ujian akan langsung dikumpulkan!';
        
        return $settings;
    }
}
