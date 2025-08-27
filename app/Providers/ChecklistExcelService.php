<?php

namespace App\Services;

use App\Models\Zone;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ChecklistExcelService
{
    /** Arabic → Latin digits map */
    private const ARABIC_DIGITS = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
    private const LATIN_DIGITS  = ['0','1','2','3','4','5','6','7','8','9'];

    /**
     * Validate the Excel file and compute zone summary/costs.
     * - Does NOT use employee code or department
     * - Fails if any zone in the sheet does not exist in zones table
     *
     * @param  string $fullPath Absolute path to uploaded Excel
     * @param  array  $defaults Optional cost defaults: ['rate_per_km'=>300, 'between_zone'=>1500]
     * @return array{
     *   zones: array<int, array{zone:int,markets_count:int,zone_repeats:int,km:float,total_km:float,rate_per_km:int,between_zone:int,total_price_iqd:float|int}>,
     *   total_cost: float|int
     * }
     * @throws \Illuminate\Validation\ValidationException
     */
    public static function validateAndSummarize(string $fullPath, array $defaults = []): array
    {
        $rateDefault    = (int) ($defaults['rate_per_km']   ?? 300);
        $betweenDefault = (int) ($defaults['between_zone']  ?? 1500);

        if (!is_file($fullPath)) {
            self::fail(['file' => 'Excel file not found.']);
        }

        try {
            $spreadsheet = IOFactory::load($fullPath);
        } catch (\Throwable $e) {
            self::fail(['file' => 'Unable to read Excel file: '.$e->getMessage()]);
        }

        // ---- Require only the Data sheet (Main is not needed for validation/calculation) ----
        $dataSheet = $spreadsheet->getSheetByName('Data') ?? $spreadsheet->getSheetByName('data');
        if (!$dataSheet) self::fail(['excel' => "Sheet 'Data' not found."]);

        // ---- Convert sheet to rows and detect header row ----
        [$rows, $headerRowIndex] = self::sheetToRowsAndHeaderRow($dataSheet);
        if ($headerRowIndex === null) {
            self::fail(['excel' => "Header row not found in 'Data'. Expected Arabic headers like: زون، عدد ماركتات زون، عدد تكرار زون."]);
        }

        // ---- Locate required columns ----
        [$zoneColIdx, $countMarketsIdx, $repeatIdx] = self::detectDataHeaderIndices($rows[$headerRowIndex]);
        if ($zoneColIdx === null || $countMarketsIdx === null || $repeatIdx === null) {
            self::fail(['excel' => "Could not detect required headers in 'Data' (زون / عدد ماركتات زون / عدد تكرار زون)."]);
        }

        // ---- Build summary per zone from Data sheet ----
        $summary = []; // [zone => ['zone'=>int,'markets_count'=>int,'zone_repeats'=>int]]
        for ($i = $headerRowIndex + 1; $i < count($rows); $i++) {
            $cells    = $rows[$i];
            $zoneStr  = self::toLatinDigits((string)($cells[$zoneColIdx]      ?? ''));
            $countStr = self::toLatinDigits((string)($cells[$countMarketsIdx] ?? ''));
            $repStr   = self::toLatinDigits((string)($cells[$repeatIdx]       ?? ''));

            // Extract integer digits only for zone
            $zoneNum  = (int) preg_replace('/\D+/', '', $zoneStr);
            $countNum = is_numeric($countStr) ? (int)$countStr : 0;
            $repNum   = is_numeric($repStr)   ? (int)$repStr   : 0;

            if ($zoneNum <= 0) continue;
            if ($countNum === 0 && $repNum === 0) continue;

            if (!isset($summary[$zoneNum])) {
                $summary[$zoneNum] = [
                    'zone'          => $zoneNum,
                    'markets_count' => 0,
                    'zone_repeats'  => 0,
                ];
            }
            $summary[$zoneNum]['markets_count'] += $countNum;
            $summary[$zoneNum]['zone_repeats']  += $repNum;
        }

        if (empty($summary)) {
            self::fail(['excel' => "No usable zone rows found in 'Data'."]);
        }

        // ---- Validate all zone codes exist in DB (zones.code) ----
        $excelZoneCodes = array_map(fn($z) => (string)$z, array_keys($summary));

        $dbCodes = Zone::query()
            ->whereIn('code', $excelZoneCodes)
            ->pluck('code', 'code')
            ->all();

        $missing = array_values(array_diff($excelZoneCodes, array_keys($dbCodes)));
        if (!empty($missing)) {
            self::fail([
                'zones' => "Unknown zone code(s): ".implode(', ', $missing).". Add them to the zones table first.",
            ]);
        }

        // ---- Load DB rows and compute totals ----
        $zonesDb = Zone::query()
            ->whereIn('code', $excelZoneCodes)
            ->get(['code','km','fixed_rate','between_zone'])
            ->keyBy('code');

        $totalCost = 0;
        foreach ($summary as $zone => &$row) {
            $z = $zonesDb[(string)$zone];

            $kmPerZone    = (float)($z->km ?? 0.0);
            $ratePerKm    = (int)  ($z->fixed_rate   ?? $rateDefault);
            $betweenZone  = (int)  ($z->between_zone ?? $betweenDefault);

            $repeats      = max(0, (int)$row['zone_repeats']);
            $markets      = max(0, (int)$row['markets_count']);

            $totalKm      = $kmPerZone * $repeats;
            $extraMarkets = max(0, $markets - $repeats);

            $distanceCost = $totalKm * $ratePerKm;
            $extraCost    = $extraMarkets * $betweenZone;
            $zoneTotal    = $distanceCost + $extraCost;

            $row['km']              = $kmPerZone;
            $row['total_km']        = $totalKm;
            $row['rate_per_km']     = $ratePerKm;
            $row['between_zone']    = $betweenZone;
            $row['total_price_iqd'] = $zoneTotal;

            $totalCost += $zoneTotal;
        }
        unset($row);

        ksort($summary, SORT_NUMERIC);

        return [
            'zones'      => $summary,
            'total_cost' => $totalCost,
        ];
    }

    // ----------------- helpers -----------------

    /** Convert worksheet to trimmed rows & detect header row by seeing a 'زون' cell. */
    private static function sheetToRowsAndHeaderRow(Worksheet $sheet): array
    {
        $rows = [];
        foreach ($sheet->toArray(null, true, true, true) as $row) {
            $rows[] = array_map(function ($v) {
                if ($v === null) return '';
                $s = str_replace("\xC2\xA0", ' ', (string)$v);
                $s = preg_replace('/\s+/u', ' ', $s);
                return trim($s);
            }, $row);
        }

        $headerRowIndex = null;
        foreach ($rows as $i => $cells) {
            foreach ($cells as $v) {
                $vv = mb_strtolower($v);
                if (in_array($vv, ['زون','زوون','zone'], true)) {
                    $headerRowIndex = $i;
                    break 2;
                }
            }
        }
        return [$rows, $headerRowIndex];
    }

    /** Find indices for زون / عدد ماركتات زون / عدد تكرار زون. */
    private static function detectDataHeaderIndices(array $headerRow): array
    {
        $zoneIdx = $countIdx = $repIdx = null;
        foreach ($headerRow as $k => $val) {
            $t = mb_strtolower($val);
            if ($zoneIdx === null && in_array($t, ['زون','زوون','zone'], true)) {
                $zoneIdx = $k;
            }
            if ($countIdx === null && ($t === 'عدد ماركتات زون' || Str::contains($t, 'ماركتات'))) {
                $countIdx = $k;
            }
            if ($repIdx === null && ($t === 'عدد تكرار زون' || Str::contains($t, 'تكرار'))) {
                $repIdx = $k;
            }
        }
        return [$zoneIdx, $countIdx, $repIdx];
    }

    /** Normalize Arabic numerals to Latin. */
    private static function toLatinDigits(string $s): string
    {
        return str_replace(self::ARABIC_DIGITS, self::LATIN_DIGITS, $s);
    }

    /** Throw a ValidationException with field-keyed messages. */
    private static function fail(array $messages): void
    {
        throw ValidationException::withMessages($messages);
    }
}
