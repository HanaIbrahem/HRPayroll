<?php

namespace App\Livewire\Checklist;

use App\Models\Checklist;
use App\Models\Employee;
use App\Services\ExcelZonesProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Str;

class ChecklistEdit extends Component
{
    use WithFileUploads;

    public Checklist $checklist;

    public ?int $employee_id = null;
    public string $employeeSearch = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $file; // optional replacement

    public function mount(Checklist $checklist): void
    {
        // If you have a policy on Checklist, you can uncomment this:
        // Gate::authorize('update', $checklist);

        $this->checklist = $checklist->load('employee');

        // Prefill employee fields
        $this->employee_id = $checklist->employee_id;
        $emp = $checklist->employee;
        if ($emp) {
            $label = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
            $this->employeeSearch = $emp->code ? "{$label} ({$emp->code})" : $label;
        }
    }

    public function render()
    {
        if($this->checklist->canEdit()){

        $employees = Employee::query()
            ->active()
            ->where('user_id', Auth::id())
            ->when($this->employeeSearch !== '', function ($q) {
                $s = $this->employeeSearch;
                $q->where(function ($qq) use ($s) {
                    $qq->where('first_name', 'like', "%{$s}%")
                       ->orWhere('last_name',  'like', "%{$s}%")
                       ->orWhere('code',       'like', "%{$s}%");
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(8)
            ->get(['id','first_name','last_name','code']);

        return view('livewire.checklist.checklist-edit', [
            'employees' => $employees,
        ]);
    }
    return redirect()->back();
    }

    protected function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(
                    fn ($q) => $q->where('user_id', Auth::id())
                ),
            ],
            // File is optional on edit
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv', 'max:20480'], // 20 MB
        ];
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['employee_id', 'file'], true)) {
            $this->validateOnly($prop);
        }
    }

    public function chooseEmployee(int $id, string $label): void
    {
        $this->employee_id   = $id;
        $this->employeeSearch = $label;
        $this->resetErrorBag('employee_id');
    }

    
    public function update(ExcelZonesProvider $provider): void
{
    $this->validate();

    // Ensure manager owns this employee
    $employee = Employee::select('id','user_id')->findOrFail($this->employee_id);
    Gate::authorize('upload-checklist-for-employee', $employee);

    if ($this->file) {
        // 1) Validate Excel content using temp path
        try {
            $provider->validate($this->file->getRealPath(), 'Data');
        } catch (ValidationException $e) {
            $this->addError('file', collect($e->errors())->flatten()->join(' '));
            return;
        }

        $disk = Storage::disk('public');
        $dir  = 'checklists'; // <— single flat directory (no new nested dirs)

        // Try to reuse the old filename if it exists; otherwise build a safe one
        $oldRel    = $this->checklist->filename;
        $reuseName = $oldRel ? basename($oldRel) : null;

        if ($reuseName) {
            $target = $reuseName; // e.g. keep "employee-visits.xlsx"
        } else {
            $orig = $this->file->getClientOriginalName();
            $base = Str::slug(pathinfo($orig, PATHINFO_FILENAME)) ?: 'checklist';
            $ext  = strtolower($this->file->getClientOriginalExtension() ?: 'xlsx');
            $target = "{$base}.{$ext}";

            // ensure unique if needed
            $i = 1;
            while ($disk->exists("$dir/$target")) {
                $target = "{$base}-{$i}.{$ext}";
                $i++;
            }
        }

        // 2) Store new file first (so we don't lose data if store fails)
        $newPath = $this->file->storeAs($dir, $target, 'public'); // "checklists/filename.xlsx"

        // 3) Delete the previous file (if any and different path)
        if ($oldRel && $oldRel !== $newPath) {
            $disk->delete($oldRel);
        }

        // 4) Update model path
        $this->checklist->filename = $newPath;
    }

    // Update employee relation
    $this->checklist->employee_id = $employee->id;
    $this->checklist->save();

    // Reset only the file field
    $this->reset('file');
    $this->resetValidation();

    $this->dispatch('toast', type: 'success', message: 'Checklist updated.');
    $this->dispatch('checklists:updated');
}

}
