<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Services\PostService;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function __construct(private readonly PostService $posts)
    {
    }

    /**
     * POST /api/posts — create a post authored by the current user.
     */
    public function store(StorePostRequest $request): JsonResponse
    {
        $post = $this->posts->create($request->user(), $request->validated());

        return PostResource::make($post->load('user'))
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
