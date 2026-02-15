<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'championship_id', 'phase',
        'team_a_id', 'team_b_id',
        'team_a_goals', 'team_b_goals',
        'team_a_yellow_cards', 'team_b_yellow_cards',
        'team_a_penalties', 'team_b_penalties',
        'winner_id'
    ];

    public function championship()
    {
        return $this->belongsTo(Championship::class);
    }

    public function teamA()
    {
        return $this->belongsTo(Team::class, 'team_a_id');
    }

    public function teamB()
    {
        return $this->belongsTo(Team::class, 'team_b_id');
    }

    public function winner()
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }
}
