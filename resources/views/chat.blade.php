@extends('layouts.app')

@section('content')

<style>
    .chat-layout {
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .chat-header {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid var(--border);
        background: var(--white);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-shrink: 0;
    }

    .chat-header-icon {
        width: 36px;
        height: 36px;
        background: var(--accent-dim);
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .chat-header-icon svg {
        width: 16px;
        height: 16px;
        color: var(--accent);
        stroke: var(--accent);
    }

    .chat-header-info h1 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.125rem;
        font-weight: 400;
        color: var(--ink);
        letter-spacing: -0.01em;
        line-height: 1.2;
    }

    .chat-header-info p {
        font-size: 0.75rem;
        color: var(--ink-3);
        margin-top: 1px;
    }

    .online-dot {
        display: inline-block;
        width: 6px;
        height: 6px;
        background: #22c55e;
        border-radius: 50%;
        margin-right: 4px;
        vertical-align: middle;
    }

    /* Messages */
    .chat-messages {
        flex: 1;
        overflow-y: auto;
        padding: 1.5rem;
        display: flex;
        flex-direction: column;
        gap: 0.625rem;
        scroll-behavior: smooth;
    }

    .chat-messages::-webkit-scrollbar { width: 4px; }
    .chat-messages::-webkit-scrollbar-track { background: transparent; }
    .chat-messages::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .msg-row {
        display: flex;
        align-items: flex-end;
        gap: 0.5rem;
        animation: msgIn 0.2s ease;
        position: relative;
    }

    @keyframes msgIn {
        from { opacity: 0; transform: translateY(6px); }
        to   { opacity: 1; transform: translateY(0); }
    }

    .msg-row.own { flex-direction: row-reverse; }

    .msg-avatar {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        background: var(--accent);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.6875rem;
        font-weight: 500;
        color: white;
        flex-shrink: 0;
        text-transform: uppercase;
        margin-bottom: 2px;
    }

    .msg-avatar.other {
        background: var(--surface-2);
        color: var(--ink-2);
        border: 1px solid var(--border);
    }

    .msg-bubble-wrap {
        max-width: 62%;
        display: flex;
        flex-direction: column;
        gap: 2px;
        position: relative;
    }

    .msg-row.own .msg-bubble-wrap { align-items: flex-end; }

    /* Admin bubble — blue & shiny */
    .msg-row.own .msg-bubble.admin-bubble {
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #3b82f6 100%);
        border-color: #2563eb;
        color: white;
        box-shadow: 0 2px 12px rgba(37,99,235,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
    }

    .msg-row.own .msg-bubble.admin-bubble .msg-edited-tag {
        color: rgba(255,255,255,0.65);
    }

    .msg-name {
        font-size: 0.6875rem;
        font-weight: 500;
        color: var(--ink-3);
        padding: 0 0.5rem;
    }

    .admin-name {
        color: var(--accent);
        font-weight: 600;
    }

    .admin-badge {
        display: inline-block;
        font-size: 0.55rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        background: var(--accent-dim);
        color: var(--accent);
        border: 1px solid rgba(232,98,42,0.25);
        border-radius: 4px;
        padding: 1px 5px;
        margin-left: 4px;
        vertical-align: middle;
    }

    .msg-row:not(.own) .msg-bubble.admin-bubble {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border-color: #93c5fd;
        color: #1e40af;
        box-shadow: 0 2px 8px rgba(37,99,235,0.1);
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 50%, #3b82f6 100%);
        border-color: #2563eb;
        color: white;
        box-shadow: 0 2px 12px rgba(37,99,235,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
    }

    /* Bubble + chevron wrapper */
    .msg-bubble-inner {
        position: relative;
        display: inline-flex;
        align-items: flex-start;
        max-width: 100%;
    }

    .msg-bubble {
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 14px 14px 14px 4px;
        padding: 0.625rem 0.875rem;
        font-size: 0.9rem;
        color: var(--ink);
        line-height: 1.5;
        word-break: break-word;
        min-width: 60px;
    }

    .msg-row.own .msg-bubble {
        background: var(--accent);
        border-color: var(--accent);
        color: white;
        border-radius: 14px 14px 4px 14px;
    }

    /* Deleted bubble */
    .msg-bubble.deleted {
        background: var(--surface) !important;
        border-color: var(--border) !important;
        color: var(--ink-3) !important;
        font-style: italic;
        border-radius: 14px !important;
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .msg-bubble.deleted svg {
        width: 14px;
        height: 14px;
        flex-shrink: 0;
        opacity: 0.6;
    }

    .msg-time {
        font-size: 0.625rem;
        color: var(--ink-3);
        padding: 0 0.5rem;
    }

    .msg-row.own .msg-time { text-align: right; }

    .msg-edited-tag {
        font-size: 0.6rem;
        color: rgba(255,255,255,0.65);
        font-style: italic;
        margin-left: 4px;
    }

    .msg-row:not(.own) .msg-edited-tag {
        color: var(--ink-3);
    }

    /* ── Chevron trigger ── */
    .msg-menu-trigger {
        opacity: 0;
        pointer-events: none;
        width: 20px;
        height: 20px;
        border-radius: 50%;
        background: rgba(0,0,0,0.12);
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        align-self: flex-start;
        margin-top: 6px;
        transition: opacity 0.15s, background 0.15s;
        position: relative;
        z-index: 1;
    }

    .msg-row.own .msg-bubble-inner .msg-menu-trigger {
        background: rgba(0,0,0,0.18);
    }

    .msg-menu-trigger svg {
        width: 11px;
        height: 11px;
        fill: rgba(0,0,0,0.55);
        flex-shrink: 0;
    }

    .msg-row.own .msg-menu-trigger svg {
        fill: rgba(255,255,255,0.85);
    }

    /* Show on hover (desktop) */
    .msg-bubble-inner:hover .msg-menu-trigger {
        opacity: 1;
        pointer-events: auto;
    }

    .msg-menu-trigger.active {
        opacity: 1 !important;
        pointer-events: auto !important;
        background: rgba(0,0,0,0.22);
    }

    /* ── Dropdown menu ── */
    .msg-dropdown {
        display: none;
        position: absolute;
        top: calc(100% + 4px);
        right: 0;
        background: var(--white);
        border: 1px solid var(--border);
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12), 0 2px 6px rgba(0,0,0,0.06);
        min-width: 140px;
        z-index: 100;
        overflow: hidden;
        animation: dropIn 0.15s ease;
    }

    /* For own messages: anchor to right side of bubble */
    .msg-row.own .msg-dropdown {
        right: 0;
        left: auto;
    }

    /* For other messages: anchor to left */
    .msg-row:not(.own) .msg-dropdown {
        left: 0;
        right: auto;
    }

    @keyframes dropIn {
        from { opacity: 0; transform: translateY(-6px) scale(0.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .msg-dropdown.open { display: block; }

    .msg-dropdown-item {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        width: 100%;
        padding: 0.625rem 0.875rem;
        border: none;
        background: none;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        color: var(--ink);
        cursor: pointer;
        text-align: left;
        transition: background 0.1s;
    }

    .msg-dropdown-item:hover { background: var(--surface); }

    .msg-dropdown-item.danger { color: #dc2626; }
    .msg-dropdown-item.danger:hover { background: #fef2f2; }

    .msg-dropdown-item svg {
        width: 14px;
        height: 14px;
        stroke: currentColor;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
        flex-shrink: 0;
    }

    /* Undo button */
    .undo-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        font-size: 0.75rem;
        font-style: normal;
        color: var(--accent);
        background: none;
        border: none;
        cursor: pointer;
        padding: 0;
        font-family: inherit;
        margin-left: 4px;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .undo-btn:hover { opacity: 0.75; }

    /* Edit inline form */
    .edit-form {
        display: none;
        flex-direction: column;
        gap: 0.375rem;
        width: 100%;
    }

    .edit-form.active { display: flex; }

    .edit-textarea {
        width: 100%;
        padding: 0.5rem 0.75rem;
        border: 1px solid rgba(232,98,42,0.4);
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        color: var(--ink);
        background: var(--white);
        resize: none;
        outline: none;
        line-height: 1.5;
        box-shadow: 0 0 0 3px rgba(232,98,42,0.07);
    }

    .edit-actions {
        display: flex;
        gap: 0.375rem;
        justify-content: flex-end;
    }

    .edit-save-btn, .edit-cancel-btn {
        font-size: 0.75rem;
        padding: 0.25rem 0.625rem;
        border-radius: 6px;
        border: 1px solid var(--border);
        cursor: pointer;
        font-family: inherit;
        transition: background 0.15s;
    }

    .edit-save-btn {
        background: var(--accent);
        color: white;
        border-color: var(--accent);
    }

    .edit-save-btn:hover { opacity: 0.85; }
    .edit-cancel-btn { background: var(--white); color: var(--ink-2); }
    .edit-cancel-btn:hover { background: var(--surface-2); }

    /* Input area */
    .chat-input-area {
        padding: 1rem 1.5rem 1.25rem;
        background: var(--white);
        border-top: 1px solid var(--border);
        flex-shrink: 0;
    }

    .chat-form {
        display: flex;
        align-items: center;
        gap: 0.625rem;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 12px;
        padding: 0.375rem 0.375rem 0.375rem 1rem;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .chat-form:focus-within {
        border-color: rgba(232,98,42,0.35);
        box-shadow: 0 0 0 3px rgba(232,98,42,0.07);
    }

    .chat-input {
        flex: 1;
        border: none;
        background: transparent;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9rem;
        color: var(--ink);
        outline: none;
        padding: 0.5rem 0;
        resize: none;
        line-height: 1.5;
        max-height: 120px;
        overflow-y: auto;
    }

    .chat-input::placeholder { color: var(--ink-3); }

    .send-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: none;
        background: var(--accent);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: opacity 0.15s, transform 0.1s;
    }

    .send-btn:hover { opacity: 0.88; }
    .send-btn:active { transform: scale(0.95); }
    .send-btn svg { width: 16px; height: 16px; }

    .empty-state {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        color: var(--ink-3);
        padding: 2rem;
    }

    .empty-state svg { width: 36px; height: 36px; opacity: 0.3; }
    .empty-state p { font-size: 0.875rem; }

    /* Typing dots animation */
    .typing-dot {
        width: 5px;
        height: 5px;
        background: var(--ink-3);
        border-radius: 50%;
        animation: typingBounce 1.2s infinite ease-in-out;
    }

    .typing-dot:nth-child(1) { animation-delay: 0s; }
    .typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .typing-dot:nth-child(3) { animation-delay: 0.4s; }

    @keyframes typingBounce {
        0%, 60%, 100% { transform: translateY(0); opacity: 0.4; }
        30%            { transform: translateY(-4px); opacity: 1; }
    }

    /* ── Mobile ── */
    @media (max-width: 640px) {
        .chat-header { padding: 0.75rem 1rem; }
        .chat-messages { padding: 1rem 0.75rem; }
        .chat-input-area { padding: 0.625rem 0.75rem 0.75rem; }

        .msg-bubble-wrap { max-width: 80%; }

        /* Always show chevron on mobile */
        .msg-menu-trigger {
            opacity: 1 !important;
            pointer-events: auto !important;
        }

        .send-btn { width: 40px; height: 40px; }
    }
    
    /* Date separator */
    .date-separator {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        margin: 0.5rem 0;
        color: var(--ink-3);
        position: sticky;
        top: 0.5rem;
        z-index: 10;
        background: linear-gradient(90deg,rgba(0, 0, 0, 0) 20%, rgba(247, 246, 243, 1) 50%, rgba(0, 0, 0, 0) 80%); /* covers the previous separator's line */
        padding: 0.25rem 0;
    }

    .date-separator::before,
    .date-separator::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border);
    }

    .date-separator span {
        font-size: 0.7rem;
        font-weight: 500;
        background: var(--surface);
        padding: 0.2rem 0.625rem;
        border-radius: 20px;
        border: 1px solid var(--border);
        color: var(--ink-3);
        white-space: nowrap;
        letter-spacing: 0.02em;
    }

    #scroll-btn {
    position: absolute;
    bottom: 90px;
    right: 1.25rem;
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: var(--white);
    border: 1px solid var(--border);
    box-shadow: 0 2px 12px rgba(0,0,0,0.12);
    cursor: pointer;
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 20;
    transition: opacity 0.2s, transform 0.2s;
}

#scroll-btn:hover {
    transform: scale(1.08);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

#scroll-btn svg {
    width: 18px;
    height: 18px;
    color: var(--ink-2);
}

</style>

<div class="chat-layout">

    {{-- Header --}}
    <div class="chat-header">
        <div class="chat-header-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
        </div>
        <div class="chat-header-info">
            <h1>Chat Room</h1>
            <p><span class="online-dot"></span>Everyone can see messages here</p>
        </div>
    </div>

    {{-- Messages --}}
    <div class="chat-messages" id="chat-box">
    @php $prevDate = null; @endphp

    @forelse($messages as $msg)
    @php
        $isOwn   = $msg->user_id === auth()->id();
        $msgDate = $msg->created_at->toDateString();
        $today     = now()->toDateString();
        $yesterday = now()->subDay()->toDateString();
        $oneWeekAgo = now()->subDays(7)->startOfDay();

        $showSeparator = $msgDate !== $prevDate;

        if ($showSeparator) {
            if ($msgDate === $today) {
                $label = 'Today';
            } elseif ($msgDate === $yesterday) {
                $label = 'Yesterday';
            } elseif ($msg->created_at->greaterThan($oneWeekAgo)) {
                $label = $msg->created_at->format('l'); // Monday, Tuesday etc
            } else {
                $label = $msg->created_at->format('d/m/Y');
            }
            $prevDate = $msgDate;
        }
    @endphp
        {{-- Date separator --}}
        @if($showSeparator)
            <div class="date-separator">
                <span>{{ $label }}</span>
            </div>
        @endif

        {{-- your existing msg-row div here, no changes --}}
            <div class="msg-row {{ $isOwn ? 'own' : '' }}" id="msg-row-{{ $msg->id }}" data-ts="{{ $msg->created_at }}">

                <div class="msg-avatar {{ $isOwn ? '' : 'other' }}">
                    {{ strtoupper(substr($msg->user->name, 0, 1)) }}
                </div>

                <div class="msg-bubble-wrap">
                    @if(!$isOwn)
                    <span class="msg-name {{ $msg->user->isAdmin() ? 'admin-name' : '' }}">
                        {{ $msg->user->name }}
                        @if($msg->user->isAdmin())
                            <span class="admin-badge">Admin</span>
                        @endif
                    </span>
                    @endif

                    {{-- Bubble + chevron --}}
                    <div class="msg-bubble-inner" id="bubble-inner-{{ $msg->id }}">

                        {{-- DELETED --}}
                        @if($msg->trashed())
                            <div class="msg-bubble deleted">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                                </svg>
                                This message was deleted
                                @if($isOwn || auth()->user()->isAdmin())
                                    <form action="/chat/{{ $msg->id }}/undo" method="POST" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="undo-btn">Undo</button>
                                    </form>
                                @endif
                            </div>

                        {{-- NORMAL / EDITED --}}
                        @else
                            {{-- Edit form (hidden by default) --}}
                            @if($isOwn || auth()->user()->isAdmin())
                                <form
                                    class="edit-form"
                                    id="edit-form-{{ $msg->id }}"
                                    onsubmit="return false;"
                                >
                                    <textarea class="edit-textarea" name="message" rows="2">{{ $msg->message }}</textarea>
                                    <div class="edit-actions">
                                        <button type="button" class="edit-cancel-btn" onclick="closeEdit({{ $msg->id }})">Cancel</button>
                                        <button type="button" class="edit-save-btn" onclick="saveEdit({{ $msg->id }})">Save</button>
                                    </div>
                                </form>
                            @endif

                            {{-- Regular bubble --}}
                            <div class="msg-bubble {{ $msg->user->isAdmin() ? 'admin-bubble' : '' }}" 
                            id="bubble-{{ $msg->id }}" 
                            style="white-space: pre-wrap;">{{ $msg->message }}@if($msg->is_edited)<span class="msg-edited-tag">(edited)</span>@endif</div>

                            {{-- Chevron — for own messages OR superadmin --}}
                            @if($isOwn || auth()->user()->isAdmin())
                                <button
                                    class="msg-menu-trigger"
                                    id="trigger-{{ $msg->id }}"
                                    onclick="toggleMenu({{ $msg->id }}, event)"
                                    type="button"
                                    aria-label="Message options"
                                >
                                    <svg viewBox="0 0 10 6" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                                    </svg>
                                </button>

                                {{-- Dropdown --}}
                                <div class="msg-dropdown" id="menu-{{ $msg->id }}">
                                    @if($isOwn || auth()->user()->isAdmin())
                                        <button class="msg-dropdown-item" onclick="closeMenu({{ $msg->id }}); openEdit({{ $msg->id }})" type="button">
                                            <svg viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Edit
                                        </button>
                                    @endif
                                    <form action="/chat/{{ $msg->id }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button class="msg-dropdown-item danger" type="submit">
                                            <svg viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endif

                    </div>{{-- end msg-bubble-inner --}}

                    <span class="msg-time">{{ $msg->created_at->format('H:i') }}</span>
                </div>

            </div>

    @empty
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                <p>No messages yet. Say hello!</p>
            </div>
        @endforelse
    </div>

    {{-- Typing indicator --}}
    <div id="typing-indicator" style="
        min-height: 28px;
        padding: 0 1.5rem 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.75rem;
        color: var(--ink-3);
        font-style: italic;
        transition: opacity 0.2s;
        opacity: 0;
    ">
        <span style="display:flex; gap:3px; align-items:center;">
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
            <span class="typing-dot"></span>
        </span>
        <span id="typing-text"></span>
    </div>

    {{-- Input --}}
    <div class="chat-input-area">
        <form action="/chat/send" method="POST" class="chat-form">
            @csrf
            <textarea
                name="message"
                class="chat-input"
                placeholder="Type a message..."
                autocomplete="off"
                rows="1"
                required
            ></textarea>
            <button type="submit" class="send-btn" aria-label="Send message">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="22" y1="2" x2="11" y2="13"/>
                    <polygon points="22 2 15 22 11 13 2 9 22 2"/>
                </svg>
            </button>
        </form>
    </div>

    <button id="scroll-btn" onclick="scrollBottom()" aria-label="Scroll to bottom">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>

</div>

<script>
const ME = {{ auth()->id() }};
const ME_ROLE = "{{ auth()->user()->role }}";
const CSRF = "{{ csrf_token() }}";
const IS_SUPER_ADMIN = {{ auth()->user()->isAdmin() ? 'true' : 'false' }};

// ── State ──
let lastTimestamp = null;
let openMenuId    = null;
let pollTimer     = null;

// ── Scroll ──
const chatBox = document.getElementById('chat-box');
function scrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}
scrollBottom();

// ── Auto-resize textarea ──
const textarea = document.querySelector('.chat-input');
textarea.addEventListener('input', function () {
    this.style.height = 'auto';
    this.style.height = Math.min(this.scrollHeight, 120) + 'px';
});

// ── Kirim pesan via AJAX ──
document.querySelector('.chat-form').addEventListener('submit', async function (e) {
    e.preventDefault();
    const msg = textarea.value.trim();
    if (!msg) return;

    textarea.value = '';
    textarea.style.height = 'auto';

    try {
        const res = await fetch('/chat/send', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ message: msg }),
        });

        const data = await res.json();
        appendMessage(data, true);   // langsung tampilin
        lastTimestamp = data.created_at;
        scrollBottom();
    } catch (err) {
        console.error('Send failed:', err);
    }
});

