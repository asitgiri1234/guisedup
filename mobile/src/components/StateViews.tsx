import React from 'react';
import { ActivityIndicator, Pressable, StyleSheet, Text, View } from 'react-native';

import { font, palette, radius, spacing } from '../theme/theme';

function Centered({ children }: { children: React.ReactNode }) {
  return <View style={styles.centered}>{children}</View>;
}

export function LoadingState({ label = 'Loading…' }: { label?: string }) {
  return (
    <Centered>
      <ActivityIndicator color={palette.accent} />
      <Text style={styles.subtitle}>{label}</Text>
    </Centered>
  );
}

interface EmptyStateProps {
  title: string;
  subtitle: string;
  glyph?: string;
}

export function EmptyState({ title, subtitle, glyph = '✦' }: EmptyStateProps) {
  return (
    <Centered>
      <Text style={styles.glyph}>{glyph}</Text>
      <Text style={styles.title}>{title}</Text>
      <Text style={styles.subtitle}>{subtitle}</Text>
    </Centered>
  );
}

interface ErrorStateProps {
  message: string;
  onRetry: () => void;
}

export function ErrorState({ message, onRetry }: ErrorStateProps) {
  return (
    <Centered>
      <Text style={[styles.glyph, { color: palette.danger }]}>⚠</Text>
      <Text style={styles.title}>Something went wrong</Text>
      <Text style={styles.subtitle}>{message}</Text>
      <Pressable
        onPress={onRetry}
        accessibilityRole="button"
        style={({ pressed }) => [styles.retry, pressed && styles.retryPressed]}
      >
        <Text style={styles.retryLabel}>Try again</Text>
      </Pressable>
    </Centered>
  );
}

const styles = StyleSheet.create({
  centered: {
    flexGrow: 1,
    alignItems: 'center',
    justifyContent: 'center',
    paddingVertical: spacing(20),
    paddingHorizontal: spacing(8),
    gap: spacing(2),
  },
  glyph: {
    fontSize: 40,
    color: palette.accent,
    marginBottom: spacing(1),
  },
  title: {
    fontSize: font.size.lg,
    fontWeight: font.weight.semibold,
    color: palette.text,
    textAlign: 'center',
  },
  subtitle: {
    fontSize: font.size.sm,
    color: palette.textMuted,
    textAlign: 'center',
    lineHeight: font.size.sm * 1.5,
  },
  retry: {
    marginTop: spacing(3),
    backgroundColor: palette.accent,
    paddingHorizontal: spacing(6),
    paddingVertical: spacing(3),
    borderRadius: radius.pill,
  },
  retryPressed: {
    opacity: 0.8,
  },
  retryLabel: {
    color: '#FFFFFF',
    fontWeight: font.weight.semibold,
    fontSize: font.size.sm,
  },
});
