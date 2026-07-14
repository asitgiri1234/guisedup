import React, { useRef, useState } from 'react';
import { StyleSheet, Text, View } from 'react-native';

import type { Post } from '../api/types';
import { font, palette, radius, shadow, spacing } from '../theme/theme';
import { timeAgo } from '../utils/timeAgo';
import { Avatar } from './Avatar';
import { ReactionButton } from './ReactionButton';

interface PostCardProps {
  post: Post;
  onReact: (postId: number) => Promise<unknown>;
}

function PostCardComponent({ post, onReact }: PostCardProps) {
  const [liked, setLiked] = useState(false);
  const [count, setCount] = useState(post.interactions_count ?? 0);
  const busy = useRef(false);

  const toggle = async () => {
    // Unlike is local-only (no delete endpoint); like is optimistic + persisted.
    if (liked) {
      setLiked(false);
      setCount((c) => Math.max(0, c - 1));
      return;
    }
    if (busy.current) return;
    busy.current = true;
    setLiked(true);
    setCount((c) => c + 1);
    try {
      await onReact(post.id);
    } catch {
      setLiked(false);
      setCount((c) => Math.max(0, c - 1));
    } finally {
      busy.current = false;
    }
  };

  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <Avatar name={post.author.name} />
        <View style={styles.headerText}>
          <Text style={styles.username} numberOfLines={1}>
            {post.author.name}
          </Text>
          <Text style={styles.meta}>{timeAgo(post.created_at)}</Text>
        </View>
        {typeof post.ranking_score === 'number' && (
          <View style={styles.scoreChip}>
            <Text style={styles.scoreText}>★ {post.ranking_score.toFixed(2)}</Text>
          </View>
        )}
      </View>

      <Text style={styles.caption}>{post.caption}</Text>

      <View style={styles.footer}>
        <ReactionButton count={count} liked={liked} onPress={toggle} />
      </View>
    </View>
  );
}

export const PostCard = React.memo(PostCardComponent);

const styles = StyleSheet.create({
  card: {
    backgroundColor: palette.surface,
    borderRadius: radius.lg,
    padding: spacing(4),
    borderWidth: 1,
    borderColor: palette.border,
    ...shadow.card,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(3),
  },
  headerText: {
    flex: 1,
  },
  username: {
    fontSize: font.size.md,
    fontWeight: font.weight.semibold,
    color: palette.text,
  },
  meta: {
    marginTop: 2,
    fontSize: font.size.xs,
    color: palette.textMuted,
  },
  scoreChip: {
    backgroundColor: palette.accentSoft,
    borderRadius: radius.pill,
    paddingHorizontal: spacing(2.5),
    paddingVertical: spacing(1),
  },
  scoreText: {
    fontSize: font.size.xs,
    fontWeight: font.weight.semibold,
    color: palette.accent,
  },
  caption: {
    marginTop: spacing(3),
    fontSize: font.size.md,
    lineHeight: font.size.md * 1.45,
    color: palette.text,
  },
  footer: {
    marginTop: spacing(3.5),
    flexDirection: 'row',
    alignItems: 'center',
  },
});
