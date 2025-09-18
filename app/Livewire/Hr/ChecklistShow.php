<?php

namespace App\Livewire\Hr;

use Livewire\Component;
use App\Models\Checklist;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Mpdf\Mpdf;
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
        $checklistpdf = $this->checklist->load([
            'employee.location',
            'employee.department',
            'user.department',
            'visitedZones.zone',
        ]);
        $html = view('exports.checklist-report', [
            'checklist' => $checklistpdf,
        ])->render();

        // Create mPDF instance with Arabic font support
        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'default_font' => 'dejavusans',
        ]);

        $mpdf->WriteHTML($html);

        $name = 'checklist-' . $this->checklist->id . '.pdf';

        return response()->streamDownload(function () use ($mpdf) {
            echo $mpdf->Output('', 'S'); // Output as string
        }, $name, ['Content-Type' => 'application/pdf']);
    }


    public function exportXlsx()
    {
        $checklist = $this->checklist->load([
            'employee.location',
            'employee.department',
            'user.department',
            'visitedZones.zone',
        ]);

        $name = 'checklist-' . $checklist->id . '.xlsx';

        return response()->streamDownload(function () use ($checklist) {
            $writer = WriterEntityFactory::createXLSXWriter();
            $writer->openToFile('php://output');

            // Title
            $writer->addRow(WriterEntityFactory::createRow([
                WriterEntityFactory::createCell('Checklist #' . $checklist->id . ' Report'),
            ]));
            $writer->addRow(WriterEntityFactory::createRow([]));

            // Overview
            $pairs = [
                ['Employee', data_get($checklist, 'employee.fullname', '—')],
                ['Employee Code', data_get($checklist, 'employee.code', '—')],
                ['Employee Location', data_get($checklist, 'employee.location.name', '—')],
                ['Employee Manager', data_get($checklist, 'user.fullname', '—')],
                ['Department', data_get($checklist, 'employee.department.name', '—')],
                ['Status', ucfirst($checklist->status)],
                ['Created', optional($checklist->created_at)->format('Y-m-d H:i')],
                ['Updated', optional($checklist->updated_at)->format('Y-m-d H:i')],
            ];
            foreach ($pairs as $row) {
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell($row[0]),
                    WriterEntityFactory::createCell((string) $row[1]),
                ]));
            }

            $writer->addRow(WriterEntityFactory::createRow([]));

            // Visited Zones table
            $writer->addRow(WriterEntityFactory::createRow([
                WriterEntityFactory::createCell('#'),
                WriterEntityFactory::createCell('Code'),
                WriterEntityFactory::createCell('From'),
                WriterEntityFactory::createCell('To'),
                WriterEntityFactory::createCell('Zone Count'),
                WriterEntityFactory::createCell('Repeat Zone'),
                WriterEntityFactory::createCell('Total Cost'),

            ]));

            foreach ($checklist->visitedZones as $i => $vz) {
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell($i + 1),
                    WriterEntityFactory::createCell((string) data_get($vz, 'zone.code', '—')),
                    WriterEntityFactory::createCell((string) data_get($vz, 'zone.from_zone', '—')),
                    WriterEntityFactory::createCell((string) data_get($vz, 'zone.to_zone', '—')),
                    WriterEntityFactory::createCell((int) $vz->zone_count),
                    WriterEntityFactory::createCell((int) $vz->repeat_count),
                    WriterEntityFactory::createCell((int) $vz->calculated_cost),
               
                ]));
            }

            if ($checklist->visitedZones->isNotEmpty()) {
                $writer->addRow(WriterEntityFactory::createRow([]));
                $writer->addRow(WriterEntityFactory::createRow([
                    WriterEntityFactory::createCell('Totals'),
                    WriterEntityFactory::createCell(''),
                    WriterEntityFactory::createCell(''),
                    WriterEntityFactory::createCell(''),
                    WriterEntityFactory::createCell((int) $checklist->visitedZones->sum('zone_count')),
                    WriterEntityFactory::createCell((int) $checklist->visitedZones->sum('repeat_count')),
                    WriterEntityFactory::createCell((int) $checklist->calculated_cost),
              
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
