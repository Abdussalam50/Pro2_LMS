<?php

namespace App\Contracts;

use App\Models\Ujian;

interface UjianHandlerInterface
{
    /**
     * Get security settings for the exam engine.
     *
     * @param Ujian $ujian
     * @return array
     */
    public function getSecuritySettings(Ujian $ujian): array;

    /**
     * Custom validation or logic before starting the exam.
     *
     * @param Ujian $ujian
     * @return bool|\Illuminate\Http\RedirectResponse
     */
    public function beforeStart(Ujian $ujian);

    /**
     * Custom logic after submitting the exam.
     *
     * @param Ujian $ujian
     * @param array $answers
     * @return void
     */
    public function afterSubmit(Ujian $ujian, array $answers): void;
}
