@extends('layouts.app')

@section('content')
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
<style>
/* ─── Reset & Root ─────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; }

:root {
  --bg: #0d0d14;
  --surface: #15151f;
  --surface2: #1c1c2a;
  --border: rgba(255,255,255,0.07);
  --card-back: #1a1a2e;
  --accent: #e94560;
  --accent2: #f5a623;
  --accent3: #53d8fb;
  --accent4: #83fb53;
  --accent5: #a78bfa;
  --success: #43e97b;
  --text: #f0f0f0;
  --text-muted: #6b7280;
  --radius: 14px;
  --radius-sm: 8px;
}

#memory-app {
  font-family: 'Nunito', sans-serif;
  background: var(--bg);
  min-height: 100vh;
  color: var(--text);
  padding: 24px 16px 60px;
  display: flex;
  flex-direction: column;
  align-items: center;
  background-image:
    radial-gradient(ellipse 60% 40% at 15% 10%, rgba(233,69,96,0.08) 0%, transparent 70%),
    radial-gradient(ellipse 50% 40% at 85% 85%, rgba(83,216,251,0.07) 0%, transparent 70%);
}

/* ─── Header ───────────────────────────────────────────────── */
.mm-header {
  text-align: center;
  margin-bottom: 28px;
}
.mm-logo {
  font-size: 2.2rem;
  font-weight: 900;
  letter-spacing: -1.5px;
  line-height: 1;
  margin-bottom: 4px;
}
.mm-logo .w1 { color: var(--accent); }
.mm-logo .w2 { color: var(--accent3); }
.mm-sub {
  font-family: 'Space Mono', monospace;
  font-size: 0.65rem;
  color: var(--text-muted);
  letter-spacing: 0.2em;
  text-transform: uppercase;
}

