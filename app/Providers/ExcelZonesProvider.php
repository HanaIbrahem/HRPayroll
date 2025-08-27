<?php

namespace App\Services;

use App\Models\Zone;
use Illuminate\Validation\ValidationException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\SheetInterface;

class ExcelZonesProvider
{
    /** Arabic → Latin digits map */
    private const ARABIC_DIGITS = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    private const LATIN_DIGITS  = ['0','1','2','3','4','5','6','7','8','9'];

    /**
     * Read the Excel and return ONLY the three columns:
     *   - زون  (zone code, as int)
     *   - عدد ماركتات زون (markets_count, as int)
     *   - عدد تكرار زون (zone_repeats, as int)
     *
     * @return array{
     *   rows: array<int, array{zone:int, markets_count:int, zone_repeats:int}>,
     *   unique_zones: array<int>
     * }
     * @throws ValidationException on file/sheet/header issues
     */
    public function read(string $filePath, string $sheet = 'Data'): array
    {
        $path = $this->normalizePath($filePath);
        if (!is_file($path)) {
            $this->fail(['file' => "Excel file not found: {$filePath}"]);
        }

        try {
            $reader = ReaderEntityFactory::createReaderFromFile($path);
            $reader->open($path);
        } catch (\Throwable $e) {
            $this->fail(['file' => 'Unable to read Excel file: '.$e->getMessage()]);
        }

        // find sheet by (case-insensitive) name
        $target = $this->findSheetByName($reader, $sheet);
        if (!$target) {
            $reader->close();
            $this->fail(['excel' => "Sheet '{$sheet}' not found."]);
        }

        // Extract & normalize ONLY the needed columns
        [$rows, $uniqueZones] = $this->extractThreeColumnsAsArray($target);

        $reader->close();

        if (empty($rows)) {
            $this->fail(['excel' => "No usable rows under headers (زون / عدد ماركتات زون / عدد تكرار زون)."]);
        }

        return [
            'rows'         => $rows,
            'unique_zones' => $uniqueZones,
        ];
    }

    /**
     * Validate that every provided zone code exists in zones.code.
     * Throws ValidationException if any are missing.
     */
    public function validateZonesExist(array $zoneCodes): void
    {
        // normalize to string (zones.code is string/varchar in your schema)
        $zoneCodes = array_values(array_unique(array_map(fn($z) => (string)$z, $zoneCodes)));

        $existing = Zone::query()
            ->whereIn('code', $zoneCodes)
            ->pluck('code', 'code')
            ->all();

        $missing = array_values(array_diff($zoneCodes, array_keys($existing)));
        if (!empty($missing)) {
            $this->fail([
                'zones' => "These zone code(s) do not match our records: ".implode(', ', $missing).".",
            ]);
        }
    }

    /**
     * Convenience method: read + validate (no calculation yet).
     *
     * @return array same shape as read()
     * @throws ValidationException
     */
    public function process(string $filePath, string $sheet = 'Data'): array
    {
        $out = $this->read($filePath, $sheet);
        $this->validateZonesExist($out['unique_zones']);
        return $out; // rows + unique_zones (ready for later calculation stage)
    }

    // ----------------------------------------------------------------
    // Private helpers
    // ----------------------------------------------------------------

    private function normalizePath(string $filePath): string
    {
        // Allow "public/..." (storage/app/public/...), or absolute path
        if (str_starts_with($filePath, 'public/')) {
            return storage_path('app/'.$filePath);
        }
        if (str_starts_with($filePath, 'storage/')) {
            return base_path($filePath);
        }
        return $filePath;
    }

    private function findSheetByName($reader, string $sheet): ?SheetInterface
    {
        $want = mb_strtolower(trim($sheet));
        foreach ($reader->getSheetIterator() as $s) {
            if (mb_strtolower(trim($s->getName())) === $want) {
                return $s;
            }
        }
        return null;
    }

    /**
     * Read the sheet and return only the three needed columns as tidy rows.
     *
     * @return array{0: array<int, array{zone:int, markets_count:int, zone_repeats:int}>, 1: array<int>}
     */
    private function extractThreeColumnsAsArray(SheetInterface $sheet): array
    {
        // 1) Read all rows to memory (your sheets are not huge; fine for admin preview).
        $rowsRaw = [];
        $clean = function ($v) {
            if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
            if ($v === null) return '';
            $s = str_replace("\xC2\xA0", ' ', (string)$v); // nbsp → space
            $s = preg_replace('/\s+/u', ' ', $s);
            return trim($s);
        };
        foreach ($sheet->getRowIterator() as $row) {
            $rowsRaw[] = array_map($clean, $row->toArray());
        }
        if (!$rowsRaw) {
            return [[], []];
        }

        // 2) Locate header row & indices of the three columns
        $headerRowIndex = null;
        $zoneIdx = $countIdx = $repIdx = null;

        foreach ($rowsRaw as $i => $cells) {
            foreach ($cells as $val) {
                $t = mb_strtolower($val);
                if (in_array($t, ['زون','زوون','zone'], true)) {
                    // assume this row contains the headers
                    $headerRowIndex = $i;
                    // find the three columns inside this header row
                    foreach ($cells as $k => $h) {
                        $hh = mb_strtolower($h);
                        if ($zoneIdx === null && in_array($hh, ['زون','زوون','zone'], true)) {
                            $zoneIdx = $k;
                        }
                        if ($countIdx === null && ($hh === 'عدد ماركتات زون' || str_contains($hh, 'ماركتات'))) {
                            $countIdx = $k;
                        }
                        if ($repIdx === null && ($hh === 'عدد تكرار زون' || str_contains($hh, 'تكرار'))) {
                            $repIdx = $k;
                        }
                    }
                    break 2;
                }
            }
        }

        if ($headerRowIndex === null || $zoneIdx === null || $countIdx === null || $repIdx === null) {
            $this->fail(['excel' => "Could not detect headers: زون / عدد ماركتات زون / عدد تكرار زون."]);
        }

        // 3) Build tidy rows
        $rows = [];
        $zonesSet = [];
        for ($i = $headerRowIndex + 1; $i < count($rowsRaw); $i++) {
            $cells = $rowsRaw[$i];

            $zoneStr  = $this->toLatinDigits((string)($cells[$zoneIdx]   ?? ''));
            $countStr = $this->toLatinDigits((string)($cells[$countIdx]  ?? ''));
            $repStr   = $this->toLatinDigits((string)($cells[$repIdx]    ?? ''));

            $zoneNum  = (int) preg_replace('/\D+/', '', $zoneStr);
            $countNum = is_numeric($countStr) ? (int)$countStr : 0;
            $repNum   = is_numeric($repStr)   ? (int)$repStr   : 0;

            // Skip empties
            if ($zoneNum <= 0) continue;
            if ($countNum === 0 && $repNum === 0) continue;

            $rows[] = [
                'zone'          => $zoneNum,
                'markets_count' => $countNum,
                'zone_repeats'  => $repNum,
            ];

            $zonesSet[$zoneNum] = true;
        }

        return [$rows, array_map('intval', array_keys($zonesSet))];
    }

    private function toLatinDigits(string $s): string
    {
        return str_replace(self::ARABIC_DIGITS, self::LATIN_DIGITS, $s);
    }

    /** Throw a ValidationException with field-keyed messages. */
    private function fail(array $messages): void
    {
        throw ValidationException::withMessages($messages);
    }
}
