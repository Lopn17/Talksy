@extends('layouts.app')

@section('content')
<style>
    /* ── Page shell ──────────────────────────────────── */
    .rps-page {
        flex: 1;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        height: 100%;
    }

    .rps-header {
        padding: 1.25rem 1.75rem 0;
        flex-shrink: 0;
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
    }

    .rps-header h1 {
        font-family: 'Instrument Serif', serif;
        font-size: 1.6rem;
        font-weight: 400;
        color: var(--ink);
        letter-spacing: -0.02em;
    }

    .rps-header p {
        font-size: 0.8125rem;
        color: var(--ink-2);
        margin-top: 0.2rem;
    }

    /* ── Body ────────────────────────────────────────── */
    .rps-body {
        flex: 1;
        padding: 1.25rem 1.75rem 1.75rem;
        overflow: auto;
        display: flex;
        flex-direction: column;
        gap: 1rem;
        max-width: 680px;
    }

    /* ── Name row ────────────────────────────────────── */
    .name-row {
        display: flex;
        gap: 0.625rem;
    }

    .name-input {
        flex: 1;
        padding: 0.6rem 0.875rem;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        background: var(--surface-2);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.9375rem;
        font-weight: 500;
        color: var(--ink);
        outline: none;
        transition: border-color 0.15s;
    }

    .name-input:focus  { border-color: var(--accent); }
    .name-input::placeholder { color: var(--ink-3); }

    .join-btn {
        padding: 0.6rem 1.25rem;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: opacity 0.15s;
        white-space: nowrap;
    }

    .join-btn:hover { opacity: 0.88; }

    /* ── Status banner ───────────────────────────────── */
    .status-banner {
        padding: 0.625rem 1rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        font-weight: 500;
        text-align: center;
        background: var(--accent-dim);
        border: 1.5px dashed rgba(232,98,42,0.35);
        color: var(--accent);
        display: none;
    }

    .status-banner.show { display: block; }

    /* ── Players grid ────────────────────────────────── */
    .players-grid {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .player-card {
        background: var(--surface-2);
        border: 1.5px solid var(--border);
        border-radius: 12px;
        padding: 1rem 1.25rem;
        transition: border-color 0.2s;
    }

    .player-card.is-me   { border-color: rgba(232,98,42,0.4); }
    .player-card.ready   { border-color: #4caf50; }
    .player-card.winner  { border-color: #4caf50; background: rgba(76,175,80,0.07); }
    .player-card.loser   { opacity: 0.55; }
    .player-card.tied    { border-color: var(--ink-3); }

    .player-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 0.875rem;
    }

    .player-name {
        font-size: 1rem;
        font-weight: 600;
        color: var(--ink);
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .me-badge {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.07em;
        background: var(--accent-dim);
        color: var(--accent);
        border-radius: 4px;
        padding: 2px 6px;
    }

    .player-status {
        font-size: 0.75rem;
        font-weight: 500;
        color: var(--ink-3);
    }

    .player-status.ready-txt { color: #4caf50; }

    /* ── Choice buttons ──────────────────────────────── */
    .choices {
        display: flex;
        gap: 0.625rem;
    }

    .choice-btn {
        flex: 1;
        padding: 0.75rem 0;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        font-size: 1.75rem;
        cursor: pointer;
        transition: all 0.15s;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
    }

    .choice-btn:hover:not(:disabled) {
        border-color: var(--accent);
        transform: scale(1.05);
    }

    .choice-btn:active:not(:disabled) { transform: scale(0.96); }

    /* Selected — only visible to you (shown as accent border) */
    .choice-btn.my-pick {
        border-color: var(--accent);
        border-width: 2px;
        background: var(--accent-dim);
    }

    /* After reveal: winner pick gets green */
    .choice-btn.win-pick {
        border-color: #4caf50;
        border-width: 2px;
        background: rgba(76,175,80,0.1);
    }

    /* Opponent's pick is hidden until reveal */
    .choice-btn.hidden-pick {
        filter: blur(8px);
        pointer-events: none;
    }

    /* Disabled for other players who haven't revealed */
    .choice-btn:disabled {
        cursor: default;
        opacity: 0.5;
    }

    /* Result badge */
    .result-badge {
        font-family: 'Instrument Serif', serif;
        font-size: 1.1rem;
        letter-spacing: -0.01em;
        padding: 2px 10px;
        border-radius: 6px;
        display: none;
    }

    .result-badge.win  { display: inline-block; color: #4caf50; }
    .result-badge.lose { display: inline-block; color: #c0392b; }
    .result-badge.tie  { display: inline-block; color: var(--ink-2); }

    /* ── Action bar ──────────────────────────────────── */
    .action-bar {
        display: flex;
        gap: 0.625rem;
    }

    .clash-btn {
        flex: 1;
        padding: 0.8rem 1rem;
        background: var(--accent);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 1rem;
        font-weight: 700;
        letter-spacing: 0.04em;
        cursor: pointer;
        transition: opacity 0.15s, transform 0.1s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    .clash-btn:hover:not(:disabled) { opacity: 0.88; }
    .clash-btn:active:not(:disabled) { transform: scale(0.98); }
    .clash-btn:disabled { opacity: 0.4; cursor: not-allowed; }

    .reset-btn {
        padding: 0.8rem 1.25rem;
        background: var(--surface-2);
        color: var(--ink);
        border: 1.5px solid var(--border);
        border-radius: 10px;
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        font-weight: 500;
        cursor: pointer;
        transition: background 0.15s;
    }

    .reset-btn:hover { background: #e3e1de; }

    /* ── Responsive ──────────────────────────────────── */
    @media (max-width: 640px) {
        .rps-body   { padding: 1rem; }
        .rps-header { padding: 1rem 1rem 0; }
        .choices    { gap: 0.4rem; }
        .choice-btn { font-size: 1.4rem; padding: 0.6rem 0; }
    }
</style>

<div class="rps-page">

    <div class="rps-header">
        <div>
            <h1>Rock Paper Scissors</h1>
            <p>Everyone picks in secret — clash to reveal</p>
        </div>
    </div>

    <div class="rps-body">

        {{-- Name input --}}
        <div class="name-row">
            <input class="name-input" id="name-input"
                   placeholder="Your name…" maxlength="20"
                   value="{{ auth()->user()->name }}">
            <button class="join-btn" id="btn-join">Join / Update</button>
        </div>

        {{-- Status --}}
        <div class="status-banner" id="status-banner">Waiting for players…</div>

        {{-- Players --}}
        <div class="players-grid" id="players-grid"></div>

        {{-- Actions --}}
        <div class="action-bar">
            <button class="clash-btn" id="btn-clash" disabled>
                ⚡ CLASH
            </button>
            <button class="reset-btn" id="btn-reset">Play again</button>
        </div>

    </div>
</div>

<script>
const CSRF  = document.querySelector('meta[name="csrf-token"]').content;
const post  = (url, data = {}) =>
    fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
        body: JSON.stringify(data),
    }).then(r => r.json());

// ── DOM ───────────────────────────────────────────────
const nameInput    = document.getElementById('name-input');
const btnJoin      = document.getElementById('btn-join');
const statusBanner = document.getElementById('status-banner');
const playersGrid  = document.getElementById('players-grid');
const btnClash     = document.getElementById('btn-clash');
const btnReset     = document.getElementById('btn-reset');

// ── State ─────────────────────────────────────────────
const MOVES    = ['rock','paper','scissors'];
const EMOJI    = { rock: '✊', paper: '✋', scissors: '✌️' };
let myPick     = null;   // only I know this locally
let hasJoined  = false;
let pollInterval = null;
let lastState  = null;

// ── Render ────────────────────────────────────────────
const render = data => {
    lastState = data;
    const { players, revealed, allReady } = data;

    // Status banner
    if (!hasJoined) {
        statusBanner.textContent = 'Enter your name and join to play.';
        statusBanner.classList.add('show');
    } else if (players.length < 2) {
        statusBanner.textContent = 'Waiting for at least one more player…';
        statusBanner.classList.add('show');
    } else if (!allReady && !revealed) {
        const waiting = players.filter(p => !p.ready).map(p => p.name).join(', ');
        statusBanner.textContent = `Waiting for: ${waiting}`;
        statusBanner.classList.add('show');
    } else if (allReady && !revealed) {
        statusBanner.textContent = 'Everyone is ready — hit CLASH!';
        statusBanner.classList.add('show');
    } else {
        statusBanner.classList.remove('show');
    }

    // Clash button
    btnClash.disabled = !(allReady && players.length >= 2 && !revealed);

    // Player cards
    playersGrid.innerHTML = '';
    players.forEach(p => {
        const card = document.createElement('div');
        card.className = 'player-card' +
            (p.isMe   ? ' is-me'  : '') +
            (revealed && p.result === 'win'  ? ' winner' : '') +
            (revealed && p.result === 'lose' ? ' loser'  : '') +
            (revealed && p.result === 'tie'  ? ' tied'   : '') +
            (!revealed && p.ready ? ' ready' : '');

        // Result badge
        let badgeHtml = '';
        if (revealed && p.result) {
            const labels = { win: '🏆 Winner', lose: '❌ Lose', tie: '🤝 Tie' };
            badgeHtml = `<span class="result-badge ${p.result}">${labels[p.result]}</span>`;
        }

        card.innerHTML = `
            <div class="player-top">
                <div class="player-name">
                    ${escHtml(p.name)}
                    ${p.isMe ? '<span class="me-badge">You</span>' : ''}
                    ${badgeHtml}
                </div>
                <span class="player-status ${p.ready ? 'ready-txt' : ''}">
                    ${p.ready ? 'Ready ✓' : 'Picking…'}
                </span>
            </div>
            <div class="choices" data-player-id="${escHtml(p.id)}">
                ${MOVES.map(m => buildChoiceBtn(p, m, revealed)).join('')}
            </div>`;

        playersGrid.appendChild(card);
    });

    // Attach click handlers only on MY card's buttons
    const myCard = players.find(p => p.isMe);
    if (myCard && !revealed) {
        const myChoicesDiv = playersGrid.querySelector(`[data-player-id="${myCard.id}"]`);
        if (myChoicesDiv) {
            myChoicesDiv.querySelectorAll('.choice-btn').forEach(btn => {
                btn.addEventListener('click', () => {
                    if (!hasJoined) return;
                    myPick = btn.dataset.move;
                    post('/games/rps/pick', { choice: myPick }).then(() => {});
                    // Re-render my row immediately for snappiness
                    myChoicesDiv.querySelectorAll('.choice-btn').forEach(b => {
                        b.classList.toggle('my-pick', b.dataset.move === myPick);
                    });
                });
            });
        }
    }
};

const buildChoiceBtn = (p, move, revealed) => {
    let cls = 'choice-btn';
    let extra = '';

    if (p.isMe) {
        // My card: show my local pick highlighted, buttons always active
        if (myPick === move) cls += ' my-pick';
        if (revealed && p.choice === move) cls += p.result === 'win' ? ' win-pick' : '';
    } else {
        // Other players
        if (revealed) {
            // Show their actual choice
            if (p.choice === move) {
                cls += p.result === 'win' ? ' win-pick' : '';
            } else {
                extra = 'disabled';
            }
        } else {
            // Hide all — blurred if they've picked
            if (p.ready) {
                cls += ' hidden-pick';
            } else {
                extra = 'disabled';
                cls += ' hidden-pick';
            }
        }
    }

    return `<button class="${cls}" data-move="${move}" ${extra}>${EMOJI[move]}</button>`;
};

const escHtml = s => s.replace(/[&<>"']/g, c =>
    ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));

// ── Poll ──────────────────────────────────────────────
const poll = () => {
    fetch('/games/rps/state').then(r => r.json()).then(data => {
        render(data);
    }).catch(() => {});
};

const startPoll = () => { if (!pollInterval) pollInterval = setInterval(poll, 1500); };
const stopPoll  = () => { clearInterval(pollInterval); pollInterval = null; };

// ── Join ──────────────────────────────────────────────
const doJoin = () => {
    const name = nameInput.value.trim();
    if (!name) { nameInput.focus(); return; }
    post('/games/rps/join', { name }).then(() => {
        hasJoined = true;
        startPoll();
        poll(); // immediate refresh
    });
};

btnJoin.addEventListener('click', doJoin);
nameInput.addEventListener('keydown', e => { if (e.key === 'Enter') doJoin(); });

// ── Clash ─────────────────────────────────────────────
btnClash.addEventListener('click', () => {
    post('/games/rps/clash').then(data => {
        if (!data.error) poll();
    });
});

// ── Reset ─────────────────────────────────────────────
btnReset.addEventListener('click', () => {
    myPick = null;
    post('/games/rps/reset').then(() => poll());
});

// ── Leave on page unload ──────────────────────────────
window.addEventListener('beforeunload', () => {
    navigator.sendBeacon('/games/rps/leave',
        new Blob([JSON.stringify({_token: CSRF})], {type:'application/json'}));
});

// ── Boot ──────────────────────────────────────────────
(() => {
    // Auto-join with auth name on load
    const name = nameInput.value.trim();
    if (name) {
        post('/games/rps/join', { name }).then(() => {
            hasJoined = true;
            startPoll();
        });
    } else {
        startPoll();
    }
})();
</script>
@endsection