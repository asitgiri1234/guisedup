<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Pgvector\Laravel\HasNeighbors;
use Pgvector\Laravel\Vector;

class Post extends Model
{
    /** @use HasFactory<\Database\Factories\PostFactory> */
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
}
