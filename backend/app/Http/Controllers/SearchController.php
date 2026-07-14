<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Http\Resources\PostResource;
use App\Services\SearchService;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __construct(private readonly SearchService $search) {}

    /**
     * GET /api/search?q=... — semantic search over posts (20 per page).
     */
    public function index(SearchRequest $request): AnonymousResourceCollection
    {
        $results = $this->search->search($request->validated('q'));

        return PostResource::collection($results);
    }
}
