<?php

namespace App\Models;

use App\Enums\InteractionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interaction extends Model
{
    /** @use HasFactory<\Database\Factories\InteractionFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'type' => InteractionType::class,
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
