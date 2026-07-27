<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ArCheckin extends Model
{
    protected $table = 'ar_checkins';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'spot_id',
        'trivia_question_id',
        'trivia_correct',
        'user_lat',
        'user_lng',
        'xp_earned',
        'checked_in_at',
    ];

    protected $casts = [
        'trivia_correct' => 'boolean',
        'user_lat'       => 'float',
        'user_lng'       => 'float',
        'xp_earned'      => 'integer',
        'checked_in_at'  => 'datetime',
    ];

    public function spot()
    {
        return $this->belongsTo(TouristSpot::class, 'spot_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function triviaQuestion()
    {
        return $this->belongsTo(TriviaQuestion::class);
    }
}
