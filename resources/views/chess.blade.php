@extends('layouts.app')

@section('content')

<link rel="stylesheet" href="{{ asset('css/chessboard-1.0.0.min.css') }}">

<style>
    .chess-page {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px;
        background: var(--surface);
        overflow-y: auto;
        min-height: 0;
    }

    .chess-card {
        background: #fff;
        border-radius: 14px;
        box-shadow: 0 4px 32px rgba(0,0,0,0.10);
        padding: 28px 28px 20px;
        width: 100%;
        max-width: 480px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
    }

    .chess-title {
        font-family: 'Instrument Serif', serif;
        font-size: 1.5rem;
        font-weight: 400;
        color: #16150f;
        letter-spacing: -0.02em;
        margin: 0;
    }

    .live-bar {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.78rem;
        color: #6b6a64;
    }

    .live-dot {
        width: 7px; height: 7px;
        border-radius: 50%;
        background: #27ae60;
        animation: livepulse 1.5s infinite;
        flex-shrink: 0;
    }

    @keyframes livepulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.3; }
    }

    .turn-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 14px;
        border-radius: 99px;
        font-size: 0.8rem;
        font-weight: 600;
        transition: background 0.3s, color 0.3s;
    }
    .turn-pill.white-turn { background: #f0d9b5; color: #4a3728; }
    .turn-pill.black-turn { background: #4a3728; color: #f0d9b5; }

    #board {
        width: 400px;
        max-width: 100%;
    }

    .chess-btns {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        justify-content: center;
        width: 100%;
    }

    .chess-btn {
        font-family: 'DM Sans', sans-serif;
        color: #fff;
        background: #16150f;
        border: none;
        padding: 0 20px;
        height: 38px;
        border-radius: 8px;
        font-size: 0.83rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.18s;
    }
    .chess-btn:hover  { background: #434343; }
    .chess-btn.danger { background: #c0392b; }
    .chess-btn.danger:hover { background: #96281b; }

    .chess-history {
        width: 100%;
        border: 1px solid #e8e7e3;
        border-radius: 8px;
        padding: 10px 12px;
        height: 90px;
        overflow-y: auto;
        background: #f7f6f3;
        font-size: 0.8rem;
        line-height: 1.7;
        color: #16150f;
        font-family: 'Courier New', monospace;
        word-break: break-word;
    }
    .chess-history:empty::before {
        content: 'No moves yet…';
        color: #b0afa9;
        font-family: 'DM Sans', sans-serif;
    }

    @media (max-width: 500px) {
        #board { width: 300px; }
        .chess-card { padding: 18px 12px 14px; }
    }

    /* ── Toast ─────────────────────────────────────────────────── */
    #chess-toast {
        position: fixed;
        top: 28px;
        left: 50%;
        transform: translateX(-50%) translateY(-120px);
        z-index: 9999;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 22px;
        border-radius: 14px;
        box-shadow: 0 8px 32px rgba(0,0,0,0.18);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.95rem;
        font-weight: 600;
        white-space: nowrap;
        transition: transform 0.4s cubic-bezier(0.34,1.56,0.64,1),
                    opacity  0.4s ease;
        opacity: 0;
        pointer-events: none;
        min-width: 220px;
        justify-content: center;
    }

    #chess-toast.show {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
        pointer-events: auto;
    }

    /* themes */
    #chess-toast.win  { background: #1a1a1a; color: #f5c518; border: 1.5px solid #f5c51855; }
    #chess-toast.draw { background: #2c2c2c; color: #e0e0e0; border: 1.5px solid #ffffff22; }

    .toast-icon { font-size: 1.4rem; line-height: 1; }
    .toast-body { display: flex; flex-direction: column; gap: 1px; }
    .toast-title { font-size: 1rem; font-weight: 700; letter-spacing: -0.01em; }
    .toast-sub   { font-size: 0.72rem; font-weight: 400; opacity: 0.7; }

    .toast-close {
        background: none;
        border: none;
        cursor: pointer;
        color: inherit;
        opacity: 0.5;
        font-size: 1.1rem;
        padding: 0 0 0 8px;
        line-height: 1;
        transition: opacity 0.15s;
        pointer-events: auto;
    }
    .toast-close:hover { opacity: 1; background: none; }
</style>

{{-- Toast --}}
<div id="chess-toast">
    <span class="toast-icon" id="toast-icon">🏆</span>
    <div class="toast-body">
        <span class="toast-title" id="toast-title">White Wins!</span>
        <span class="toast-sub"   id="toast-sub">by checkmate</span>
    </div>
    <button class="toast-close" id="toast-close">✕</button>
</div>

<div class="chess-page">
    <div class="chess-card">

        <h1 class="chess-title">♟ Talksy Chess</h1>

        <div class="live-bar">
            <span class="live-dot"></span>
            <span id="online-txt">syncing…</span>
        </div>

        <span id="turn-indicator" class="turn-pill white-turn">⬜ White's turn</span>

        <div id="board"></div>

        <div class="chess-btns">
            <button class="chess-btn" id="btn-flip">Flip Board</button>
            <button class="chess-btn danger" id="btn-reset">Reset Board</button>
        </div>

        <div class="chess-history" id="move-history"></div>

    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script src="{{ asset('js/chessboard-1.0.0.min.js') }}"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chess.js/0.10.3/chess.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {

    const POLL_MS  = 800;
    const CSRF     = document.querySelector('meta[name="csrf-token"]').content;
    const USERNAME = @json(auth()->user()->name ?? 'Player');

    let board      = null;
    let game       = new Chess();
    let lastFen    = '';
    let isSending  = false;
    let toastShown = false; // prevent repeated toasts for same game-over

    const moveHistoryEl = document.getElementById('move-history');
    const turnIndicator = document.getElementById('turn-indicator');
    const onlineTxt     = document.getElementById('online-txt');
    const toast         = document.getElementById('chess-toast');
    const toastIcon     = document.getElementById('toast-icon');
    const toastTitle    = document.getElementById('toast-title');
    const toastSub      = document.getElementById('toast-sub');
    let toastTimer      = null;

    // ── Toast ─────────────────────────────────────────────────────
    // status: 'checkmate' | 'draw' | 'stalemate'
    // winnerColor: 'w' | 'b' | null  (null for draws)
    function showToast(status, winnerColor) {
        if (toastShown) return;
        toastShown = true;

        clearTimeout(toastTimer);

        if (status === 'checkmate') {
            const winner = winnerColor === 'w' ? 'White' : 'Black';
            toastIcon.textContent  = winnerColor === 'w' ? '⬜' : '⬛';
            toastTitle.textContent = winner + ' Wins! 🏆';
            toastSub.textContent   = 'by checkmate';
            toast.className        = 'win';
        } else if (status === 'draw') {
            toastIcon.textContent  = '🤝';
            toastTitle.textContent = 'It\'s a Draw!';
            toastSub.textContent   = 'by insufficient material / 50-move rule';
            toast.className        = 'draw';
        } else if (status === 'stalemate') {
            toastIcon.textContent  = '🤝';
            toastTitle.textContent = 'Stalemate!';
            toastSub.textContent   = 'No legal moves — it\'s a draw';
            toast.className        = 'draw';
        }

        // Slide in
        requestAnimationFrame(() => {
            requestAnimationFrame(() => toast.classList.add('show'));
        });

        // Auto-dismiss after 8s
        toastTimer = setTimeout(hideToast, 8000);
    }

    function hideToast() {
        toast.classList.remove('show');
    }

    document.getElementById('toast-close').addEventListener('click', hideToast);

    // ── UI helpers ────────────────────────────────────────────────
    function updateTurnUI() {
        const isWhite = game.turn() === 'w';
        turnIndicator.className   = 'turn-pill ' + (isWhite ? 'white-turn' : 'black-turn');
        turnIndicator.textContent = isWhite ? "⬜ White's turn" : "⬛ Black's turn";
    }

    function renderHistory(history) {
        let out = '';
        history.forEach((entry, idx) => {
            const moveNum = Math.floor(idx / 2) + 1;
            out += (idx % 2 === 0 ? moveNum + '. ' : '') + entry.san + ' ';
        });
        moveHistoryEl.textContent = out;
        moveHistoryEl.scrollTop   = moveHistoryEl.scrollHeight;
    }

    function gameStatus() {
        if (game.in_checkmate()) return 'checkmate';
        if (game.in_draw())      return 'draw';
        if (game.in_stalemate()) return 'stalemate';
        return 'playing';
    }

    // When status is 'checkmate', the CURRENT turn is the LOSER
    // (chess.js flips the turn after the move, so game.turn() = the side that lost)
    // Winner = the other side
    function winnerFromGame() {
        // game.turn() is the side who has NO moves (the loser)
        return game.turn() === 'w' ? 'b' : 'w';
    }

    // ── Board callbacks ───────────────────────────────────────────
    function onDragStart() {
        return !game.game_over();
    }

    function onDrop(source, target) {
        const move = game.move({ from: source, to: target, promotion: 'q' });
        if (move === null) return 'snapback';

        const newFen = game.fen();
        const status = gameStatus();

        lastFen   = newFen;
        isSending = true;

        updateTurnUI();

        if (status !== 'playing') {
            const winner = status === 'checkmate' ? winnerFromGame() : null;
            showToast(status, winner);
        }

        fetch('/games/chess/move', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body: JSON.stringify({
                fen: newFen, turn: game.turn(),
                san: move.san, status, player: USERNAME,
                winner: status === 'checkmate' ? winnerFromGame() : null,
            }),
        })
        .then(r => r.json())
        .then(() => { isSending = false; })
        .catch(() => { isSending = false; });
    }

    function onSnapEnd() {
        board.position(game.fen());
    }

    // ── Init board ────────────────────────────────────────────────
    const pieceTheme = '{{ asset('img/chesspieces/wikipedia') }}' + '/{piece}.png';

    board = Chessboard('board', {
        showNotation: true,
        draggable:    true,
        position:     'start',
        onDragStart,
        onDrop,
        onSnapEnd,
        moveSpeed:    'fast',
        snapSpeed:    50,
        pieceTheme,
    });

    $(window).on('resize', board.resize);

    // ── Polling ───────────────────────────────────────────────────
    function poll() {
        if (isSending) return;

        fetch('/games/chess/state')
            .then(r => r.json())
            .then(data => {
                onlineTxt.textContent = 'Live';
                if (data.fen === lastFen) return;

                lastFen = data.fen;
                game.load(data.fen);
                board.position(data.fen);
                updateTurnUI();
                renderHistory(data.history);

                // Show toast on the receiving side too
                if (data.status !== 'playing') {
                    // winner is stored in cache by the moving client
                    const winner = data.winner ?? null;
                    showToast(data.status, winner);
                }
            })
            .catch(() => { onlineTxt.textContent = 'reconnecting…'; });
    }

    poll();
    setInterval(poll, POLL_MS);

    // ── Buttons ───────────────────────────────────────────────────
    document.getElementById('btn-flip').addEventListener('click', () => board.flip());

    document.getElementById('btn-reset').addEventListener('click', () => {
        if (!confirm('Reset the board for everyone?')) return;
        fetch('/games/chess/reset', {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(() => {
            game.reset();
            board.start();
            moveHistoryEl.textContent = '';
            lastFen    = game.fen();
            toastShown = false;
            hideToast();
            updateTurnUI();
        });
    });

    updateTurnUI();
});
</script>

@endsection