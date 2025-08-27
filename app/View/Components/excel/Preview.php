<?php

namespace App\View\Components\Excel;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;


class Preview extends Component
{
    public string $filePath;
    public string $sheet;      // e.g. "Data"
    public array  $rows = [];  // 2D array of strings
    public ?string $error = null;

    /**
     * @param string $filePath Absolute or storage path (public/storage/... or storage_path(...))
     * @param string $sheet    Sheet name to show (case-insensitive), e.g. "Data"
     * @param int    $maxRows  Optional: cap rows for performance
     */
    public function __construct(string $filePath, string $sheet = 'Data', int $maxRows = 500)
    {
        $this->filePath = $filePath;
        $this->sheet    = $sheet;

        try {
            $path = $this->normalizePath($filePath);
            if (!is_file($path)) {
                $this->error = "Excel file not found: {$filePath}";
                return;
            }

            $reader = ReaderEntityFactory::createReaderFromFile($path);
            $reader->open($path);

            $target = null;
            foreach ($reader->getSheetIterator() as $s) {
                if (mb_strtolower(trim($s->getName())) === mb_strtolower(trim($sheet))) {
                    $target = $s;
                    break;
                }
            }

            if (!$target) {
                $reader->close();
                $this->error = "Sheet '{$sheet}' not found.";
                return;
            }

            $rows = [];
            $clean = function ($v) {
                if ($v instanceof \DateTimeInterface) return $v->format('Y-m-d');
                if ($v === null) return '';
                $s = str_replace("\xC2\xA0", ' ', (string)$v); // NBSP → space
                $s = preg_replace('/\s+/u', ' ', $s);
                return trim($s);
            };

            $count = 0;
            foreach ($target->getRowIterator() as $row) {
                $rows[] = array_map($clean, $row->toArray());
                $count++;
                if ($count >= $maxRows) break;
            }

            $reader->close();

            // Remove fully-empty trailing rows (nice for display)
            $this->rows = $this->trimEmptyRows($rows);
        } catch (\Throwable $e) {
            $this->error = "Unable to read Excel file: ".$e->getMessage();
        }
    }

    private function normalizePath(string $filePath): string
    {
        // Allow passing "public/checklists/abc.xlsx" or absolute paths
        if (str_starts_with($filePath, 'public/')) {
            return storage_path('app/'.$filePath);
        }
        if (str_starts_with($filePath, 'storage/')) {
            // e.g. storage/app/public/...
            return base_path($filePath);
        }
        return $filePath; // assume absolute
    }

    private function trimEmptyRows(array $rows): array
    {
        // drop empty rows at end
        for ($i = count($rows) - 1; $i >= 0; $i--) {
            $isEmpty = true;
            foreach ($rows[$i] as $cell) {
                if ($cell !== '' && $cell !== null) { $isEmpty = false; break; }
            }
            if ($isEmpty) unset($rows[$i]); else break;
        }
        return array_values($rows);
    }

    public function render(): View|Closure|string
    {
        return view('components.excel.preview');
    }
}
