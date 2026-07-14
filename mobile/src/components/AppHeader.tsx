import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { font, palette, spacing } from '../theme/theme';
import { Avatar } from './Avatar';

interface AppHeaderProps {
  userName?: string | null;
  subtitle: string;
}

export function AppHeader({ userName, subtitle }: AppHeaderProps) {
  return (
    <View style={styles.container}>
      <View style={styles.textCol}>
        <Text style={styles.brand}>
          Guised<Text style={styles.brandAccent}> Up</Text>
        </Text>
        <Text style={styles.subtitle}>{subtitle}</Text>
      </View>
      {userName ? <Avatar name={userName} size={38} /> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: spacing(4),
    paddingTop: spacing(2),
    paddingBottom: spacing(3),
  },
  textCol: {
    flex: 1,
  },
  brand: {
    fontSize: font.size.xxl,
    fontWeight: font.weight.bold,
    color: palette.text,
    letterSpacing: -0.6,
  },
  brandAccent: {
    color: palette.accent,
  },
  subtitle: {
    marginTop: 2,
    fontSize: font.size.xs,
    color: palette.textMuted,
    textTransform: 'uppercase',
    letterSpacing: 1,
  },
});
