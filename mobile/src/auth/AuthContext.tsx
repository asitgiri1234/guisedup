import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

import { fetchMe, login } from '../api/auth';
import { config } from '../config';
import { tokenStore } from './tokenStore';

type AuthStatus = 'loading' | 'authenticated' | 'error';

export interface AuthUser {
  id: number;
  name: string;
}

interface AuthValue {
  token: string | null;
  user: AuthUser | null;
  status: AuthStatus;
  error: string | null;
  retry: () => void;
  signOut: () => void;
}

const AuthContext = createContext<AuthValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState<string | null>(null);
  const [user, setUser] = useState<AuthUser | null>(null);
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [error, setError] = useState<string | null>(null);

  const bootstrap = useCallback(async () => {
    setStatus('loading');
    setError(null);
    try {
      // Reuse a stored token only if it still validates — this self-heals after
      // the backend is re-seeded (which revokes old tokens).
      const existing = await tokenStore.get();
      if (existing) {
        try {
          const me = await fetchMe(existing);
          setToken(existing);
          setUser(me);
          setStatus('authenticated');
          return;
        } catch {
          await tokenStore.clear();
        }
      }

      // Obtain a fresh token with the demo credentials (no login screen).
      const result = await login(config.demo.email, config.demo.password);
      await tokenStore.set(result.token);
      setToken(result.token);
      setUser(result.user);
      setStatus('authenticated');
    } catch (e) {
      setError(e instanceof Error ? e.message : 'Could not sign in.');
      setStatus('error');
    }
  }, []);

  useEffect(() => {
    void bootstrap();
  }, [bootstrap]);

  const signOut = useCallback(() => {
    void tokenStore.clear();
    setToken(null);
    setUser(null);
    void bootstrap();
  }, [bootstrap]);

  const value = useMemo<AuthValue>(
    () => ({ token, user, status, error, retry: bootstrap, signOut }),
    [token, user, status, error, bootstrap, signOut],
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuth(): AuthValue {
  const ctx = useContext(AuthContext);
  if (!ctx) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return ctx;
}
