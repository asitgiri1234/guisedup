<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\User;

class InteractionService
{
    /**
     * Record an engagement event for the given user.
     *
     * @param  array{post_id: int, type: string}  $data
     */
    public function log(User $user, array $data): Interaction
    {
        return $user->interactions()->create([
            'post_id' => $data['post_id'],
            'type' => $data['type'],
        ]);
    }
}
