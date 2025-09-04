<?php

namespace App\View\Components\Excel;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

class Preview extends Component
{
    public string $filePath;
    public string $sheet;
    public int    $maxRows;
    public array  $rows = [];
    public ?string $error = null;

    public function __construct(string $filePath, string $sheet = 'Data', int $maxRows = 500)
    {
        $this->filePath = $filePath;
        $this->sheet    = $sheet;
        $this->maxRows  = max(1, $maxRows);

        try {
            $path = $this->normalizePath($filePath);
            if (!is_file($path)) {
                $this->error = "Excel file not found: {$filePath}";
                return;
            }

            $reader = ReaderEntityFactory::createReaderFromFile($path);
            $reader->open($path);

            // Pick target sheet (case-insensitive), fallback to first
            $target = null; $first = null;
            foreach ($reader->getSheetIterator() as $s) {
                $first ??= $s;
                if (mb_strtolower(trim($s->getName())) === mb_strtolower(trim($this->sheet))) {
                    $target = $s; break;
                }
            }
            $target ??= $first;
            if (!$target) {
                $reader->close();
                $this->error = "No sheets found in workbook.";
                return;
            }

            $clean = static function ($v) {
                if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
                if ($v === null) return '';
                $s = str_replace("\xC2\xA0", ' ', (string)$v);  // NBSP → space
                $s = preg_replace('/\s+/u', ' ', $s);
                return trim($s);
            };

            $rows = []; $count = 0;
            foreach ($target->getRowIterator() as $row) {
                $rows[] = array_map($clean, $row->toArray());
                if (++$count >= $this->maxRows) break;
            }
            $reader->close();

            // Normalize to rectangle, then trim empty rows & columns
            $rows = $this->rectangularize($rows);
            $rows = $this->trimEmptyRows($rows);
            $rows = $this->trimEmptyColumns($rows);

            $this->rows = $rows;

        } catch (\Throwable $e) {
            $this->error = "Unable to read Excel file: " . $e->getMessage();
        }
    }

    private function normalizePath(string $p): string
    {
        $p = trim($p);

        // Absolute? (C:\, \\server\, or /)
        if (preg_match('~^([a-zA-Z]:[\\/]|/|\\\\\\\\)~', $p)) return $p;

        // With storage:link, relative public-disk path lives under storage/app/public
        $p = ltrim(str_replace('\\', '/', $p), '/');

        // If someone passed "storage/..." (public symlink) -> map back to storage/app/public/...
        if (str_starts_with($p, 'storage/')) {
            $rel = preg_replace('~^storage/~', '', $p);
            return storage_path('app/public/' . $rel);
        }

        // Default: treat as public disk relative path ("checklists/...")
        return storage_path('app/public/' . $p);
    }

    private function rectangularize(array $rows): array
    {
        $max = 0;
        foreach ($rows as $r) $max = max($max, count($r));
        if ($max === 0) return $rows;

        foreach ($rows as &$r) {
            if (count($r) < $max) $r = array_pad($r, $max, '');
        }
        unset($r);
        return $rows;
    }

    private function trimEmptyRows(array $rows): array
    {
        // drop fully-empty trailing rows
        for ($i = count($rows) - 1; $i >= 0; $i--) {
            if ($this->rowHasAnyValue($rows[$i])) break;
            unset($rows[$i]);
        }
        return array_values($rows);
    }

    private function trimEmptyColumns(array $rows): array
    {
        if (empty($rows)) return $rows;
        $cols = count($rows[0]);
        $keep = array_fill(0, $cols, false);

        // Mark columns that have at least one non-empty cell
        for ($c = 0; $c < $cols; $c++) {
            foreach ($rows as $r) {
                $val = $r[$c] ?? '';
                if ($val !== '') { $keep[$c] = true; break; }
            }
        }

        // If all columns are empty, keep none
        if (!in_array(true, $keep, true)) return [];

        // Rebuild rows with kept columns only
        $out = [];
        foreach ($rows as $r) {
            $new = [];
            for ($c = 0; $c < $cols; $c++) {
                if ($keep[$c]) $new[] = $r[$c] ?? '';
            }
            $out[] = $new;
        }
        return $out;
    }

    private function rowHasAnyValue(array $row): bool
    {
        foreach ($row as $v) {
            if ($v !== '' && $v !== null) return true;
        }
        return false;
    }

    public function render(): View|Closure|string
    {
        return view('components.excel.preview');
    }
}
