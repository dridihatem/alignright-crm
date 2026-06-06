<?php

namespace App\Console\Commands;

use App\Services\CaseIdentifierRegenerator;
use Illuminate\Console\Command;

class RegenerateCaseIdentifiers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * Examples:
     *   php artisan cases:regenerate-ids                 # regenerate both
     *   php artisan cases:regenerate-ids --cases         # only case IDs (AR-####)
     *   php artisan cases:regenerate-ids --patients      # only patient refs (PT-####)
     *   php artisan cases:regenerate-ids --dry-run       # preview counts, change nothing
     *
     * @var string
     */
    protected $signature = 'cases:regenerate-ids
        {--cases : Regenerate only case IDs (AR-####)}
        {--patients : Regenerate only patient references (PT-####)}
        {--dry-run : Show what would change without writing to the database}
        {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Regenerate all case IDs (AR-####) and patient references (PT-####) for existing records';

    public function handle(CaseIdentifierRegenerator $regenerator): int
    {
        $onlyCases = (bool) $this->option('cases');
        $onlyPatients = (bool) $this->option('patients');
        $dryRun = (bool) $this->option('dry-run');

        // If neither flag is provided, do both.
        $doCases = $onlyCases || (!$onlyCases && !$onlyPatients);
        $doPatients = $onlyPatients || (!$onlyCases && !$onlyPatients);

        $targets = [];
        if ($doCases) {
            $targets[] = 'case IDs (AR-####)';
        }
        if ($doPatients) {
            $targets[] = 'patient references (PT-####)';
        }

        $this->info(($dryRun ? '[DRY RUN] ' : '') . 'Regenerating: ' . implode(' + ', $targets));

        if (!$dryRun && !$this->option('force')) {
            if (!$this->confirm('This will overwrite existing identifiers. Continue?', false)) {
                $this->warn('Aborted.');
                return self::SUCCESS;
            }
        }

        $result = $regenerator->regenerate($doCases, $doPatients, $dryRun);

        $this->newLine();
        $this->table(
            ['Target', $dryRun ? 'Would update' : 'Updated'],
            [
                ['Cases', $result['cases']],
                ['Patients', $result['patients']],
            ]
        );

        $this->info($dryRun ? 'Dry run complete. No changes were saved.' : 'Done.');

        return self::SUCCESS;
    }
}
