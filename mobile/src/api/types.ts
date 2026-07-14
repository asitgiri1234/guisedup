export interface Author {
  id: number;
  name: string;
}

export interface Post {
  id: number;
  caption: string;
  image_url: string | null;
  interactions_count?: number;
  author: Author;
  ranking_score?: number;
  created_at: string;
}

export interface PaginationMeta {
  current_page: number;
  last_page: number;
  per_page: number;
  total: number;
}

export interface Paginated<T> {
  data: T[];
  meta: PaginationMeta;
}
