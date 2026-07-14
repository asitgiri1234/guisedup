<?php

namespace App\Enums;

/**
 * The kinds of engagement a user can have with a post.
 * These feed the feed-ranking signal and (later) implicit-feedback training.
 */
enum InteractionType: string
{
    case Like = 'like';
    case View = 'view';
    case Save = 'save';
    case Share = 'share';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(static fn (self $case): string => $case->value, self::cases());
    }
}
