@extends('layouts.app')

@section('content')
<style>
@import url('https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap');

/* ── Variables ──────────────────────────────────────────── */
:root {
    --jd: #1a3a1a;
    --lg: #6abf5e;
    --lm: #a8e063;
    --sun: #f9d342;
    --amb: #f4a11d;
    --coral: #ff6b6b;
    --sky: #74d7f7;
}

/* ── Page shell ─────────────────────────────────────────── */
.ac-page {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    overflow-y: auto;
    padding: 20px 12px 40px;
    background: radial-gradient(ellipse at 20% 0%, #2d6e1f 0%, #1a3a1a 40%, #0d2010 100%);
    min-height: 0;
    position: relative;
}

/* fireflies */
.ac-fireflies { position:fixed; inset:0; pointer-events:none; z-index:0; }
.ac-firefly {
    position:absolute; width:5px; height:5px; background:var(--lm);
    border-radius:50%; box-shadow:0 0 8px 3px rgba(168,224,99,.7);
    animation:acFly var(--dur,8s) ease-in-out infinite var(--delay,0s); opacity:0;
}
@keyframes acFly {
    0%   { opacity:0; transform:translate(0,0) scale(.5); }
    20%  { opacity:1; }
    50%  { opacity:.6; transform:translate(var(--tx,40px),var(--ty,-60px)) scale(1); }
    80%  { opacity:1; }
    100% { opacity:0; transform:translate(var(--tx2,80px),var(--ty2,20px)) scale(.5); }
}

/* ── Header ─────────────────────────────────────────────── */
.ac-header { text-align:center; margin-bottom:10px; position:relative; z-index:1; }
.ac-title {
    font-family:'Fredoka One',cursive;
    font-size:clamp(1.6rem,5vw,2.5rem);
    color:var(--lm);
    text-shadow:0 3px 0 var(--jd), 0 0 20px rgba(168,224,99,.4);
}
.ac-subtitle { font-size:.7rem; color:rgba(255,255,255,.4); letter-spacing:3px; text-transform:uppercase; margin-top:2px; }

/* live dot */
.ac-live { display:flex; align-items:center; gap:5px; font-size:.75rem; color:rgba(255,255,255,.45); justify-content:center; margin-top:4px; }
.ac-live-dot { width:7px; height:7px; border-radius:50%; background:#27ae60; animation:livePulse 1.5s infinite; }
@keyframes livePulse { 0%,100%{opacity:1;} 50%{opacity:.3;} }

/* ── Rank legend ─────────────────────────────────────────── */
.ac-legend {
    display:flex; gap:4px; flex-wrap:wrap; justify-content:center;
    background:rgba(0,0,0,.3); border:1px solid rgba(255,255,255,.08);
    border-radius:12px; padding:6px 12px; margin-bottom:10px;
    max-width:480px; width:100%; position:relative; z-index:1;
}
.ac-legend-item { display:flex; align-items:center; gap:3px; font-size:.65rem; color:rgba(255,255,255,.7); }
.ac-legend-num  { background:rgba(255,255,255,.1); border-radius:4px; padding:1px 4px; font-weight:700; font-size:.6rem; }

/* ── Turn pill ───────────────────────────────────────────── */
.ac-who-turn {
    border-radius:16px; padding:10px 16px;
    margin-bottom:8px; font-family:'Fredoka One',cursive; font-size:.95rem;
    backdrop-filter:blur(10px); white-space:nowrap; position:relative; z-index:1;
    transition:all .3s cubic-bezier(.34,1.56,.64,1);
}
.ac-blue-turn  { background:rgba(116,214,247,.2); border:1.5px solid rgba(116,215,247,.5); color:var(--sky); box-shadow:0 0 14px rgba(116,214,247,.25); }
.ac-red-turn   { background:rgba(255,107,107,.2); border:1.5px solid rgba(255,107,107,.5); color:var(--coral); box-shadow:0 0 14px rgba(255,107,107,.25); }

/* ── Player banners ─────────────────────────────────────── */
.ac-banner {
    width:100%; max-width:420px;
    display:flex; align-items:center; justify-content:space-between;
    padding:8px 14px; border-radius:14px; margin:3px 0;
    backdrop-filter:blur(10px); transition:all .35s cubic-bezier(.34,1.56,.64,1);
    position:relative; z-index:1;
}
.ac-b1       { background:rgba(116,215,247,.07); border:1.5px solid rgba(116,215,247,.2); }
.ac-b2       { background:rgba(255,107,107,.07); border:1.5px solid rgba(255,107,107,.2); }
.ac-b1.ac-active { background:rgba(116,215,247,.18); border-color:rgba(116,215,247,.6); box-shadow:0 0 14px rgba(116,215,247,.18); }
.ac-b2.ac-active { background:rgba(255,107,107,.18); border-color:rgba(255,107,107,.6); box-shadow:0 0 14px rgba(255,107,107,.18); }
.ac-bname    { font-family:'Fredoka One',cursive; font-size:1rem; display:flex; align-items:center; gap:5px; }
.ac-c1 { color:var(--sky); } .ac-c2 { color:var(--coral); }
.ac-bscore   { font-family:'Fredoka One',cursive; font-size:1.4rem; }
.ac-bcap     { display:flex; flex-wrap:wrap; gap:2px; font-size:.9rem; max-width:130px; justify-content:flex-end; }
.ac-dot      { width:7px; height:7px; border-radius:50%; display:inline-block; animation:dotPulse 1s ease-in-out infinite; }
.ac-dot-1    { background:var(--sky);  box-shadow:0 0 6px var(--sky); }
.ac-dot-2    { background:var(--coral);box-shadow:0 0 6px var(--coral); }
.ac-dot-hide { visibility:hidden; }
@keyframes dotPulse { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.35);opacity:.7;} }

