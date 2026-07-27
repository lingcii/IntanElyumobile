<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QuestCompletion extends Model
{
    protected $table = 'quest_completions';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'quest_id',
        'xp_earned',
        'completed_at',
    ];

    protected $casts = [
        'xp_earned'    => 'integer',
        'completed_at' => 'datetime',
    ];

    public function quest()
    {
        return $this->belongsTo(Quest::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
