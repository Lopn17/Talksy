@extends('layouts.app')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap');

    /* ── Page shell ── */
    .catch-page {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: #0d0d0f;
        overflow-y: auto;
        min-height: 0;
        font-family: 'Space Grotesk', sans-serif;
        padding: 12px 10px 20px;
        gap: 10px;
    }

    /* ── Game + sidebar wrapper ── */
    .game-layout {
        display: flex;
        flex-direction: column;
        gap: 10px;
        width: 100%;
        max-width: 540px;
        align-items: stretch;
    }

    @media (min-width: 960px) {
        .catch-page {
            align-items: center;
            justify-content: center;
            flex-direction: row;
            padding: 20px 24px;
            align-items: flex-start;
        }
        .game-layout {
            flex-direction: row;
            max-width: none;
            align-items: flex-start;
            gap: 16px;
        }
        #game-wrap {
            flex-shrink: 0;
            width: 480px !important;
            max-width: 480px !important;
        }
        .info-bar {
            width: 220px;
            min-width: 200px;
            max-width: 220px;
            flex-shrink: 0;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        .info-card.full {
            grid-column: unset;
        }
        #btn-reset-catch {
            grid-column: unset;
            width: 100%;
        }
    }

    /* ── Game canvas ── */
    #game-wrap {
        position: relative;
        width: 100%;
        max-width: 540px;
        overflow: hidden;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,0.08);
        touch-action: none;
        user-select: none;
        background: #87CEEB;
        transition: background 1.2s ease;
        flex-shrink: 0;
    }
    #game-wrap.night { background: #0d1b2a; }

    /* HUD */
    #hud {
        position: absolute;
        top: 8px; left: 0; right: 0;
        display: flex;
        justify-content: space-between;
        padding: 0 10px;
        z-index: 10;
        pointer-events: none;
    }
    .hud-pill {
        background: rgba(0,0,0,0.38);
        backdrop-filter: blur(6px);
        border-radius: 20px;
        padding: 3px 10px;
        font-size: 11px; font-weight: 600;
        color: #fff;
        font-family: 'Space Mono', monospace;
    }
    .hud-pill span { color: #ffd166; }

    /* Clouds */
    .cloud {
        position: absolute; background: #fff;
        border-radius: 50px; opacity: 0.8;
        transition: opacity 1.2s ease; pointer-events: none;
    }
    .cloud::before,.cloud::after {
        content:''; position:absolute; background:#fff; border-radius:50%;
    }
    .cloud::before{width:55%;height:150%;top:-40%;left:15%;}
    .cloud::after{width:40%;height:120%;top:-25%;right:15%;}
    #game-wrap.night .cloud{opacity:0.05;}
    .cx1{width:90px;height:28px;top:30px;animation:cmove 18s linear infinite;}
    .cx2{width:70px;height:22px;top:60px;animation:cmove 26s linear infinite 5s;}
    .cx3{width:110px;height:32px;top:14px;animation:cmove 22s linear infinite 11s;}
    @keyframes cmove{from{left:-150px}to{left:110%}}

    /* Stars */
    #stars{position:absolute;inset:0;pointer-events:none;opacity:0;transition:opacity 1.2s;}
    #game-wrap.night #stars{opacity:1;}
    .star{position:absolute;width:2px;height:2px;background:#fff;border-radius:50%;}

    /* Balls */
    .game-ball{
        position:absolute;border-radius:50%;z-index:5;
        box-shadow:0 2px 8px rgba(0,0,0,0.3);
        will-change:transform;pointer-events:none;
    }

    /* My basket only */
    .game-basket{
        position:absolute;border-radius:10px 10px 5px 5px;z-index:6;
        will-change:transform;
    }
    .game-basket::after{
        content:'';position:absolute;top:5px;left:7px;right:7px;height:4px;
        background:rgba(255,255,255,0.22);border-radius:3px;
    }

    /* Badges */
    .game-badge{
        position:absolute;left:50%;transform:translateX(-50%);
        border-radius:12px;font-size:10px;font-weight:600;
        padding:3px 10px;z-index:10;opacity:0;transition:opacity 0.4s;
        white-space:nowrap;font-family:'Space Mono',monospace;pointer-events:none;
    }
    #speed-badge{top:36px;background:rgba(255,200,50,0.92);color:#4a3200;}
    #night-badge{top:58px;background:rgba(80,120,255,0.35);color:#c8d8ff;}

    /* ── Overlay ── */
    #overlay{
        position:absolute;inset:0;
        display:flex;flex-direction:column;
        align-items:center;justify-content:center;
        background:rgba(0,0,0,0.75);
        backdrop-filter:blur(5px);
        border-radius:14px;z-index:20;color:#fff;
        gap:6px;padding:20px 18px;
        overflow-y:auto;
    }
    #overlay h2{font-size:20px;font-weight:700;letter-spacing:-0.02em;text-align:center;}
    #overlay p{font-size:12px;color:rgba(255,255,255,0.6);text-align:center;line-height:1.5;}
    #overlay small{font-size:10px;color:rgba(255,255,255,0.32);text-align:center;}
    .result-score{font-size:28px;font-weight:700;color:#ffd166;font-family:'Space Mono',monospace;}

    .spawn-control{
        display:flex;align-items:center;gap:8px;
        background:rgba(255,255,255,0.06);
        border:1px solid rgba(255,255,255,0.12);
        border-radius:10px;padding:8px 12px;margin-top:2px;
        width:100%;max-width:280px;
    }
    .spawn-control label{font-size:11px;color:rgba(255,255,255,0.55);white-space:nowrap;flex:1;}
    .spawn-control input{
        width:54px;background:rgba(255,255,255,0.1);
        border:1px solid rgba(255,255,255,0.18);border-radius:6px;
        color:#fff;font-family:'Space Mono',monospace;
        font-size:13px;font-weight:700;text-align:center;padding:3px 4px;outline:none;
        -webkit-appearance:none;
    }
    .spawn-control input:focus{border-color:#ffd166;}
    .spawn-hint{font-size:9px;color:rgba(255,255,255,0.25);}

    #start-btn{
        margin-top:6px;padding:11px 0;width:100%;max-width:280px;
        border-radius:24px;border:none;
        background:#ffd166;color:#1a1200;
        font-family:'Space Grotesk',sans-serif;
        font-size:14px;font-weight:700;cursor:pointer;
        transition:background 0.15s,transform 0.1s;
        -webkit-tap-highlight-color:transparent;
    }
    #start-btn:hover{background:#ffe599;}
    #start-btn:active{transform:scale(0.97);}

    /* ── Bottom / Side info bar ── */
    .info-bar{
        width:100%;max-width:540px;
        display:grid;
        grid-template-columns:1fr 1fr;
        gap:8px;
    }

    .info-card{
        background:#18181c;
        border:1px solid rgba(255,255,255,0.07);
        border-radius:12px;
        padding:10px 12px;
    }
    .info-card.full{grid-column:1/-1;}

    .info-title{
        font-size:9px;font-weight:700;letter-spacing:0.1em;
        text-transform:uppercase;color:rgba(255,255,255,0.3);
        margin-bottom:6px;
    }

    /* ── Players online list ── */
    .players-list{
        display:flex;flex-direction:column;gap:5px;
    }
    .player-row{
        display:flex;align-items:center;gap:8px;
        background:rgba(255,255,255,0.04);
        border-radius:8px;padding:6px 8px;
    }
    .player-dot{width:8px;height:8px;border-radius:50%;flex-shrink:0;}
    .player-row-name{
        font-size:12px;font-weight:500;color:#ddd;flex:1;
        white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    }
    .player-you-tag{
        font-size:8px;font-weight:700;
        color:rgba(255,255,255,0.35);
        background:rgba(255,255,255,0.08);
        border-radius:4px;padding:1px 5px;
    }
    .player-status{
        font-size:10px;font-weight:600;
        font-family:'Space Mono',monospace;
        color:rgba(255,255,255,0.35);
        white-space:nowrap;
    }
    .player-status.playing{color:#2ecc71;}
    .player-status.game-over{color:#e74c3c;}

    /* Stats grid */
    .stats-grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:4px;}
    .stat-item{display:flex;flex-direction:column;gap:1px;}
    .stat-lbl{font-size:9px;color:rgba(255,255,255,0.35);}
    .stat-val{font-size:16px;font-weight:700;color:#fff;font-family:'Space Mono',monospace;line-height:1.1;}
    .stat-val.danger{color:#ff6b6b;}

    .live-row{display:flex;align-items:center;gap:5px;font-size:10px;color:rgba(255,255,255,0.3);margin-top:6px;}
    .live-dot{width:5px;height:5px;border-radius:50%;background:#27ae60;animation:lpulse 1.5s infinite;flex-shrink:0;}
    @keyframes lpulse{0%,100%{opacity:1}50%{opacity:0.3}}

    #btn-reset-catch{
        grid-column:1/-1;
        padding:9px;
        background:rgba(192,57,43,0.13);border:1px solid rgba(192,57,43,0.28);
        border-radius:8px;color:#ff6b6b;
        font-family:'Space Grotesk',sans-serif;font-size:12px;font-weight:600;
        cursor:pointer;transition:background 0.15s;
        -webkit-tap-highlight-color:transparent;
    }
    #btn-reset-catch:hover,#btn-reset-catch:active{background:rgba(192,57,43,0.26);}

    /* Touch hint */
    #touch-hint{
        position:absolute;bottom:70px;left:0;right:0;
        text-align:center;font-size:10px;
        color:rgba(255,255,255,0.3);pointer-events:none;z-index:4;
    }

    /* ── Leaderboard ── */
    .lb-row {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 5px 8px;
        border-radius: 8px;
        background: rgba(255,255,255,0.03);
        margin-bottom: 4px;
    }
    .lb-rank {
        font-size: 10px;
        font-weight: 700;
        font-family: 'Space Mono', monospace;
        color: rgba(255,255,255,0.25);
        width: 18px;
        text-align: right;
        flex-shrink: 0;
    }
    .lb-rank.gold   { color: #ffd166; }
    .lb-rank.silver { color: #b0bec5; }
    .lb-rank.bronze { color: #cd7f32; }
    .lb-dot {
        width: 8px; height: 8px;
        border-radius: 50%; flex-shrink: 0;
    }
    .lb-name {
        font-size: 12px; font-weight: 500;
        color: #ddd; flex: 1;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .lb-name.lb-me { color: #ffd166; }
    .lb-score {
        font-size: 12px; font-weight: 700;
        font-family: 'Space Mono', monospace;
        color: #fff;
        flex-shrink: 0;
    }
    .lb-you-tag {
        font-size: 8px; font-weight: 700;
        color: rgba(255,255,255,0.35);
        background: rgba(255,255,255,0.08);
        border-radius: 4px; padding: 1px 5px;
    }

</style>

<div class="catch-page">
    <div class="game-layout">

        <div id="game-wrap">
            <div id="stars"></div>
            <div class="cloud cx1"></div>
            <div class="cloud cx2"></div>
            <div class="cloud cx3"></div>

            <div id="hud">
                <div class="hud-pill">Score: <span id="hud-score">0</span></div>
                <div class="hud-pill">Misses: <span id="hud-misses">0</span>/5</div>
            </div>

            <div class="game-badge" id="speed-badge">⚡ Speed up!</div>
            <div class="game-badge" id="night-badge">🌙 Night mode</div>
            <div id="touch-hint">Drag or slide to move your basket</div>

            <div id="overlay">
                <h2>🎯 Catch the Ball</h2>
                <p>Move your basket to catch falling balls!<br>Others can watch you play live.</p>
                <small>5 misses = game over</small>

                <div class="spawn-control">
                    <label>Interval (frames):</label>
                    <input type="number" id="spawn-input" value="40" min="10" max="200" step="5" inputmode="numeric">
                </div>
                <div class="spawn-hint">Lower = harder · 10 (insane) → 200 (easy)</div>
                <button id="start-btn">Start Game</button>
            </div>
        </div>

        <div class="info-bar">

            {{-- Players Online --}}
            <div class="info-card full">
                <div class="info-title">Players Online</div>
                <div class="players-list" id="players-list">
                    <span style="font-size:11px;color:rgba(255,255,255,0.25)">No players yet…</span>
                </div>
                <div class="live-row"><span class="live-dot"></span><span id="live-txt">syncing…</span></div>
            </div>

            {{-- My Stats --}}
            <div class="info-card full">
                <div class="info-title">My Stats</div>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-lbl">Score</div>
                        <div class="stat-val" id="side-score">0</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-lbl">Misses</div>
                        <div class="stat-val danger" id="side-misses">0/5</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-lbl">Interval</div>
                        <div class="stat-val" id="side-interval">40</div>
                    </div>
                </div>
            </div>

            <div class="info-card full">
                <div class="info-title">🏆 Leaderboard · All Time</div>
                <div id="leaderboard-list">
                    <span style="font-size:11px;color:rgba(255,255,255,0.25)">Loading…</span>
                </div>
            </div>

            <button id="btn-reset-catch">↺ Reset My Game</button>

        </div>

    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', () => {

    const CSRF    = document.querySelector('meta[name="csrf-token"]').content;
    const MY_NAME = @json(auth()->user()->name ?? 'Player');
    const MY_ID   = 'user_{{ auth()->id() }}';

    const PLAYER_COLORS = ['#e74c3c','#3498db','#2ecc71','#f39c12','#9b59b6','#1abc9c','#e91e63','#ff5722'];
    const BALL_COLORS   = ['#ff6b6b','#ffa94d','#ffd166','#a8e063','#4ecdc4','#74b9ff','#a29bfe','#fd79a8'];
    const MY_COLOR      = PLAYER_COLORS[parseInt(MY_ID.slice(-3), 36) % PLAYER_COLORS.length];

    // ── Dimensions ────────────────────────────────────────────────
    const wrap = document.getElementById('game-wrap');
    let WRAP_W, WRAP_H, BASKET_W, BASKET_H, BALL_D, CATCH_Y, BASKET_BOTTOM;

    function setDims() {
        const W = wrap.clientWidth;
        const H = Math.min(500, Math.max(300, window.innerHeight - 260));
        wrap.style.height = H + 'px';

        // change
        WRAP_W        = W;
        WRAP_H        = H;
        BASKET_W      = Math.max(70, Math.min(100, W * 0.19));
        BASKET_H      = 32;
        BALL_D        = Math.max(20, Math.min(26, W * 0.05));
        BASKET_BOTTOM = 22;
        CATCH_Y       = WRAP_H - BASKET_BOTTOM - BASKET_H;

        if (myBasketEl) {
            myBasketEl.style.width  = BASKET_W + 'px';
            myBasketEl.style.height = BASKET_H + 'px';
            myBasketEl.style.bottom = BASKET_BOTTOM + 'px';
        }
        myBasketX = clamp(myBasketX);
    }

    // ── DOM References ────────────────────────────────────────────
    const overlay       = document.getElementById('overlay');
    const hudScore      = document.getElementById('hud-score');
    const hudMisses     = document.getElementById('hud-misses');
    const sideScore     = document.getElementById('side-score');
    const sideMisses    = document.getElementById('side-misses');
    const sideInterval  = document.getElementById('side-interval');
    const playersList   = document.getElementById('players-list');
    const liveTxt       = document.getElementById('live-txt');
    const speedBadge    = document.getElementById('speed-badge');
    const nightBadge    = document.getElementById('night-badge');
    const starsEl       = document.getElementById('stars');
    const touchHint     = document.getElementById('touch-hint');
    const lbList        = document.getElementById('leaderboard-list');

    // Create Stars
    for (let i = 0; i < 55; i++) {
        const s = document.createElement('div');
        s.className = 'star';
        s.style.cssText = `left:${Math.random()*100}%;top:${Math.random()*70}%;opacity:${(0.3+Math.random()*0.7).toFixed(2)}`;
        starsEl.appendChild(s);
    }

    // ── RNG ───────────────────────────────────────────────────────
    function makeRng(seed) {
        return function() {
            seed |= 0; seed = seed + 0x6D2B79F5 | 0;
            let t = Math.imul(seed ^ seed >>> 15, 1 | seed);
            t = t + Math.imul(t ^ t >>> 7, 61 | t) ^ t;
            return ((t ^ t >>> 14) >>> 0) / 4294967296;
        };
    }

    // ── My Basket ─────────────────────────────────────────────────
    let myBasketX = 0;
    let myBasketEl = null;

    function createMyBasket() {
        if (myBasketEl) return;
        myBasketEl = document.createElement('div');
        myBasketEl.className = 'game-basket';
        myBasketEl.style.cssText = `width:${BASKET_W}px;height:${BASKET_H}px;bottom:${BASKET_BOTTOM}px;background:${MY_COLOR};box-shadow:0 0 12px ${MY_COLOR}55;`;
        wrap.appendChild(myBasketEl);
    }

    // ── Balls ─────────────────────────────────────────────────────
    const ballEls = {};
    let balls = [], ballIdCounter = 0;

    function getBallEl(id, color) {
        if (!ballEls[id]) {
            const el = document.createElement('div');
            el.className = 'game-ball';
            el.style.cssText = `width:${BALL_D}px;height:${BALL_D}px;background:${color};`;
            wrap.appendChild(el);
            ballEls[id] = el;
        }
        return ballEls[id];
    }

    function removeBallEl(id) {
        if (ballEls[id]) {
            ballEls[id].remove();
            delete ballEls[id];
        }
    }

    function clearAllBalls() {
        balls.forEach(b => removeBallEl(b.id));
        Object.keys(ballEls).forEach(id => removeBallEl(id));
        balls = [];
    }

    // ── Game State ────────────────────────────────────────────────
    let running = false, raf = null;
    let rng;
    let frame = 0, score = 0, misses = 0;
    let spawnTimer = 0, ballSpeed = 3;
    let spawnIntervalFrames = 40;
    let isNight = false;
    let lastSentScore = -1, lastSentMisses = -1;

    // ── Helpers ───────────────────────────────────────────────────
    function clamp(x) { 
        return Math.max(0, Math.min(WRAP_W - BASKET_W, x)); 
    }

    function flash(el) { 
        el.style.opacity = '1'; 
        setTimeout(() => el.style.opacity = '0', 1800); 
    }

    function setNight(on) {
        if (isNight === on) return;
        isNight = on;
        wrap.classList.toggle('night', on);
        if (on) flash(nightBadge);
    }

    function updateHUD() {
        hudScore.textContent   = score;
        hudMisses.textContent  = misses;
        sideScore.textContent  = score;
        sideMisses.textContent = misses + '/5';
    }

    function esc(s) {
        return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // ── Leaderboard ───────────────────────────────────────────────
    function renderLeaderboard(entries) {
        if (!entries || !entries.length) {
            lbList.innerHTML = '<span style="font-size:11px;color:rgba(255,255,255,0.25)">No scores yet — be the first!</span>';
            return;
        }

        const rankClass = ['gold', 'silver', 'bronze'];
        const medals    = ['🥇','🥈','🥉'];

        lbList.innerHTML = entries.map((e, i) => {
            const isMe = e.player_id === MY_ID;
            const rank = i < 3 
                ? `<span class="lb-rank ${rankClass[i]}">${medals[i]}</span>`
                : `<span class="lb-rank">#${i+1}</span>`;

            const name = e.name.length > 14 ? e.name.slice(0,14) + '…' : e.name;

            return `
                <div class="lb-row">
                    ${rank}
                    <div class="lb-dot" style="background:${e.color}"></div>
                    <span class="lb-name${isMe ? ' lb-me' : ''}">${esc(name)}</span>
                    ${isMe ? '<span class="lb-you-tag">YOU</span>' : ''}
                    <span class="lb-score">${e.score}</span>
                </div>`;
        }).join('');
    }

    function pollLeaderboard() {
        fetch('/games/catch/leaderboard')
            .then(r => r.json())
            .then(d => renderLeaderboard(d.leaderboard || []))
            .catch(err => console.error('Leaderboard fetch failed:', err));
    }

    // ── Spawn Ball ────────────────────────────────────────────────
    function spawnBall() {
        const id    = 'b_' + (++ballIdCounter);
        const x     = (BALL_D/2) + rng() * (WRAP_W - BALL_D * 2.5);
        const color = BALL_COLORS[Math.floor(rng() * BALL_COLORS.length)];
        const speed = ballSpeed * (0.85 + rng() * 0.3);
        balls.push({id, x, y: -BALL_D, color, speed});
    }

    // ── Main Game Loop ────────────────────────────────────────────
    function gameLoop() {
        if (!running) return;
        frame++;
        spawnTimer++;

        // Night & Speed Progression
        if (score > 0 && score % 20 === 0) setNight(true);
        if (score > 0 && score % 50 === 0) setNight(false);
        if (score > 0 && score % 10 === 0) {
            ballSpeed = 3 + Math.floor(score / 10) * 0.7;
            flash(speedBadge);
        }

        if (spawnTimer >= spawnIntervalFrames) {
            spawnBall();
            spawnTimer = 0;
        }

        const bLeft  = myBasketX;
        const bRight = myBasketX + BASKET_W;

        let sd = 0, md = 0;

        for (let i = balls.length - 1; i >= 0; i--) {
            const b = balls[i];
            b.y += b.speed;

            if (b.y + BALL_D >= CATCH_Y) {
                const bc = b.x + BALL_D / 2;
                if (bc > bLeft && bc < bRight) {
                    sd++;
                    removeBallEl(b.id);
                    balls.splice(i, 1);
                } else if (b.y > WRAP_H + 20) {
                    md++;
                    removeBallEl(b.id);
                    balls.splice(i, 1);
                }
            }
        }

        if (sd > 0) score += sd;
        if (md > 0) misses += md;

        // Render balls
        balls.forEach(b => {
            const el = getBallEl(b.id, b.color);
            el.style.left = b.x + 'px';
            el.style.top  = b.y + 'px';
        });

        if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
        updateHUD();

        // Send score update
        if ((score !== lastSentScore || misses !== lastSentMisses) && frame % 3 === 0) {
            lastSentScore = score;
            lastSentMisses = misses;
            fetch('/games/catch/score', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
                body: JSON.stringify({player_id: MY_ID, score, misses})
            }).catch(() => {});
        }

        if (misses >= 5) {
            endGame();
            return;
        }

        raf = requestAnimationFrame(gameLoop);
    }

    // ── Start & End Game ──────────────────────────────────────────
    function startGame(interval) {
        spawnIntervalFrames = interval;
        rng = makeRng(Date.now() & 0xFFFFFF);

        frame = 0; score = 0; misses = 0; spawnTimer = 0;
        ballSpeed = 3; ballIdCounter = 0;
        lastSentScore = -1; lastSentMisses = -1;
        isNight = false;

        wrap.classList.remove('night');
        clearAllBalls();
        updateHUD();
        sideInterval.textContent = interval;

        running = true;
        overlay.style.display = 'none';
        touchHint.style.opacity = '1';
        setTimeout(() => touchHint.style.opacity = '0', 3000);

        raf = requestAnimationFrame(gameLoop);
    }

    function endGame() {
        running = false;
        cancelAnimationFrame(raf);
        clearAllBalls();

        fetch('/games/catch/score', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({player_id: MY_ID, score, misses: 5})
        }).then(() => pollLeaderboard()).catch(() => {});

        overlay.innerHTML = `
            <h2>💥 Game Over</h2>
            <div class="result-score">${score}</div>
            <p>You missed 5 balls. Better luck next time!</p>
            <div class="spawn-control">
                <label>Interval (frames):</label>
                <input type="number" id="spawn-input" value="${spawnIntervalFrames}" min="10" max="200" step="5" inputmode="numeric">
            </div>
            <div class="spawn-hint">Lower = harder · 10 (insane) → 200 (easy)</div>
            <button id="start-btn">Play Again</button>
        `;
        overlay.style.display = 'flex';
        document.getElementById('start-btn').addEventListener('click', handleStart);
    }

    function showStartOverlay() {
        overlay.innerHTML = `
            <h2>🎯 Catch the Ball</h2>
            <p>Move your basket to catch falling balls!<br>Others can watch you play live.</p>
            <small>5 misses = game over</small>
            <div class="spawn-control">
                <label>Interval (frames):</label>
                <input type="number" id="spawn-input" value="${spawnIntervalFrames}" min="10" max="200" step="5" inputmode="numeric">
            </div>
            <div class="spawn-hint">Lower = harder · 10 (insane) → 200 (easy)</div>
            <button id="start-btn">Start Game</button>
        `;
        overlay.style.display = 'flex';
        document.getElementById('start-btn').addEventListener('click', handleStart);
    }

    function handleStart() {
        const inp = document.getElementById('spawn-input');
        const interval = Math.max(10, Math.min(200, parseInt(inp?.value) || 40));

        fetch('/games/catch/start', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({player_id: MY_ID, spawnInterval: interval})
        }).catch(() => {});

        startGame(interval);
    }

    // ── Button Listeners ──────────────────────────────────────────
    document.getElementById('start-btn').addEventListener('click', handleStart);

    document.getElementById('btn-reset-catch').addEventListener('click', () => {
        if (running && !confirm('Quit your current game?')) return;

        running = false;
        cancelAnimationFrame(raf);
        clearAllBalls();

        fetch('/games/catch/reset', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({player_id: MY_ID})
        }).catch(() => {});

        showStartOverlay();
    });

    // ── Players Online ────────────────────────────────────────────
    function renderPlayers(players) {
        if (!players || !players.length) {
            playersList.innerHTML = '<span style="font-size:11px;color:rgba(255,255,255,0.25)">No players yet…</span>';
            return;
        }

        playersList.innerHTML = players.map(p => {
            const isMe = p.id === MY_ID;
            let statusText = 'idle', statusClass = '';

            if (p.running) {
                statusText = `▶ ${p.score}pts`;
                statusClass = 'playing';
            } else if (p.over) {
                statusText = `✕ ${p.score}pts`;
                statusClass = 'game-over';
            }

            const name = p.name.length > 14 ? p.name.slice(0,14) + '…' : p.name;

            return `
                <div class="player-row">
                    <div class="player-dot" style="background:${p.color}"></div>
                    <span class="player-row-name">${esc(name)}</span>
                    ${isMe ? '<span class="player-you-tag">YOU</span>' : ''}
                    <span class="player-status ${statusClass}">${statusText}</span>
                </div>`;
        }).join('');
    }

    // ── Heartbeat ─────────────────────────────────────────────────
    function sendHeartbeat() {
        fetch('/games/catch/join', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF},
            body: JSON.stringify({player_id: MY_ID, name: MY_NAME, color: MY_COLOR, basket_x: myBasketX})
        }).catch(() => {});
    }
    setInterval(sendHeartbeat, 200);
    sendHeartbeat();

    // ── Polling ───────────────────────────────────────────────────
    function poll() {
        fetch('/games/catch/state')
            .then(r => r.json())
            .then(data => {
                liveTxt.textContent = (data.players || []).length + ' online';
                renderPlayers(data.players || []);
            })
            .catch(() => liveTxt.textContent = 'reconnecting…');
    }
    setInterval(poll, 800);
    poll();

    // Leaderboard polling
    setInterval(pollLeaderboard, 5000);
    pollLeaderboard();

    // ── Controls ──────────────────────────────────────────────────
    let isDragging = false, dragStartX = 0, dragStartBasket = 0;

    wrap.addEventListener('mousemove', e => {
        if (isDragging) return;
        const r = wrap.getBoundingClientRect();
        myBasketX = clamp(e.clientX - r.left - BASKET_W / 2);
        if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
    });

    // Touch
    wrap.addEventListener('touchstart', e => {
        isDragging = true;
        dragStartX = e.touches[0].clientX;
        dragStartBasket = myBasketX;
        e.preventDefault();
    }, {passive: false});

    wrap.addEventListener('touchmove', e => {
        if (!isDragging) return;
        myBasketX = clamp(dragStartBasket + (e.touches[0].clientX - dragStartX));
        if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
        e.preventDefault();
    }, {passive: false});

    wrap.addEventListener('touchend', () => isDragging = false);
    wrap.addEventListener('touchcancel', () => isDragging = false);

    // Mouse Drag
    wrap.addEventListener('mousedown', e => {
        isDragging = true;
        dragStartX = e.clientX;
        dragStartBasket = myBasketX;
    });

    document.addEventListener('mousemove', e => {
        if (!isDragging) return;
        myBasketX = clamp(dragStartBasket + (e.clientX - dragStartX));
        if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
    });

    document.addEventListener('mouseup', () => isDragging = false);

    // Keyboard

    let basketSpeed = 50

    document.addEventListener('keydown', e => {
        if (e.key === 'ArrowLeft') {
            myBasketX = clamp(myBasketX - basketSpeed);
            if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
            e.preventDefault();
        }
        if (e.key === 'ArrowRight') {
            myBasketX = clamp(myBasketX + basketSpeed);
            if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
            e.preventDefault();
        }
    });

    // ── Initialize ────────────────────────────────────────────────
    setDims();
    myBasketX = WRAP_W / 2 - BASKET_W / 2;
    createMyBasket();
    myBasketEl.style.left = myBasketX + 'px';

    window.addEventListener('resize', () => {
        setDims();
        myBasketX = clamp(myBasketX);
        if (myBasketEl) myBasketEl.style.left = myBasketX + 'px';
    });

});
</script>

@endsection