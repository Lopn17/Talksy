<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemoryPlayer extends Model
{
    protected $fillable = ['game_id', 'name', 'score', 'order'];

    public function game()
    {
        return $this->belongsTo(MemoryGame::class, 'game_id');
    }
}