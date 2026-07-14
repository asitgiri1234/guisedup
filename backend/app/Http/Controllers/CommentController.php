<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCommentRequest;
use App\Http\Resources\CommentResource;
use App\Models\Post;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CommentController extends Controller
{
    public function __construct(private readonly CommentService $comments) {}

    /**
     * GET /api/posts/{post}/comments — newest-first, paginated (20 per page).
     */
    public function index(Post $post): AnonymousResourceCollection
    {
        return CommentResource::collection($this->comments->paginate($post));
    }

    /**
     * POST /api/posts/{post}/comments — add a comment to the post.
     */
    public function store(StoreCommentRequest $request, Post $post): JsonResponse
    {
        $comment = $this->comments->create($request->user(), $post, $request->validated('body'));

        return CommentResource::make($comment)
            ->response()
            ->setStatusCode(JsonResponse::HTTP_CREATED);
    }
}
