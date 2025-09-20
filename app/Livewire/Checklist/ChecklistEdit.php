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
use Carbon\Carbon;
class ChecklistEdit extends Component
{
    use WithFileUploads;

    public Checklist $checklist;

    public ?int $employee_id = null;
    public string $employeeSearch = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $file; // optional replacement

    public string $note = '';
    public ?string $start_date = null;
    public ?string $end_date = null;

    public function mount(Checklist $checklist): void
    {
        // Gate::authorize('update', $checklist);

        $this->checklist = $checklist->load('employee');

        // Prefill employee
        $this->employee_id = $checklist->employee_id;
        if ($emp = $checklist->employee) {
            $label = trim(($emp->first_name ?? '') . ' ' . ($emp->last_name ?? ''));
            $this->employeeSearch = $emp->code ? "{$label} ({$emp->code})" : $label;
        }

        // Prefill note
        $this->note = (string) ($checklist->note ?? '');
        $this->start_date = $checklist->start_date
            ? Carbon::parse($checklist->start_date)->toDateString()
            : Carbon::now()->startOfMonth()->toDateString();

        $this->end_date = $checklist->end_date
            ? Carbon::parse($checklist->end_date)->toDateString()
            : Carbon::now()->toDateString();
    }

    public function render()
    {
        if (!$this->checklist->canEdit()) {
            return redirect()->back();
        }

        $employees = Employee::query()
            ->active()
            ->where('user_id', Auth::id())
            ->when($this->employeeSearch !== '', function ($q) {
                $s = $this->employeeSearch;
                $q->where(function ($qq) use ($s) {
                    $qq->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%")
                        ->orWhere('code', 'like', "%{$s}%");
                });
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->limit(8)
            ->get(['id', 'first_name', 'last_name', 'code']);

        return view('livewire.checklist.checklist-edit', [
            'employees' => $employees,
        ]);
    }

    protected function rules(): array
    {
        return [
            'employee_id' => [
                'required',
                Rule::exists('employees', 'id')->where(
                    fn($q) => $q->where('user_id', Auth::id())
                ),
            ],
            // File is optional on edit
            'file' => ['nullable', 'file', 'mimes:xlsx,xls,csv', 'max:2480'],
            'note' => ['nullable', 'string', 'max:500'],
            'start_date' => ['required', 'date', 'before_or_equal:end_date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    public function updated($prop): void
    {
        if (in_array($prop, ['employee_id', 'file', 'note'], true)) {
            $this->validateOnly($prop);
        }
    }

    public function chooseEmployee(int $id, string $label): void
    {
        $this->employee_id = $id;
        $this->employeeSearch = $label;
        $this->resetErrorBag('employee_id');
    }

    public function update(ExcelZonesProvider $provider): void
    {
        $this->validate();

        // Ensure manager owns this employee
        $employee = Employee::select('id', 'user_id')->findOrFail($this->employee_id);
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
            $folder = now()->format('Y/m') . '/checklists';

            $oldRel = $this->checklist->filename;
            $reuseName = $oldRel ? basename($oldRel) : null;

            if ($reuseName) {
                $target = $reuseName;
            } else {
                $orig = $this->file->getClientOriginalName();
                $base = Str::slug(pathinfo($orig, PATHINFO_FILENAME)) ?: 'checklist';
                $ext = strtolower($this->file->getClientOriginalExtension() ?: 'xlsx');
                $target = "{$base}.{$ext}";
                $i = 1;
                while ($disk->exists("$folder/$target")) {
                    $target = "{$base}-{$i}.{$ext}";
                    $i++;
                }
            }

            // 2) Store new file in structured folder
            $newPath = $this->file->storeAs($folder, $target, 'public');

            // 3) Delete the previous file if different
            if ($oldRel && $oldRel !== $newPath) {
                $disk->delete($oldRel);
            }

            // 4) Update model with new path
            $this->checklist->filename = $newPath;
        }

        $this->checklist->start_date = $this->start_date;
        $this->checklist->end_date = $this->end_date;
        $this->checklist->employee_id = $employee->id;
        $this->checklist->note = $this->note;
        $this->checklist->save();

        $this->reset('file');
        $this->resetValidation();

        $this->dispatch('toast', type: 'success', message: 'Checklist updated.');
        $this->dispatch('checklists:updated');
    }

}