/* ── Board ───────────────────────────────────────────────── */
.ac-board-outer { position:relative; z-index:1; transition:transform .55s cubic-bezier(.34,1.56,.64,1); }

.ac-board {
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:8px; padding:14px;
    background:rgba(0,0,0,.42);
    border:2px solid rgba(255,255,255,.08);
    border-radius:20px;
    box-shadow:0 8px 40px rgba(0,0,0,.5), inset 0 1px 0 rgba(255,255,255,.05);
    backdrop-filter:blur(12px);
}

.ac-tile {
    width:80px; height:80px; border-radius:14px;
    display:flex; align-items:center; justify-content:center;
    cursor:pointer; position:relative; overflow:hidden;
    transition:transform .15s, box-shadow .15s, background .2s;
    user-select:none;
}
.ac-tile::before {
    content:''; position:absolute; inset:0; border-radius:inherit;
    background:linear-gradient(135deg,rgba(255,255,255,.1) 0%,transparent 60%);
    pointer-events:none;
}
.ac-tile:hover:not(.ac-empty) { transform:translateY(-3px) scale(1.04); z-index:10; }

.ac-empty  { background:rgba(0,0,0,.2); border:1px dashed rgba(255,255,255,.07); cursor:default; }
.ac-empty:hover { transform:none; }

.ac-hidden {
    background:linear-gradient(135deg,#3d7a35,#2d5a27);
    border:2px solid rgba(255,255,255,.12);
    box-shadow:0 3px 10px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,255,255,.15);
}
.ac-hidden:hover { box-shadow:0 6px 18px rgba(0,0,0,.4), 0 0 0 2px rgba(168,224,99,.4); }

.ac-qmark {
    font-family:'Fredoka One',cursive; font-size:2.3rem;
    color:rgba(255,255,255,.8); text-shadow:0 2px 4px rgba(0,0,0,.3);
    animation:qPulse 2s ease-in-out infinite;
}
@keyframes qPulse { 0%,100%{transform:scale(1);opacity:.8;} 50%{transform:scale(1.1);opacity:1;} }

