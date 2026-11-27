<div class="space-y-6">
    <div>
        <div class="text-xs font-semibold uppercase tracking-wider text-teal-700">Administration</div>
        <h1 class="text-2xl font-bold text-slate-900 mt-1">Users &amp; Roles</h1>
        <p class="text-sm text-slate-600 mt-1">Manage user accounts, owner-scoped role assignments, and quarterly access reviews.</p>
    </div>

    <form wire:submit="invite" class="bg-white border border-slate-200 rounded-xl p-5 shadow-sm grid grid-cols-1 md:grid-cols-5 gap-3">
        <input wire:model="name" type="text" placeholder="Full name" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <input wire:model="email" type="email" placeholder="Email address" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
        <select wire:model="role" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            @foreach($roles as $roleName)
                <option value="{{ $roleName }}">{{ $roleName }}</option>
            @endforeach
        </select>
        <select wire:model="owner_type" class="border border-slate-300 rounded-lg px-3 py-2 text-sm">
            <option value="national">National</option>
            <option value="rbda">RBDA (Model 1)</option>
            <option value="state">State (Model 2)</option>
        </select>
        <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white font-semibold rounded-lg px-4 py-2 text-sm">Invite User</button>
    </form>

    <div class="bg-white border border-slate-200 rounded-xl shadow-sm overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-xs font-semibold uppercase text-slate-500">
                <tr>
                    <th class="px-4 py-3">Name</th>
                    <th class="px-4 py-3">Role</th>
                    <th class="px-4 py-3">Scope</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach($users as $u)
                    <tr>
                        <td class="px-4 py-3">
                            <div class="font-medium text-slate-900">{{ $u->name }}</div>
                            <div class="text-xs text-slate-500">{{ $u->email }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $u->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                        <td class="px-4 py-3 uppercase text-xs font-semibold text-slate-600">{{ $u->owner_type }} {{ $u->owner_id ? '#'.$u->owner_id : '' }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $u->status === 'active' ? 'bg-teal-100 text-teal-800' : 'bg-amber-100 text-amber-800' }}">
                                {{ ucfirst($u->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleStatus({{ $u->id }})" class="text-xs font-semibold text-teal-700 hover:underline">
                                {{ $u->status === 'active' ? 'Disable' : 'Activate' }}
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
