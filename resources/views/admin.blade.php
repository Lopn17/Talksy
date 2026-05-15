@extends('layouts.app')

@section('content')

<style>
    .admin-layout {
        flex: 1;
        overflow-y: auto;
        padding: 2rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 1.5rem;
    }

    .page-title {
        width: 100%;
        max-width: 680px;
    }

    .page-title h1 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.5rem;
        font-weight: 400;
        color: var(--ink);
        letter-spacing: -0.02em;
    }

    .page-title p {
        font-size: 0.8125rem;
        color: var(--ink-3);
        margin-top: 4px;
    }

    .admin-card {
        width: 100%;
        max-width: 680px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
    }

    .admin-card-header {
        padding: 1.125rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }

    .admin-card-header-left {
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .admin-card-header h2 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.075rem;
        font-weight: 400;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .admin-card-header svg {
        width: 16px;
        height: 16px;
        stroke: var(--accent);
        fill: none;
        stroke-width: 1.75;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    /* User list */
    .user-list {
        display: flex;
        flex-direction: column;
    }

    .user-item {
        display: flex;
        align-items: center;
        gap: 0.875rem;
        padding: 0.875rem 1.5rem;
        border-bottom: 1px solid var(--border);
        transition: background 0.1s;
    }

    .user-item:last-child { border-bottom: none; }
    .user-item:hover { background: var(--surface); }

    .user-item-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.875rem;
        font-weight: 500;
        color: white;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .user-item-avatar.admin-avatar {
        background: #2563eb;
    }

    .user-item-info {
        flex: 1;
        min-width: 0;
    }

    .user-item-name {
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--ink);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .role-badge {
        font-size: 0.625rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        padding: 0.15rem 0.4rem;
        border-radius: 4px;
        background: #dbeafe;
        color: #1d4ed8;
    }

    .role-badge.user {
        background: var(--surface-2);
        color: var(--ink-3);
    }

    .user-item-email {
        font-size: 0.75rem;
        color: var(--ink-3);
        margin-top: 1px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .user-item-actions {
        display: flex;
        gap: 0.375rem;
        flex-shrink: 0;
    }

    .action-btn {
        width: 30px;
        height: 30px;
        border-radius: 7px;
        border: 1px solid var(--border);
        background: var(--white);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.15s, border-color 0.15s;
    }

    .action-btn svg {
        width: 13px;
        height: 13px;
        stroke: var(--ink-2);
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .action-btn:hover { background: var(--surface-2); }

    .action-btn.danger:hover {
        background: #fef2f2;
        border-color: #fca5a5;
    }

    .action-btn.danger:hover svg { stroke: #dc2626; }

    /* Modal */
    .modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,0.4);
        z-index: 200;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .modal-backdrop.open { display: flex; }

    .modal {
        background: var(--white);
        border-radius: 16px;
        width: 100%;
        max-width: 420px;
        box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        animation: modalIn 0.2s ease;
        overflow: hidden;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: scale(0.96) translateY(8px); }
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }

    .modal-header {
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .modal-header h3 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.125rem;
        font-weight: 400;
        color: var(--ink);
    }

    .modal-close {
        background: none;
        border: none;
        cursor: pointer;
        color: var(--ink-3);
        padding: 4px;
        border-radius: 6px;
        display: flex;
        transition: color 0.15s, background 0.15s;
    }

    .modal-close:hover { color: var(--ink); background: var(--surface); }
    .modal-close svg { width: 18px; height: 18px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }

    .modal-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .field-group {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
    }

    .field-label {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--ink-2);
    }

    .field-input, .field-select {
        padding: 0.625rem 0.875rem;
        border: 1px solid var(--border);
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        color: var(--ink);
        background: var(--surface);
        outline: none;
        transition: border-color 0.15s, box-shadow 0.15s;
        width: 100%;
    }

    .field-input:focus, .field-select:focus {
        border-color: rgba(232,98,42,0.4);
        box-shadow: 0 0 0 3px rgba(232,98,42,0.07);
        background: var(--white);
    }

    .field-error {
        font-size: 0.75rem;
        color: #dc2626;
    }

    .modal-footer {
        padding: 1rem 1.5rem;
        border-top: 1px solid var(--border);
        display: flex;
        justify-content: flex-end;
        gap: 0.5rem;
    }

    .btn {
        padding: 0.5rem 1.125rem;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        border: 1px solid var(--border);
        transition: opacity 0.15s, background 0.15s;
    }

    .btn-primary {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    .btn-primary:hover { opacity: 0.88; }
    .btn-secondary { background: var(--white); color: var(--ink-2); }
    .btn-secondary:hover { background: var(--surface-2); }

    .alert-success {
        padding: 0.625rem 0.875rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        background: #f0fdf4;
        color: #15803d;
        border: 1px solid #bbf7d0;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        width: 100%;
        max-width: 680px;
    }

    .alert-success svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2.5;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
    }

    @media (max-width: 640px) {
        .admin-layout { padding: 1.25rem 1rem; }
        .user-item { padding: 0.75rem 1rem; }
        .admin-card-header { padding: 1rem; }
    }
</style>

<div class="admin-layout">

    <div class="page-title">
        <h1>Manage Users</h1>
        <p>Create, edit, or delete user accounts</p>
    </div>

    @if(session('success'))
        <div class="alert-success">
            <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- User list --}}
    <div class="admin-card">
        <div class="admin-card-header">
            <div class="admin-card-header-left">
                <svg viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                <h2>All Users ({{ $users->count() }})</h2>
            </div>
            <button class="btn btn-primary" onclick="openCreateModal()" style="font-size:0.8125rem; padding: 0.4rem 0.875rem;">
                + New User
            </button>
        </div>

        <div class="user-list">
            @foreach($users as $user)
                <div class="user-item">
                    <div class="user-item-avatar {{ $user->isAdmin() ? 'admin-avatar' : '' }}">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div class="user-item-info">
                        <div class="user-item-name">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span style="font-size:0.625rem;color:var(--ink-3);">(you)</span>
                            @endif
                            <span class="role-badge {{ $user->role }}">{{ $user->role }}</span>
                        </div>
                        <div class="user-item-email">{{ $user->email }}</div>
                    </div>
                    <div class="user-item-actions">
                        <button
                            class="action-btn"
                            onclick="openEditModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}', '{{ $user->role }}')"
                            title="Edit"
                        >
                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        </button>
                        @if($user->id !== auth()->id())
                            <form action="/admin/users/{{ $user->id }}" method="POST" onsubmit="return confirm('Delete {{ $user->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button class="action-btn danger" type="submit" title="Delete">
                                    <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>

