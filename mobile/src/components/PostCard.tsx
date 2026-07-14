import { Image } from 'expo-image';
import React, { useState } from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { reactToPost } from '../api/posts';
import type { Post, ReactionType } from '../api/types';
import { font, palette, radius, shadow, spacing } from '../theme/theme';
import { timeAgo } from '../utils/timeAgo';
import { Avatar } from './Avatar';
import { CommentsModal } from './CommentsModal';
import { ReactionBar } from './ReactionBar';

interface PostCardProps {
  post: Post;
  token: string | null;
}

function PostCardComponent({ post, token }: PostCardProps) {
  const [imageFailed, setImageFailed] = useState(false);
  const [commentsOpen, setCommentsOpen] = useState(false);
  const [commentCount, setCommentCount] = useState(post.comments_count ?? 0);

  const onReact = (type: ReactionType) => {
    if (!token) return Promise.reject(new Error('Not authenticated'));
    return reactToPost(post.id, type, token);
  };

  const showImage = Boolean(post.image_url) && !imageFailed;

  return (
    <View style={styles.card}>
      <View style={styles.header}>
        <Avatar name={post.author.name} size={40} />
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

      {showImage ? (
        <Image
          source={post.image_url}
          style={styles.image}
          contentFit="cover"
          transition={220}
          onError={() => setImageFailed(true)}
          accessibilityLabel={post.caption}
        />
      ) : (
        <View style={[styles.image, styles.imageFallback]}>
          <Text style={styles.fallbackGlyph}>✦</Text>
        </View>
      )}

      <View style={styles.body}>
        <View style={styles.actions}>
          <ReactionBar counts={post.reactions} onReact={onReact} />
          <View style={styles.actionsSpacer} />
          <Pressable
            onPress={() => setCommentsOpen(true)}
            hitSlop={6}
            accessibilityRole="button"
            accessibilityLabel="Comments"
            style={({ pressed }) => [styles.commentButton, pressed && styles.commentPressed]}
          >
            <Text style={styles.commentGlyph}>💬</Text>
            <Text style={styles.commentCount}>{commentCount}</Text>
          </Pressable>
        </View>

        <Text style={styles.caption}>{post.caption}</Text>

        {commentCount > 0 && (
          <Pressable onPress={() => setCommentsOpen(true)}>
            <Text style={styles.viewComments}>
              View {commentCount === 1 ? '1 comment' : `all ${commentCount} comments`}
            </Text>
          </Pressable>
        )}
      </View>

      <CommentsModal
        visible={commentsOpen}
        onClose={() => setCommentsOpen(false)}
        postId={post.id}
        token={token}
        onAdded={() => setCommentCount((c) => c + 1)}
      />
    </View>
  );
}

export const PostCard = React.memo(PostCardComponent);

const styles = StyleSheet.create({
  card: {
    backgroundColor: palette.surface,
    borderRadius: radius.lg,
    borderWidth: 1,
    borderColor: palette.border,
    overflow: 'hidden',
    ...shadow.card,
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(3),
    paddingHorizontal: spacing(4),
    paddingVertical: spacing(3.5),
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
  image: {
    width: '100%',
    aspectRatio: 4 / 5,
    backgroundColor: palette.imagePlaceholder,
  },
  imageFallback: {
    alignItems: 'center',
    justifyContent: 'center',
  },
  fallbackGlyph: {
    fontSize: 44,
    color: palette.textFaint,
  },
  body: {
    paddingHorizontal: spacing(4),
    paddingTop: spacing(3),
    paddingBottom: spacing(4),
  },
  actions: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(2),
  },
  actionsSpacer: {
    flex: 1,
  },
  commentButton: {
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
  commentPressed: {
    opacity: 0.7,
  },
  commentGlyph: {
    fontSize: font.size.md,
  },
  commentCount: {
    fontSize: font.size.sm,
    fontWeight: font.weight.semibold,
    color: palette.textMuted,
    minWidth: 12,
    textAlign: 'center',
  },
  caption: {
    marginTop: spacing(3),
    fontSize: font.size.md,
    lineHeight: font.size.md * 1.45,
    color: palette.text,
  },
  viewComments: {
    marginTop: spacing(2),
    fontSize: font.size.sm,
    color: palette.textMuted,
  },
});
