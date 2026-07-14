<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInteractionRequest;
use App\Http\Resources\InteractionResource;
use App\Services\InteractionService;
use Illuminate\Http\JsonResponse;

class InteractionController extends Controller
{
    public function __construct(private readonly InteractionService $interactions)
    {
    }

    /**
     * POST /api/interactions — log an engagement event for the current user.
     */
    public function store(StoreInteractionRequest $request): JsonResponse
    {
        $interaction = $this->interactions->log($request->user(), $request->validated());

        return InteractionResource::make($interaction)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
