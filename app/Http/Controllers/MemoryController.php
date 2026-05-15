<?php

namespace App\Http\Controllers;

use App\Models\MemoryGame;
use App\Models\MemoryPlayer;
use Illuminate\Http\Request;

class MemoryController extends Controller
{
    // All emojis pool
    private const EMOJIS = [
        '🐶','🐱','🐭','🐹','🐰','🦊','🐻','🐼','🐨','🐯',
        '🦁','🐮','🐸','🐵','🦆','🦉','🦋','🐢','🐙','🦀',
        '🐡','🦈','🐊','🦒','🦓','🦏','🐘','🦔','🦦','🦥',
        '🌸','🌻','🍎','🍋','🍇','🍓','🌈','🌵','🍄','🌴',
        '🍕','🍔','🌮','🍜','🍣','🍩','🍦','🧁','🍭','🥝',
        '⚡','🔮','💎','🎸','🚀','🌙','☀️','🎯','🎲','🏆',
        '🎪','🎨','🧲','🔭','🎭','🎬','🎹','🧸','🪄','🎠',
        '👾','🤖','👻','🎃','🧿','🪩','🫧','🪸','🧊','🌊',
    ];

    private const CONFIGS = [
        'easy'     => ['cols' => 4, 'rows' => 3, 'pairs' => 6],
        'medium'   => ['cols' => 4, 'rows' => 4, 'pairs' => 8],
        'hard'     => ['cols' => 6, 'rows' => 4, 'pairs' => 12],
        'veryhard' => ['cols' => 6, 'rows' => 6, 'pairs' => 18],
        'insane'   => ['cols' => 8, 'rows' => 6, 'pairs' => 24],
    ];

    /** Show main game page */
    public function index()
    {
        $game = MemoryGame::with('players')->latest()->first();
        if (!$game) {
            $game = $this->createGame('easy');
        }
        return view('memory', compact('game'));
    }

    /** GET /games/memory/state — polling endpoint */
    public function state()
    {
        $game = MemoryGame::with('players')->latest()->first();
        if (!$game) {
            $game = $this->createGame('easy');
        }
        return response()->json($this->formatState($game));
    }

    /** POST /games/memory/start — start/restart with difficulty */
    public function start(Request $request)
    {
        $request->validate(['difficulty' => 'required|in:easy,medium,hard,veryhard,insane']);

        // Keep existing players, reset the board
        $game = MemoryGame::with('players')->latest()->first();
        if ($game) {
            $players = $game->players->map(fn($p) => ['name' => $p->name, 'order' => $p->order])->toArray();
            $game->delete();
        } else {
            $players = [];
        }

        $game = $this->createGame($request->difficulty, $players);

        return response()->json($this->formatState($game));
    }

    /** POST /games/memory/player — add a player */
    public function addPlayer(Request $request)
    {
        $request->validate(['name' => 'required|string|max:32']);

        $game = MemoryGame::with('players')->latest()->firstOrFail();

        $count = $game->players()->count();
        if ($count >= 8) {
            return response()->json(['error' => 'Max 8 players'], 422);
        }

        $player = $game->players()->create([
            'name'  => trim($request->name),
            'score' => 0,
            'order' => $count,
        ]);

        // Set first player as current if none set
        if (!$game->current_player_id) {
            $game->current_player_id = $player->id;
            $game->status = 'playing';
            $game->save();
        }

        $game->refresh()->load('players');
        return response()->json($this->formatState($game));
    }

    /** DELETE /games/memory/player/{id} */
    public function removePlayer($id)
    {
        $player = MemoryPlayer::findOrFail($id);
        $game = MemoryGame::with('players')->find($player->game_id);

        $wasCurrent = $game->current_player_id == $id;
        $player->delete();

        $game->refresh()->load('players');

        // Re-order remaining players
        $game->players()->get()->each(function ($p, $i) {
            $p->update(['order' => $i]);
        });

        // If deleted player was current, advance turn
        if ($wasCurrent) {
            $this->advanceTurn($game);
        }

        $game->refresh()->load('players');
        return response()->json($this->formatState($game));
    }

    /** POST /games/memory/flip — flip a card */
    public function flip(Request $request)
    {
        $request->validate(['card_id' => 'required|integer', 'player_id' => 'required|integer']);

        $game = MemoryGame::with('players')->latest()->firstOrFail();

        if ($game->status === 'finished') {
            return response()->json(['error' => 'Game is finished'], 422);
        }

        // Verify player exists in this game
        $player = $game->players->firstWhere('id', $request->player_id);
        if (!$player) {
            return response()->json(['error' => 'Player not in game'], 422);
        }

        // Must be this player's turn
        if ($game->current_player_id && $game->current_player_id != $request->player_id) {
            return response()->json(['error' => 'Not your turn'], 422);
        }

        $cards      = $game->cards;
        $flippedIds = $game->flipped_ids ?? [];
        $cardId     = (int) $request->card_id;

        // Find the card
        $cardIndex = null;
        foreach ($cards as $i => $card) {
            if ($card['id'] === $cardId) {
                $cardIndex = $i;
                break;
            }
        }

        if ($cardIndex === null) {
            return response()->json(['error' => 'Card not found'], 422);
        }

        $card = $cards[$cardIndex];

        // Skip if already matched or already flipped
        if ($card['matched'] || in_array($cardId, $flippedIds)) {
            return response()->json($this->formatState($game));
        }

        // Only allow flipping if we have < 2 flipped
        if (count($flippedIds) >= 2) {
            return response()->json(['error' => 'Wait for mismatch to resolve'], 422);
        }

        // Flip the card
        $cards[$cardIndex]['flipped'] = true;
        $flippedIds[] = $cardId;

        if (count($flippedIds) === 2) {
            // Check for match
            $ids    = $flippedIds;
            $cardA  = $this->findCard($cards, $ids[0]);
            $cardB  = $this->findCard($cards, $ids[1]);

            if ($cardA && $cardB && $cardA['emoji'] === $cardB['emoji']) {
                // Match! Mark both as matched, award point
                foreach ($cards as &$c) {
                    if (in_array($c['id'], $ids)) {
                        $c['matched'] = true;
                        $c['flipped'] = true;
                    }
                }
                unset($c);

                $flippedIds = [];
                $player->increment('score');

                // Check if all matched
                $allMatched = collect($cards)->every(fn($c) => $c['matched']);
                if ($allMatched) {
                    $game->status = 'finished';
                }
                // Current player keeps the turn on a match (reward)
            } else {
                // Mismatch — mark as mismatch so frontend can animate, then auto-resolve
                // We keep flipped_ids as [id1, id2] with a 'mismatch' flag
                $game->cards       = $cards;
                $game->flipped_ids = array_merge($ids, ['mismatch']);
                $game->save();

                return response()->json($this->formatState($game));
            }
        }

        $game->cards       = $cards;
        $game->flipped_ids = $flippedIds;

        if ($game->status !== 'finished') {
            $game->status = 'playing';
        }

        $game->save();

        return response()->json($this->formatState($game));
    }