// Enter to send
textarea.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        this.closest('form').dispatchEvent(new Event('submit'));
    }
});

// ── Polling ──
async function pollMessages() {
    try {
        const url = lastTimestamp
            ? `/chat/messages?since=${encodeURIComponent(lastTimestamp)}`
            : `/chat/messages`;

        const res  = await fetch(url);
        const data = await res.json();

        // Handle new messages
        const msgs = data.new ?? data; // fallback if old format
        if (msgs.length > 0) {
            if (lastTimestamp !== null) {
                const atBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight < 80;
                msgs.forEach(m => {
                    if (!document.getElementById('msg-row-' + m.id)) {
                        appendMessage(m, m.user_id === ME);
                    }
                });
                if (atBottom) scrollBottom();
            }
            lastTimestamp = msgs[msgs.length - 1].created_at;
        } else if (lastTimestamp === null) {
            lastTimestamp = new Date().toISOString().slice(0, 19).replace('T', ' ');
        }

        // Handle updated (edited/deleted) messages
        if (data.updated) {
            data.updated.forEach(m => {
                const bubble   = document.getElementById('bubble-' + m.id);
                const trigger  = document.getElementById('trigger-' + m.id);
                const menu     = document.getElementById('menu-' + m.id);
                const editForm = document.getElementById('edit-form-' + m.id);

                if (!bubble) return;

                if (m.deleted_at) {
                    // Soft deleted
                    bubble.className = 'msg-bubble deleted';
                    bubble.style.borderRadius = '14px';
                    bubble.style.whiteSpace = '';
                    bubble.style.display = '';
                    bubble.innerHTML = `
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;flex-shrink:0;opacity:0.6">
                            <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                        </svg>
                        This message was deleted
                        ${m.user_id === ME || IS_SUPER_ADMIN ? `<button class="undo-btn" onclick="undoDelete(${m.id})">Undo</button>` : ''}
                    `;
                    if (trigger)  trigger.style.display = 'none';
                    if (menu) menu.style.display = 'none';
                    if (editForm) editForm.classList.remove('active');
                } else {
                    // Restored or edited
                    bubble.className = 'msg-bubble' + (m.user.role === 'admin' ? ' admin-bubble' : '');
                    bubble.style.borderRadius = '';
                    bubble.style.whiteSpace = 'pre-wrap';
                    bubble.style.display = '';
                    bubble.innerHTML = escHtml(m.message)
                        + (m.is_edited ? '<span class="msg-edited-tag">(edited)</span>' : '');
                    if (trigger)  trigger.style.display = '';
                    if (editForm) editForm.classList.remove('active');

                    // Restore dropdown if it was removed
                    if (!menu && trigger) {
                        const newMenu = document.createElement('div');
                        newMenu.className = 'msg-dropdown';
                        newMenu.id = 'menu-' + m.id;
                        newMenu.innerHTML = `
                            <button class="msg-dropdown-item" onclick="closeMenu(${m.id}); openEdit(${m.id})" type="button">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button class="msg-dropdown-item danger" onclick="deleteMsg(${m.id})" type="button">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        `;
                        trigger.insertAdjacentElement('afterend', newMenu);
                    }
                }
            });
        }

    } catch (err) {
        console.error('Poll error:', err);
    }
    pollTimer = setTimeout(pollMessages, 2500);
}

