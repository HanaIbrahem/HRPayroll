<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\Checklist;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

class ChecklistShow extends Component
{
    public Checklist $checklist;

    public $sheet = '';
    public $path;

    // If user changes the <select wire:model="sheets"> value, mirror it into $sheet
    public function updatedSheet($value): void
    {
        $this->sheet = $value ?: null;

    }



    public function mount(Checklist $checklist)
    {

        $this->checklist->load([
            'employee.location',
            'employee.department',
            'user.department',
            'visitedZones.zone',
        ]);


    }

    public function exportPdf()
    {
        $html = view('exports.checklist-report', [
            'checklist' => $this->checklist,
        ])->render();

        $pdf = app('dompdf.wrapper')
            ->setPaper('a4')
            ->loadHTML($html);

        $name = 'checklist-'.$this->checklist->id.'.pdf';

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->output();
        }, $name, ['Content-Type' => 'application/pdf']);
    }

    public function exportXlsx()
    {
        $name = 'checklist-'.$this->checklist->id.'.xlsx';

        return response()->streamDownload(function () {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile('php://output');

            // --- Title
            $writer->addRow(WriterEntityFactory::createRow([
                WriterEntityFactory::createCell('Checklist #'.$this->checklist->id.' Report'),
            ]));
            $writer->addRow(WriterEntityFactory::createRow([]));

            // --- Overview (key/value)
            $pairs = [
                ['Employee', data_get($this->checklist,'employee.fullname','—')],
                ['Employee Code', data_get($this->checklist,'employee.code','—')],
                ['Employee Location', data_get($this->checklist,'employee.location.name','—')],
                ['Employee Manager', data_get($this->checklist,'user.fullname','—')],
                ['Department', data_get($this->checklist,'employee.department.name','—')],
                ['Status', ucfirst($this->checklist->status)],
                ['Created', optional($this->checklist->created_at)->format('Y-m-d H:i')],
                ['Updated', optional($this->checklist->updated_at)->format('Y-m-d H:i')],
            ];
            foreach ($pairs as $row) {
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell($row[0]),
                    WriterEntityFactory::createCell((string) $row[1]),
                ]));
            }

            $writer->addRow(WriterEntityFactory::createRow([]));

            // --- Visited Zones table
            $writer->addRow(WriterEntityFactory::createRow([
                WriterEntityFactory::createCell('#'),
                WriterEntityFactory::createCell('Code'),
                WriterEntityFactory::createCell('From'),
                WriterEntityFactory::createCell('To'),
                WriterEntityFactory::createCell('Zone Count'),
                WriterEntityFactory::createCell('Repeat Zone'),
            ]));

            foreach ($this->checklist->visitedZones as $i => $vz) {
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell($i + 1),
                    WriterEntityFactory::createCell((string) data_get($vz,'zone.code','—')),
                    WriterEntityFactory::createCell((string) data_get($vz,'zone.from_zone','—')),
                    WriterEntityFactory::createCell((string) data_get($vz,'zone.to_zone','—')),
                    WriterEntityFactory::createCell((int) $vz->zone_count),
                    WriterEntityFactory::createCell((int) $vz->repeat_count),
                ]));
            }

            // Totals
            if ($this->checklist->visitedZones->isNotEmpty()) {
                $writer->addRow(WriterEntityFactory::createRow([]));
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell('Totals'),
                    WriterEntityFactory::createCell(''),
                    WriterEntityFactory::createCell(''),
                    WriterEntityFactory::createCell(''),
                    WriterEntityFactory::createCell((int) $this->checklist->visitedZones->sum('zone_count')),
                    WriterEntityFactory::createCell((int) $this->checklist->visitedZones->sum('repeat_count')),
                ]));
            }

            $writer->close();
        }, $name, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }
    public function getExcelPathProperty(): ?string
    {
        // adapt to your column name(s):
        return $this->checklist->filename ?? $this->checklist->file_path ?? null;
    }

    public function getExcelSheetProperty(): ?string
    {
        // adapt to your column name(s):
        return $this->checklist->filename ?? $this->checklist->sheet ?? 'Data';
    }

    public function downloadExcel()
    {
        if (!$this->filename)
            return;

        // If file is in storage/app/... and you have a private disk, you can stream it.
        // If it's public, you can also just link to Storage::url().
        return response()->download(Storage::path($this->filename));
    }

    public function render()
    {
        return view('livewire.hr.checklist-show');
    }
}
