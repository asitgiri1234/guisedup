<?php

namespace App\Enums;

/**
 * The kinds of engagement a user can have with a post.
 * These feed the feed-ranking signal and (later) implicit-feedback training.
 */
enum InteractionType: string
{
    // Emoji reactions surfaced in the app (❤️ 🔥 👏).
    case Like = 'like';
    case Fire = 'fire';
    case Clap = 'clap';

    // Implicit / non-reaction signals.
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

    /**
     * The reaction emoji types (the ones with buttons in the UI).
     *
     * @return list<self>
     */
    public static function reactions(): array
    {
        return [self::Like, self::Fire, self::Clap];
    }
}
