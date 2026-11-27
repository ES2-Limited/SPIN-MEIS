<?php

namespace App\Services;

use App\Models\Indicator;
use App\Models\IndicatorValue;
use App\Models\Rbda;
use App\Models\State;
use App\Models\UploadImport;
use App\Models\User;
use InvalidArgumentException;

class SpreadsheetImportService
{
    public function generateTemplate(Indicator $indicator): string
    {
        $lines = [
            'indicator_code,owner_type,owner_code,year,quarter,numerator,denominator,value,comment',
            "{$indicator->code},state,KN,2025,1,,,100,Sample entry",
        ];

        return implode("\n", $lines)."\n";
    }

    /**
     * Validate CSV content and return a preview without saving IndicatorValue rows.
     *
     * @return array{import: UploadImport, valid_rows: array<int, array<string, mixed>>, errors: array<int, array{row: int, message: string}>}
     */
    public function validateAndPreview(string $csvContent, User $actor, string $filename): array
    {
        $lines = array_values(array_filter(
            preg_split('/\r\n|\r|\n/', trim($csvContent)),
            fn ($line) => trim($line) !== ''
        ));

        if (count($lines) < 2) {
            throw new InvalidArgumentException('CSV file must contain a header row and at least one data row.');
        }

        $headers = array_map('trim', str_getcsv(array_shift($lines)));
        $validRows = [];
        $errors = [];
        $seenKeys = [];

        foreach ($lines as $idx => $line) {
            $rowNumber = $idx + 2;
            $cells = str_getcsv($line);
            if (count($cells) !== count($headers)) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Column count does not match header.'];

                continue;
            }

            $row = array_combine($headers, array_map('trim', $cells));

            $indicator = Indicator::where('code', $row['indicator_code'] ?? '')->first();
            if (! $indicator) {
                $errors[] = ['row' => $rowNumber, 'message' => "Unknown indicator_code: {$row['indicator_code']}."];

                continue;
            }

            $year = filter_var($row['year'] ?? '', FILTER_VALIDATE_INT);
            $quarter = filter_var($row['quarter'] ?? '', FILTER_VALIDATE_INT);
            if ($year === false || $year < 2025 || $year > 2035) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Year must be between 2025 and 2035.'];

                continue;
            }
            if ($quarter === false || $quarter < 1 || $quarter > 4) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Quarter must be between 1 and 4.'];

                continue;
            }

            if (! is_numeric($row['value'] ?? '')) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Value must be numeric.'];

                continue;
            }

            $ownerType = strtolower($row['owner_type'] ?? '');
            $ownerCode = strtoupper($row['owner_code'] ?? '');
            $ownerId = $this->resolveOwnerId($ownerType, $ownerCode);

            if ($ownerType !== 'national' && $ownerId === null) {
                $errors[] = ['row' => $rowNumber, 'message' => "Unknown owner ({$ownerType}: {$ownerCode})."];

                continue;
            }

            // Enforce owner scope
            if ($actor->owner_type !== 'national') {
                if ($ownerType !== $actor->owner_type || (int) $ownerId !== (int) $actor->owner_id) {
                    $errors[] = ['row' => $rowNumber, 'message' => 'Row belongs to another owner scope and is rejected.'];

                    continue;
                }
            }

            $dedupKey = "{$indicator->id}|{$ownerType}|{$ownerId}|{$year}|{$quarter}";
            if (isset($seenKeys[$dedupKey])) {
                $errors[] = ['row' => $rowNumber, 'message' => 'Duplicate indicator/period row in file.'];

                continue;
            }
            $seenKeys[$dedupKey] = true;

            $validRows[] = [
                'indicator_id' => $indicator->id,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
                'year' => $year,
                'quarter' => $quarter,
                'numerator' => is_numeric($row['numerator'] ?? '') ? (float) $row['numerator'] : null,
                'denominator' => is_numeric($row['denominator'] ?? '') ? (float) $row['denominator'] : null,
                'value' => (float) $row['value'],
                'comment' => $row['comment'] ?? null,
            ];
        }

        $import = UploadImport::create([
            'filename' => $filename,
            'file_hash' => hash('sha256', $csvContent),
            'owner_type' => $actor->owner_type,
            'owner_id' => $actor->owner_id,
            'uploaded_by' => $actor->id,
            'status' => 'previewed',
            'total_rows' => count($lines),
            'valid_rows' => count($validRows),
            'error_rows' => count($errors),
            'errors' => $errors,
        ]);

        return [
            'import' => $import,
            'valid_rows' => $validRows,
            'errors' => $errors,
        ];
    }

    /**
     * Commit validated rows as Draft IndicatorValues. Re-uploading the same file/period does not duplicate.
     *
     * @param  array<int, array<string, mixed>>  $validRows
     */
    public function commit(UploadImport $import, array $validRows, User $actor): int
    {
        if ($import->error_rows > 0) {
            throw new InvalidArgumentException('Cannot commit an import that has row-level errors.');
        }

        $inserted = 0;

        foreach ($validRows as $row) {
            $exists = IndicatorValue::withoutGlobalScopes()
                ->where('indicator_id', $row['indicator_id'])
                ->where('owner_type', $row['owner_type'])
                ->where('owner_id', $row['owner_id'])
                ->where('year', $row['year'])
                ->where('quarter', $row['quarter'])
                ->exists();

            if ($exists) {
                continue;
            }

            IndicatorValue::create([
                ...$row,
                'channel' => 'upload',
                'source' => $import->filename,
                'status' => 'draft',
            ]);
            $inserted++;
        }

        $import->update([
            'status' => 'committed',
            'committed_at' => now(),
        ]);

        return $inserted;
    }

    protected function resolveOwnerId(string $ownerType, string $ownerCode): ?int
    {
        return match ($ownerType) {
            'state' => State::where('code', $ownerCode)->value('id'),
            'rbda' => Rbda::where('code', $ownerCode)->value('id'),
            default => null,
        };
    }
}
