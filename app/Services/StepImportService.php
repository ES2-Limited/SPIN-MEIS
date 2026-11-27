<?php

namespace App\Services;

use App\Models\ProcurementPackage;

class StepImportService
{
    /**
     * Import STEP export CSV matched on package_reference.
     * Mismatched package references are reported, not silently dropped.
     *
     * @return array{matched: int, mismatched: array<int, string>}
     */
    public function import(string $csvContent): array
    {
        $lines = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($csvContent)),
            fn ($line) => trim($line) !== ''
        ));

        if (count($lines) < 2) {
            return ['matched' => 0, 'mismatched' => []];
        }

        $headers = array_map('trim', str_getcsv(array_shift($lines)));
        $matched = 0;
        $mismatched = [];

        foreach ($lines as $line) {
            $cells = str_getcsv($line);
            if (count($cells) !== count($headers)) {
                continue;
            }
            $row = array_combine($headers, array_map('trim', $cells));
            $ref = $row['package_reference'] ?? '';

            $package = ProcurementPackage::withoutGlobalScopes()
                ->where('package_reference', $ref)
                ->first();

            if (! $package) {
                $mismatched[] = $ref;

                continue;
            }

            $package->update([
                'actual_date' => $row['actual_date'] ?: $package->actual_date,
                'status' => $row['status'] ?: $package->status,
            ]);
            $matched++;
        }

        return [
            'matched' => $matched,
            'mismatched' => $mismatched,
        ];
    }
}
