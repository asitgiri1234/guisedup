import React, { useMemo } from 'react';
import { StyleSheet, Text, View } from 'react-native';

import { font, radius } from '../theme/theme';

const AVATAR_COLORS = ['#7C4DFF', '#E0447A', '#2FA98C', '#E08A2F', '#4D7CFF', '#B0509C'];

function initialsOf(name: string): string {
  const parts = name.trim().split(/\s+/).filter(Boolean);
  if (parts.length === 0) return '?';
  const letters = parts.slice(0, 2).map((p) => p[0]?.toUpperCase() ?? '');
  return letters.join('') || '?';
}

function colorFor(name: string): string {
  let hash = 0;
  for (let i = 0; i < name.length; i++) {
    hash = (hash * 31 + name.charCodeAt(i)) % 997;
  }
  return AVATAR_COLORS[hash % AVATAR_COLORS.length];
}

interface AvatarProps {
  name: string;
  size?: number;
}

/** Placeholder avatar: initials on a colour derived from the name. */
export function Avatar({ name, size = 44 }: AvatarProps) {
  const background = useMemo(() => colorFor(name), [name]);
  const dimension = { width: size, height: size, borderRadius: radius.pill };

  return (
    <View style={[styles.circle, dimension, { backgroundColor: background }]}>
      <Text style={[styles.initials, { fontSize: size * 0.38 }]}>{initialsOf(name)}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  circle: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  initials: {
    color: '#FFFFFF',
    fontWeight: font.weight.bold,
    letterSpacing: 0.5,
  },
});
