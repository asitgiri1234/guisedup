<?php

namespace App\Models;

use App\Enums\InteractionType;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Post extends Model
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasNeighbors;

    protected $fillable = [
        'user_id',
        'caption',
        'image_url',
        'embedding',
    ];

    protected function casts(): array
    {
        return [
            'embedding' => Vector::class,
        ];
    }

    /**
     * Eager-load the counts the feed/search/UI need: total interactions,
     * per-emoji reaction tallies, and comments.
     *
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    public function scopeWithEngagementCounts(Builder $query): Builder
    {
        return $query->withCount([
            'interactions',
            'interactions as like_count' => fn (Builder $q) => $q->where('type', InteractionType::Like->value),
            'interactions as fire_count' => fn (Builder $q) => $q->where('type', InteractionType::Fire->value),
            'interactions as clap_count' => fn (Builder $q) => $q->where('type', InteractionType::Clap->value),
            'comments',
        ]);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<Interaction, $this>
     */
    public function interactions(): HasMany
    {
        return $this->hasMany(Interaction::class);
    }

    /**
     * @return HasMany<Comment, $this>
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
