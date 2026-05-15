<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CatchController extends Controller
{
    private const PLAYERS_KEY     = 'catch_game_players';
    private const LEADERBOARD_KEY = 'catch_leaderboard';   // ← new
    private const TTL             = 3600;
    private const PLAYER_TTL      = 4;

    private function playerStateKey(string $playerId): string
    {
        return 'catch_player_state_' . $playerId;
    }

    public function index()
    {
        return view('catch');
    }

    public function state()
    {
        $players = $this->activePlayers();
        foreach ($players as &$player) {
            $state = Cache::get($this->playerStateKey($player['id']), [
                'running' => false, 'over' => false,
                'score' => 0, 'misses' => 0, 'spawnInterval' => 40,
            ]);
            $player['running']       = $state['running'];
            $player['over']          = $state['over'];
            $player['score']         = $state['score'];
            $player['misses']        = $state['misses'];
            $player['spawnInterval'] = $state['spawnInterval'];
        }
        unset($player);
        return response()->json(['players' => array_values($players)]);
    }

    public function join(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string|max:64',
            'name'      => 'required|string|max:32',
            'color'     => 'required|string|max:16',
            'basket_x'  => 'required|numeric',
        ]);
        $players = Cache::get(self::PLAYERS_KEY, []);
        $players[$request->player_id] = [
            'id'        => $request->player_id,
            'name'      => $request->name,
            'color'     => $request->color,
            'basket_x'  => (float) $request->basket_x,
            'last_seen' => now()->timestamp,
        ];
        Cache::put(self::PLAYERS_KEY, $players, self::TTL);
        return response()->json(['ok' => true]);
    }

    public function start(Request $request)
    {
        $request->validate([
            'player_id'     => 'required|string|max:64',
            'spawnInterval' => 'required|integer|min:10|max:200',
        ]);
        Cache::put($this->playerStateKey($request->player_id), [
            'running'       => true,
            'over'          => false,
            'score'         => 0,
            'misses'        => 0,
            'spawnInterval' => (int) $request->spawnInterval,
            'updated_at'    => now()->timestamp,
        ], self::TTL);
        return response()->json(['ok' => true]);
    }

    public function score(Request $request)
    {
        $request->validate([
            'player_id' => 'required|string|max:64',
            'score'     => 'required|integer|min:0',
            'misses'    => 'required|integer|min:0',
        ]);

        $key   = $this->playerStateKey($request->player_id);
        $state = Cache::get($key, [
            'running' => false, 'over' => false,
            'score' => 0, 'misses' => 0, 'spawnInterval' => 40,
        ]);

        if (!$state['running'] || $state['over']) {
            // ← Still update leaderboard on the final "game over" push
            if ((int) $request->score > 0) {
                $this->updateLeaderboard($request->player_id, (int) $request->score);
            }
            return response()->json(['ok' => false]);
        }

        $state['score']      = (int) $request->score;
        $state['misses']     = (int) $request->misses;
        $state['over']       = $state['misses'] >= 5;
        $state['running']    = !$state['over'];
        $state['updated_at'] = now()->timestamp;

        Cache::put($key, $state, self::TTL);

        // Update leaderboard whenever score is positive
        if ($state['score'] > 0) {
            $this->updateLeaderboard($request->player_id, $state['score']);
        }

        return response()->json([
            'ok'     => true,
            'score'  => $state['score'],
            'misses' => $state['misses'],
            'over'   => $state['over'],
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate(['player_id' => 'required|string|max:64']);
        Cache::put($this->playerStateKey($request->player_id), [
            'running'       => false,
            'over'          => false,
            'score'         => 0,
            'misses'        => 0,
            'spawnInterval' => 40,
            'updated_at'    => now()->timestamp,
        ], self::TTL);
        return response()->json(['ok' => true]);
    }

    /**
     * GET /games/catch/leaderboard
     * Returns top 10 all-time scores (persisted in cache).
     */
    public function leaderboard()
    {
        $board = Cache::get(self::LEADERBOARD_KEY, []);
        // Sort descending by score
        usort($board, fn($a, $b) => $b['score'] <=> $a['score']);
        return response()->json(['leaderboard' => array_slice($board, 0, 10)]);
    }

    // ── Private helpers ───────────────────────────────────────────

    private function updateLeaderboard(string $playerId, int $newScore): void
    {
        // Grab player info for name/color
        $players = Cache::get(self::PLAYERS_KEY, []);
        $info    = $players[$playerId] ?? null;
        if (!$info) return;

        $board = Cache::get(self::LEADERBOARD_KEY, []);

        // Find existing entry for this player
        $found = false;
        foreach ($board as &$entry) {
            if ($entry['player_id'] === $playerId) {
                if ($newScore > $entry['score']) {
                    $entry['score']    = $newScore;
                    $entry['name']     = $info['name'];   // keep name fresh
                    $entry['color']    = $info['color'];
                    $entry['achieved'] = now()->timestamp;
                }
                $found = true;
                break;
            }
        }
        unset($entry);

        if (!$found) {
            $board[] = [
                'player_id' => $playerId,
                'name'      => $info['name'],
                'color'     => $info['color'],
                'score'     => $newScore,
                'achieved'  => now()->timestamp,
            ];
        }

        // Keep only top 50 to avoid unbounded growth
        usort($board, fn($a, $b) => $b['score'] <=> $a['score']);
        $board = array_slice($board, 0, 50);

        Cache::put(self::LEADERBOARD_KEY, $board, 60 * 60 * 24 * 30); // 30 days
    }

    private function activePlayers(): array
    {
        $players = Cache::get(self::PLAYERS_KEY, []);
        $cutoff  = now()->timestamp - self::PLAYER_TTL;
        $active  = array_filter($players, fn($p) => $p['last_seen'] >= $cutoff);
        Cache::put(self::PLAYERS_KEY, $active, self::TTL);
        return $active;
    }
}