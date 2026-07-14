import { apiRequest } from './http';
import type { Paginated, Post, ReactionType } from './types';

export function fetchFeed(page: number, token: string, signal?: AbortSignal): Promise<Paginated<Post>> {
  return apiRequest<Paginated<Post>>(`/feed?page=${page}`, { token, signal });
}

export function searchPosts(
  query: string,
  page: number,
  token: string,
  signal?: AbortSignal,
): Promise<Paginated<Post>> {
  return apiRequest<Paginated<Post>>(`/search?q=${encodeURIComponent(query)}&page=${page}`, {
    token,
    signal,
  });
}

/** Logs an emoji reaction (like / fire / clap) for the given post. */
export function reactToPost(postId: number, type: ReactionType, token: string): Promise<unknown> {
  return apiRequest('/interactions', {
    method: 'POST',
    token,
    body: { post_id: postId, type },
  });
}
