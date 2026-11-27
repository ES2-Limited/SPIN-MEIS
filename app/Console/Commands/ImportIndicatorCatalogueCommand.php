<?php

namespace App\Console\Commands;

use App\Services\IndicatorCatalogueImporter;
use Illuminate\Console\Command;

class ImportIndicatorCatalogueCommand extends Command
{
    protected $signature = 'spin:import-catalogue';

    protected $description = 'Import the 115-line SPIN indicator catalogue and output a validation report.';

    public function handle(IndicatorCatalogueImporter $importer): int
    {
        $report = $importer->import();

        $this->info("Imported {$report['total']} indicators.");
        foreach ($report['counts_by_category'] as $slug => $count) {
            $this->line(" - {$slug}: {$count}");
        }

        $this->newLine();
        $this->warn('Validation Report (Known Source Defects):');
        foreach ($report['defects'] as $defect) {
            $this->line(" [{$defect['type']}] {$defect['detail']}");
        }

        return self::SUCCESS;
    }
}
