@extends('layouts.app')

@section('content')
<style>
    /* ── Page shell ─────────────────────────────────────── */
    .sudoku-page {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        height: 100%;
        --box-border: rgba(0,0,0,0.18);     /* Light mode box border */
    }

    .sudoku-header {
        padding: 1.25rem 1.75rem 0;
        flex-shrink: 0;
    }

    .sudoku-header h1 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.6rem;
        font-weight: 400;
        color: var(--ink);
        letter-spacing: -0.02em;
    }

    .sudoku-header p {
        font-size: 0.8125rem;
        color: var(--ink-2);
        margin-top: 0.2rem;
    }

    /* ── Main layout: board + sidebar ───────────────────── */
    .sudoku-body {
        flex: 1;
        display: flex;
        gap: 1.5rem;
        padding: 1.25rem 1.75rem 1.75rem;
        overflow: auto;
        align-items: flex-start;
    }

    /* ── Board side ─────────────────────────────────────── */
    .sudoku-board-wrap {
        flex-shrink: 0;
    }

    .sudoku-grid {
        display: grid;
        grid-template-columns: repeat(9, 1fr);
        gap: 3px;
        background: var(--border);
        border: 2px solid var(--border);
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 12px rgba(0,0,0,0.07);
    }

    .s-cell {
        width: 48px;
        height: 48px;
        background: var(--surface);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.125rem;
        font-weight: 500;
        color: var(--accent);
        cursor: pointer;
        transition: background 0.1s, color 0.1s;
        position: relative;
        user-select: none;
        font-family: 'DM Sans', sans-serif;
    }

    .s-cell.mine   { color: var(--accent); }        /* orange — already your accent */
    .s-cell.theirs { color: #3b82f6; }              /* blue */

    /* keep selected state readable */
    .s-cell.selected.mine,
    .s-cell.selected.theirs { color: #fff; }

    /* 3×3 box separators */
    .s-cell[data-col="2"], 
    .s-cell[data-col="5"] { 
        border-right: 2px solid var(--box-border); 
    }

    .s-cell[data-row="2"], 
    .s-cell[data-row="5"] { 
        border-bottom: 2px solid var(--box-border); 
    }

    .s-cell:hover                     { background: var(--accent-dim); }
    .s-cell.clue                      { color: var(--ink); font-weight: 600; cursor: default; background: var(--surface-2); }
    .s-cell.clue:hover                { background: var(--surface-2); }
    .s-cell.selected                  { background: var(--accent); color: #fff; }
    .s-cell.highlighted               { background: rgba(232,98,42,0.07); }
    .s-cell.err                       { background: #fdecea; color: #c0392b; }
    .s-cell.selected.err              { background: #c0392b; color: #fff; }
    .s-cell.selected .note-num {
        color: #fff;
    }

    @keyframes pop {
        0%   { transform: scale(1.35); }
        100% { transform: scale(1); }
    }
    .s-cell.pop { animation: pop 0.2s cubic-bezier(0.175,0.885,0.32,1.275); }

    @keyframes shake {
        0%,100% { transform: translateX(0); }
        25%      { transform: translateX(5px) rotate(4deg); }
        75%      { transform: translateX(-5px) rotate(-4deg); }
    }
    .s-cell.shake { animation: shake 0.3s ease; }

    /* ── Right panel ─────────────────────────────────────── */
    .sudoku-panel {
        flex: 1;
        min-width: 200px;
        max-width: 280px;
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    /* stat chips */
    .panel-stats {
        display: flex;
        gap: 0.625rem;
    }

    .stat-chip {
        flex: 1;
        background: var(--surface-2);
        border-radius: 8px;
        padding: 0.625rem 0.75rem;
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
    }

    .stat-chip .label {
        font-size: 0.6875rem;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        color: var(--ink-2);
    }

    .stat-chip .value {
        font-family: 'Instrument Serif', serif;
        font-size: 1.25rem;
        color: var(--ink);
        letter-spacing: -0.01em;
    }

    .stat-chip .value.accent { color: var(--accent); }

    /* number pad */
    .numpad {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 6px;
    }

    .num-btn {
        aspect-ratio: 1;
        background: var(--surface-2);
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 1.1rem;
        font-weight: 500;
        color: var(--ink);
        cursor: pointer;
        transition: background 0.15s, color 0.15s, transform 0.1s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .num-btn:hover       { background: var(--accent-dim); color: var(--accent); transform: scale(1.06); }
    .num-btn:active      { transform: scale(0.95); }
    .num-btn.del-btn     { background: #fdecea; color: #c0392b; font-size: 0.875rem; }
    .num-btn.del-btn:hover { background: #f5c6cb; }

    /* action buttons */
    .panel-actions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .action-btn {
        width: 100%;
        padding: 0.65rem 1rem;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        transition: background 0.15s, opacity 0.15s;
    }

    .action-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

    .btn-primary   { background: var(--accent); color: #fff; }
    .btn-primary:hover { opacity: 0.88; }

    .btn-secondary { background: var(--surface-2); color: var(--ink); }
    .btn-secondary:hover { background: #e3e1de; }

    .btn-ghost {
        background: transparent;
        color: var(--ink-2);
        border: 1.5px solid var(--border);
    }
    .btn-ghost:hover { background: var(--surface-2); color: var(--ink); }

    /* level selector */
    .level-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: var(--surface-2);
        border-radius: 8px;
        padding: 0.5rem 0.75rem;
    }

    .level-row span {
        font-size: 0.8125rem;
        color: var(--ink-2);
        font-weight: 500;
    }

    .level-toggle {
        display: flex;
        align-items: center;
        gap: 0.375rem;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: 6px;
        padding: 0.3rem 0.6rem;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.8125rem;
        font-weight: 500;
        color: var(--ink);
        cursor: pointer;
        transition: border-color 0.15s, color 0.15s;
    }

    .level-toggle:hover { border-color: var(--accent); color: var(--accent); }
    .level-toggle svg   { width: 12px; height: 12px; }

    /* waiting banner */
    .waiting-banner {
        background: var(--accent-dim);
        border: 1.5px dashed rgba(232,98,42,0.35);
        border-radius: 8px;
        padding: 0.75rem 1rem;
        font-size: 0.8125rem;
        color: var(--accent);
        text-align: center;
        line-height: 1.5;
    }

    /* overlay screens */
    .overlay-screen {
        position: absolute;
        inset: 0;
        background: rgba(247,246,243,0.92);
        backdrop-filter: blur(4px);
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 1rem;
        border-radius: 10px;
        z-index: 10;
    }

    .overlay-screen.active { display: flex; }

    .overlay-title {
        font-family: 'Instrument Serif', serif;
        font-size: 2rem;
        color: var(--ink);
        letter-spacing: -0.02em;
    }

    .overlay-sub {
        font-size: 0.875rem;
        color: var(--ink-2);
    }

    .overlay-time {
        font-family: 'Instrument Serif', serif;
        font-size: 3rem;
        color: var(--accent);
        letter-spacing: -0.02em;
    }

    .overlay-btn {
        padding: 0.7rem 2rem;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.15s;
    }

    .overlay-btn-primary { background: var(--accent); color: #fff; }
    .overlay-btn-primary:hover { opacity: 0.88; }
    .overlay-btn-secondary { background: var(--surface-2); color: var(--ink); }
    .overlay-btn-secondary:hover { background: #e3e1de; }

    /* ── Responsive: stack on mobile ───────────────────── */
    @media (max-width: 640px) {
        .sudoku-body {
            flex-direction: column;
            align-items: center;
            padding: 1rem;
            gap: 1rem;
        }

        .sudoku-panel {
            max-width: 100%;
            width: 100%;
        }

        .s-cell {
            width: 36px;
            height: 36px;
            font-size: 0.9rem;
        }

        .sudoku-header {
            padding: 1rem 1rem 0;
        }
    }

    @media (max-width: 380px) {
        .s-cell { width: 30px; height: 30px; font-size: 0.8rem; }
    }

    /* ── Note cells ─────────────────────────────────────── */
    .note-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        grid-template-rows: repeat(3, 1fr);
        width: 100%;
        height: 100%;
        padding: 3px;
        gap: 1px;
        pointer-events: none;
    }

    .note-num {
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 9px;
        font-weight: 500;
        color: var(--accent);
        line-height: 1;
    }

    .note-num.empty { visibility: hidden; }

    /* Pencil mode toggle button */
    .pencil-btn {
        width: 100%;
        padding: 0.65rem 1rem;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        background: var(--surface-2);
        color: var(--ink);
        transition: all 0.15s;
    }

    .pencil-btn:hover { border-color: var(--accent); color: var(--accent); }

    .pencil-btn.pencil-active {
        background: rgba(232,98,42,0.12);
        border-color: var(--accent);
        color: var(--accent);
    }

    .pencil-btn svg { width: 15px; height: 15px; flex-shrink: 0; }

    .pencil-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: var(--ink-3);
        margin-left: auto;
        transition: background 0.15s;
    }

    .pencil-btn.pencil-active .pencil-dot { background: var(--accent); }

    /* Smaller note font on mobile */
    @media (max-width: 640px) {
        .note-num { font-size: 7px; }
    }

    /* ── Dark mode ─────────────────────────────────────── */
    .sudoku-dark {
        --surface:      #1a1b23;
        --surface-2:    #22232e;
        --border:       rgba(255,255,255,0.07);
        --box-border:   rgba(255,255,255,0.35);     /* Stronger divider for dark mode */
        --ink:          #e8e6e1;
        --ink-2:        #8b8a84;
        --ink-3:        #4a4a46;
        --accent-dim:   rgba(232,98,42,0.15);

        background: #1a1b23;
        min-height: 100%;
    }

    .sudoku-dark .sudoku-page       { background: var(--surface); }
    .sudoku-dark .s-cell            { background: var(--surface); }
    .sudoku-dark .s-cell.clue       { background: var(--surface-2); }
    .sudoku-dark .s-cell:hover      { background: var(--accent-dim); }
    .sudoku-dark .s-cell.highlighted{ background: rgba(232,98,42,0.12); }
    .sudoku-dark .overlay-screen    { background: rgba(26,27,35,0.93); }
    .sudoku-dark .s-cell.err        { background: rgba(192,57,43,0.25); color: #e57373; }

    /* ── Toggle button ── */
    .dark-toggle {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 1.5px solid var(--border);
        background: var(--surface-2);
        color: var(--ink-2);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: border-color 0.15s, color 0.15s, background 0.15s;
    }

    .dark-toggle:hover {
        border-color: var(--accent);
        color: var(--accent);
    }

    .dark-toggle svg {
        width: 16px;
        height: 16px;
        display: block;
    }
</style>

<div class="sudoku-page">

    <div class="sudoku-header">
        <div style="display:flex; align-items:center; justify-content:space-between;">
            <div>
                <h1>Sudoku</h1>
                <p>Cooperative — everyone shares the same board in real time</p>
            </div>
            <button class="dark-toggle" id="dark-toggle" aria-label="Toggle dark mode">
                <svg id="dark-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="sudoku-body">

        {{-- Board --}}
        <div class="sudoku-board-wrap">
            <div style="position: relative;">
                <div class="sudoku-grid" id="sudoku-grid">
                    @for ($i = 0; $i < 81; $i++)
                        @php $row = intdiv($i, 9); $col = $i % 9; @endphp
                        <div class="s-cell"
                             data-index="{{ $i }}"
                             data-row="{{ $row }}"
                             data-col="{{ $col }}">
                        </div>
                    @endfor
                </div>

                {{-- Pause overlay --}}
                <div class="overlay-screen" id="pause-overlay">
                    <div class="overlay-title">Paused</div>
                    <div class="overlay-sub">Game is paused for everyone</div>
                    <button class="overlay-btn overlay-btn-primary" id="ol-resume">Resume</button>
                    <button class="overlay-btn overlay-btn-secondary" id="ol-new-from-pause">New game</button>
                </div>

                {{-- Completed overlay --}}
                <div class="overlay-screen" id="done-overlay">
                    <div class="overlay-title">🎉 Completed!</div>
                    <div class="overlay-sub">Solved in</div>
                    <div class="overlay-time" id="done-time">00:00:00</div>
                    <button class="overlay-btn overlay-btn-primary" id="ol-new-from-done">New game</button>
                </div>
            </div>
        </div>

        {{-- Right panel --}}
        <div class="sudoku-panel">

            {{-- Stats --}}
            <div class="panel-stats">
                <div class="stat-chip">
                    <span class="label">Level</span>
                    <span class="value" id="stat-level">—</span>
                </div>
                <div class="stat-chip">
                    <span class="label">Time</span>
                    <span class="value accent" id="stat-time">—</span>
                </div>
            </div>

            {{-- Waiting notice (hidden when game active) --}}
            <div class="waiting-banner" id="waiting-banner">
                No game running.<br>Pick a level and start one!
            </div>

            {{-- Number pad --}}
            <div class="numpad" id="numpad">
                @for ($n = 1; $n <= 9; $n++)
                    <button class="num-btn" data-num="{{ $n }}">{{ $n }}</button>
                @endfor
                <button class="num-btn del-btn" id="btn-delete">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 4H8l-7 8 7 8h13a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2z"/>
                        <line x1="18" y1="9" x2="12" y2="15"/><line x1="12" y1="9" x2="18" y2="15"/>
                    </svg>
                </button>
            </div>

            {{-- Actions --}}
            <div class="panel-actions">

                {{-- Level toggle + start --}}
                <div class="level-row">
                    <span>Difficulty</span>
                    <button class="level-toggle" id="btn-level">
                        Easy
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="6 9 12 15 18 9"/>
                        </svg>
                    </button>
                </div>

                <button class="pencil-btn" id="btn-pencil">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M17 3a2.828 2.828 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5L17 3z"/>
                    </svg>
                    <span id="pencil-label">Pencil (notes)</span>
                    <kbd style="font-size:10px; opacity:0.5; font-family:monospace; margin-left:auto;">N</kbd>
                </button>

                <button class="action-btn btn-primary" id="btn-start">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="5 3 19 12 5 21 5 3"/>
                    </svg>
                    New game (for everyone)
                </button>

                <button class="action-btn btn-ghost" id="btn-pause">
                    <svg id="pause-icon-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>
                    </svg>
                    <span id="pause-label">Pause</span>
                </button>
            </div>

        </div>
    </div>
</div>
<meta name="session-id" content="{{ session()->getId() }}">
<script>
    // ── Config ────────────────────────────────────────────────────────
const CSRF   = document.querySelector('meta[name="csrf-token"]').content;
const LEVELS = ['Easy','Medium','Hard','Very Hard','Insane','Inhuman'];
const MY_SESSION = document.querySelector('meta[name="session-id"]').content;

const post = (url, data = {}) =>
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(data),
    }).then(r => r.json());

const showTime = s => new Date(s * 1000).toISOString().substr(11, 8);

// ── DOM ───────────────────────────────────────────────────────────
const cells         = document.querySelectorAll('.s-cell');
const statLevel     = document.getElementById('stat-level');
const statTime      = document.getElementById('stat-time');
const waitingBanner = document.getElementById('waiting-banner');
const pauseOverlay  = document.getElementById('pause-overlay');
const doneOverlay   = document.getElementById('done-overlay');
const doneTime      = document.getElementById('done-time');
const btnPause      = document.getElementById('btn-pause');
const pauseLabel    = document.getElementById('pause-label');
const pauseIconSvg  = document.getElementById('pause-icon-svg');
const btnLevel      = document.getElementById('btn-level');
const btnPencil     = document.getElementById('btn-pencil');
const pencilLabel   = document.getElementById('pencil-label');

// ── State ─────────────────────────────────────────────────────────
let levelIndex    = 0;
let selectedCell  = -1;
let currentPuzzle = null;
let lastBoardStr  = null;
let isPaused      = false;
let pollInterval  = null;
let pencilMode    = false;

// notes[i] = Set of candidate numbers for cell i (persisted locally)
let notes = loadNotes();

function loadNotes() {
    try {
        const raw = localStorage.getItem('sudoku_notes');
        if (!raw) return Array.from({length: 81}, () => new Set());
        return JSON.parse(raw).map(arr => new Set(arr));
    } catch { return Array.from({length: 81}, () => new Set()); }
}

function saveNotes() {
    localStorage.setItem('sudoku_notes', JSON.stringify(notes.map(s => [...s])));
}

function clearAllNotes() {
    notes = Array.from({length: 81}, () => new Set());
    saveNotes();
}

// ── Pencil mode toggle ────────────────────────────────────────────
const togglePencil = () => {
    pencilMode = !pencilMode;
    btnPencil.classList.toggle('pencil-active', pencilMode);
    pencilLabel.textContent = pencilMode ? 'Pencil aktif' : 'Pencil (notes)';
};

btnPencil.addEventListener('click', togglePencil);

// ── Render cell notes ─────────────────────────────────────────────
const renderNotes = (cell, idx) => {
    cell.innerHTML = '';
    const ns = notes[idx];
    const ng = document.createElement('div');
    ng.className = 'note-grid';
    for (let n = 1; n <= 9; n++) {
        const nd = document.createElement('div');
        nd.className = 'note-num' + (ns.has(n) ? '' : ' empty');
        nd.textContent = ns.has(n) ? n : '';
        ng.appendChild(nd);
    }
    cell.appendChild(ng);
};

// ── Render ────────────────────────────────────────────────────────
const renderBoard = (puzzle, board, authors) => {
    cells.forEach((cell, i) => {
        const row    = Math.floor(i / 9), col = i % 9;
        const clue   = puzzle[row][col];
        const val    = board[row][col];
        const author = authors?.[row]?.[col];

        cell.className = 's-cell';
        cell.dataset.row = row;
        cell.dataset.col = col;
        cell.removeAttribute('data-value');
        cell.textContent = '';

        if (clue !== 0) {
            cell.classList.add('clue');
            cell.textContent = clue;
            cell.setAttribute('data-value', clue);
        } else if (val !== 0) {
            cell.textContent = val;
            cell.setAttribute('data-value', val);
            notes[i].clear();

            // Color by author
            if (author === MY_SESSION) cell.classList.add('mine');
            else if (author)           cell.classList.add('theirs');
        } else if (notes[i].size > 0) {
            renderNotes(cell, i);
        }
    });

    if (selectedCell >= 0) {
        cells[selectedCell].classList.add('selected');
        applyHighlight(selectedCell);
    }
};

const applyHighlight = idx => {
    const row = Math.floor(idx / 9), col = idx % 9;
    cells.forEach((c, i) => {
        if (i === idx) return;
        const r = Math.floor(i / 9), cc = i % 9;
        const sameBox = Math.floor(r/3) === Math.floor(row/3) && Math.floor(cc/3) === Math.floor(col/3);
        if (r === row || cc === col || sameBox) c.classList.add('highlighted');
    });
};

const clearHighlight = () => cells.forEach(c => c.classList.remove('highlighted', 'selected'));

const flashErr = idx => {
    cells[idx].classList.add('shake');
    setTimeout(() => cells[idx].classList.remove('shake'), 320);
};

const showWaiting = show => { waitingBanner.style.display = show ? 'block' : 'none'; };

// ── Pause UI sync ─────────────────────────────────────────────────
const syncPauseBtn = paused => {
    isPaused = paused;
    pauseLabel.textContent = paused ? 'Resume' : 'Pause';
    pauseIconSvg.innerHTML = paused
        ? `<polygon points="5 3 19 12 5 21 5 3"/>`
        : `<rect x="6" y="4" width="4" height="16"/><rect x="14" y="4" width="4" height="16"/>`;
    pauseOverlay.classList.toggle('active', paused);
};

// ── Polling ───────────────────────────────────────────────────────
const startPoll = () => { if (!pollInterval) pollInterval = setInterval(poll, 1500); };
const stopPoll  = () => { clearInterval(pollInterval); pollInterval = null; };

const poll = () => {
    fetch('/games/sudoku/state').then(r => r.json()).then(data => {
        if (data.status === 'waiting') {
            showWaiting(true);
            statLevel.textContent = '—';
            statTime.textContent  = '—';
            pauseOverlay.classList.remove('active');
            doneOverlay.classList.remove('active');
            return;
        }

        showWaiting(false);
        statLevel.textContent = data.level;
        statTime.textContent  = showTime(data.seconds);

        if (data.completed) {
            currentPuzzle = data.puzzle;
            renderBoard(data.puzzle, data.board, data.authors);
            doneTime.textContent = showTime(data.seconds);
            doneOverlay.classList.add('active');
            pauseOverlay.classList.remove('active');
            stopPoll();
            return;
        }

        syncPauseBtn(data.paused);

        const boardStr = JSON.stringify(data.board);
        if (boardStr !== lastBoardStr || !currentPuzzle) {
            lastBoardStr  = boardStr;
            currentPuzzle = data.puzzle;
            renderBoard(data.puzzle, data.board, data.authors);
        }
    }).catch(() => {});
};

// ── Cell click ────────────────────────────────────────────────────
cells.forEach((cell, idx) => {
    cell.addEventListener('click', () => {
        clearHighlight();
        selectedCell = idx;
        cell.classList.add('selected');
        applyHighlight(idx);
    });
});

// ── Place number ──────────────────────────────────────────────────
const placeNumber = value => {
    if (selectedCell < 0 || !currentPuzzle) return;
    if (cells[selectedCell].classList.contains('clue')) return;
    if (isPaused) return;

    const row = Math.floor(selectedCell / 9), col = selectedCell % 9;

    // ── Pencil mode: toggle notes locally ──
    if (pencilMode) {
        if (value === 0) {
            notes[selectedCell].clear();
        } else {
            // Jangan tambah note kalau cell sudah terisi angka final
            const currentVal = currentPuzzle ? currentPuzzle[row][col] : 0;
            if (currentVal !== 0) return;
            if (cells[selectedCell].getAttribute('data-value') && 
                !notes[selectedCell].size) return; // ada nilai final

            if (notes[selectedCell].has(value)) notes[selectedCell].delete(value);
            else notes[selectedCell].add(value);
        }
        saveNotes();

        // Re-render cell tanpa kirim ke server
        const cell = cells[selectedCell];
        cell.textContent = '';
        cell.classList.remove('err');
        if (notes[selectedCell].size > 0) {
            renderNotes(cell, selectedCell);
        }
        return;
    }

    // ── Normal mode: kirim ke server ──
    if (value === 0) {
        cells[selectedCell].textContent = '';
        cells[selectedCell].removeAttribute('data-value');
        notes[selectedCell].clear();
        saveNotes();
    } else {
        cells[selectedCell].textContent = value;
        cells[selectedCell].setAttribute('data-value', value);
        cells[selectedCell].classList.add('pop');
        setTimeout(() => cells[selectedCell].classList.remove('pop'), 220);
    }

    post('/games/sudoku/move', { row, col, value })
        .then(data => {
            if (data.error) { flashErr(selectedCell); return; }
            lastBoardStr = JSON.stringify(data.board);
            // Hapus notes di cell yang baru diisi
            notes[selectedCell].clear();
            saveNotes();
            if (data.completed) {
                doneTime.textContent = showTime(data.seconds);
                doneOverlay.classList.add('active');
                stopPoll();
            }
        });
};

document.querySelectorAll('.num-btn:not(.del-btn)').forEach(btn => {
    btn.addEventListener('click', () => placeNumber(parseInt(btn.dataset.num)));
});
document.getElementById('btn-delete').addEventListener('click', () => placeNumber(0));

// ── Keyboard ──────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
    if (e.key === 'n' || e.key === 'N') { togglePencil(); return; }
    if (e.key >= '1' && e.key <= '9')                               placeNumber(parseInt(e.key));
    if (e.key === 'Backspace' || e.key === 'Delete' || e.key === '0') placeNumber(0);
    if (!['ArrowUp','ArrowDown','ArrowLeft','ArrowRight'].includes(e.key)) return;
    e.preventDefault();
    if (selectedCell < 0) { cells[0].click(); return; }
    const dirs = { ArrowUp: -9, ArrowDown: 9, ArrowLeft: -1, ArrowRight: 1 };
    const next = selectedCell + dirs[e.key];
    if (next >= 0 && next < 81) cells[next].click();
});

// ── Level toggle ──────────────────────────────────────────────────
btnLevel.addEventListener('click', () => {
    levelIndex = (levelIndex + 1) % LEVELS.length;
    btnLevel.childNodes[0].textContent = LEVELS[levelIndex] + ' ';
});

// ── Start ─────────────────────────────────────────────────────────
document.getElementById('btn-start').addEventListener('click', () => {
    if (!confirm('Start new game for everyone?')) return;
    selectedCell  = -1;
    currentPuzzle = null;
    lastBoardStr  = null;
    clearHighlight();
    clearAllNotes();  // reset notes saat new game
    doneOverlay.classList.remove('active');
    post('/games/sudoku/start', { level: LEVELS[levelIndex] })
        .then(() => { showWaiting(false); startPoll(); });
});

// ── Pause / Resume ────────────────────────────────────────────────
btnPause.addEventListener('click', () => { post('/games/sudoku/pause', { paused: !isPaused }); });
document.getElementById('ol-resume').addEventListener('click', () => { post('/games/sudoku/pause', { paused: false }); });

// ── New game from overlays ────────────────────────────────────────
['ol-new-from-pause','ol-new-from-done'].forEach(id => {
    document.getElementById(id).addEventListener('click', () => {
        post('/games/sudoku/reset').then(() => {
            pauseOverlay.classList.remove('active');
            doneOverlay.classList.remove('active');
            currentPuzzle = null;
            lastBoardStr  = null;
            selectedCell  = -1;
            statLevel.textContent = '—';
            statTime.textContent  = '—';
            showWaiting(true);
            stopPoll();
            clearAllNotes();
            cells.forEach(c => { c.textContent = ''; c.className = 's-cell'; });
        });
    });
});

// ── Dark mode ─────────────────────────────────────────────────────
const sudokuPage  = document.querySelector('.sudoku-page');
const darkToggle  = document.getElementById('dark-toggle');
const darkIcon    = document.getElementById('dark-icon');

const MOON = `<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>`;
const SUN  = `<circle cx="12" cy="12" r="5"/>
    <line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/>
    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
    <line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/>
    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>`;

const applyDark = dark => {
    sudokuPage.classList.toggle('sudoku-dark', dark);
    darkIcon.innerHTML = dark ? SUN : MOON;
    localStorage.setItem('sudoku_dark', dark);
};

darkToggle.addEventListener('click', () => applyDark(!sudokuPage.classList.contains('sudoku-dark')));
applyDark(localStorage.getItem('sudoku_dark') === 'true');

// ── Boot ──────────────────────────────────────────────────────────
(() => {
    showWaiting(true);
    fetch('/games/sudoku/state').then(r => r.json()).then(data => {
        if (data.status !== 'waiting') { showWaiting(false); startPoll(); }
    });
})();
</script>

@endsection