<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'is_read',
        'module',
        'action_url',
        'spot_name',
        'municipality_name',
        'actor_name',
    ];

    protected $casts = [
        'data'    => 'array',
        'is_read' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Safely create a notification for a user.
     */
    public static function createSafely(int $userId, string $type, string $title, string $message, array $extraData = []): ?self
    {
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('notifications')) {
                return null;
            }

            return self::create(array_merge([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'is_read' => false,
            ], $extraData));
        } catch (\Throwable $e) {
            return null;
        }
    }
}
