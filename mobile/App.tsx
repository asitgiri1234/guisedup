import { StatusBar } from 'expo-status-bar';
import { Platform, StatusBar as RNStatusBar, SafeAreaView, StyleSheet } from 'react-native';

import { AuthProvider } from './src/auth/AuthContext';
import { FeedScreen } from './src/screens/FeedScreen';
import { palette } from './src/theme/theme';

export default function App() {
  return (
    <SafeAreaView style={styles.root}>
      <StatusBar style="dark" />
      <AuthProvider>
        <FeedScreen />
      </AuthProvider>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  root: {
    flex: 1,
    backgroundColor: palette.bg,
    paddingTop: Platform.OS === 'android' ? RNStatusBar.currentHeight : 0,
  },
});
