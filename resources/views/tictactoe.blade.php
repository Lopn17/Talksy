@extends('layouts.app')

@section('content')
<style>
    .ttt-page {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 2rem;
        overflow-y: auto;
        background: var(--surface);
    }

    .ttt-wrap {
        display: flex;
        gap: 2rem;
        align-items: flex-start;
        flex-wrap: wrap;
        justify-content: center;
    }

    .ttt-main { text-align: center; }

    .ttt-title {
        font-family: 'Instrument Serif', serif;
        font-size: 2.4rem;
        color: var(--ink);
        letter-spacing: -0.02em;
        margin-bottom: 0.25rem;
    }

    .ttt-subtitle {
        font-size: 0.875rem;
        color: var(--ink-2);
        margin-bottom: 1.5rem;
    }

    .ttt-board-wrap {
        background: var(--surface-2);
        padding: 1.5rem;
        border-radius: 16px;
        border: 1px solid var(--border);
        position: relative;
        display: inline-block;
    }

    .ttt-status {
        font-size: 1rem;
        font-weight: 500;
        color: var(--ink-2);
        margin-bottom: 1rem;
        min-height: 1.5rem;
    }

    .ttt-board {
        display: grid;
        gap: 6px;
    }

    .ttt-cell {
        background: white;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        cursor: pointer;
        border: 1px solid var(--border);
        transition: transform 0.12s, box-shadow 0.12s, opacity 0.2s;
        user-select: none;
    }

    .ttt-cell:hover {
        transform: scale(1.06);
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }

    /* .ttt-cell.fading {
        opacity: 0.3;
    } */

    /* Win line */
    .win-line {
        position: absolute;
        background: var(--accent);
        height: 6px;
        border-radius: 3px;
        transform-origin: left center;
        pointer-events: none;
        z-index: 10;
        opacity: 0;
        transition: opacity 0.3s;
        top: 0; left: 0; width: 0;
    }
    .win-line.visible { opacity: 1; }

    /* Result overlay */
    .ttt-overlay {
        display: none;
        position: absolute;
        inset: 0;
        background: rgba(247,246,243,0.93);
        border-radius: 16px;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        z-index: 20;
        gap: 0.5rem;
    }
    .ttt-overlay.visible { display: flex; }
    .overlay-emoji { font-size: 3rem; }
    .overlay-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.75rem;
        color: var(--ink);
    }
    .overlay-sub { font-size: 0.8rem; color: var(--ink-2); }
    .overlay-btn {
        margin-top: 0.5rem;
        padding: 0.6rem 1.5rem;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.15s;
    }
    .overlay-btn:hover { opacity: 0.85; }

    /* Sidebar */
    .ttt-sidebar {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 1.5rem;
        width: 200px;
        flex-shrink: 0;
    }
    .ttt-sidebar h3 {
        font-size: 0.7rem;
        font-weight: 500;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--ink-2);
        margin-bottom: 0.75rem;
    }
    .size-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        margin-bottom: 1.5rem;
    }
    .size-btn {
        padding: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: white;
        color: var(--ink);
        cursor: pointer;
        transition: all 0.15s;
    }
    .size-btn:hover { border-color: var(--accent); color: var(--accent); }
    .size-btn.active { background: var(--accent); color: white; border-color: var(--accent); }

    .new-game-btn {
        width: 100%;
        padding: 0.65rem;
        background: var(--accent);
        color: white;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.15s;
        margin-top: 0.5rem;
    }
    .new-game-btn:hover { opacity: 0.85; }

    .how-to {
        font-size: 0.8rem;
        color: var(--ink-2);
        line-height: 1.6;
        margin-bottom: 1.5rem;
    }
    .how-to span { display: block; }

    @media (max-width: 600px) {
        .ttt-page { padding: 1rem; }
        .ttt-sidebar { width: 100%; }
    }
</style>

<div class="ttt-page">
    <div class="ttt-wrap">

        <div class="ttt-main">
            <h1 class="ttt-title">Tic Tac Toe</h1>
            <p class="ttt-subtitle" id="ttt-subtitle">Shared board · Everyone plays together</p>

            <div class="ttt-board-wrap" id="board-wrap">
                <div class="ttt-status" id="ttt-status">Loading…</div>
                <div class="ttt-board"  id="ttt-board"></div>
                <div class="win-line"   id="win-line"></div>

                <div class="ttt-overlay" id="ttt-overlay">
                    <div class="overlay-emoji" id="overlay-emoji">🎉</div>
                    <div class="overlay-title" id="overlay-title">X Wins!</div>
                    <div class="overlay-sub">Anyone can start a new game</div>
                    <button class="overlay-btn" onclick="resetGame()">New Game</button>
                </div>
            </div>
        </div>

        <div class="ttt-sidebar">
            <h3>Board Size</h3>
            <div class="size-grid" id="size-grid"></div>

            <h3>How to play</h3>
            <div class="how-to">
                <span>🖱️ Click any cell to place</span>
                <span>🔄 Click again to cycle X → O → clear</span>
                <span>👥 Everyone shares this board live</span>
            </div>

            <button class="new-game-btn" onclick="resetGame()">New Game</button>
        </div>

    </div>
</div>

