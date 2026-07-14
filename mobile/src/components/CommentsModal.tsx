import React, { useState } from 'react';
import {
  FlatList,
  KeyboardAvoidingView,
  type ListRenderItemInfo,
  Modal,
  Platform,
  Pressable,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';

import type { Comment } from '../api/types';
import { useComments } from '../hooks/useComments';
import { font, palette, radius, spacing } from '../theme/theme';
import { timeAgo } from '../utils/timeAgo';
import { Avatar } from './Avatar';
import { EmptyState, ErrorState, LoadingState } from './StateViews';

interface CommentsModalProps {
  visible: boolean;
  onClose: () => void;
  postId: number;
  token: string | null;
  onAdded?: () => void;
}

export function CommentsModal({ visible, onClose, postId, token, onAdded }: CommentsModalProps) {
  const { comments, status, error, total, loadMore, retry, add } = useComments(
    postId,
    token,
    visible,
  );
  const [text, setText] = useState('');
  const [sending, setSending] = useState(false);

  const submit = async () => {
    const body = text.trim();
    if (!body || sending) return;
    setSending(true);
    try {
      await add(body);
      setText('');
      onAdded?.();
    } catch {
      // keep the text so the user can retry
    } finally {
      setSending(false);
    }
  };

  const renderItem = ({ item }: ListRenderItemInfo<Comment>) => (
    <View style={styles.row}>
      <Avatar name={item.author.name} size={34} />
      <View style={styles.rowText}>
        <Text style={styles.author}>
          {item.author.name} <Text style={styles.time}>· {timeAgo(item.created_at)}</Text>
        </Text>
        <Text style={styles.body}>{item.body}</Text>
      </View>
    </View>
  );

  const renderEmpty = () => {
    if (status === 'loading') return <LoadingState label="Loading comments…" />;
    if (status === 'error') return <ErrorState message={error ?? 'Failed to load.'} onRetry={retry} />;
    if (status === 'empty') {
      return <EmptyState glyph="💬" title="No comments yet" subtitle="Be the first to say something." />;
    }
    return null;
  };

  return (
    <Modal visible={visible} animationType="slide" transparent onRequestClose={onClose}>
      <KeyboardAvoidingView
        style={styles.fill}
        behavior={Platform.OS === 'ios' ? 'padding' : undefined}
      >
        <Pressable style={styles.backdrop} onPress={onClose} accessibilityLabel="Close comments" />
        <View style={styles.sheet}>
          <View style={styles.handle} />
          <View style={styles.header}>
            <Text style={styles.title}>Comments</Text>
            <Text style={styles.total}>{total}</Text>
            <View style={styles.spacer} />
            <Pressable onPress={onClose} hitSlop={10}>
              <Text style={styles.close}>✕</Text>
            </Pressable>
          </View>

          <FlatList
            data={comments}
            renderItem={renderItem}
            keyExtractor={(item) => String(item.id)}
            ListEmptyComponent={renderEmpty}
            contentContainerStyle={comments.length === 0 ? styles.listEmpty : styles.list}
            ItemSeparatorComponent={() => <View style={styles.separator} />}
            onEndReached={loadMore}
            onEndReachedThreshold={0.5}
            keyboardShouldPersistTaps="handled"
            showsVerticalScrollIndicator={false}
          />

          <View style={styles.composer}>
            <TextInput
              style={styles.input}
              value={text}
              onChangeText={setText}
              placeholder="Add a comment…"
              placeholderTextColor={palette.textFaint}
              multiline
            />
            <Pressable
              onPress={submit}
              disabled={!text.trim() || sending}
              style={({ pressed }) => [
                styles.send,
                (!text.trim() || sending) && styles.sendDisabled,
                pressed && styles.sendPressed,
              ]}
            >
              <Text style={styles.sendLabel}>Send</Text>
            </Pressable>
          </View>
        </View>
      </KeyboardAvoidingView>
    </Modal>
  );
}

const styles = StyleSheet.create({
  fill: {
    flex: 1,
  },
  backdrop: {
    flex: 1,
    backgroundColor: palette.overlay,
  },
  sheet: {
    height: '76%',
    backgroundColor: palette.bg,
    borderTopLeftRadius: radius.lg,
    borderTopRightRadius: radius.lg,
    paddingTop: spacing(2),
  },
  handle: {
    alignSelf: 'center',
    width: 40,
    height: 4,
    borderRadius: radius.pill,
    backgroundColor: palette.borderStrong,
    marginBottom: spacing(2),
  },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: spacing(2),
    paddingHorizontal: spacing(4),
    paddingBottom: spacing(3),
    borderBottomWidth: 1,
    borderBottomColor: palette.border,
  },
  title: {
    fontSize: font.size.lg,
    fontWeight: font.weight.bold,
    color: palette.text,
  },
  total: {
    fontSize: font.size.sm,
    color: palette.textMuted,
  },
  spacer: {
    flex: 1,
  },
  close: {
    fontSize: font.size.lg,
    color: palette.textMuted,
  },
  list: {
    padding: spacing(4),
  },
  listEmpty: {
    flexGrow: 1,
  },
  row: {
    flexDirection: 'row',
    gap: spacing(3),
  },
  rowText: {
    flex: 1,
  },
  author: {
    fontSize: font.size.sm,
    fontWeight: font.weight.semibold,
    color: palette.text,
  },
  time: {
    fontWeight: font.weight.regular,
    color: palette.textMuted,
  },
  body: {
    marginTop: 2,
    fontSize: font.size.md,
    lineHeight: font.size.md * 1.4,
    color: palette.text,
  },
  separator: {
    height: spacing(4),
  },
  composer: {
    flexDirection: 'row',
    alignItems: 'flex-end',
    gap: spacing(2),
    paddingHorizontal: spacing(4),
    paddingVertical: spacing(3),
    borderTopWidth: 1,
    borderTopColor: palette.border,
    backgroundColor: palette.surface,
  },
  input: {
    flex: 1,
    maxHeight: 100,
    fontSize: font.size.md,
    color: palette.text,
    backgroundColor: palette.surfaceAlt,
    borderRadius: radius.md,
    borderWidth: 1,
    borderColor: palette.border,
    paddingHorizontal: spacing(3),
    paddingVertical: spacing(2.5),
  },
  send: {
    paddingHorizontal: spacing(4),
    paddingVertical: spacing(3),
    borderRadius: radius.pill,
    backgroundColor: palette.accent,
  },
  sendDisabled: {
    opacity: 0.4,
  },
  sendPressed: {
    opacity: 0.8,
  },
  sendLabel: {
    color: '#FFFFFF',
    fontWeight: font.weight.semibold,
    fontSize: font.size.sm,
  },
});
