<?php

namespace App\Http\Controllers\Concerns;

use App\Models\CasePatient;
use App\Models\Patient;

trait GeneratesCaseIdentifiers
{
    /**
     * Generate a case identifier, e.g. AR-1234
     * Format: 'AR-' + random 4-digit number (unique).
     */
    protected function generateCaseId(): string
    {
        do {
            $candidate = 'AR-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (CasePatient::where('case_id', $candidate)->exists());

        return $candidate;
    }

    /**
     * Generate a patient reference, e.g. PT-1234
     * Format: 'PT-' + random 4-digit number (unique).
     */
    protected function generatePatientReference(): string
    {
        do {
            $candidate = 'PT-' . str_pad((string) mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
        } while (Patient::where('reference', $candidate)->exists());

        return $candidate;
    }
}
