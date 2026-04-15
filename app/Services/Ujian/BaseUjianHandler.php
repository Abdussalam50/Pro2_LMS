<?php

namespace App\Services\Ujian;

use App\Contracts\UjianHandlerInterface;
use App\Models\Ujian;

class BaseUjianHandler implements UjianHandlerInterface
{
    public function getSecuritySettings(Ujian $ujian): array
    {
        $mode = $ujian->mode_batasan;
        
        return [
            'mode' => $mode ?? 'open',
            'isRestricted' => ($mode === 'strict' || $mode === 'materi_only'),
            'showOverlay' => ($mode === 'strict' || $mode === 'materi_only'),
            'maxWarnings' => 3,
            'allowCopyPaste' => ($mode === 'open'),
            'forceFullscreen' => ($mode === 'strict' || $mode === 'materi_only'),
            'overlayMessage' => 'Anda terdeteksi keluar dari layar penuh / berpindah tab. Silakan kembali fokus pada ujian.',
        ];
    }

    public function beforeStart(Ujian $ujian)
    {
        return true;
    }

    public function afterSubmit(Ujian $ujian, array $answers): void
    {
        // Default behavior handled by Livewire component
    }
}
