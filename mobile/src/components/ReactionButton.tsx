import React, { useRef } from 'react';
import { Animated, Pressable, StyleSheet, Text } from 'react-native';

import { font, palette, radius, spacing } from '../theme/theme';

interface ReactionButtonProps {
  count: number;
  liked: boolean;
  onPress: () => void;
}

/** A like button with a heart glyph, count, and a small press animation. */
export function ReactionButton({ count, liked, onPress }: ReactionButtonProps) {
  const scale = useRef(new Animated.Value(1)).current;

  const handlePress = () => {
    Animated.sequence([
      Animated.spring(scale, { toValue: 1.3, useNativeDriver: true, speed: 40, bounciness: 12 }),
      Animated.spring(scale, { toValue: 1, useNativeDriver: true, speed: 40, bounciness: 12 }),
    ]).start();
    onPress();
  };

  return (
    <Pressable
      onPress={handlePress}
      hitSlop={8}
      accessibilityRole="button"
      accessibilityLabel={liked ? 'Liked' : 'Like'}
      style={({ pressed }) => [
        styles.button,
        liked && styles.buttonLiked,
        pressed && styles.buttonPressed,
      ]}
    >
      <Animated.Text style={[styles.heart, liked && styles.heartLiked, { transform: [{ scale }] }]}>
        {liked ? '♥' : '♡'}
      </Animated.Text>
      <Text style={[styles.count, liked && styles.countLiked]}>{count}</Text>
    </Pressable>
  );
}

const styles = StyleSheet.create({
  button: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(1.5),
    paddingVertical: spacing(1.5),
    paddingHorizontal: spacing(3),
    borderRadius: radius.pill,
    backgroundColor: palette.surfaceAlt,
    borderWidth: 1,
    borderColor: palette.border,
  },
  buttonLiked: {
    backgroundColor: palette.likeSoft,
    borderColor: palette.likeSoft,
  },
  buttonPressed: {
    opacity: 0.7,
  },
  heart: {
    fontSize: font.size.lg,
    color: palette.textMuted,
    lineHeight: font.size.lg + 2,
  },
  heartLiked: {
    color: palette.like,
  },
  count: {
    fontSize: font.size.sm,
    fontWeight: font.weight.semibold,
    color: palette.textMuted,
  },
  countLiked: {
    color: palette.like,
  },
});
