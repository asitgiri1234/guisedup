import React from 'react';
import { Pressable, StyleSheet, Text, TextInput, View } from 'react-native';

import { font, palette, radius, spacing } from '../theme/theme';

interface SearchBarProps {
  value: string;
  onChangeText: (text: string) => void;
  onClear: () => void;
}

export function SearchBar({ value, onChangeText, onClear }: SearchBarProps) {
  return (
    <View style={styles.container}>
      <Text style={styles.icon}>⌕</Text>
      <TextInput
        style={styles.input}
        value={value}
        onChangeText={onChangeText}
        placeholder="Search looks…"
        placeholderTextColor={palette.textFaint}
        autoCapitalize="none"
        autoCorrect={false}
        returnKeyType="search"
        clearButtonMode="never"
      />
      {value.length > 0 && (
        <Pressable onPress={onClear} hitSlop={10} accessibilityLabel="Clear search">
          <Text style={styles.clear}>✕</Text>
        </Pressable>
      )}
    </View>
  );
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(2),
    backgroundColor: palette.surface,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: palette.border,
    paddingHorizontal: spacing(3.5),
    height: 46,
  },
  icon: {
    fontSize: 20,
    color: palette.textMuted,
  },
  input: {
    flex: 1,
    fontSize: font.size.md,
    color: palette.text,
    padding: 0,
  },
  clear: {
    fontSize: font.size.md,
    color: palette.textMuted,
    paddingHorizontal: spacing(1),
  },
});