</div>

{{-- Create Modal --}}
<div class="modal-backdrop" id="create-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Create New User</h3>
            <button class="modal-close" onclick="closeCreateModal()">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form action="/admin/users" method="POST">
            @csrf
            <div class="modal-body">
                <div class="field-group">
                    <label class="field-label">Name</label>
                    <input type="text" name="name" class="field-input" placeholder="Full name" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Email</label>
                    <input type="email" name="email" class="field-input" placeholder="user@example.com" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Password</label>
                    <input type="password" name="password" class="field-input" placeholder="••••••••" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Role</label>
                    <select name="role" class="field-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeCreateModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Create User</button>
            </div>
        </form>
    </div>
</div>

{{-- Edit Modal --}}
<div class="modal-backdrop" id="edit-modal">
    <div class="modal">
        <div class="modal-header">
            <h3>Edit User</h3>
            <button class="modal-close" onclick="closeEditModal()">
                <svg viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <form id="edit-form" action="" method="POST">
            @csrf
            @method('PATCH')
            <div class="modal-body">
                <div class="field-group">
                    <label class="field-label">Name</label>
                    <input type="text" name="name" id="edit-name" class="field-input" required>
                </div>
                <div class="field-group">
                    <label class="field-label">Email</label>
                    <input type="email" name="email" id="edit-email" class="field-input" required>
                </div>
                <div class="field-group">
                    <label class="field-label">New Password <span style="color:var(--ink-3);font-weight:400;">(leave blank to keep)</span></label>
                    <input type="password" name="password" class="field-input" placeholder="••••••••">
                </div>
                <div class="field-group">
                    <label class="field-label">Role</label>
                    <select name="role" id="edit-role" class="field-select">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('create-modal').classList.add('open');
}
function closeCreateModal() {
    document.getElementById('create-modal').classList.remove('open');
}

function openEditModal(id, name, email, role) {
    document.getElementById('edit-name').value  = name;
    document.getElementById('edit-email').value = email;
    document.getElementById('edit-role').value  = role;
    document.getElementById('edit-form').action = `/admin/users/${id}`;
    document.getElementById('edit-modal').classList.add('open');
}
function closeEditModal() {
    document.getElementById('edit-modal').classList.remove('open');
}

// Close on backdrop click
document.getElementById('create-modal').addEventListener('click', function(e) {
    if (e.target === this) closeCreateModal();
});
document.getElementById('edit-modal').addEventListener('click', function(e) {
    if (e.target === this) closeEditModal();
});
</script>

@endsection