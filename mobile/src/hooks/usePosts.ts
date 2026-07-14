import { useCallback, useEffect, useRef, useState } from 'react';

import { fetchFeed, searchPosts } from '../api/posts';
import type { PaginationMeta, Post } from '../api/types';
import { ApiError } from '../api/http';

export type PostsStatus =
  | 'idle'
  | 'loading' // first page loading
  | 'refreshing' // pull-to-refresh
  | 'loadingMore' // next page loading
  | 'success'
  | 'empty'
  | 'error';

type LoadMode = 'initial' | 'refresh' | 'append';

export interface UsePostsResult {
  posts: Post[];
  status: PostsStatus;
  error: string | null;
  hasMore: boolean;
  total: number | null;
  loadMore: () => void;
  refresh: () => void;
  retry: () => void;
}

/**
 * Loads a paginated list of posts — the ranked feed when `query` is empty, or
 * semantic search results otherwise — with infinite scrolling. Switching the
 * query resets the list; in-flight responses that arrive out of order are
 * discarded via a monotonically increasing request id.
 */
export function usePosts(query: string, token: string | null): UsePostsResult {
  const trimmed = query.trim();

  const [posts, setPosts] = useState<Post[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [status, setStatus] = useState<PostsStatus>('idle');
  const [error, setError] = useState<string | null>(null);

  const requestId = useRef(0);
  const inFlight = useRef(false);

  const load = useCallback(
    async (page: number, mode: LoadMode) => {
      if (!token || inFlight.current) return;

      const id = ++requestId.current;
      inFlight.current = true;
      setError(null);
      setStatus(mode === 'append' ? 'loadingMore' : mode === 'refresh' ? 'refreshing' : 'loading');

      try {
        const response = trimmed
          ? await searchPosts(trimmed, page, token)
          : await fetchFeed(page, token);

        if (id !== requestId.current) return; // superseded by a newer request

        setMeta(response.meta);
        setPosts((prev) => (mode === 'append' ? [...prev, ...response.data] : response.data));
        setStatus(mode !== 'append' && response.data.length === 0 ? 'empty' : 'success');
      } catch (e) {
        if (id !== requestId.current) return;
        setError(e instanceof ApiError ? e.message : 'Something went wrong.');
        setStatus('error');
      } finally {
        if (id === requestId.current) inFlight.current = false;
      }
    },
    [token, trimmed],
  );

  // (Re)load the first page whenever the query or token changes.
  useEffect(() => {
    if (!token) return;
    requestId.current += 1; // invalidate any in-flight request
    inFlight.current = false;
    setPosts([]);
    setMeta(null);
    void load(1, 'initial');
  }, [trimmed, token, load]);

  const hasMore = meta ? meta.current_page < meta.last_page : false;

  const loadMore = useCallback(() => {
    if (!hasMore || inFlight.current || !meta) return;
    void load(meta.current_page + 1, 'append');
  }, [hasMore, meta, load]);

  const refresh = useCallback(() => {
    void load(1, 'refresh');
  }, [load]);

  const retry = useCallback(() => {
    void load(1, 'initial');
  }, [load]);

  return {
    posts,
    status,
    error,
    hasMore,
    total: meta?.total ?? null,
    loadMore,
    refresh,
    retry,
  };
}
