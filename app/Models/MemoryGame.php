<?php

namespace App\Models;

use App\Models\MemoryPlayer;
use Illuminate\Database\Eloquent\Model;

class MemoryGame extends Model
{
    protected $fillable = ['difficulty', 'cards', 'flipped_ids', 'current_player_id', 'status'];

    protected $casts = [
        'cards'       => 'array',
        'flipped_ids' => 'array',
    ];

    public function players()
    {
        return $this->hasMany(MemoryPlayer::class, 'game_id')->orderBy('order');
    }

    public function currentPlayer()
    {
        return $this->belongsTo(MemoryPlayer::class, 'current_player_id');
    }
}