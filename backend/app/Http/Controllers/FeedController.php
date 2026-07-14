<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Services\FeedService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    public function __construct(private readonly FeedService $feed)
    {
    }

    /**
     * GET /api/feed — ranked, paginated feed (20 per page).
     */
    public function index(): AnonymousResourceCollection
    {
        return PostResource::collection($this->feed->paginate());
    }
}
