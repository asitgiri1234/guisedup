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
import { AppHeader } from '../components/AppHeader';
import { PostCard } from '../components/PostCard';
import { SearchBar } from '../components/SearchBar';
import { EmptyState, ErrorState, LoadingState } from '../components/StateViews';
import { useAuth } from '../auth/AuthContext';
import { useDebouncedValue } from '../hooks/useDebouncedValue';
import { usePosts } from '../hooks/usePosts';
import { font, palette, spacing } from '../theme/theme';

export function FeedScreen() {
  const { token, user, status: authStatus, error: authError, retry: retryAuth } = useAuth();

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

  const sectionLabel = useMemo(() => {
    if (status !== 'success' && status !== 'loadingMore') return null;
    const label = isSearching ? `Results for “${debouncedQuery.trim()}”` : 'For you';
    return (
      <View style={styles.sectionRow}>
        <Text style={styles.sectionLabel}>{label}</Text>
        {typeof total === 'number' && (
          <Text style={styles.sectionCount}>
            {total} {total === 1 ? 'post' : 'posts'}
          </Text>
        )}
      </View>
    );
  }, [status, isSearching, debouncedQuery, total]);

  const renderEmpty = () => {
    if (status === 'loading') return <LoadingState label="Loading looks…" />;
    if (status === 'error') {
      return <ErrorState message={error ?? 'Could not load posts.'} onRetry={retry} />;
    }
    if (status === 'empty') {
      return isSearching ? (
        <EmptyState
          glyph="⌕"
          title="No matches"
          subtitle={`Nothing found for “${debouncedQuery.trim()}”. Try another search.`}
        />
      ) : (
        <EmptyState
          title="Your feed is quiet"
          subtitle="Follow creators and react to posts to personalise what you see."
        />
      );
    }
    return null;
  };

  const body = () => {
    if (authStatus === 'loading') return <LoadingState label="Signing you in…" />;
    if (authStatus === 'error') {
      return <ErrorState message={authError ?? 'Could not sign in.'} onRetry={retryAuth} />;
    }

    return (
      <FlatList
        data={posts}
        renderItem={renderItem}
        keyExtractor={(item) => String(item.id)}
        ListHeaderComponent={sectionLabel}
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
  };

  return (
    <View style={styles.screen}>
      <AppHeader userName={user?.name} subtitle={isSearching ? 'Search results' : 'Style feed'} />
      <View style={styles.searchWrap}>
        <SearchBar value={query} onChangeText={setQuery} onClear={() => setQuery('')} />
      </View>
      <View style={styles.body}>{body()}</View>
    </View>
  );
}

const styles = StyleSheet.create({
  screen: {
    flex: 1,
    backgroundColor: palette.bg,
  },
  searchWrap: {
    paddingHorizontal: spacing(4),
    paddingBottom: spacing(3),
  },
  body: {
    flex: 1,
  },
  content: {
    paddingHorizontal: spacing(4),
    paddingTop: spacing(1),
    paddingBottom: spacing(10),
  },
  contentEmpty: {
    flexGrow: 1,
    paddingHorizontal: spacing(4),
  },
  sectionRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingBottom: spacing(3),
  },
  sectionLabel: {
    fontSize: font.size.sm,
    fontWeight: font.weight.semibold,
    color: palette.text,
  },
  sectionCount: {
    fontSize: font.size.xs,
    color: palette.textFaint,
    textTransform: 'uppercase',
    letterSpacing: 0.6,
  },
  separator: {
    height: spacing(4),
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
