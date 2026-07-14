/**
 * Runtime configuration. Override via EXPO_PUBLIC_* env vars (e.g. in a .env
 * file or the shell) without code changes.
 *
 * Default API host is 10.0.2.2 — the Android emulator's alias for the host
 * machine's localhost, where `php artisan serve` runs on port 8000.
 */
export const config = {
  apiBaseUrl: process.env.EXPO_PUBLIC_API_URL ?? 'http://10.0.2.2:8000/api',

  // Demo credentials used to bootstrap a token (no login screen in this phase).
  demo: {
    email: process.env.EXPO_PUBLIC_DEMO_EMAIL ?? 'alice@example.com',
    password: process.env.EXPO_PUBLIC_DEMO_PASSWORD ?? 'password',
  },
} as const;
