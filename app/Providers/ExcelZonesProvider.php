<?php
declare(strict_types=1);

namespace App\Services;

use App\Models\Zone;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Reader\SheetInterface;

class ExcelZonesProvider
{
    /** Arabic → Latin digits map */
    private const ARABIC_DIGITS = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
    private const LATIN_DIGITS = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

    /**
     * Parse and return ONLY the three columns:
     *  - زون            => zone (int)
     *  - عدد ماركتات زون => markets_count (int)
     *  - عدد تكرار زون   => zone_repeats (int)
     *
     * @return array{
     *   rows: array<int, array{zone:int, markets_count:int, zone_repeats:int}>,
     *   unique_zones: array<int>
     * }
     * @throws ValidationException
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
            $this->fail(['file' => 'Unable to read Excel file: ' . $e->getMessage()]);
        }

        $target = $this->findSheetByName($reader, $sheet);
        if (!$target) {
            $reader->close();
            $this->fail(['excel' => "Sheet '{$sheet}' not found."]);
        }

        // Streamed extraction (no big in-memory array)
        [$rows, $uniqueZones] = $this->extractThreeColumnsAsArray($target);

        $reader->close();

        if (empty($rows)) {
            $this->fail(['excel' => "No usable rows under headers (زون / عدد ماركتات زون / عدد تكرار زون)."]);
        }

        return [
            'rows' => $rows,
            'unique_zones' => $uniqueZones,
        ];
    }

    /**
     * Validation-only wrapper: parse + validate active zones (no DB writes).
     * @return array{rows: array<int, array{zone:int, markets_count:int, zone_repeats:int}>, unique_zones: array<int>}
     * @throws ValidationException
     */
    public function validate(string $filePath, string $sheet = 'Data'): array
    {
        $out = $this->read($filePath, $sheet);
        $this->validateZonesExist($out['unique_zones']); // will report missing/inactive/duplicates
        return $out;
    }

