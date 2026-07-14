<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Embedding Driver
    |--------------------------------------------------------------------------
    |
    | Which embedding implementation to bind to the EmbeddingService contract.
    | Phase 2 ships the "mock" driver. Phase 3 will add an "http" driver that
    | calls the Python microservice — without touching any calling code, since
    | everything depends on the interface.
    |
    */

    'driver' => env('EMBEDDING_DRIVER', 'mock'),

    /*
    |--------------------------------------------------------------------------
    | Vector Dimensions
    |--------------------------------------------------------------------------
    |
    | Dimensionality of the vectors stored in the `posts.embedding` column.
    | This MUST match the migration and the real model used in Phase 3.
    |
    */

    'dimensions' => (int) env('EMBEDDING_DIMENSIONS', 384),

];
