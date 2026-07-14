import { useCallback, useEffect, useRef, useState } from 'react';

import { addComment, fetchComments } from '../api/comments';
import { ApiError } from '../api/http';
import type { Comment, PaginationMeta } from '../api/types';

type CommentsStatus = 'idle' | 'loading' | 'loadingMore' | 'success' | 'empty' | 'error';

export interface UseCommentsResult {
  comments: Comment[];
  status: CommentsStatus;
  error: string | null;
  total: number;
  hasMore: boolean;
  loadMore: () => void;
  retry: () => void;
  add: (body: string) => Promise<void>;
}

/**
 * Loads a post's comments (paginated, newest-first) once `enabled` is true,
 * and posts new ones optimistically to the top of the list.
 */
export function useComments(
  postId: number,
  token: string | null,
  enabled: boolean,
): UseCommentsResult {
  const [comments, setComments] = useState<Comment[]>([]);
  const [meta, setMeta] = useState<PaginationMeta | null>(null);
  const [status, setStatus] = useState<CommentsStatus>('idle');
  const [error, setError] = useState<string | null>(null);
  const inFlight = useRef(false);

  const load = useCallback(
    async (page: number, mode: 'initial' | 'append') => {
      if (!token || inFlight.current) return;
      inFlight.current = true;
      setError(null);
      setStatus(mode === 'append' ? 'loadingMore' : 'loading');
      try {
        const res = await fetchComments(postId, page, token);
        setMeta(res.meta);
        setComments((prev) => (mode === 'append' ? [...prev, ...res.data] : res.data));
        setStatus(mode === 'initial' && res.data.length === 0 ? 'empty' : 'success');
      } catch (e) {
        setError(e instanceof ApiError ? e.message : 'Could not load comments.');
        setStatus('error');
      } finally {
        inFlight.current = false;
      }
    },
    [postId, token],
  );

  useEffect(() => {
    if (!enabled || !token) return;
    setComments([]);
    setMeta(null);
    void load(1, 'initial');
  }, [enabled, token, load]);

  const hasMore = meta ? meta.current_page < meta.last_page : false;

  const loadMore = useCallback(() => {
    if (hasMore && !inFlight.current && meta) void load(meta.current_page + 1, 'append');
  }, [hasMore, meta, load]);

  const add = useCallback(
    async (body: string) => {
      if (!token) throw new Error('Not authenticated');
      const created = await addComment(postId, body, token);
      setComments((prev) => [created, ...prev]);
      setMeta((m) => (m ? { ...m, total: m.total + 1 } : m));
      setStatus('success');
    },
    [postId, token],
  );

  return {
    comments,
    status,
    error,
    total: meta?.total ?? comments.length,
    hasMore,
    loadMore,
    retry: () => void load(1, 'initial'),
    add,
  };
}
