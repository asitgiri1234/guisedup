import { apiRequest } from './http';
import type { Comment, Paginated } from './types';

export function fetchComments(
  postId: number,
  page: number,
  token: string,
  signal?: AbortSignal,
): Promise<Paginated<Comment>> {
  return apiRequest<Paginated<Comment>>(`/posts/${postId}/comments?page=${page}`, { token, signal });
}

export async function addComment(postId: number, body: string, token: string): Promise<Comment> {
  const response = await apiRequest<{ data: Comment }>(`/posts/${postId}/comments`, {
    method: 'POST',
    token,
    body: { body },
  });
  return response.data;
}