.ac-p1 { background:linear-gradient(135deg,#1a4a6e,#0d3a5e); border:2px solid rgba(116,215,247,.45); box-shadow:0 3px 12px rgba(0,0,0,.3), inset 0 1px 0 rgba(116,215,247,.2); }
.ac-p2 { background:linear-gradient(135deg,#6e1a1a,#5e0d0d); border:2px solid rgba(255,107,107,.45); box-shadow:0 3px 12px rgba(0,0,0,.3), inset 0 1px 0 rgba(255,107,107,.2); }

.ac-selected      { box-shadow:0 0 0 3px var(--sun), 0 6px 20px rgba(249,211,66,.4) !important; transform:translateY(-3px) scale(1.06) !important; z-index:20; }
.ac-valid-move    { box-shadow:0 0 0 2px rgba(168,224,99,.7), 0 4px 12px rgba(168,224,99,.2); }
.ac-valid-move::after    { content:''; position:absolute; inset:0; border-radius:inherit; background:rgba(168,224,99,.15); animation:vPulse 1s ease-in-out infinite; }
.ac-valid-capture { box-shadow:0 0 0 2px rgba(255,107,107,.8), 0 4px 16px rgba(255,107,107,.3); }
.ac-valid-capture::after { content:''; position:absolute; inset:0; border-radius:inherit; background:rgba(255,107,107,.15); animation:vPulse .8s ease-in-out infinite; }
@keyframes vPulse { 0%,100%{opacity:.5;} 50%{opacity:1;} }

.ac-tile-inner { display:flex; flex-direction:column; align-items:center; justify-content:center; gap:1px; position:relative; z-index:1; width:100%; height:100%; }
.ac-emoji  { font-size:2.1rem; line-height:1; filter:drop-shadow(0 2px 4px rgba(0,0,0,.4)); transition:transform .2s; }
.ac-tile:hover .ac-emoji { transform:scale(1.1); }
.ac-pname  { font-size:.42rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:rgba(255,255,255,.7); }
.ac-rank-badge { position:absolute; top:5px; right:6px; font-size:.58rem; font-weight:800; color:rgba(255,255,255,.85); background:rgba(0,0,0,.4); border-radius:4px; padding:1px 4px; font-family:'Fredoka One',cursive; }

/* ── Status / controls ───────────────────────────────────── */
.ac-status { text-align:center; font-size:.8rem; color:rgba(255,255,255,.5); padding:5px; min-height:22px; position:relative; z-index:1; }
.ac-status span { color:var(--lm); font-weight:700; }
.ac-controls { display:flex; gap:10px; align-items:center; justify-content:center; flex-wrap:wrap; margin-top:2px; position:relative; z-index:1; }
.ac-btn { font-family:'Fredoka One',cursive; border:none; border-radius:11px; padding:10px 18px; cursor:pointer; transition:all .2s; font-size:.85rem; color:var(--jd); background:linear-gradient(135deg,var(--lm),var(--lg)); box-shadow:0 3px 12px rgba(106,191,94,.4); }
.ac-btn:hover { transform:translateY(-2px); box-shadow:0 5px 18px rgba(106,191,94,.5); }
.ac-moves { font-family:'Fredoka One',cursive; font-size:.72rem; color:rgba(255,255,255,.35); letter-spacing:1px; }
.ac-moves b { color:var(--sun); font-size:.95rem; }

/* ── Animations ─────────────────────────────────────────── */
@keyframes acAttack { 0%{transform:scale(1);} 30%{transform:scale(1.25) rotate(-5deg);} 60%{transform:scale(.9) rotate(3deg);} 100%{transform:scale(1);} }
.ac-attacking { animation:acAttack .4s ease; }
@keyframes acShake  { 0%,100%{transform:translateX(0);} 20%{transform:translateX(-6px);} 40%{transform:translateX(6px);} 60%{transform:translateX(-4px);} 80%{transform:translateX(4px);} }
.ac-shaking   { animation:acShake .35s ease; }
@keyframes acFlip   { 0%{transform:rotateY(90deg) scale(.8);opacity:0;} 60%{transform:rotateY(-10deg) scale(1.05);opacity:1;} 100%{transform:rotateY(0) scale(1);opacity:1;} }
.ac-flip      { animation:acFlip .5s cubic-bezier(.34,1.56,.64,1); }

/* particles */
.ac-particles { position:fixed; inset:0; pointer-events:none; z-index:200; overflow:hidden; }
.ac-particle  { position:absolute; border-radius:50%; animation:pFly var(--pd,.8s) ease-out forwards; }
@keyframes pFly { 0%{transform:translate(0,0) scale(1);opacity:1;} 100%{transform:translate(var(--px,0),var(--py,-60px)) scale(0);opacity:0;} }

/* ── Game-over overlay ───────────────────────────────────── */
.ac-overlay { position:fixed; inset:0; background:rgba(0,0,0,.75); backdrop-filter:blur(8px); display:flex; align-items:center; justify-content:center; z-index:300; opacity:0; pointer-events:none; transition:opacity .4s; }
.ac-overlay.show { opacity:1; pointer-events:all; }
.ac-popup {
    background:linear-gradient(135deg,#1e4a1a,#2d5a27);
    border:2px solid rgba(168,224,99,.3); border-radius:24px;
    padding:34px 38px; text-align:center; max-width:310px; width:90%;
    box-shadow:0 20px 60px rgba(0,0,0,.6);
    transform:scale(.8) translateY(20px);
    transition:transform .4s cubic-bezier(.34,1.56,.64,1);
}
.ac-overlay.show .ac-popup { transform:scale(1) translateY(0); }
.ac-popup-trophy { font-size:3.8rem; margin-bottom:10px; animation:trophySpin .6s ease .3s both; }
@keyframes trophySpin { from{transform:scale(0) rotate(-20deg);} to{transform:scale(1) rotate(0);} }
.ac-popup-title  { font-family:'Fredoka One',cursive; font-size:1.9rem; text-shadow:0 2px 8px rgba(0,0,0,.3); margin-bottom:5px; }
.ac-popup-sub    { color:rgba(255,255,255,.6); font-size:.85rem; margin-bottom:18px; }
.ac-popup-scores { display:flex; justify-content:center; gap:22px; margin-bottom:22px; }
.ac-ps-num  { font-family:'Fredoka One',cursive; font-size:1.9rem; }
.ac-ps-lbl  { font-size:.62rem; text-transform:uppercase; color:rgba(255,255,255,.4); }
.ac-btn-play-again { font-family:'Fredoka One',cursive; font-size:1.05rem; color:var(--jd); background:linear-gradient(135deg,var(--sun),var(--amb)); border:none; border-radius:12px; padding:12px 30px; cursor:pointer; box-shadow:0 4px 16px rgba(249,211,66,.4); transition:all .2s; }
.ac-btn-play-again:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(249,211,66,.5); }

/* ── Responsive ─────────────────────────────────────────── */
@media (max-width:560px) {
    .ac-tile { width:64px; height:64px; border-radius:10px; }
    .ac-emoji { font-size:1.65rem; }
    .ac-board { gap:6px; padding:10px; }
}
@media (max-width:380px) {
    .ac-tile { width:54px; height:54px; }
    .ac-emoji { font-size:1.4rem; }
}
</style>

{{-- Particles + fireflies --}}
<div class="ac-particles" id="acParticles"></div>
<div class="ac-fireflies" id="acFireflies"></div>

{{-- Game-over overlay --}}
<div class="ac-overlay" id="acOverlay">
    <div class="ac-popup">
        <div class="ac-popup-trophy" id="acTrophy">🏆</div>
        <div class="ac-popup-title"  id="acTitle">Biru Menang!</div>
        <div class="ac-popup-sub"   id="acSub">Semua hewan musuh telah dikalahkan!</div>
        <div class="ac-popup-scores">
            <div><div class="ac-ps-num" style="color:var(--sky)"   id="acS1">0</div><div class="ac-ps-lbl">🔵 Biru</div></div>
            <div><div class="ac-ps-num" style="color:var(--coral)" id="acS2">0</div><div class="ac-ps-lbl">🔴 Merah</div></div>
        </div>
        <button class="ac-btn-play-again" id="acBtnPlayAgain">🔄 Main Lagi!</button>
    </div>
</div>

{{-- Main page content --}}
<div class="ac-page" id="acPage">

    <div class="ac-header">
        <div class="ac-title">🌿 Animal Chess</div>
        <div class="ac-subtitle">Free for All · Shared Board</div>
        <div class="ac-live"><span class="ac-live-dot"></span><span id="acOnline">Syncing…</span></div>
    </div>

    <div class="ac-legend" id="acLegend"></div>

    <div class="ac-who-turn ac-blue-turn" id="acWhoTurn">🔵 Giliran Biru</div>

    <div style="width:100%;max-width:420px;position:relative;z-index:1;">
        {{-- P2 banner --}}
        <div class="ac-banner ac-b2" id="acBannerP2">
            <div class="ac-bname ac-c2"><span class="ac-dot ac-dot-2 ac-dot-hide" id="acDot2"></span>🔴 Merah</div>
            <div class="ac-bcap" id="acCap2"></div>
            <div class="ac-bscore ac-c2" id="acScore2">0</div>
        </div>

        <div class="ac-board-outer" id="acBoardOuter">
            <div class="ac-board" id="acBoard"></div>
        </div>

        {{-- P1 banner --}}
        <div class="ac-banner ac-b1 ac-active" id="acBannerP1">
            <div class="ac-bname ac-c1"><span class="ac-dot ac-dot-1" id="acDot1"></span>🔵 Biru</div>
            <div class="ac-bcap" id="acCap1"></div>
            <div class="ac-bscore ac-c1" id="acScore1">0</div>
        </div>
    </div>

    <div class="ac-status" id="acStatus">Klik <span>?</span> untuk buka kartu!</div>

    <div class="ac-controls">
        <button class="ac-btn" id="acBtnReset">🔄 Restart</button>
        <div class="ac-moves">Langkah: <b id="acMoveCount">0</b>/50</div>
    </div>
</div>

<script>
(function () {
    /* ── Constants ─────────────────────────────────────────────── */
    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const POLL_MS = 1000;
    const ANIMALS = [
        { id:'elephant',name:'Gajah',   emoji:'🐘',rank:8 },
        { id:'lion',    name:'Singa',   emoji:'🦁',rank:7 },
        { id:'tiger',   name:'Harimau', emoji:'🐯',rank:6 },
        { id:'panther', name:'Panther', emoji:'🐆',rank:5 },
        { id:'wolf',    name:'Serigala',emoji:'🐺',rank:4 },
        { id:'fox',     name:'Rubah',   emoji:'🦊',rank:3 },
        { id:'cat',     name:'Kucing',  emoji:'🐱',rank:2 },
        { id:'mouse',   name:'Tikus',   emoji:'🐭',rank:1 },
    ];

    /* ── State ─────────────────────────────────────────────────── */
    let state        = null;   // last server state
    let selected     = null;   // {r,c}
    let validMoves   = [];     // [{r,c}]
    let isSending    = false;
    let lastUpdated  = 0;
    let gameOverShown= false;

    /* ── DOM refs ──────────────────────────────────────────────── */
    const $board    = document.getElementById('acBoard');
    const $online   = document.getElementById('acOnline');
    const $whoTurn  = document.getElementById('acWhoTurn');
    const $score1   = document.getElementById('acScore1');
    const $score2   = document.getElementById('acScore2');
    const $cap1     = document.getElementById('acCap1');
    const $cap2     = document.getElementById('acCap2');
    const $dot1     = document.getElementById('acDot1');
    const $dot2     = document.getElementById('acDot2');
    const $banner1  = document.getElementById('acBannerP1');
    const $banner2  = document.getElementById('acBannerP2');
    const $moveCount= document.getElementById('acMoveCount');
    const $status   = document.getElementById('acStatus');
    const $overlay  = document.getElementById('acOverlay');
    const $trophy   = document.getElementById('acTrophy');
    const $title    = document.getElementById('acTitle');
    const $sub      = document.getElementById('acSub');
    const $s1       = document.getElementById('acS1');
    const $s2       = document.getElementById('acS2');

    /* ── Legend ─────────────────────────────────────────────────── */
    document.getElementById('acLegend').innerHTML =
        [...ANIMALS].reverse().map(a =>
            `<div class="ac-legend-item"><span>${a.emoji}</span><span class="ac-legend-num">${a.rank}</span></div>`
        ).join('');

    /* ── Fireflies ───────────────────────────────────────────────── */
    (function spawnFireflies() {
        const c = document.getElementById('acFireflies');
        for (let i = 0; i < 16; i++) {
            const f = document.createElement('div'); f.className = 'ac-firefly';
            f.style.cssText = `left:${Math.random()*100}%;top:${Math.random()*100}%;--dur:${6+Math.random()*8}s;--delay:${Math.random()*8}s;--tx:${(Math.random()-.5)*120}px;--ty:${-30-Math.random()*80}px;--tx2:${(Math.random()-.5)*80}px;--ty2:${(Math.random()-.5)*50}px;`;
            c.appendChild(f);
        }
    })();

    /* ── Render ─────────────────────────────────────────────────── */
    function render(s) {
        if (!s) return;
        $board.innerHTML = '';

        for (let r = 0; r < 4; r++) {
            for (let c = 0; c < 4; c++) {
                $board.appendChild(makeTile(r, c, s));
            }
        }

        const p  = s.current_player;
        const sc = s.scores;

        $score1.textContent = sc[1];
        $score2.textContent = sc[2];
        $cap1.innerHTML = (s.captured[1] || []).map(e => `<span title="${e.name}">${e.emoji}</span>`).join('');
        $cap2.innerHTML = (s.captured[2] || []).map(e => `<span title="${e.name}">${e.emoji}</span>`).join('');
        $moveCount.textContent = s.move_count;

        if (p === 1) {
            $whoTurn.className   = 'ac-who-turn ac-blue-turn';
            $whoTurn.textContent = '🔵 Giliran Biru';
            $banner1.classList.add('ac-active');    $banner2.classList.remove('ac-active');
            $dot1.classList.remove('ac-dot-hide'); $dot2.classList.add('ac-dot-hide');
        } else {
            $whoTurn.className   = 'ac-who-turn ac-red-turn';
            $whoTurn.textContent = '🔴 Giliran Merah';
            $banner2.classList.add('ac-active');    $banner1.classList.remove('ac-active');
            $dot2.classList.remove('ac-dot-hide'); $dot1.classList.add('ac-dot-hide');
        }

        if (s.game_over && !gameOverShown) showGameOver(s);
    }

    function makeTile(r, c, s) {
        const cell      = s.board[r][c];
        const isSel     = selected && selected.r === r && selected.c === c;
        const vm        = validMoves.find(m => m.r === r && m.c === c);
        const isCap     = vm && cell && cell.owner !== s.current_player;

        const div = document.createElement('div');
        div.className   = 'ac-tile';
        div.dataset.r   = r;
        div.dataset.c   = c;

        const inner = document.createElement('div');
        inner.className = 'ac-tile-inner';

        if (!cell) {
            div.classList.add('ac-empty');
            if (vm) div.classList.add('ac-valid-move');
        } else if (!cell.revealed) {
            div.classList.add('ac-hidden');
            const q = document.createElement('span'); q.className = 'ac-qmark'; q.textContent = '?';
            inner.appendChild(q);
        } else {
            div.classList.add(cell.owner === 1 ? 'ac-p1' : 'ac-p2');
            inner.innerHTML = `<span class="ac-emoji">${cell.emoji}</span><span class="ac-pname">${cell.name}</span>`;
            const badge = document.createElement('div');
            badge.className   = 'ac-rank-badge';
            badge.textContent = cell.rank;
            div.appendChild(badge);
        }

        div.appendChild(inner);

        if (isSel)  div.classList.add('ac-selected');
        if (vm && !isCap) div.classList.add('ac-valid-move');
        if (isCap)  div.classList.add('ac-valid-capture');

        div.addEventListener('click', () => onTileClick(r, c));
        return div;
    }

    /* ── Tile click ─────────────────────────────────────────────── */
    function onTileClick(r, c) {
        if (!state || state.game_over || isSending) return;
        const cell = state.board[r][c];

        // Reveal hidden piece (any player can flip any hidden piece, it's their "move")
        if (cell && !cell.revealed) {
            sendMove({ action: 'reveal', from_r: r, from_c: c });
            selected = null; validMoves = [];
            return;
        }

        // Select / deselect own piece
        if (selected) {
            const vm = validMoves.find(m => m.r === r && m.c === c);
            if (vm) {
                sendMove({ action: 'move', from_r: selected.r, from_c: selected.c, to_r: r, to_c: c });
                selected = null; validMoves = [];
                return;
            }
            // Click same piece → deselect
            if (cell && cell.owner === state.current_player && cell.revealed && selected.r === r && selected.c === c) {
                selected = null; validMoves = []; render(state); return;
            }
            // Click another own piece → switch selection
            if (cell && cell.owner === state.current_player && cell.revealed) {
                selected = { r, c }; validMoves = getValidMoves(r, c); playSound('select'); render(state);
                setStatus(`Pilih tujuan untuk <span>${cell.name}</span>`); return;
            }
            selected = null; validMoves = []; render(state); return;
        }

        if (cell && cell.revealed && cell.owner === state.current_player) {
            selected = { r, c }; validMoves = getValidMoves(r, c); playSound('select'); render(state);
            setStatus(`Pilih tujuan untuk <span>${cell.name}</span>`);
        }
    }

    function getValidMoves(r, c) {
        const piece = state.board[r][c];
        if (!piece || !piece.revealed) return [];
        const moves = [];
        for (const [dr, dc] of [[-1,0],[1,0],[0,-1],[0,1]]) {
            const nr = r+dr, nc = c+dc;
            if (nr < 0 || nr >= 4 || nc < 0 || nc >= 4) continue;
            const t = state.board[nr][nc];
            if (!t) { moves.push({ r: nr, c: nc }); continue; }
            if (t.owner !== piece.owner) {
                // can always try to attack (server decides outcome incl. unrevealed)
                moves.push({ r: nr, c: nc });
            }
        }
        return moves;
    }

    /* ── Network ────────────────────────────────────────────────── */
    function sendMove(payload) {
        if (isSending) return;
        isSending = true;
        playSound(payload.action === 'reveal' ? 'reveal' : 'move');

        fetch('/games/animal-chess/move', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
            body:    JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            if (data.ok) {
                applyState(data.state, true);
            } else {
                setStatus(`<span style="color:var(--coral)">${data.error || 'Error'}</span>`);
            }
        })
        .catch(() => setStatus('<span>Gagal mengirim langkah…</span>'))
        .finally(() => { isSending = false; });
    }

    function poll() {
        if (isSending) return;
        fetch('/games/animal-chess/state')
            .then(r => r.json())
            .then(data => {
                $online.textContent = 'Live · Shared Board';
                if (data.updated_at !== lastUpdated) {
                    applyState(data, false);
                }
            })
            .catch(() => { $online.textContent = 'Reconnecting…'; });
    }

    function applyState(s, isMine) {
        const prev = state;
        state       = s;
        lastUpdated = s.updated_at;

        // Determine what changed for sound/particles
        if (isMine && prev) {
            const prevTotal = countPieces(prev);
            const newTotal  = countPieces(s);
            if (newTotal < prevTotal) playSound('capture');
        }

        selected   = null;
        validMoves = [];
        render(s);
        setStatus('Klik <span>?</span> untuk buka kartu, atau pilih hewan!');
    }

    function countPieces(s) {
        let n = 0;
        s.board.forEach(row => row.forEach(cell => { if (cell) n++; }));
        return n;
    }

    /* ── Game over ───────────────────────────────────────────────── */
    function showGameOver(s) {
        gameOverShown = true;
        $s1.textContent = s.scores[1];
        $s2.textContent = s.scores[2];

        const w = s.winner;
        if (w === 'draw') {
            $trophy.textContent = '🤝';
            $title.textContent  = 'Permainan Seri!';
            $title.style.color  = 'var(--sun)';
            $sub.textContent    = s.over_reason === 'max_moves'
                ? 'Batas 50 langkah, skor sama!'
                : 'Kedua tim memiliki poin yang sama!';
        } else {
            const isP1 = w === '1';
            $trophy.textContent = '🏆';
            $title.textContent  = isP1 ? '🔵 Biru Menang!' : '🔴 Merah Menang!';
            $title.style.color  = isP1 ? 'var(--sky)' : 'var(--coral)';
            $sub.textContent    = s.over_reason === 'max_moves'
                ? 'Menang berdasarkan poin (batas 50 langkah)!'
                : 'Semua hewan musuh telah dikalahkan!';
        }

        $overlay.classList.add('show');
        playSound('gameover');
        spawnParticles(null, null, 'win', true);
    }

    /* ── Reset ───────────────────────────────────────────────────── */
    function resetGame() {
        if (!confirm('Reset papan untuk semua pemain?')) return;
        fetch('/games/animal-chess/reset', {
            method:  'POST',
            headers: { 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            gameOverShown = false;
            $overlay.classList.remove('show');
            applyState(data.state, false);
            setStatus('Klik <span>?</span> untuk buka kartu!');
        });
    }

    document.getElementById('acBtnReset').addEventListener('click', resetGame);
    document.getElementById('acBtnPlayAgain').addEventListener('click', resetGame);

    /* ── Helpers ─────────────────────────────────────────────────── */
    function setStatus(h) { $status.innerHTML = h; }

    function getTileEl(r, c) { return $board.querySelector(`.ac-tile[data-r="${r}"][data-c="${c}"]`); }
    function animTile(r, c, cls) {
        const t = getTileEl(r, c); if (!t) return;
        t.classList.add(cls); setTimeout(() => t.classList.remove(cls), 400);
    }

    function spawnParticles(r, c, type, big = false) {
        const cont = document.getElementById('acParticles');
        let x, y;
        if (r !== null) {
            const t = getTileEl(r, c);
            if (t) { const rc = t.getBoundingClientRect(); x = rc.left + rc.width/2; y = rc.top + rc.height/2; }
            else { x = innerWidth/2; y = innerHeight/2; }
        } else { x = innerWidth/2; y = innerHeight/2; }

        const cols = type === 'win'
            ? ['#f9d342','#a8e063','#74d7f7','#ff6b6b']
            : ['#ff6b6b','#ff9999','#ffcccc'];

        for (let i = 0; i < (big ? 32 : 14); i++) {
            const p   = document.createElement('div'); p.className = 'ac-particle';
            const sz  = 4 + Math.random()*6;
            const ang = Math.random() * Math.PI * 2;
            const d   = 40 + Math.random() * (big ? 130 : 65);
            p.style.cssText = `left:${x}px;top:${y}px;width:${sz}px;height:${sz}px;background:${cols[Math.floor(Math.random()*cols.length)]};--px:${Math.cos(ang)*d}px;--py:${Math.sin(ang)*d}px;--pd:${.5+Math.random()*.7}s;box-shadow:0 0 4px currentColor;`;
            cont.appendChild(p);
            setTimeout(() => p.remove(), 1400);
        }
    }

    /* ── Audio ───────────────────────────────────────────────────── */
    let actx;
    function getCtx() { if (!actx) actx = new (window.AudioContext || window.webkitAudioContext)(); return actx; }
    function playTone(f, t, d, v = 0.15) {
        try {
            const c = getCtx(), o = c.createOscillator(), g = c.createGain();
            o.connect(g); g.connect(c.destination);
            o.type = t; o.frequency.value = f;
            g.gain.setValueAtTime(v, c.currentTime);
            g.gain.exponentialRampToValueAtTime(0.001, c.currentTime + d);
            o.start(); o.stop(c.currentTime + d);
        } catch(e) {}
    }
    function playSound(n) {
        switch(n) {
            case 'reveal':  playTone(520,'sine',.2,.12); setTimeout(()=>playTone(780,'sine',.15,.1),80); break;
            case 'select':  playTone(440,'sine',.12,.08); break;
            case 'move':    playTone(380,'triangle',.15,.1); break;
            case 'capture': playTone(660,'square',.1,.08); setTimeout(()=>playTone(220,'sawtooth',.25,.15),60); break;
            case 'lose':    playTone(220,'sawtooth',.3,.12); setTimeout(()=>playTone(150,'sawtooth',.3,.15),100); break;
            case 'gameover':[500,400,300,200].forEach((f,i)=>setTimeout(()=>playTone(f,'sine',.4,.15),i*120)); break;
        }
    }

    /* ── Start ───────────────────────────────────────────────────── */
    poll();
    setInterval(poll, POLL_MS);
})();
</script>
@endsection