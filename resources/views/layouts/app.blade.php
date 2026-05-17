<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Talksy</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="{{ asset('assets/talksy-logo.png')}}">
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Serif:ital@0;1&family=DM+Sans:ital,wght@0,300;0,400;0,500;1,300&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --sidebar-bg: #0f1117;
            --sidebar-w: 240px;
            --surface: #f7f6f3;
            --surface-2: #eeecea;
            --border: rgba(0,0,0,0.08);
            --ink: #16150f;
            --ink-2: #6b6a64;
            --ink-3: #b0afa9;
            --accent: #e8622a;
            --accent-dim: rgba(232,98,42,0.12);
            --white: #ffffff;
            --sidebar-text: rgba(255,255,255,0.7);
            --sidebar-text-active: #ffffff;
            --sidebar-item: rgba(255,255,255,0.06);
            --sidebar-item-hover: rgba(255,255,255,0.1);
        }

        body {
            font-family: 'DM Sans', sans-serif;
            display: flex;
            height: 100vh;
            overflow: hidden;
            background: var(--surface);
            color: var(--ink);
        }

        /* ── Overlay ── */
        .sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.45);
            z-index: 40;
            opacity: 0;
            transition: opacity 0.25s;
        }

        .sidebar-overlay.visible {
            opacity: 1;
        }

        /* ── Sidebar ── */
        .sidebar {
            width: var(--sidebar-w);
            background: var(--sidebar-bg);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            border-right: 1px solid rgba(255,255,255,0.04);
            z-index: 50;
            transition: transform 0.25s cubic-bezier(0.4,0,0.2,1),
                        width 0.25s cubic-bezier(0.4,0,0.2,1),
                        min-width 0.25s cubic-bezier(0.4,0,0.2,1);
            overflow: hidden;
        }

        /* Desktop collapsed state */
        .sidebar.collapsed {
            transform: translateX(-100%);
            width: 0;
            min-width: 0;
        }

        .sidebar-header {
            padding: 1.5rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.06);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 0.625rem;
        }

        .logo-mark {
            width: 28px;
            height: 28px;
            background: var(--accent);
            border-radius: 7px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .logo-mark svg {
            width: 14px;
            height: 14px;
            fill: white;
        }

        .logo-name {
            font-family: 'Instrument Serif', serif;
            font-size: 1.25rem;
            color: var(--sidebar-text-active);
            letter-spacing: -0.02em;
            font-weight: 400;
        }

        /* Close button inside sidebar */
        .sidebar-close-btn {
            display: flex;
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255,255,255,0.5);
            padding: 4px;
            border-radius: 6px;
            transition: color 0.15s, background 0.15s;
        }

        .sidebar-close-btn:hover {
            color: white;
            background: rgba(255,255,255,0.08);
        }

        .sidebar-close-btn svg {
            width: 18px;
            height: 18px;
            display: block;
        }

        .sidebar-nav {
            flex: 1;
            padding: 1rem 0.75rem;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .nav-label {
            font-size: 0.6875rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255,255,255,0.3);
            padding: 0.5rem 0.5rem 0.375rem;
            margin-top: 0.5rem;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.55rem 0.75rem;
            border-radius: 6px;
            color: var(--sidebar-text);
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 400;
            transition: background 0.15s, color 0.15s;
            cursor: pointer;
        }

        .nav-item:hover {
            background: var(--sidebar-item-hover);
            color: var(--sidebar-text-active);
        }

        .nav-item.active {
            background: var(--sidebar-item-hover);
            color: var(--sidebar-text-active);
        }

        .nav-item svg {
            width: 16px;
            height: 16px;
            flex-shrink: 0;
            opacity: 0.8;
        }

        .sidebar-footer {
            padding: 0.875rem 0.75rem;
            border-top: 1px solid rgba(255,255,255,0.06);
        }

        .user-row {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.5rem 0.625rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
        }

        .avatar {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            background: var(--accent);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 500;
            color: white;
            flex-shrink: 0;
            text-transform: uppercase;
        }

        .user-info { flex: 1; min-width: 0; }

        .user-name {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--sidebar-text-active);
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .user-status {
            font-size: 0.6875rem;
            color: rgba(255,255,255,0.35);
        }

        .logout-form { width: 100%; }

        .logout-btn {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: none;
            background: transparent;
            color: rgba(255,255,255,0.4);
            font-family: 'DM Sans', sans-serif;
            font-size: 0.8125rem;
            cursor: pointer;
            border-radius: 6px;
            transition: background 0.15s, color 0.15s;
            text-align: left;
        }

        .logout-btn:hover {
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.75);
        }

        .logout-btn svg { width: 14px; height: 14px; }

        /* ── Main content ── */
        .content {
            flex: 1;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            background: var(--surface);
            min-width: 0;
        }

        /* ── Top bar (always visible) ── */
        .mobile-topbar {
            display: flex;
            align-items: center;
            gap: 0.625rem;
            padding: 0.625rem 1rem;
            background: var(--sidebar-bg);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            flex-shrink: 0;
            z-index: 10;
        }

        .hamburger-btn {
            background: none;
            border: none;
            cursor: pointer;
            color: rgba(255,255,255,0.7);
            padding: 4px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: color 0.15s, background 0.15s;
        }

        .hamburger-btn:hover {
            color: white;
            background: rgba(255,255,255,0.08);
        }

        .hamburger-btn svg { width: 20px; height: 20px; display: block; }

        .hamburger-btn.hidden{opacity: 0}

        .mobile-topbar-logo {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .mobile-topbar-logo .logo-mark {
            width: 24px;
            height: 24px;
            border-radius: 6px;
        }

        .mobile-topbar-logo .logo-mark svg { width: 12px; height: 12px; }

        .mobile-topbar-logo span {
            font-family: 'Instrument Serif', serif;
            font-size: 1.125rem;
            color: white;
            letter-spacing: -0.02em;
        }

        .mobile-topbar-logo.hidden{opacity: 0;}

        /* ── Responsive ── */
        @media (max-width: 640px) {
            body { flex-direction: column; }

            .sidebar-overlay { display: block; }

            .sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                transform: translateX(-100%);
                width: var(--sidebar-w) !important;
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .sidebar.collapsed {
                transform: translateX(-100%);
                width: var(--sidebar-w) !important;
            }

            .content { flex: 1; overflow: hidden; }
        }
    </style>
</head>
<body>

    {{-- Mobile overlay --}}
    <div class="sidebar-overlay" id="sidebar-overlay" onclick="closeSidebar()"></div>

    <aside class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-mark">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z"/>
                    </svg>
                </div>
                <span class="logo-name">Talksy</span>
            </div>
            {{-- Close button (mobile only) --}}
            <button class="sidebar-close-btn" onclick="closeSidebar()" aria-label="Close menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>

        <nav class="sidebar-nav">
            <span class="nav-label">Menu</span>
            <a href="/chat" class="nav-item {{ request()->is('chat*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
                </svg>
                Chat Room
            </a>
            @if(auth()->user()->isAdmin())
            <a href="/admin" class="nav-item {{ request()->is('admin*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"/>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"/>
                </svg>
                Manage Users
            </a>            
            <a href="/register" class="nav-item {{ request()->is('admin*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <line x1="19" y1="8" x2="19" y2="14"/>
                    <line x1="16" y1="11" x2="22" y2="11"/>
                </svg>
                Register
            </a>
            @endif

            <span class="nav-label">Games</span>
            <a href="/games/chess" class="nav-item {{ request()->is('games/chess*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="2"/><path d="M8 16v-4m0 0V8m0 4h4m4 4v-4m0 0V8"/>
                </svg>
                Chess
            </a>
            <a href="/games/rps" class="nav-item {{ request()->is('games/rps*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 11V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2"/><path d="M14 10V4a2 2 0 0 0-2-2a2 2 0 0 0-2 2v2"/><path d="M10 10.5V6a2 2 0 0 0-2-2a2 2 0 0 0-2 2v8"/><path d="M18 8a2 2 0 1 1 4 0v6a8 8 0 0 1-8 8h-2c-2.8 0-4.5-.86-5.99-2.34l-3.6-3.6a2 2 0 0 1 2.83-2.82L7 15"/>
                </svg>
                Rock Paper Scissors
            </a>
            <a href="/games/sudoku" class="nav-item {{ request()->is('games/sudoku*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="2"/><path d="M8 2v20M16 2v20M2 8h20M2 16h20"/>
                </svg>
                Sudoku
            </a>
            <a href="/games/catch" class="nav-item {{ request()->is('games/catch') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <!-- Simple ball icon -->
                    <circle cx="12" cy="12" r="8"/>
                    <path d="M12 4v16M4 12h16"/>
                </svg>
                Catching Ball
            </a>
            <a href="/games/tictactoe" class="nav-item {{ request()->is('games/tictactoe*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="2" y="2" width="20" height="20" rx="2"/>
                    <line x1="9" y1="2" x2="9" y2="22"/>
                    <line x1="15" y1="2" x2="15" y2="22"/>
                    <line x1="2" y1="9" x2="22" y2="9"/>
                    <line x1="2" y1="15" x2="22" y2="15"/>
                </svg>
                Tic Tac Toe
            </a>
            <a href="/games/memory" class="nav-item {{ request()->is('games/memory*') ? 'active' : '' }}" onclick="closeSidebar()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1"/>
                    <rect x="14" y="3" width="7" height="7" rx="1"/>
                    <rect x="3" y="14" width="7" height="7" rx="1"/>
                    <rect x="14" y="14" width="7" height="7" rx="1"/>
                </svg>
                Memory Game
            </a>
            <a href="/games/animal-chess" class="nav-item {{ request()->is('games/animal-chess*') ? 'active' : '' }}" onclick="closeSidebar()">
                🐾 Animal Chess
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="/profile" style="text-decoration:none;">
                <div class="user-row" style="cursor:pointer; transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.06)'"
                    onmouseout="this.style.background=''">
                    <div class="avatar">{{ substr(auth()->user()->name ?? 'U', 0, 1) }}</div>
                    <div class="user-info">
                        <div class="user-name">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="user-status">Online</div>
                    </div>
                </div>
            </a>
            <form action="/logout" method="POST" class="logout-form">
                @csrf
                <button type="submit" class="logout-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                        <polyline points="16 17 21 12 16 7"/>
                        <line x1="21" y1="12" x2="9" y2="12"/>
                    </svg>
                    Sign out
                </button>
            </form>
        </div>
    </aside>

    <main class="content">
        {{-- Top bar (hamburger) --}}
        <div class="mobile-topbar">
            <button class="hamburger-btn hidden" id="hamburger-btn" onclick="openSidebar()" aria-label="Open menu">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="3" y1="6" x2="21" y2="6"/>
                    <line x1="3" y1="12" x2="21" y2="12"/>
                    <line x1="3" y1="18" x2="21" y2="18"/>
                </svg>
            </button>
            <div class="mobile-topbar-logo hidden" id="mobile-topbar-logo">
                <div class="logo-mark">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M20 2H4c-1.1 0-2 .9-2 2v18l4-4h14c1.1 0 2-.9 2-2V4c0-1.1-.9-2-2-2z" fill="white"/>
                    </svg>
                </div>
                <span>Talksy</span>
            </div>
        </div>

        @yield('content')
    </main>

    <script>
        const ham = document.getElementById('hamburger-btn');
        const topbar = document.getElementById('mobile-topbar-logo');
        if (window.innerWidth <= 640) {
            ham.classList.remove('hidden');
            topbar.classList.remove('hidden');
            overlay.style.display = 'block';
            requestAnimationFrame(() => overlay.classList.add('visible'));
            document.body.style.overflow = 'hidden';
        }
    // Desktop: toggle collapse
    function openSidebar() {
        const sidebar = document.getElementById('sidebar');
        const ham = document.getElementById('hamburger-btn');
        const topbar = document.getElementById('mobile-topbar-logo');
        sidebar.classList.remove('collapsed');
        sidebar.classList.add('open');
        ham.classList.add('hidden');
        topbar.classList.add('hidden');
        const overlay = document.getElementById('sidebar-overlay');
        if (window.innerWidth <= 640) {
            ham.classList.add('hidden');
            topbar.classList.add('hidden');
            overlay.style.display = 'block';
            requestAnimationFrame(() => overlay.classList.add('visible'));
            document.body.style.overflow = 'hidden';
        }
    }

    function closeSidebar() {
        const sidebar = document.getElementById('sidebar');
        const ham = document.getElementById('hamburger-btn');
        const topbar = document.getElementById('mobile-topbar-logo');
        sidebar.classList.remove('open');
        sidebar.classList.add('collapsed');
        ham.classList.remove('hidden');
        topbar.classList.remove('hidden');
        const overlay = document.getElementById('sidebar-overlay');
        overlay.classList.remove('visible');
        setTimeout(() => { overlay.style.display = 'none'; }, 250);
        document.body.style.overflow = '';
    }
    </script>

</body>
</html> 