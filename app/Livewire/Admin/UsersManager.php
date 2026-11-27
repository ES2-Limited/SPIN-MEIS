<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Livewire\Component;
use Spatie\Permission\Models\Role;

class UsersManager extends Component
{
    public string $name = '';

    public string $email = '';

    public string $role = 'SPIU M&E Officer';

    public string $owner_type = 'state';

    public ?int $owner_id = null;

    public function invite(): void
    {
        $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:users,email'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'owner_type' => ['required', 'in:national,rbda,state'],
            'owner_id' => ['nullable', 'integer'],
        ]);

        $user = User::create([
            'name' => $this->name,
            'email' => $this->email,
            'password' => Hash::make(Str::random(24)),
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_type === 'national' ? null : $this->owner_id,
            'status' => 'invited',
            'invited_at' => now(),
        ]);

        $user->assignRole($this->role);
        $this->reset(['name', 'email', 'owner_id']);
    }

    public function toggleStatus(int $userId): void
    {
        $user = User::findOrFail($userId);
        if ($user->status === 'active') {
            $user->update(['status' => 'disabled', 'disabled_at' => now()]);
        } else {
            $user->update(['status' => 'active', 'activated_at' => now(), 'disabled_at' => null]);
        }
    }

    public function render(): View
    {
        return view('livewire.admin.users-manager', [
            'users' => User::with('roles')->orderBy('name')->get(),
            'roles' => Role::orderBy('name')->pluck('name'),
        ])->layout('components.layouts.app', ['title' => 'Users & Roles']);
    }
}
