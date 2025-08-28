<?php

namespace App\Livewire\Checklist;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Models\Checklist;
use App\Models\Employee;
use App\Services\ExcelZonesProvider;
use Livewire\WithFileUploads;
class ChecklistCreate extends Component
{
    use WithFileUploads;
    public ?int $employee_id = null;
    public string $employeeSearch = '';
    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $file;

    public function render()
    {
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

        return view('livewire.checklist.checklist-create', [

            'employees' => $employees
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
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:20480'], // 20 MB
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
        $this->employee_id = $id;
        $this->employeeSearch = $label;
        $this->resetErrorBag('employee_id');
    }

    public function save(ExcelZonesProvider $provider): void
    {
        $this->validate();

        // Ensure manager owns this employee
        $employee = Employee::select('id', 'user_id')->findOrFail($this->employee_id);
        Gate::authorize('upload-checklist-for-employee', $employee);


        try {
            $provider = app(ExcelZonesProvider::class);
            $provider->validate($this->file->getRealPath(), 'Data');
        } catch (ValidationException $e) {
            $this->addError('file', collect($e->errors())->flatten()->join(' '));
            return;
        }
        // 2) Store after successful validation
        $path = $this->file->store('checklists', 'public');

        Checklist::create([
            'user_id' => Auth::id(),
            'employee_id' => $employee->id,
            'filename' => $path,
            'status' => 'open',
        ]);

        // Reset form
        $this->reset(['employee_id', 'employeeSearch', 'file']);
        $this->resetValidation();

        // Toast + optional table refresh
        $this->dispatch('toast', type: 'success', message: 'Checklist created.');
        $this->dispatch('checklists:updated');
    }

}
