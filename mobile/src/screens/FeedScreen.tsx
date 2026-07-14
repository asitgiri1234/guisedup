import React, { useCallback, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  type ListRenderItemInfo,
  RefreshControl,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { reactToPost } from '../api/posts';
import type { Post } from '../api/types';
import { PostCard } from '../components/PostCard';
import { SearchBar } from '../components/SearchBar';
import { EmptyState, ErrorState, LoadingState } from '../components/StateViews';
import { useAuth } from '../auth/AuthContext';
import { useDebouncedValue } from '../hooks/useDebouncedValue';
import { usePosts } from '../hooks/usePosts';
import { font, palette, spacing } from '../theme/theme';

export function FeedScreen() {
  const { token, status: authStatus, error: authError, retry: retryAuth } = useAuth();

  const [query, setQuery] = useState('');
  const debouncedQuery = useDebouncedValue(query, 350);
  const isSearching = debouncedQuery.trim().length > 0;

  const { posts, status, error, hasMore, total, loadMore, refresh, retry } = usePosts(
    debouncedQuery,
    token,
  );

  const onReact = useCallback(
    (postId: number) => {
      if (!token) return Promise.reject(new Error('Not authenticated'));
      return reactToPost(postId, token);
    },
    [token],
  );

  const renderItem = useCallback(
    ({ item }: ListRenderItemInfo<Post>) => <PostCard post={item} onReact={onReact} />,
    [onReact],
  );

  const listHeader = useMemo(
    () => (
      <View style={styles.header}>
        <Text style={styles.brand}>Guised Up</Text>
        <Text style={styles.subtitle}>
          {isSearching ? 'Search results' : 'Your personalised feed'}
        </Text>
        <View style={styles.searchWrap}>
          <SearchBar value={query} onChangeText={setQuery} onClear={() => setQuery('')} />
        </View>
        {status === 'success' && typeof total === 'number' && (
          <Text style={styles.count}>
            {total} {total === 1 ? 'post' : 'posts'}
          </Text>
        )}
      </View>
    ),
    [isSearching, query, status, total],
  );

  // --- Auth gating -----------------------------------------------------------
  if (authStatus === 'loading') {
    return <LoadingState label="Signing you in…" />;
  }
  if (authStatus === 'error') {
    return <ErrorState message={authError ?? 'Could not sign in.'} onRetry={retryAuth} />;
  }

  // --- Body content depending on the posts request ---------------------------
  const renderEmpty = () => {
    if (status === 'loading') return <LoadingState label="Loading posts…" />;
    if (status === 'error') {
      return <ErrorState message={error ?? 'Could not load posts.'} onRetry={retry} />;
    }
    if (status === 'empty') {
      return isSearching ? (
        <EmptyState
          glyph="⌕"
          title="No matches"
          subtitle={`Nothing found for "${debouncedQuery.trim()}". Try another search.`}
        />
      ) : (
        <EmptyState
          title="Your feed is quiet"
          subtitle="Follow creators and interact with posts to personalise what you see."
        />
      );
    }
    return null;
  };

  return (
    <FlatList
      data={posts}
      renderItem={renderItem}
      keyExtractor={(item) => String(item.id)}
      ListHeaderComponent={listHeader}
      ListEmptyComponent={renderEmpty}
      contentContainerStyle={posts.length === 0 ? styles.contentEmpty : styles.content}
      ItemSeparatorComponent={() => <View style={styles.separator} />}
      onEndReached={loadMore}
      onEndReachedThreshold={0.5}
      showsVerticalScrollIndicator={false}
      keyboardDismissMode="on-drag"
      keyboardShouldPersistTaps="handled"
      refreshControl={
        <RefreshControl
          refreshing={status === 'refreshing'}
          onRefresh={refresh}
          tintColor={palette.accent}
          colors={[palette.accent]}
        />
      }
      ListFooterComponent={
        status === 'loadingMore' ? (
          <View style={styles.footer}>
            <ActivityIndicator color={palette.accent} />
          </View>
        ) : !hasMore && posts.length > 0 ? (
          <Text style={styles.end}>You’re all caught up</Text>
        ) : null
      }
    />
  );
}

const styles = StyleSheet.create({
  content: {
    padding: spacing(4),
    gap: 0,
  },
  contentEmpty: {
    flexGrow: 1,
    padding: spacing(4),
  },
  header: {
    marginBottom: spacing(4),
  },
  brand: {
    fontSize: font.size.xxl,
    fontWeight: font.weight.bold,
    color: palette.text,
    letterSpacing: -0.5,
  },
  subtitle: {
    marginTop: spacing(1),
    fontSize: font.size.sm,
    color: palette.textMuted,
  },
  searchWrap: {
    marginTop: spacing(4),
  },
  count: {
    marginTop: spacing(3),
    fontSize: font.size.xs,
    fontWeight: font.weight.semibold,
    color: palette.textFaint,
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
  separator: {
    height: spacing(3),
  },
  footer: {
    paddingVertical: spacing(6),
  },
  end: {
    textAlign: 'center',
    paddingVertical: spacing(6),
    fontSize: font.size.sm,
    color: palette.textFaint,
  },
});
