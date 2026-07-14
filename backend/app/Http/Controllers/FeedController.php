<?php

namespace App\Http\Controllers;

use App\Http\Resources\PostResource;
use App\Services\FeedService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class FeedController extends Controller
{
    public function __construct(private readonly FeedService $feed) {}

    /**
     * GET /api/feed — ranked, personalised feed for the current user (20 per page).
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        return PostResource::collection($this->feed->paginate($request->user()));
    }
}
