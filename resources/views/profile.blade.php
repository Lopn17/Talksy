@extends('layouts.app')

@section('content')

<style>
    .profile-layout {
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
        max-width: 480px;
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

    .profile-avatar-section {
        width: 100%;
        max-width: 480px;
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1.25rem 1.5rem;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    .profile-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        font-weight: 500;
        color: white;
        text-transform: uppercase;
        flex-shrink: 0;
    }

    .profile-avatar-info h3 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.125rem;
        font-weight: 400;
        color: var(--ink);
    }

    .profile-avatar-info p {
        font-size: 0.8125rem;
        color: var(--ink-3);
        margin-top: 2px;
    }

    .profile-card {
        width: 100%;
        max-width: 480px;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    .profile-card-header {
        padding: 1.125rem 1.5rem;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 0.625rem;
    }

    .profile-card-header h2 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.075rem;
        font-weight: 400;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .profile-card-header svg {
        width: 16px;
        height: 16px;
        stroke: var(--accent);
        fill: none;
        stroke-width: 1.75;
        stroke-linecap: round;
        stroke-linejoin: round;
    }

    .profile-card-body {
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 1.125rem;
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

    .field-input {
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

    .field-input:focus {
        border-color: rgba(232,98,42,0.4);
        box-shadow: 0 0 0 3px rgba(232,98,42,0.07);
        background: var(--white);
    }

    .field-input.is-error {
        border-color: #dc2626;
        box-shadow: 0 0 0 3px rgba(220,38,38,0.07);
    }

    .field-error {
        font-size: 0.75rem;
        color: #dc2626;
    }

    .save-btn {
        align-self: flex-end;
        padding: 0.5rem 1.25rem;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
    }

    .save-btn:hover { opacity: 0.88; }
    .save-btn:active { transform: scale(0.97); }

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
        .profile-layout { padding: 1.25rem 1rem; }
    }
</style>

<div class="profile-layout">

    <div class="page-title">
        <h1>Account Settings</h1>
        <p>Update your name, email, and password</p>
    </div>

    {{-- Avatar card --}}
    <div class="profile-avatar-section">
        <div class="profile-avatar">{{ substr($user->name, 0, 1) }}</div>
        <div class="profile-avatar-info">
            <h3>{{ $user->name }}</h3>
            <p>{{ $user->email }}</p>
        </div>
    </div>

    {{-- Profile info --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
            <h2>Profile Info</h2>
        </div>
        <div class="profile-card-body">

            @if(session('success'))
                <div class="alert-success">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            <form action="/profile" method="POST" style="display:flex;flex-direction:column;gap:1.125rem;">
                @csrf
                @method('PATCH')

                <div class="field-group">
                    <label class="field-label">Name</label>
                    <input type="text" name="name"
                        class="field-input {{ $errors->has('name') ? 'is-error' : '' }}"
                        value="{{ old('name', $user->name) }}"
                        placeholder="Your name"
                        autocomplete="name">
                    @error('name') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">Email</label>
                    <input type="email" name="email"
                        class="field-input {{ $errors->has('email') ? 'is-error' : '' }}"
                        value="{{ old('email', $user->email) }}"
                        placeholder="you@example.com"
                        autocomplete="email">
                    @error('email') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <button type="submit" class="save-btn">Save Changes</button>
            </form>
        </div>
    </div>

    {{-- Password --}}
    <div class="profile-card">
        <div class="profile-card-header">
            <svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            <h2>Change Password</h2>
        </div>
        <div class="profile-card-body">

            @if(session('password_success'))
                <div class="alert-success">
                    <svg viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    {{ session('password_success') }}
                </div>
            @endif

            <form action="/profile/password" method="POST" style="display:flex;flex-direction:column;gap:1.125rem;">
                @csrf
                @method('PATCH')

                <div class="field-group">
                    <label class="field-label">Current Password</label>
                    <input type="password" name="current_password"
                        class="field-input {{ $errors->has('current_password') ? 'is-error' : '' }}"
                        placeholder="••••••••"
                        autocomplete="current-password">
                    @error('current_password') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">New Password</label>
                    <input type="password" name="password"
                        class="field-input {{ $errors->has('password') ? 'is-error' : '' }}"
                        placeholder="••••••••"
                        autocomplete="new-password">
                    @error('password') <span class="field-error">{{ $message }}</span> @enderror
                </div>

                <div class="field-group">
                    <label class="field-label">Confirm New Password</label>
                    <input type="password" name="password_confirmation"
                        class="field-input"
                        placeholder="••••••••"
                        autocomplete="new-password">
                </div>

                <button type="submit" class="save-btn">Update Password</button>
            </form>
        </div>
    </div>

</div>

@endsection