    /**
     * READ + VALIDATE + AGGREGATE + INSERT (no calculation).
     *
     * - Aggregates by zone (sum market & repeat counts).
     * - Replaces existing rows for this checklist.
     * - Inserts only: zone_id, zone_count, repeat_count.
     *
     * @return array{inserted:int, items: array<int, array{zone_code:string, zone_id:int, zone_count:int, repeat_count:int}>}
     * @throws ValidationException
     */
    public function process(string $filePath, int $checklistId, string $sheet = 'Data'): array
    {
        $out = $this->read($filePath, $sheet);
        $this->validateZonesExist($out['unique_zones']);

        $agg = $this->aggregateByZone($out['rows']); // ['41' => ['zone_count'=>..,'repeat_count'=>..], ...]
        if (empty($agg)) {
            return ['inserted' => 0, 'items' => []];
        }

        // Map code -> id (active only, collapse duplicates deterministically)
        $codes = array_keys($agg);
        $zonesMap = Zone::query()
            ->active()
            ->whereIn('code', $codes)
            ->orderByDesc('updated_at')   // prefer latest updated
            ->orderBy('id')               // tiebreaker
            ->get(['id', 'code'])
            ->unique('code')              // collapse duplicates by code
            ->pluck('id', 'code')
            ->all();

        // Guard against any mapping holes (shouldn't happen after validateZonesExist)
        $missingMap = array_values(array_diff($codes, array_keys($zonesMap)));
        if ($missingMap) {
            $this->fail(['zones_map' => 'Unable to map these codes to active zones: ' . implode(', ', $missingMap) . '.']);
        }

        $now = now();
        $toInsert = [];
        $itemsOut = [];
        foreach ($agg as $code => $pair) {
            $zoneId = (int) $zonesMap[$code];
            $zoneCount = (int) $pair['zone_count'];
            $repeatCount = (int) $pair['repeat_count'];

            $toInsert[] = [
                'checklist_id' => $checklistId,
                'zone_id' => $zoneId,
                'zone_count' => $zoneCount,
                'repeat_count' => $repeatCount,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $itemsOut[] = [
                'zone_code' => $code,
                'zone_id' => $zoneId,
                'zone_count' => $zoneCount,
                'repeat_count' => $repeatCount,
            ];
        }

        DB::transaction(function () use ($checklistId, $toInsert) {
            DB::table('visited_zones')->where('checklist_id', $checklistId)->delete();
            if ($toInsert) {
                DB::table('visited_zones')->insert($toInsert);
            }
        });

        return ['inserted' => count($toInsert), 'items' => $itemsOut];
    }

    /**
     * Validate zone codes against DB with clear errors:
     *  - zones_missing: codes not present at all
     *  - zones_inactive: present but inactive
     *  - zones_duplicate: codes with multiple DB rows (you should fix these)
     *
     * @throws ValidationException
     */
    public function validateZonesExist(array $zoneCodes): void
    {
        $zoneCodes = array_values(array_unique(array_map(fn($z) => (string) $z, $zoneCodes)));
        if (empty($zoneCodes)) {
            $this->fail(['zones' => 'No zone codes found in the sheet.']);
        }

        // All present
        $allFound = Zone::query()
            ->whereIn('code', $zoneCodes)
            ->pluck('code')
            ->all();

        // Active present
        $active = Zone::query()
            ->active()
            ->whereIn('code', $zoneCodes)
            ->pluck('code')
            ->all();

        // Duplicates among requested set
        $dups = Zone::query()
            ->select('code', DB::raw('COUNT(*) as c'))
            ->whereIn('code', $zoneCodes)
            ->groupBy('code')
            ->having('c', '>', 1)
            ->pluck('code')
            ->all();

        $missing = array_values(array_diff($zoneCodes, $allFound));
        $inactive = array_values(array_diff($allFound, $active));

        $errors = [];
        if ($missing)
            $errors['zones_missing'] = 'These zone code(s) do not exist: ' . implode(', ', $missing) . '.';
        if ($inactive)
            $errors['zones_inactive'] = 'These zone code(s) are inactive: ' . implode(', ', $inactive) . '.';
        if ($dups)
            $errors['zones_duplicate'] = 'Duplicate zone code(s) in database: ' . implode(', ', $dups) . '. Please fix zones.';

        if ($errors) {
            $this->fail($errors);
        }
    }

    // ----------------- helpers -----------------

    private function normalizePath(string $filePath): string
    {
        $p = trim($filePath);

        // If it's already absolute (Unix /, Windows C:\, UNC \\server\)
        if (preg_match('~^([a-zA-Z]:[\\/]|/|\\\\\\\\)~', $p)) {
            return $p;
        }

        // Normalize slashes and strip leading /
        $p = ltrim(str_replace('\\', '/', $p), '/');

        // 1) If someone saved with "public/..." (public disk physical path is storage/app/public/...)
        if (str_starts_with($p, 'public/')) {
            $candidate = storage_path('app/' . $p); // -> storage/app/public/...
            if (is_file($candidate))
                return $candidate;
        }

        // 2) Browser-style "storage/..." (symlink) → real file under storage/app/public/...
        if (str_starts_with($p, 'storage/')) {
            $rel = preg_replace('~^storage/~', '', $p);
            $candidate = storage_path('app/public/' . $rel);
            if (is_file($candidate))
                return $candidate;
            // also try the public symlink (rarely needed server-side)
            $publicSymlink = public_path($p);
            if (is_file($publicSymlink))
                return $publicSymlink;
        }

        // 3) Plain relative like "checklists/..." — try private first, then public
        $local = storage_path('app/' . $p);           // local (private)
        if (is_file($local))
            return $local;

        $public = storage_path('app/public/' . $p);   // public disk
        if (is_file($public))
            return $public;

        // 4) As a last resort, try under public/ root
        $publicRoot = public_path($p);
        if (is_file($publicRoot))
            return $publicRoot;

        // Nothing matched — return as-is (so caller can see failure)
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
     * Stream the sheet and return tidy rows + unique codes.
     * @return array{0: array<int, array{zone:int, markets_count:int, zone_repeats:int}>, 1: array<int>}
     */
    private function extractThreeColumnsAsArray(SheetInterface $sheet): array
    {
        $rows = [];
        $zonesSet = [];

        $headerFound = false;
        $zoneIdx = $countIdx = $repIdx = null;

        foreach ($sheet->getRowIterator() as $row) {
            $cells = array_map([$this, 'cleanCell'], $row->toArray());

            if (!$headerFound) {
                // Detect header row
                foreach ($cells as $k => $cell) {
                    $lower = mb_strtolower($cell);
                    if (in_array($lower, ['زون', 'زوون', 'zone'], true)) {
                        $headerFound = true;

                        // Find indices of the three headers in this row
                        foreach ($cells as $kk => $h) {
                            $hh = mb_strtolower($h);
                            if ($zoneIdx === null && in_array($hh, ['زون', 'زوون', 'zone'], true)) {
                                $zoneIdx = $kk;
                            }
                            if ($countIdx === null && ($hh === 'عدد ماركتات زون' || str_contains($hh, 'ماركتات'))) {
                                $countIdx = $kk;
                            }
                            if ($repIdx === null && ($hh === 'عدد تكرار زون' || str_contains($hh, 'تكرار'))) {
                                $repIdx = $kk;
                            }
                        }
                        break;
                    }
                }
                continue;
            }

            // Past the header row: collect rows
            if ($zoneIdx === null || $countIdx === null || $repIdx === null) {
                $this->fail(['excel' => "Could not detect headers: زون / عدد ماركتات زون / عدد تكرار زون."]);
            }

            $zoneStr = $this->toLatinDigits((string) ($cells[$zoneIdx] ?? ''));
            $countStr = $this->toLatinDigits((string) ($cells[$countIdx] ?? ''));
            $repStr = $this->toLatinDigits((string) ($cells[$repIdx] ?? ''));

            $zoneNum = (int) preg_replace('/\D+/', '', $zoneStr);
            $countNum = is_numeric($countStr) ? (int) $countStr : 0;
            $repNum = is_numeric($repStr) ? (int) $repStr : 0;

            if ($zoneNum <= 0)
                continue;
            if ($countNum === 0 && $repNum === 0)
                continue;

            $rows[] = [
                'zone' => $zoneNum,
                'markets_count' => $countNum,
                'zone_repeats' => $repNum,
            ];
            $zonesSet[$zoneNum] = true;
        }

        if (!$headerFound) {
            $this->fail(['excel' => "Header row not found. Expected headers row containing: زون."]);
        }

        return [$rows, array_map('intval', array_keys($zonesSet))];
    }

    private function cleanCell($v): string
    {
        if ($v instanceof \DateTimeInterface)
            return $v->format('Y-m-d');
        if ($v === null)
            return '';
        $s = str_replace("\xC2\xA0", ' ', (string) $v); // NBSP → space
        $s = preg_replace('/\s+/u', ' ', $s);
        return trim($s);
    }

    /** Group rows by zone and sum counts. @return array<string, array{zone_count:int, repeat_count:int}> */
    private function aggregateByZone(array $rows): array
    {
        $agg = [];
        foreach ($rows as $r) {
            $code = (string) $r['zone'];
            $agg[$code]['zone_count'] = ($agg[$code]['zone_count'] ?? 0) + (int) $r['markets_count'];
            $agg[$code]['repeat_count'] = ($agg[$code]['repeat_count'] ?? 0) + (int) $r['zone_repeats'];
        }
        return $agg;
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
