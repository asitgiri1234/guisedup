<?php

return [

    'per_page' => 20,

    /*
    | How many recent candidate posts to score per feed request. Ranking runs
    | in PHP over this bounded set, so keep it sane for large tables.
    */
    'candidate_limit' => (int) env('FEED_CANDIDATE_LIMIT', 500),

    /*
    | Time-decay half-life control (hours). Larger tau = slower decay.
    */
    'time_decay_tau_hours' => (float) env('FEED_TIME_DECAY_TAU_HOURS', 72),

    /*
    | Signal weights, keyed by RankingSignal::key(). They need not sum to 1,
    | but keeping them normalised makes scores easy to reason about.
    */
    'weights' => [
        'authenticity' => (float) env('FEED_WEIGHT_AUTHENTICITY', 0.20),
        'relationship' => (float) env('FEED_WEIGHT_RELATIONSHIP', 0.30),
        'semantic' => (float) env('FEED_WEIGHT_SEMANTIC', 0.35),
        'time_decay' => (float) env('FEED_WEIGHT_TIME_DECAY', 0.15),
    ],

];
