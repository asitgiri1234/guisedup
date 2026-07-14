import React, { createContext, useCallback, useContext, useEffect, useMemo, useState } from 'react';

import { login } from '../api/auth';
import { config } from '../config';
import { tokenStore } from './tokenStore';

type AuthStatus = 'loading' | 'authenticated' | 'error';

interface AuthValue {
  token: string | null;
  status: AuthStatus;
  error: string | null;
  retry: () => void;
  signOut: () => void;
}

const AuthContext = createContext<AuthValue | undefined>(undefined);

export function AuthProvider({ children }: { children: React.ReactNode }) {
  const [token, setToken] = useState<string | null>(null);
  const [status, setStatus] = useState<AuthStatus>('loading');
  const [error, setError] = useState<string | null>(null);

  const bootstrap = useCallback(async () => {
    setStatus('loading');
    setError(null);
    try {
      const existing = await tokenStore.get();
      if (existing) {
        setToken(existing);
        setStatus('authenticated');
        return;
      }

      // No stored token: obtain one with the demo credentials.
      const result = await login(config.demo.email, config.demo.password);
      await tokenStore.set(result.token);
      setToken(result.token);
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
    void bootstrap();
  }, [bootstrap]);

  const value = useMemo<AuthValue>(
    () => ({ token, status, error, retry: bootstrap, signOut }),
    [token, status, error, bootstrap, signOut],
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
