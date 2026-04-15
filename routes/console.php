<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

use Illuminate\Support\Facades\Schedule;
use App\Models\NotifikasiTerjadwal;
use App\Services\FirebaseNotificationService;
use Illuminate\Support\Facades\DB;

Schedule::call(function () {
    $now = now();
    $notifikasis = NotifikasiTerjadwal::where('status', 'active')
        ->where('waktu_kirim', '<=', $now)
        ->get();

    $fcm = app(FirebaseNotificationService::class);

    foreach ($notifikasis as $notif) {
        $dosenData = DB::table('dosens')->where('dosen_id', $notif->dosen_id)->first();
        $dosenName = $dosenData ? $dosenData->nama : 'Dosen Anda';

        $fcm->sendToKelas(
            $notif->kelas_id,
            $notif->judul,
            $notif->isi,
            [
                'type' => 'info',
                'sender' => $dosenName,
                'url' => '/dashboard'
            ]
        );

        // Update Terakhir Dikirim dan Hitung Waktu Berikutnya
        $notif->terakhir_dikirim = $now;

        if ($notif->perulangan === 'daily') {
            $notif->waktu_kirim = $notif->waktu_kirim->addDay();
        } elseif ($notif->perulangan === 'weekly') {
            $notif->waktu_kirim = $notif->waktu_kirim->addWeek();
        } else {
            $notif->status = 'completed';
        }

        $notif->save();
    }
})->everyMinute();
