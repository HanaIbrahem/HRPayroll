<?php

namespace App\Livewire\Checklist;

use Livewire\Component;
use App\Models\Checklist;
use Illuminate\Support\Facades\Storage;

class CkecklistShow extends Component
{
    public Checklist $checklist;

    public $sheet='';
    public $path;

    // If user changes the <select wire:model="sheets"> value, mirror it into $sheet
public function updatedSheet($value): void
{
    $this->sheet = $value ?: null;

}



    public function mount( Checklist $checklist)
    {
       
        $this->checklist->load(['employee']);
        

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
        if (!$this->filename) return;

        // If file is in storage/app/... and you have a private disk, you can stream it.
        // If it's public, you can also just link to Storage::url().
        return response()->download(Storage::path($this->filename));
    }

    public function render()
    {
        return view('livewire.checklist.ckecklist-show');
    }
    
}
