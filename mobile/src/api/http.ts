import { config } from '../config';

export class ApiError extends Error {
  constructor(
    message: string,
    public readonly status: number,
  ) {
    super(message);
    this.name = 'ApiError';
  }
}

interface RequestOptions {
  method?: 'GET' | 'POST' | 'PUT' | 'DELETE';
  body?: unknown;
  token?: string | null;
  signal?: AbortSignal;
}

/**
 * Thin fetch wrapper: sets JSON headers, attaches the bearer token, and turns
 * non-2xx responses into a typed {@link ApiError}.
 */
export async function apiRequest<T>(path: string, options: RequestOptions = {}): Promise<T> {
  const headers: Record<string, string> = { Accept: 'application/json' };

  if (options.body !== undefined) {
    headers['Content-Type'] = 'application/json';
  }
  if (options.token) {
    headers.Authorization = `Bearer ${options.token}`;
  }

  let response: Response;
  try {
    response = await fetch(`${config.apiBaseUrl}${path}`, {
      method: options.method ?? 'GET',
      headers,
      body: options.body !== undefined ? JSON.stringify(options.body) : undefined,
      signal: options.signal,
    });
  } catch {
    throw new ApiError('Network error — is the API reachable?', 0);
  }

  const raw = await response.text();
  const parsed = raw ? (JSON.parse(raw) as unknown) : null;

  if (!response.ok) {
    const message =
      (parsed as { message?: string } | null)?.message ?? `Request failed (${response.status})`;
    throw new ApiError(message, response.status);
  }

  return parsed as T;
}
