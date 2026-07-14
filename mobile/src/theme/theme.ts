/**
 * Central design tokens. Everything visual references these so the app has a
 * deliberate, consistent look rather than the default React Native styling.
 */

export const palette = {
  bg: '#F6F4F1',
  surface: '#FFFFFF',
  surfaceAlt: '#FBFAF8',
  border: '#EAE5DE',
  borderStrong: '#DCD5CA',
  text: '#201E1B',
  textMuted: '#8A8378',
  textFaint: '#B4ACA0',
  accent: '#7C4DFF',
  accentSoft: '#EFE9FF',
  danger: '#D6503C',
  dangerSoft: '#FBEAE6',
  like: '#E0447A',
  likeSoft: '#FBE6EE',
} as const;

export const spacing = (n: number): number => n * 4;

export const radius = {
  sm: 8,
  md: 14,
  lg: 20,
  pill: 999,
} as const;

export const font = {
  size: {
    xs: 12,
    sm: 13,
    md: 15,
    lg: 17,
    xl: 22,
    xxl: 28,
  },
  weight: {
    regular: '400',
    medium: '500',
    semibold: '600',
    bold: '700',
  },
} as const;

export const shadow = {
  card: {
    shadowColor: '#2B2620',
    shadowOffset: { width: 0, height: 6 },
    shadowOpacity: 0.06,
    shadowRadius: 12,
    elevation: 2,
  },
} as const;

export const theme = { palette, spacing, radius, font, shadow } as const;