/* ─── Difficulty ───────────────────────────────────────────── */
.mm-diff-row {
  display: flex;
  gap: 6px;
  flex-wrap: wrap;
  justify-content: center;
  margin-bottom: 20px;
}
.diff-btn {
  padding: 7px 18px;
  border-radius: 50px;
  border: 1.5px solid var(--border);
  background: transparent;
  color: var(--text-muted);
  font-family: 'Nunito', sans-serif;
  font-size: 0.82rem;
  font-weight: 800;
  cursor: pointer;
  transition: all 0.18s;
  letter-spacing: 0.03em;
}
.diff-btn:hover { border-color: rgba(255,255,255,0.2); color: var(--text); }
.diff-btn.active-easy     { background: var(--accent);  border-color: var(--accent);  color: #fff; }
.diff-btn.active-medium   { background: var(--accent2); border-color: var(--accent2); color: #1a1a1a; }
.diff-btn.active-hard     { background: var(--accent3); border-color: var(--accent3); color: #1a1a1a; }
.diff-btn.active-veryhard { background: var(--accent4); border-color: var(--accent4); color: #1a1a1a; }
.diff-btn.active-insane   { background: var(--accent5); border-color: var(--accent5); color: #fff; }

/* ─── Main layout ──────────────────────────────────────────── */
.mm-layout {
  display: flex;
  gap: 20px;
  align-items: flex-start;
  width: 100%;
  max-width: 1100px;
  justify-content: center;
}

/* ─── Sidebar ──────────────────────────────────────────────── */
.mm-sidebar {
  width: 230px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 14px;
}

.mm-panel {
  background: var(--surface);
  border: 1px solid var(--border);
  border-radius: var(--radius);
  padding: 16px;
}
.mm-panel-title {
  font-family: 'Space Mono', monospace;
  font-size: 0.6rem;
  text-transform: uppercase;
  letter-spacing: 0.18em;
  color: var(--text-muted);
  margin-bottom: 12px;
}

/* Stats */
.mm-stats {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 8px;
}
.stat-box {
  background: var(--surface2);
  border-radius: var(--radius-sm);
  padding: 10px 8px;
  text-align: center;
}
.stat-box .sl { font-size: 0.58rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.1em; font-weight: 700; margin-bottom: 2px; }
.stat-box .sv { font-size: 1.3rem; font-weight: 900; color: var(--text); font-variant-numeric: tabular-nums; }
.stat-box.span2 { grid-column: span 2; }

/* Players */
.player-list { display: flex; flex-direction: column; gap: 6px; }

.player-item {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 9px 10px;
  border-radius: var(--radius-sm);
  background: var(--surface2);
  border: 1.5px solid transparent;
  transition: all 0.2s;
  position: relative;
}
.player-item.is-turn {
  border-color: var(--accent2);
  background: rgba(245,166,35,0.06);
}
.player-item.is-turn::before {
  content: '▶';
  position: absolute;
  left: -16px;
  color: var(--accent2);
  font-size: 0.55rem;
  animation: blink 0.9s steps(1) infinite;
}
@keyframes blink { 0%,100% { opacity:1; } 50% { opacity:0; } }

.player-avatar {
  width: 28px; height: 28px;
  border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  font-size: 0.7rem;
  font-weight: 900;
  flex-shrink: 0;
  color: #fff;
}
.player-info { flex: 1; min-width: 0; }
.player-name { font-size: 0.82rem; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.player-score-badge {
  font-family: 'Space Mono', monospace;
  font-size: 0.78rem;
  font-weight: 700;
  padding: 2px 7px;
  border-radius: 20px;
  background: var(--surface);
  color: var(--accent2);
  flex-shrink: 0;
}
.player-del {
  background: none;
  border: none;
  color: var(--text-muted);
  cursor: pointer;
  font-size: 0.9rem;
  padding: 2px;
  line-height: 1;
  transition: color 0.15s;
  flex-shrink: 0;
}
.player-del:hover { color: var(--accent); }

/* Add player */
.add-player-form { display: flex; gap: 6px; }
.add-player-input {
  flex: 1;
  background: var(--surface2);
  border: 1.5px solid var(--border);
  color: var(--text);
  border-radius: var(--radius-sm);
  padding: 8px 10px;
  font-family: 'Nunito', sans-serif;
  font-size: 0.85rem;
  font-weight: 700;
  outline: none;
  transition: border-color 0.15s;
}
.add-player-input:focus { border-color: rgba(255,255,255,0.25); }
.add-player-input::placeholder { color: var(--text-muted); font-weight: 600; }
.btn-add {
  background: var(--accent);
  border: none;
  color: #fff;
  border-radius: var(--radius-sm);
  padding: 8px 12px;
  font-family: 'Nunito', sans-serif;
  font-size: 1rem;
  font-weight: 900;
  cursor: pointer;
  transition: all 0.15s;
}
.btn-add:hover { background: #ff6b84; }

/* Turn indicator */
.turn-banner {
  background: var(--surface2);
  border: 1.5px solid var(--accent2);
  border-radius: var(--radius-sm);
  padding: 10px 12px;
  text-align: center;
  font-size: 0.8rem;
  color: var(--text-muted);
}
.turn-name { font-size: 1rem; font-weight: 900; color: var(--accent2); margin-top: 2px; }

/* New game btn */
.btn-new-game {
  background: transparent;
  border: 1.5px solid var(--border);
  color: var(--text-muted);
  font-family: 'Nunito', sans-serif;
  font-size: 0.85rem;
  font-weight: 800;
  padding: 10px 16px;
  border-radius: var(--radius-sm);
  cursor: pointer;
  width: 100%;
  transition: all 0.15s;
  letter-spacing: 0.03em;
}
.btn-new-game:hover { border-color: var(--accent); color: var(--accent); }

/* ─── Board ─────────────────────────────────────────────────── */
.mm-board-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 14px;
}

/* Who's clicking label */
.who-label {
  font-family: 'Space Mono', monospace;
  font-size: 0.65rem;
  text-transform: uppercase;
  letter-spacing: 0.12em;
  color: var(--text-muted);
  margin-bottom: 4px;
  text-align: center;
}
.who-select {
  background: var(--surface2);
  border: 1.5px solid var(--border);
  color: var(--text);
  border-radius: var(--radius-sm);
  padding: 7px 12px;
  font-family: 'Nunito', sans-serif;
  font-size: 0.9rem;
  font-weight: 700;
  outline: none;
  cursor: pointer;
  appearance: none;
  padding-right: 28px;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236b7280' stroke-width='1.5' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 10px center;
  transition: border-color 0.15s;
}
.who-select:focus { border-color: rgba(255,255,255,0.25); }

.mm-board {
  display: grid;
  gap: 8px;
}

/* Cards */
.card {
  cursor: pointer;
  perspective: 900px;
}
.card-inner {
  width: 100%; height: 100%;
  position: relative;
  transform-style: preserve-3d;
  transition: transform 0.45s cubic-bezier(0.4,0,0.2,1);
}
.card.flipped .card-inner,
.card.matched .card-inner { transform: rotateY(180deg); }
.card:not(.flipped):not(.matched):hover .card-inner { transform: scale(1.06) rotateY(6deg); }
.card.locked { cursor: not-allowed; opacity: 0.6; }

.face {
  position: absolute; inset: 0;
  border-radius: 12px;
  display: flex; align-items: center; justify-content: center;
  backface-visibility: hidden;
}
.face-back {
  background: var(--card-back);
  border: 1.5px solid rgba(255,255,255,0.07);
  font-size: 1.4rem;
  color: rgba(255,255,255,0.12);
}
.face-front {
  background: var(--surface);
  border: 1.5px solid rgba(255,255,255,0.1);
  transform: rotateY(180deg);
  font-size: 2.2rem;
}
.card.matched .face-front {
  background: rgba(67,233,123,0.1);
  border-color: rgba(67,233,123,0.5);
}

/* Shake on mismatch */
.card.shake .card-inner {
  animation: shake 0.42s ease-out;
}
@keyframes shake {
  0%,100% { transform: rotateY(180deg) translateX(0); }
  25% { transform: rotateY(180deg) translateX(-6px); }
  75% { transform: rotateY(180deg) translateX(6px); }
}

/* ─── Win modal ─────────────────────────────────────────────── */
.mm-overlay {
  display: none;
  position: fixed; inset: 0;
  background: rgba(0,0,0,0.75);
  backdrop-filter: blur(8px);
  align-items: center; justify-content: center;
  z-index: 200;
}
.mm-overlay.show { display: flex; }
.mm-modal {
  background: var(--surface);
  border: 1px solid rgba(255,255,255,0.1);
  border-radius: 24px;
  padding: 40px 44px;
  text-align: center;
  animation: popIn 0.38s cubic-bezier(0.34,1.56,0.64,1);
  max-width: 360px; width: 90%;
}
@keyframes popIn {
  from { transform: scale(0.7); opacity: 0; }
  to   { transform: scale(1);   opacity: 1; }
}
.modal-emoji { font-size: 3rem; margin-bottom: 10px; }
.modal-title { font-size: 1.9rem; font-weight: 900; color: var(--accent2); margin-bottom: 6px; }
.modal-sub { color: var(--text-muted); font-size: 0.9rem; margin-bottom: 20px; }

.podium { display: flex; flex-direction: column; gap: 6px; margin-bottom: 24px; }
.podium-row {
  display: flex; align-items: center; gap: 10px;
  background: var(--surface2);
  border-radius: var(--radius-sm);
  padding: 8px 12px;
}
.podium-rank { font-size: 1.2rem; width: 28px; text-align: center; }
.podium-name { flex: 1; font-weight: 800; text-align: left; }
.podium-pts {
  font-family: 'Space Mono', monospace;
  font-size: 0.85rem;
  color: var(--accent2);
  font-weight: 700;
}

.btn-play-again {
  background: var(--accent);
  border: none; color: #fff;
  font-family: 'Nunito', sans-serif;
  font-size: 1rem; font-weight: 900;
  padding: 13px 36px;
  border-radius: 50px;
  cursor: pointer;
  transition: all 0.18s;
}
.btn-play-again:hover { background: #ff6b84; transform: translateY(-2px); }

/* ─── Responsive ────────────────────────────────────────────── */
@media (max-width: 768px) {
  .mm-layout { flex-direction: column; align-items: center; }
  .mm-sidebar { width: 100%; max-width: 420px; flex-direction: row; flex-wrap: wrap; }
  .mm-sidebar > * { flex: 1 1 180px; }
}
@media (max-width: 420px) {
  .mm-logo { font-size: 1.8rem; }
  .mm-sidebar > * { flex: 1 1 100%; }
}
</style>

<div id="memory-app">

  {{-- Header --}}
  <div class="mm-header">
    <div class="mm-logo"><span class="w1">Memory</span> <span class="w2">Match</span></div>
    <div class="mm-sub">multiplayer · flip &amp; find pairs · earn points</div>
  </div>

  {{-- Difficulty --}}
  <div class="mm-diff-row">
    @foreach(['easy'=>'Easy','medium'=>'Medium','hard'=>'Hard','veryhard'=>'Very Hard','insane'=>'Insane'] as $key=>$label)
      <button class="diff-btn" data-diff="{{ $key }}" onclick="selectDiff('{{ $key }}')">{{ $label }}</button>
    @endforeach
  </div>

  {{-- Main layout --}}
  <div class="mm-layout">

    {{-- Sidebar --}}
    <div class="mm-sidebar">

      {{-- Stats --}}
      <div class="mm-panel">
        <div class="mm-panel-title">Stats</div>
        <div class="mm-stats">
          <div class="stat-box">
            <div class="sl">Pairs</div>
            <div class="sv" id="st-pairs">0/0</div>
          </div>
          <div class="stat-box">
            <div class="sl">Status</div>
            <div class="sv" id="st-status" style="font-size:0.75rem;padding-top:4px">—</div>
          </div>
        </div>
      </div>

      {{-- Turn + Players --}}
      <div class="mm-panel">
        <div class="mm-panel-title">Players</div>

        <div class="turn-banner" id="turn-banner" style="margin-bottom:10px">
          <div style="font-size:0.7rem;color:var(--text-muted)">Current Turn</div>
          <div class="turn-name" id="turn-name">—</div>
        </div>

        <div class="player-list" id="player-list"></div>

        <div style="margin-top:10px">
          <div class="add-player-form">
            <input class="add-player-input" id="new-player-name" placeholder="Player name…" maxlength="24"
                   onkeydown="if(event.key==='Enter') addPlayer()">
            <button class="btn-add" onclick="addPlayer()">+</button>
          </div>
        </div>
      </div>

      {{-- Controls --}}
      <div class="mm-panel">
        <div class="mm-panel-title">Controls</div>
        <button class="btn-new-game" onclick="newGame()">↺ New Game</button>
      </div>

    </div>

    {{-- Board --}}
    <div class="mm-board-wrap">
      <div>
        <div class="who-label">You are playing as</div>
        <select class="who-select" id="who-select"></select>
      </div>
      <div class="mm-board" id="mm-board"></div>
    </div>

  </div>

</div>

{{-- Win modal --}}
<div class="mm-overlay" id="mm-overlay">
  <div class="mm-modal">
    <div class="modal-emoji">🏆</div>
    <div class="modal-title" id="modal-winner">Game Over!</div>
    <div class="modal-sub" id="modal-sub">Final scores</div>
    <div class="podium" id="modal-podium"></div>
    <button class="btn-play-again" onclick="closeModal()">Play Again</button>
  </div>
</div>

<script>
// ─── State ──────────────────────────────────────────────────────────
const PLAYER_COLORS = ['#e94560','#53d8fb','#f5a623','#83fb53','#a78bfa','#fb5353','#53fbcb','#fb9f53'];
const CARD_SIZES = { easy: 90, medium: 88, hard: 75, veryhard: 62, insane: 52 };

let state = null;
let selectedDiff = '{{ $game->difficulty }}';
let pollTimer = null;
let isMismatchPending = false;
let mismatchTimeout = null;

// ─── Polling ─────────────────────────────────────────────────────────
function startPolling() {
  clearInterval(pollTimer);
  pollTimer = setInterval(fetchState, 1500);
}

function fetchState() {
  fetch('/games/memory/state')
    .then(r => r.json())
    .then(data => {
      const wasMyTurn = state && myPlayerId() && state.current_player_id == myPlayerId();
      state = data;
      renderAll();

      // If mismatch detected and not already pending resolution
      if (data.is_mismatch && !isMismatchPending) {
        isMismatchPending = true;
        clearTimeout(mismatchTimeout);
        mismatchTimeout = setTimeout(() => {
          resolveFlip();
        }, 900);
      }

      if (data.status === 'finished') {
        clearInterval(pollTimer);
        setTimeout(showWinModal, 600);
      }
    })
    .catch(() => {});
}

// ─── My player selection ─────────────────────────────────────────────
function myPlayerId() {
  const sel = document.getElementById('who-select');
  return sel && sel.value ? parseInt(sel.value) : null;
}

// ─── Init ────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  fetchState();
  startPolling();
  markActiveDiff(selectedDiff);
});

// ─── Diff ────────────────────────────────────────────────────────────
function selectDiff(d) {
  selectedDiff = d;
  markActiveDiff(d);
  newGame();
}
function markActiveDiff(d) {
  document.querySelectorAll('.diff-btn').forEach(b => {
    b.className = 'diff-btn';
    if (b.dataset.diff === d) b.classList.add('active-' + d);
  });
}

// ─── New Game ────────────────────────────────────────────────────────
function newGame() {
  isMismatchPending = false;
  clearTimeout(mismatchTimeout);
  fetch('/games/memory/start', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    body: JSON.stringify({ difficulty: selectedDiff })
  }).then(r => r.json()).then(data => {
    state = data;
    markActiveDiff(data.difficulty);
    selectedDiff = data.difficulty;
    renderAll();
    startPolling();
    document.getElementById('mm-overlay').classList.remove('show');
  });
}

// ─── Add Player ──────────────────────────────────────────────────────
function addPlayer() {
  const inp = document.getElementById('new-player-name');
  const name = inp.value.trim();
  if (!name) return;
  fetch('/games/memory/player', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    body: JSON.stringify({ name })
  }).then(r => r.json()).then(data => {
    if (data.error) return alert(data.error);
    inp.value = '';
    state = data;
    renderAll();
  });
}

// ─── Remove Player ───────────────────────────────────────────────────
function removePlayer(id) {
  fetch('/games/memory/player/' + id, {
    method: 'DELETE',
    headers: { 'X-CSRF-TOKEN': csrfToken() }
  }).then(r => r.json()).then(data => {
    state = data;
    renderAll();
  });
}

// ─── Flip Card ───────────────────────────────────────────────────────
function flipCard(cardId) {
  const pid = myPlayerId();
  if (!pid) { alert('Select your player first!'); return; }
  if (!state) return;
  if (state.status === 'finished') return;
  if (isMismatchPending) return;

  // Optimistic: check if it's our turn
  if (state.current_player_id && state.current_player_id != pid) {
    // Still allow — free-for-all; server enforces turn
  }

  fetch('/games/memory/flip', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
    body: JSON.stringify({ card_id: cardId, player_id: pid })
  }).then(r => r.json()).then(data => {
    if (data.error) {
      // Quietly ignore not-your-turn, show others
      if (data.error !== 'Not your turn' && data.error !== 'Wait for mismatch to resolve') {
        console.warn(data.error);
      }
      return;
    }
    state = data;
    renderAll();

    if (data.is_mismatch && !isMismatchPending) {
      isMismatchPending = true;
      clearTimeout(mismatchTimeout);
      mismatchTimeout = setTimeout(() => resolveFlip(), 900);
    }

    if (data.status === 'finished') {
      clearInterval(pollTimer);
      setTimeout(showWinModal, 600);
    }
  });
}

// ─── Resolve mismatch ────────────────────────────────────────────────
function resolveFlip() {
  fetch('/games/memory/resolve', {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': csrfToken() }
  }).then(r => r.json()).then(data => {
      state = data;
      renderAll();
      isMismatchPending = false;
  });
}

// ─── Render ──────────────────────────────────────────────────────────
function renderAll() {
  if (!state) return;
  renderBoard();
  renderPlayers();
  renderStats();
  renderWhoSelect();
  renderTurnBanner();
}

function renderBoard() {
  const board = document.getElementById('mm-board');
  const sz = CARD_SIZES[state.difficulty] || 80;
  board.style.gridTemplateColumns = `repeat(${state.cols}, ${sz}px)`;
  board.style.gridTemplateRows    = `repeat(${state.rows}, ${sz}px)`;

  const myId = myPlayerId();
  const isMyTurn = !state.current_player_id || state.current_player_id == myId;
  const flippedCount = state.flipped_ids ? state.flipped_ids.length : 0;

  // Rebuild only if card count changed (avoid full re-render on every poll)
  if (board.children.length !== state.cards.length) {
    board.innerHTML = '';
    state.cards.forEach(card => {
      const el = document.createElement('div');
      el.className = 'card';
      el.id = 'card-' + card.id;
      el.style.width = sz + 'px';
      el.style.height = sz + 'px';
      el.innerHTML = `<div class="card-inner">
        <div class="face face-back">✦</div>
        <div class="face face-front" id="face-${card.id}"></div>
      </div>`;
      el.addEventListener('click', () => flipCard(card.id));
      board.appendChild(el);
    });
  }

  state.cards.forEach(card => {
    const el = document.getElementById('card-' + card.id);
    if (!el) return;
    const face = document.getElementById('face-' + card.id);

    el.className = 'card';
    if (card.matched) {
      el.classList.add('matched');
      if (face) face.textContent = card.emoji;
    } else if (card.flipped) {
      el.classList.add('flipped');
      if (face) face.textContent = card.emoji;

      // Shake if mismatch
      if (state.is_mismatch && state.flipped_ids && state.flipped_ids.includes(card.id)) {
        el.classList.add('shake');
      }
    } else {
      if (face) face.textContent = '';
      // Lock if: not my turn OR mismatch pending OR 2 cards already flipped
      const locked = (!isMyTurn && state.players.length > 1) || isMismatchPending || (flippedCount >= 2 && !state.is_mismatch);
      if (locked) el.classList.add('locked');
    }
  });
}

function renderPlayers() {
  const list = document.getElementById('player-list');
  if (!state.players || state.players.length === 0) {
    list.innerHTML = '<div style="color:var(--text-muted);font-size:0.8rem;text-align:center;padding:8px 0">No players yet — add one!</div>';
    return;
  }

  // Sort by score desc for display
  const sorted = [...state.players].sort((a, b) => b.score - a.score);
  list.innerHTML = sorted.map((p, i) => {
    const color = PLAYER_COLORS[p.order % PLAYER_COLORS.length];
    const isCurrentTurn = state.current_player_id == p.id;
    const initials = p.name.split(' ').map(w => w[0]).join('').toUpperCase().slice(0, 2);
    return `<div class="player-item ${isCurrentTurn ? 'is-turn' : ''}">
      <div class="player-avatar" style="background:${color}">${initials}</div>
      <div class="player-info">
        <div class="player-name">${escHtml(p.name)}</div>
      </div>
      <div class="player-score-badge">${p.score}pt</div>
      <button class="player-del" onclick="removePlayer(${p.id})" title="Remove player">×</button>
    </div>`;
  }).join('');
}

function renderWhoSelect() {
  const sel = document.getElementById('who-select');
  const prevVal = sel.value;

  if (!state.players || state.players.length === 0) {
    sel.innerHTML = '<option value="">— no players —</option>';
    return;
  }

  sel.innerHTML = state.players
    .map(p => `<option value="${p.id}">${escHtml(p.name)}</option>`)
    .join('');

  // Preserve previous selection if still valid
  if (prevVal && state.players.find(p => p.id == prevVal)) {
    sel.value = prevVal;
  }
}

function renderStats() {
  document.getElementById('st-pairs').textContent =
    `${state.matched_pairs}/${state.pairs}`;
  const statusMap = { waiting: 'Waiting', playing: 'Playing', finished: 'Done!' };
  document.getElementById('st-status').textContent = statusMap[state.status] || state.status;
}

function renderTurnBanner() {
  const name = state.players && state.current_player_id
    ? state.players.find(p => p.id == state.current_player_id)?.name || '—'
    : '—';
  document.getElementById('turn-name').textContent = name;
}

// ─── Win Modal ───────────────────────────────────────────────────────
function showWinModal() {
  if (!state || !state.players) return;
  const sorted = [...state.players].sort((a, b) => b.score - a.score);
  const winner = sorted[0];
  const ranks = ['🥇','🥈','🥉','4️⃣','5️⃣','6️⃣','7️⃣','8️⃣'];

const topScore = winner ? winner.score : 0;
const tied = sorted.filter(p => p.score === topScore);

document.querySelector('.modal-emoji').textContent = tied.length > 1 ? '🤝' : '🏆';
document.getElementById('modal-winner').textContent =
    tied.length > 1
        ? `It's a Tie! 🤝`
        : (winner ? `${winner.name} Wins!` : 'Game Over!');

document.getElementById('modal-sub').textContent =
    tied.length > 1
        ? `${tied.map(p => p.name).join(' & ')} tied with ${topScore} pt${topScore !== 1 ? 's' : ''}`
        : `Final scores — ${state.pairs} pairs`;
  document.getElementById('modal-sub').textContent =
    `Final scores — ${state.pairs} pairs`;

  document.getElementById('modal-podium').innerHTML = sorted.map((p, i) => `
    <div class="podium-row">
      <div class="podium-rank">${ranks[i] || (i+1)+'.'}</div>
      <div class="podium-name">${escHtml(p.name)}</div>
      <div class="podium-pts">${p.score} pt${p.score !== 1 ? 's' : ''}</div>
    </div>
  `).join('');

  document.getElementById('mm-overlay').classList.add('show');
}

function closeModal() {
  document.getElementById('mm-overlay').classList.remove('show');
  newGame();
}

// ─── Utility ─────────────────────────────────────────────────────────
function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content || '';
}
function escHtml(str) {
  return String(str).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
</script>
@endsection

