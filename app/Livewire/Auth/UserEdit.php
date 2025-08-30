<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Department;
class UserEdit extends Component
{
    public User $user;

    // Form fields
    public string $fname = '';
    public string $lname = '';
    public string $user_name = '';
    public string $user_role = '';
    public ?string $password = null;
    public ?string $password_confirmation = null;

    public ?int $department_id = null;
    public string $deptSearch = '';

    public array $roleOptions = ['manager', 'hr']; 

    public function mount(User $user): void
    {
        $this->user = $user;

        $this->fname       = (string) ($user->first_name ?? '');
        $this->lname       = (string) ($user->last_name ?? '');
        $this->user_name   = (string) ($user->username ?? '');
        $this->user_role   = (string) ($user->role ?? '');
        $this->department_id = $user->department_id;

        // Pre-fill visible department search text
        $this->deptSearch = optional($user->department)->name ?? '';
    }

    protected function rules(): array
    {
        return [
            'fname'       => ['required','string','min:2','max:20'],
            'lname'       => ['required','string','min:2','max:20'],
            'user_name'   => [
                'required','string','min:3','max:10','alpha_dash',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'user_role'     => ['required', Rule::in($this->roleOptions)],
            'department_id' => ['required','exists:departments,id'],
            'password' => ['nullable','string','min:8','confirmed'],
        ];
    }

    private function normalize(): void
    {
        $this->fname      = trim($this->fname);
        $this->lname      = trim($this->lname);
        $this->user_name  = Str::lower(trim($this->user_name)); // keep usernames normalized
        $this->user_role  = trim($this->user_role);
        $this->deptSearch = trim($this->deptSearch);
    }

    /** Department picker helpers */
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
        $data = $this->validate();

        // Build the update payload
        $payload = [
            'first_name'    => $this->fname,
            'last_name'     => $this->lname,
            'username'      => $this->user_name,
            'role'          => $this->user_role,
            'department_id' => $this->department_id,
        ];

        if (!empty($this->password)) {
            $payload['password'] = Hash::make($this->password);
        }

        $this->user->update($payload);

        // Optionally reset only password fields
        $this->reset(['password','password_confirmation']);
        $this->resetValidation();

        $this->dispatch('toast', type: 'success', message: 'User updated.');
        $this->dispatch('users:updated');
    }

    public function render()
    {
        $deptResults = Department::query()
            ->active()
            ->when($this->deptSearch !== '', fn($q) =>
                $q->where('name', 'like', '%'.$this->deptSearch.'%'))
            ->orderBy('name')
            ->limit(8)
            ->get(['id','name']);

        return view('livewire.auth.user-edit', compact('deptResults'));
    }
}
