<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Department;
class UserComponent extends Component
{
   
    public string $fname  = '';
    public string $lname  = '';
    public string $user_name = '';
    public string $user_role = '';
    public string $password = '';
    public string $password_confirmation = '';

    public ?int $department_id = null;
    public string $deptSearch = '';

    public array $roleOptions = ['manager', 'hr']; 

    protected function rules(): array
    {
        return [
            'fname'       => ['required','string','min:2','max:20'],
            'lname'       => ['required','string','min:2','max:20'],
            'user_name'   => [
                'required','string','min:3','max:10','alpha_dash',
                Rule::unique('users', 'username'),
            ],
            'user_role'     => ['required', Rule::in($this->roleOptions)],
            'department_id' => ['nullable','exists:departments,id'],
            'password'      => ['required','string','min:8','confirmed'],
        ];
    }

    /** Normalize inputs before validating/saving */
    private function normalize(): void
    {
        $this->fname      = trim($this->fname);
        $this->lname      = trim($this->lname);
        $this->user_name  = Str::lower(trim($this->user_name)); // normalize username
        $this->user_role  = trim($this->user_role);
        $this->deptSearch = trim($this->deptSearch);
    }

    /** Called by the suggestions list to set department */
    public function chooseDepartment(int $id, string $name): void
    {
        $this->department_id = $id;
        $this->deptSearch    = $name;
        $this->resetErrorBag('department_id');
    }

    public function updatedDeptSearch($value): void
    {
        if ($this->department_id) {
            $current = Department::whereKey($this->department_id)->value('name');
            if ($current !== $value) {
                $this->department_id = null;
            }
        }
    }

    public function save(): void
    {
        $this->normalize();
       $data    = $this->validate();

        User::create([
            'first_name'    => $this->fname,
            'last_name'     => $this->lname,
            'username'      => $this->user_name,
            'role'          => $this->user_role,
            'department_id' => $this->department_id,
            'is_active'     => true,
            'password'      => Hash::make($this->password),
        ]);

        // Reset form
        $this->reset([
            'fname','lname','user_name','user_role',
            'password','password_confirmation','department_id','deptSearch'
        ]);
        $this->resetValidation();

        // Toast + table refresh
        $this->dispatch('toast', type: 'success', message: 'User created.');
        $this->dispatch('users:updated');
    }

    public function render()
    {
        $deptResults = Department::query()
        ->active()
        ->when($this->deptSearch !== '', fn($q) =>
            $q->where('name', 'like', '%'.$this->deptSearch.'%'))
            ->orderBy('name')->limit(8)
            ->get(['id','name']);

        return view('livewire.auth.user-component', compact('deptResults'));
    }
}