<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let state       = null;
    let lastVersion = -1;

    // ── Size buttons ──
    const SIZES = [3, 4, 5, 6];
    const sizeGrid = document.getElementById('size-grid');
    SIZES.forEach(s => {
        const btn = document.createElement('button');
        btn.className    = 'size-btn';
        btn.textContent  = `${s}×${s}`;
        btn.dataset.size = s;
        btn.addEventListener('click', () => resetGame(s));
        sizeGrid.appendChild(btn);
    });

    function updateSizeBtns(size) {
        document.querySelectorAll('.size-btn').forEach(b =>
            b.classList.toggle('active', parseInt(b.dataset.size) === size)
        );
    }

    // ── Render ──
    function renderBoard(s) {
        const boardEl = document.getElementById('ttt-board');
        const size    = s.board_size;
        const pxCell  = size <= 4 ? 90 : size === 5 ? 72 : 58;

        boardEl.style.gridTemplateColumns = `repeat(${size}, ${pxCell}px)`;
        boardEl.innerHTML = '';

        const fadingX = s.pieces_x.length === size ? s.pieces_x[0] : null;
        const fadingO = s.pieces_o.length === size ? s.pieces_o[0] : null;

        s.board.forEach((val, idx) => {
            const cell = document.createElement('div');
            cell.className   = 'ttt-cell';
            cell.style.width  = pxCell + 'px';
            cell.style.height = pxCell + 'px';
            cell.textContent  = val === 'X' ? '❌' : val === 'O' ? '⭕' : '';

            if ((idx === fadingX && val === 'X') || (idx === fadingO && val === 'O')) {
                cell.classList.add('fading');
                cell.title = 'Oldest piece — vanishes on next placement';
            }

            if (s.status === 'playing') {
                cell.addEventListener('click', () => sendMove(idx));
            }

            boardEl.appendChild(cell);
        });
    }

    // ── Win line ──
    function drawWinLine(combo, s) {
        const boardEl = document.getElementById('ttt-board');
        const line    = document.getElementById('win-line');
        const first   = boardEl.children[combo[0]];
        const last    = boardEl.children[combo[combo.length - 1]];
        const bRect   = boardEl.getBoundingClientRect();
        const fRect   = first.getBoundingClientRect();
        const lRect   = last.getBoundingClientRect();

        const sx = fRect.left + fRect.width  / 2 - bRect.left;
        const sy = fRect.top  + fRect.height / 2 - bRect.top;
        const ex = lRect.left + lRect.width  / 2 - bRect.left;
        const ey = lRect.top  + lRect.height / 2 - bRect.top;

        const len   = Math.hypot(ex - sx, ey - sy);
        const angle = Math.atan2(ey - sy, ex - sx) * 180 / Math.PI;

        line.style.left      = sx + 'px';
        line.style.top       = (sy - 3) + 'px';
        line.style.width     = len + 'px';
        line.style.transform = `rotate(${angle}deg)`;
        line.classList.add('visible');
    }

    function clearWinLine() {
        const line = document.getElementById('win-line');
        line.classList.remove('visible');
        line.style.width = '0';
    }

    function computeWin(board, player, size) {
        const check = c => c.every(i => board[i] === player);
        for (let r = 0; r < size; r++) {
            const row = Array.from({length: size}, (_, c) => r * size + c);
            if (check(row)) return row;
        }
        for (let c = 0; c < size; c++) {
            const col = Array.from({length: size}, (_, r) => r * size + c);
            if (check(col)) return col;
        }
        const main = Array.from({length: size}, (_, i) => i * size + i);
        if (check(main)) return main;
        const anti = Array.from({length: size}, (_, i) => i * size + (size - 1 - i));
        if (check(anti)) return anti;
        return null;
    }

    // ── Apply state ──
    function applyState(s) {
        state = s;
        updateSizeBtns(s.board_size);
        renderBoard(s);
        clearWinLine();

        const statusEl = document.getElementById('ttt-status');
        const overlay  = document.getElementById('ttt-overlay');

        overlay.classList.remove('visible');

        if (s.status === 'playing') {
        // Jadi:
        const turnEmoji = s.turn === 'X' ? '❌' : '⭕';
        statusEl.textContent = `Giliran ${turnEmoji} — klik cell kosong`;

        } else if (s.status === 'draw') {
            statusEl.textContent = "It's a draw!";
            document.getElementById('overlay-emoji').textContent = '🤝';
            document.getElementById('overlay-title').textContent = "It's a Draw!";
            overlay.classList.add('visible');

        } else {
            const winner = s.status === 'won_x' ? 'X' : 'O';
            const emoji  = winner === 'X' ? '❌' : '⭕';
            statusEl.textContent = `${winner} wins!`;
            document.getElementById('overlay-emoji').textContent = '🎉';
            document.getElementById('overlay-title').textContent = `${emoji} Wins!`;
            overlay.classList.add('visible');

            const combo = computeWin(s.board, winner, s.board_size);
            if (combo) drawWinLine(combo, s);
        }
    }

    // ── Move ──
    async function sendMove(index) {
        if (!state || state.status !== 'playing') return;
        try {
            const res  = await fetch('/games/tictactoe/move', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({ index }),
            });
            const data = await res.json();
            if (res.ok) { lastVersion = data.version; applyState(data); }
        } catch (e) { console.error(e); }
    }

    // ── Reset ──
    async function resetGame(size) {
        size = size || (state ? state.board_size : 3);
        try {
            const res  = await fetch('/games/tictactoe/reset', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                body:    JSON.stringify({ size }),
            });
            const data = await res.json();
            if (res.ok) { lastVersion = data.version; applyState(data); }
        } catch (e) { console.error(e); }
    }

    // ── Poll ──
    async function poll() {
        try {
            const res  = await fetch('/games/tictactoe/state');
            const data = await res.json();
            if (data.version !== lastVersion) {
                lastVersion = data.version;
                applyState(data);
            }
        } catch (e) { /* retry */ }
        setTimeout(poll, 1500);
    }

    poll();
</script>
@endsection