    /** POST /games/memory/resolve — called by frontend after mismatch animation */
    public function resolve()
    {
        $game = MemoryGame::with('players')->latest()->firstOrFail();
        $flippedIds = $game->flipped_ids ?? [];

        // ✅ If already resolved (no mismatch sentinel), don't advance turn again
        if (!in_array('mismatch', $flippedIds)) {
            return response()->json($this->formatState($game));
        }

        // Strip 'mismatch' sentinel
        $ids = array_filter($flippedIds, fn($v) => $v !== 'mismatch');
        $ids = array_values($ids);

        $cards = $game->cards;
        foreach ($cards as &$card) {
            if (in_array($card['id'], $ids)) {
                $card['flipped'] = false;
            }
        }
        unset($card);

        $game->cards = $cards;
        $game->flipped_ids = [];
        $game->save();

        $this->advanceTurn($game);
        $game->refresh()->load('players');

        return response()->json($this->formatState($game));
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function createGame(string $difficulty, array $keepPlayers = []): MemoryGame
    {
        $config = self::CONFIGS[$difficulty];
        $pairs  = $config['pairs'];

        $emojis = self::EMOJIS;
        shuffle($emojis);
        $chosen = array_slice($emojis, 0, $pairs);

        $deck = array_merge($chosen, $chosen);
        shuffle($deck);

        $cards = [];
        foreach ($deck as $i => $emoji) {
            $cards[] = ['id' => $i, 'emoji' => $emoji, 'flipped' => false, 'matched' => false];
        }

        $game = MemoryGame::create([
            'difficulty'        => $difficulty,
            'cards'             => $cards,
            'flipped_ids'       => [],
            'current_player_id' => null,
            'status'            => 'waiting',
        ]);

        foreach ($keepPlayers as $i => $p) {
            $game->players()->create([
                'name'  => $p['name'],
                'score' => 0,
                'order' => $i,
            ]);
        }

        if ($game->players()->count() > 0) {
            $first = $game->players()->orderBy('order')->first();
            $game->current_player_id = $first->id;
            $game->status = 'playing';
            $game->save();
        }

        $game->refresh()->load('players');
        return $game;
    }

    private function findCard(array $cards, int $id): ?array
    {
        foreach ($cards as $card) {
            if ($card['id'] === $id) return $card;
        }
        return null;
    }

    private function advanceTurn(MemoryGame $game): void
    {
        $players = $game->players()->orderBy('order')->get();
        if ($players->isEmpty()) {
            $game->current_player_id = null;
            $game->save();
            return;
        }

        $currentOrder = $players->firstWhere('id', $game->current_player_id)?->order ?? -1;
        $next = $players->firstWhere('order', '>', $currentOrder) ?? $players->first();

        $game->current_player_id = $next->id;
        $game->save();
    }

    private function formatState(MemoryGame $game): array
    {
        $config = self::CONFIGS[$game->difficulty];
        $flippedIds = $game->flipped_ids ?? [];
        $isMismatch = in_array('mismatch', $flippedIds);
        $flippedIds = array_values(array_filter($flippedIds, fn($v) => $v !== 'mismatch'));

        // Hide emoji of unflipped, unmatched cards
        $cards = array_map(function ($card) use ($flippedIds) {
            return [
                'id'      => $card['id'],
                'emoji'   => ($card['flipped'] || $card['matched'] || in_array($card['id'], $flippedIds))
                             ? $card['emoji']
                             : null,
                'flipped' => $card['flipped'] || in_array($card['id'], $flippedIds),
                'matched' => $card['matched'],
            ];
        }, $game->cards);

        $matchedCount = count(array_filter($game->cards, fn($c) => $c['matched'])) / 2;

        return [
            'game_id'           => $game->id,
            'difficulty'        => $game->difficulty,
            'cols'              => $config['cols'],
            'rows'              => $config['rows'],
            'pairs'             => $config['pairs'],
            'matched_pairs'     => (int) $matchedCount,
            'status'            => $game->status,
            'cards'             => array_values($cards),
            'flipped_ids'       => $flippedIds,
            'is_mismatch'       => $isMismatch,
            'current_player_id' => $game->current_player_id,
            'players'           => $game->players->map(fn($p) => [
                'id'    => $p->id,
                'name'  => $p->name,
                'score' => $p->score,
                'order' => $p->order,
            ])->values()->toArray(),
        ];
    }
}