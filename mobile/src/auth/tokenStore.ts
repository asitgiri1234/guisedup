import * as SecureStore from 'expo-secure-store';
import { Platform } from 'react-native';

const KEY = 'guisedup.auth.token';

// SecureStore is unavailable on web; fall back to an in-memory value there so
// the app still runs in a browser during development.
let memoryToken: string | null = null;
const isWeb = Platform.OS === 'web';

export const tokenStore = {
  async get(): Promise<string | null> {
    if (isWeb) return memoryToken;
    return SecureStore.getItemAsync(KEY);
  },
  async set(token: string): Promise<void> {
    if (isWeb) {
      memoryToken = token;
      return;
    }
    await SecureStore.setItemAsync(KEY, token);
  },
  async clear(): Promise<void> {
    if (isWeb) {
      memoryToken = null;
      return;
    }
    await SecureStore.deleteItemAsync(KEY);
  },
};
