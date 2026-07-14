import React, { useRef, useState } from 'react';
import { Pressable, StyleSheet, Text } from 'react-native';

import type { ReactionCounts, ReactionType } from '../api/types';
import { font, palette, radius, spacing } from '../theme/theme';

const REACTIONS: { type: ReactionType; emoji: string; label: string }[] = [
  { type: 'like', emoji: '❤️', label: 'Like' },
  { type: 'fire', emoji: '🔥', label: 'Fire' },
  { type: 'clap', emoji: '👏', label: 'Clap' },
];

interface ReactionBarProps {
  counts?: ReactionCounts;
  onReact: (type: ReactionType) => Promise<unknown>;
}

export function ReactionBar({ counts, onReact }: ReactionBarProps) {
  const [local, setLocal] = useState<ReactionCounts>({
    like: counts?.like ?? 0,
    fire: counts?.fire ?? 0,
    clap: counts?.clap ?? 0,
  });
  const [picked, setPicked] = useState<Record<ReactionType, boolean>>({
    like: false,
    fire: false,
    clap: false,
  });
  const busy = useRef(false);

  const toggle = async (type: ReactionType) => {
    if (picked[type]) {
      // Un-react locally (no delete endpoint).
      setPicked((p) => ({ ...p, [type]: false }));
      setLocal((c) => ({ ...c, [type]: Math.max(0, c[type] - 1) }));
      return;
    }
    if (busy.current) return;
    busy.current = true;
    setPicked((p) => ({ ...p, [type]: true }));
    setLocal((c) => ({ ...c, [type]: c[type] + 1 }));
    try {
      await onReact(type);
    } catch {
      setPicked((p) => ({ ...p, [type]: false }));
      setLocal((c) => ({ ...c, [type]: Math.max(0, c[type] - 1) }));
    } finally {
      busy.current = false;
    }
  };

  return (
    <>
      {REACTIONS.map(({ type, emoji, label }) => (
        <Pressable
          key={type}
          onPress={() => toggle(type)}
          hitSlop={6}
          accessibilityRole="button"
          accessibilityLabel={label}
          style={({ pressed }) => [
            styles.pill,
            picked[type] && styles.pillActive,
            pressed && styles.pillPressed,
          ]}
        >
          <Text style={styles.emoji}>{emoji}</Text>
          <Text style={[styles.count, picked[type] && styles.countActive]}>{local[type]}</Text>
        </Pressable>
      ))}
    </>
  );
}

const styles = StyleSheet.create({
  pill: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(1.5),
    paddingVertical: spacing(1.5),
    paddingHorizontal: spacing(2.5),
    borderRadius: radius.pill,
    backgroundColor: palette.surfaceAlt,
    borderWidth: 1,
    borderColor: palette.border,
  },
  pillActive: {
    backgroundColor: palette.accentSoft,
    borderColor: palette.accentSoft,
  },
  pillPressed: {
    opacity: 0.7,
  },
  emoji: {
    fontSize: font.size.md,
  },
  count: {
    fontSize: font.size.sm,
    fontWeight: font.weight.semibold,
    color: palette.textMuted,
    minWidth: 12,
    textAlign: 'center',
  },
  countActive: {
    color: palette.accent,
  },
});
