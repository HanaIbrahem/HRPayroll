<?php

namespace App\Livewire;

use App\Models\Department;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class DepartmentComponent extends Component
{
    use WithPagination;

    // Table state
    #[Url] public string $sortField = 'name';
    #[Url] public string $sortDirection = 'asc';
    #[Url] public int $perPage = 10;

    // Global search
    #[Url(as: 'q')] public string $q = '';

    // Specific (column) filters
    #[Url] public string $name = '';
    #[Url] public string $status = ''; // '', '1', '0'
    #[Url] public ?string $from = null; // YYYY-MM-DD
    #[Url] public ?string $to   = null; // YYYY-MM-DD

    public function updated($prop): void
    {
        if (in_array($prop, ['q','name','status','from','to','perPage'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->q = '';
        $this->name = '';
        $this->status = '';
        $this->from = null;
        $this->to = null;
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
        $this->resetPage();
    }

    public function toggleActive(int $id): void
    {
        $dep = Department::findOrFail($id);
        $dep->is_active = ! $dep->is_active;
        $dep->save();

        session()->flash('ok', 'Status updated.');
    }

    public function delete(int $id): void
    {
        $dep = Department::findOrFail($id);
        $dep->delete();

        session()->flash('ok', 'Department deleted.');
        $this->resetPage();
    }

    public function render()
    {
        $query = Department::query();

        // --- Global search (q): matches name, id, or status keywords ---
        if ($this->q !== '') {
            $needle = trim($this->q);
            $lower  = strtolower($needle);

            $query->where(function ($sub) use ($needle, $lower) {
                $sub->where('name', 'like', "%{$needle}%");

                if (ctype_digit($needle)) {
                    $sub->orWhere('id', (int) $needle);
                }

                if (in_array($lower, ['active', 'فعال', 'true', '1'], true)) {
                    $sub->orWhere('is_active', true);
                }
                if (in_array($lower, ['inactive', 'غير فعال', 'false', '0'], true)) {
                    $sub->orWhere('is_active', false);
                }
            });
        }

        // --- Column-specific filters ---
        $query
            ->when($this->name !== '', fn($q) => $q->where('name', 'like', "%{$this->name}%"))
            ->when($this->status !== '', fn($q) => $q->where('is_active', (bool) intval($this->status)))
            ->when($this->from, fn($q) => $q->whereDate('created_at', '>=', $this->from))
            ->when($this->to,   fn($q) => $q->whereDate('created_at', '<=', $this->to));

        // Sorting
        $sortable = ['name', 'is_active', 'created_at'];
        if (in_array($this->sortField, $sortable, true)) {
            $query->orderBy($this->sortField, $this->sortDirection);
        } else {
            $query->latest();
        }

        $departments = $query->paginate($this->perPage);

        return view('livewire.department-component', compact('departments'));
    }
}
