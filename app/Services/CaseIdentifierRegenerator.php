<?php

namespace App\Services;

use App\Models\CasePatient;
use App\Models\Patient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Regenerates the human-readable case identifiers and patient references
 * into the current format:
 *   - Case ID:          AR-#### (AR- + 4 random digits)
 *   - Patient reference: PT-#### (PT- + 4 random digits)
 *
 * These columns are display-only identifiers. No other table references them
 * as a foreign key (related records use the numeric primary keys), so updating
 * them in place is safe.
 */
class CaseIdentifierRegenerator
{
    public const CASE_PREFIX = 'AR-';
    public const PATIENT_PREFIX = 'PT-';

    /**
     * Regenerate identifiers for cases and/or patients.
     *
     * @param  bool  $cases    Regenerate case_patients.case_id
     * @param  bool  $patients Regenerate patients.reference
     * @param  bool  $dryRun   When true, nothing is persisted; only counts are returned
     * @return array{cases:int, patients:int, dry_run:bool}
     */
    public function regenerate(bool $cases = true, bool $patients = true, bool $dryRun = false): array
    {
        $result = ['cases' => 0, 'patients' => 0, 'dry_run' => $dryRun];

        if (!$cases && !$patients) {
            return $result;
        }

        $runner = function () use ($cases, $patients, $dryRun, &$result) {
            if ($cases) {
                $result['cases'] = $this->regenerateCaseIds($dryRun);
            }

            if ($patients) {
                $result['patients'] = $this->regeneratePatientReferences($dryRun);
            }
        };

        if ($dryRun) {
            $runner();
        } else {
            DB::transaction($runner);
        }

        Log::info('Case identifier regeneration completed', $result);

        return $result;
    }

    /**
     * Reassign a fresh AR-#### identifier to every case.
     */
    protected function regenerateCaseIds(bool $dryRun): int
    {
        $used = [];
        $count = 0;

        CasePatient::orderBy('id')->chunkById(200, function ($cases) use (&$used, &$count, $dryRun) {
            foreach ($cases as $case) {
                $newId = $this->uniqueIdentifier(
                    self::CASE_PREFIX,
                    $used,
                    fn (string $candidate) => CasePatient::where('case_id', $candidate)->exists()
                );

                $used[$newId] = true;

                if (!$dryRun) {
                    $case->case_id = $newId;
                    $case->save();
                }

                $count++;
            }
        });

        return $count;
    }

    /**
     * Reassign a fresh PT-#### reference to every patient.
     */
    protected function regeneratePatientReferences(bool $dryRun): int
    {
        $used = [];
        $count = 0;

        Patient::orderBy('id')->chunkById(200, function ($patients) use (&$used, &$count, $dryRun) {
            foreach ($patients as $patient) {
                $newRef = $this->uniqueIdentifier(
                    self::PATIENT_PREFIX,
                    $used,
                    fn (string $candidate) => Patient::where('reference', $candidate)->exists()
                );

                $used[$newRef] = true;

                if (!$dryRun) {
                    $patient->reference = $newRef;
                    $patient->save();
                }

                $count++;
            }
        });

        return $count;
    }

    /**
     * Build a unique "{prefix}####" identifier, avoiding both in-memory
     * collisions (within this run) and existing database values.
     */
    protected function uniqueIdentifier(string $prefix, array $used, callable $existsInDb): string
    {
        $attempts = 0;

        do {
            // After exhausting reasonable attempts on the 4-digit space, widen it
            // so the loop can never run forever (e.g. more than ~10k records).
            $max = $attempts < 50 ? 9999 : 999999;
            $width = $attempts < 50 ? 4 : 6;
            $candidate = $prefix . str_pad((string) mt_rand(0, $max), $width, '0', STR_PAD_LEFT);
            $attempts++;
        } while (isset($used[$candidate]) || $existsInDb($candidate));

        return $candidate;
    }
}