// Init: set lastTimestamp dari pesan terakhir yang udah dirender blade
    const existingRows = chatBox.querySelectorAll('.msg-row[data-ts]');
    if (existingRows.length > 0) {
        lastTimestamp = existingRows[existingRows.length - 1].dataset.ts;
    } else {
        lastTimestamp = ''; // string kosong = belum ada pesan
    }
    pollMessages();

    // ── Render pesan baru ──
    function appendMessage(msg, isOwn) {
        // Hapus empty state kalau ada
        const empty = chatBox.querySelector('.empty-state');
        if (empty) empty.remove();

        const initials = msg.user.name.charAt(0).toUpperCase();
        const time     = new Date(msg.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
        const text     = escHtml(msg.message);

        const row = document.createElement('div');
        row.className = `msg-row ${isOwn ? 'own' : ''}`;
        row.id        = 'msg-row-' + msg.id;
        row.dataset.ts = msg.created_at;

        row.innerHTML = `
            <div class="msg-avatar ${isOwn ? '' : 'other'}">${initials}</div>
            <div class="msg-bubble-wrap">
                ${!isOwn ? `
                    <span class="msg-name ${msg.user.role === 'superadmin' || msg.user.role === 'admin' ? 'admin-name' : ''}">
                        ${escHtml(msg.user.name)}
                        ${msg.user.role === 'admin' || msg.user.role === 'superadmin' ? '<span class="admin-badge">Admin</span>' : ''}
                    </span>
                ` : ''}
                <div class="msg-bubble-inner" id="bubble-inner-${msg.id}">
                    ${isOwn || IS_SUPER_ADMIN ? `
                        <form class="edit-form" id="edit-form-${msg.id}">
                            <textarea class="edit-textarea" rows="2">${text}</textarea>
                            <div class="edit-actions">
                                <button type="button" class="edit-cancel-btn" onclick="closeEdit(${msg.id})">Cancel</button>
                                <button type="button" class="edit-save-btn" onclick="saveEdit(${msg.id})">Save</button>
                            </div>
                        </form>
                    ` : ''}
                    <div class="msg-bubble ${msg.user.role === 'admin' ? 'admin-bubble' : ''}" id="bubble-${msg.id}" style="white-space:pre-wrap;">${text}</div>
                    ${isOwn || IS_SUPER_ADMIN ? `
                        <button class="msg-menu-trigger" id="trigger-${msg.id}" onclick="toggleMenu(${msg.id}, event)" type="button">
                            <svg viewBox="0 0 10 6"><path d="M1 1l4 4 4-4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" fill="none"/></svg>
                        </button>
                        <div class="msg-dropdown" id="menu-${msg.id}">
                            <button class="msg-dropdown-item" onclick="closeMenu(${msg.id}); openEdit(${msg.id})" type="button">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit
                            </button>
                            <button class="msg-dropdown-item danger" onclick="deleteMsg(${msg.id})" type="button">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M9 6V4h6v2"/></svg>
                                Delete
                            </button>
                        </div>
                    ` : ''}
                </div>
                <span class="msg-time">${time}</span>
            </div>
        `;

        chatBox.appendChild(row);
    }

    // ── Delete via AJAX ──
async function deleteMsg(id) {
    closeMenu(id);
    try {
        await fetch(`/chat/${id}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-TOKEN': CSRF },
        });

        const bubble  = document.getElementById('bubble-' + id);
        const trigger = document.getElementById('trigger-' + id);
        const menu    = document.getElementById('menu-' + id);
        const editForm = document.getElementById('edit-form-' + id);

        if (bubble) {
            bubble.className = 'msg-bubble deleted';
            bubble.style.borderRadius = '14px';
            bubble.style.whiteSpace = '';
            bubble.style.display = '';
            bubble.innerHTML = `
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:14px;height:14px;flex-shrink:0;opacity:0.6">
                    <circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/>
                </svg>
                This message was deleted
                <button class="undo-btn" onclick="undoDelete(${id})">Undo</button>
            `;
        }
        if (trigger)  trigger.style.display = 'none';
        if (menu)     menu.style.display = 'none'; // hide, don't remove
        if (editForm) editForm.classList.remove('active');

    } catch (err) {
        console.error('Delete failed:', err);
    }
}

async function saveEdit(id) {
    const ta  = document.querySelector(`#edit-form-${id} textarea`);
    const msg = ta.value.trim();
    if (!msg) return;

    try {
        const res  = await fetch(`/chat/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': CSRF,
            },
            body: JSON.stringify({ message: msg }),
        });
        const data = await res.json();

        const bubble  = document.getElementById('bubble-' + id);
        const trigger = document.getElementById('trigger-' + id);
        const menu    = document.getElementById('menu-' + id);

        if (bubble) {
            bubble.className = 'msg-bubble' + (m.user.role === 'admin' ? ' admin-bubble' : '');
            bubble.style.borderRadius = '';
            bubble.style.whiteSpace = 'pre-wrap';
            bubble.innerHTML = escHtml(data.message)
                + (data.is_edited ? '<span class="msg-edited-tag">(edited)</span>' : '');
        }
        if (trigger) trigger.style.display = '';
        if (menu)    menu.style.display = '';  // restore visibility

        closeEdit(id);
    } catch (err) {
        console.error('Edit failed:', err);
    }
}
    // ── Undo delete via AJAX ──
async function undoDelete(id) {
    try {
        await fetch(`/chat/${id}/undo`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
        });

        const res = await fetch(`/chat/${id}/get`, {
            headers: { 'Accept': 'application/json' }
        });
        const msg = await res.json();

        const bubble   = document.getElementById('bubble-' + id);
        const trigger  = document.getElementById('trigger-' + id);
        const menu     = document.getElementById('menu-' + id);
        const editForm = document.getElementById('edit-form-' + id);

        if (bubble) {
            bubble.className = 'msg-bubble' + (msg.user.role === 'admin' ? ' admin-bubble' : '');
            bubble.style.borderRadius = '';
            bubble.style.whiteSpace = 'pre-wrap';
            bubble.style.display = '';
            bubble.innerHTML = escHtml(msg.message)
                + (msg.is_edited ? '<span class="msg-edited-tag">(edited)</span>' : '');
        }
        if (trigger)  trigger.style.display = '';
        if (menu)     menu.style.display = '';  // just show it again
        if (editForm) {
            const ta = editForm.querySelector('textarea');
            if (ta) ta.value = msg.message;
        }

    } catch (err) {
        console.error('Undo failed:', err);
    }
}

    // ── Edit via AJAX ──
    function openEdit(id) {
        const bubble  = document.getElementById('bubble-' + id);
        const trigger = document.getElementById('trigger-' + id);
        const form    = document.getElementById('edit-form-' + id);

        if (!form) return; // no edit form = can't edit

        if (bubble) bubble.style.display = 'none';
        if (trigger) trigger.style.display = 'none';
        form.classList.add('active');

        // Set textarea value from current bubble text (for admin editing others' messages)
        const ta = form.querySelector('textarea');
        if (ta && bubble) {
            // Get text without the (edited) tag
            const clone = bubble.cloneNode(true);
            const editedTag = clone.querySelector('.msg-edited-tag');
            if (editedTag) editedTag.remove();
            ta.value = clone.innerText || clone.textContent;
        }

        if (ta) {
            ta.focus();
            ta.setSelectionRange(ta.value.length, ta.value.length);
        }
    }

    function closeEdit(id) {
        document.getElementById('bubble-' + id).style.display = '';
        const trigger = document.getElementById('trigger-' + id);
        if (trigger) trigger.style.display = '';
        document.getElementById('edit-form-' + id).classList.remove('active');
    }

    async function saveEdit(id) {
        const ta  = document.querySelector(`#edit-form-${id} textarea`);
        const msg = ta.value.trim();
        if (!msg) return;

        try {
            const res = await fetch(`/chat/${id}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json', // ADD THIS
                    'X-CSRF-TOKEN': CSRF,
                },
                body: JSON.stringify({ message: msg }),
            });
            const data = await res.json();

            const bubble = document.getElementById('bubble-' + id);
            bubble.innerHTML = escHtml(data.message) + (data.is_edited ? '<span class="msg-edited-tag">(edited)</span>' : '');
            closeEdit(id);
        } catch (err) {
            console.error('Edit failed:', err);
        }
    }
    // ── Dropdown menu ──
    function toggleMenu(id, e) {
        e.stopPropagation();
        if (openMenuId && openMenuId !== id) closeMenu(openMenuId);
        const menu    = document.getElementById('menu-' + id);
        const trigger = document.getElementById('trigger-' + id);
        const isOpen  = menu.classList.contains('open');
        if (isOpen) {
            closeMenu(id);
        } else {
            openMenuId = id;
            menu.classList.add('open');
            trigger.classList.add('active');

            const rect       = menu.parentElement.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            menu.style.top    = spaceBelow < 120 ? 'auto' : 'calc(100% + 4px)';
            menu.style.bottom = spaceBelow < 120 ? 'calc(100% + 4px)' : 'auto';
        }
    }

    function closeMenu(id) {
        const menu    = document.getElementById('menu-' + id);
        const trigger = document.getElementById('trigger-' + id);
        if (menu)    menu.classList.remove('open');
        if (trigger) trigger.classList.remove('active');
        if (openMenuId === id) openMenuId = null;
    }

    document.addEventListener('click', () => { if (openMenuId) closeMenu(openMenuId); });

    // ── Helper ──
    function escHtml(str) {
        return str.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#039;');
    }
    
// ── Typing indicator ──
let typingPingTimeout = null;
let typingPollInterval = null;

document.querySelector('.chat-input').addEventListener('input', function () {
    clearTimeout(typingPingTimeout);
    fetch('/chat/typing', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': CSRF,
        },
    }).catch(() => {});
    typingPingTimeout = setTimeout(() => {}, 3000);
});

function startTypingPoll() {
    clearInterval(typingPollInterval);
    typingPollInterval = setInterval(async () => {
        try {
            const res  = await fetch('/chat/who-typing');
            const data = await res.json();
            const indicator = document.getElementById('typing-indicator');
            const text      = document.getElementById('typing-text');
            if (data.typers && data.typers.length > 0) {
                const names = data.typers.join(', ');
                text.textContent = data.typers.length === 1
                    ? `${names} is typing...`
                    : `${names} are typing...`;
                indicator.style.opacity = '1';
            } else {
                indicator.style.opacity = '0';
            }
        } catch (e) {}
    }, 2000);
}

startTypingPoll();

document.addEventListener('visibilitychange', () => {
    if (document.hidden) clearInterval(typingPollInterval);
    else startTypingPoll();
});

function scrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

// Show/hide scroll button
chatBox.addEventListener('scroll', () => {
    const btn = document.getElementById('scroll-btn');
    const distFromBottom = chatBox.scrollHeight - chatBox.scrollTop - chatBox.clientHeight;
    btn.style.display = distFromBottom > 200 ? 'flex' : 'none';
});
</script>

@endsection