<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Story {
  const META_CACHE_KEY = 'fictioneer_story_data_collection';

  /**
   * Get collection of a story's data.
   *
   * @since 4.3.0
   * @since 5.25.0 - Refactored with custom SQL query.
   * @since 5.33.2 - Refactored, split up, and moved into Story class.
   *
   * @param int|string $story_id       ID of the story.
   * @param bool       $show_comments  Optional. Whether the comment count is needed.
   *                                   Default true.
   * @param array      $args           Optional. Array of additional arguments.
   *
   * @return array|bool Data of the story or false if invalid.
   */

  public static function get_data( int|string $story_id, bool $show_comments = true, array $args = [] ) : array|bool {
    $story_id = fictioneer_validate_id( $story_id, 'fcn_story' );

    if ( ! $story_id ) {
      return false;
    }

    $now = time();

    // Cache?
    $cache = self::get_cached_data_if_fresh( $story_id );

    if ( $cache ) {
      if ( ! $show_comments ) {
        return $cache;
      }

      if ( self::comment_count_refresh_required( $cache, $now, $args ) ) {
        $cache = self::refresh_comment_count( $story_id, $cache, $now );
      }

      return $cache;
    }

    // Build fresh
    $data = self::build_story_data( $story_id, $now );

    self::persist_story_data( $story_id, $data, $show_comments );

    return $data;
  }

  /**
   * Backfill parent ID for chapters that belong to a story.
   *
   * @since 5.33.2
   *
   * @param int|string $story_id     ID of the story.
   * @param array|null $chapter_ids  Optional. Chapter IDs associated with the story.
   *                                 Default null (will be fetched).
   * @param int|null   $limit        Optional. Max number of chapters to update in one call.
   *                                 Default 300. Set to -1 for no limit.
   *
   * @return int Number of affected rows.
   */

  public static function fix_chapter_parents( int|string $story_id, ?array $chapter_ids = null, ?int $limit = null ) : int {
    global $wpdb;

    $story_id = fictioneer_validate_id( $story_id, 'fcn_story' );

    if ( ! $story_id ) {
      return 0;
    }

    if ( $chapter_ids === null ) {
      $chapter_ids = fictioneer_get_story_chapter_ids( $story_id );
    }

    if ( ! $chapter_ids ) {
      return 0;
    }

    $chapter_ids = array_values( array_unique( array_map( 'intval', $chapter_ids ) ) );

    if ( ! $chapter_ids ) {
      return 0;
    }

    if ( $limit === null ) {
      $limit = 300;
    }

    $limit = max( -1, min( 5000, (int) $limit ) );

    if ( $limit > 0 && count( $chapter_ids ) > $limit ) {
      $chapter_ids = array_slice( $chapter_ids, 0, $limit );
    }

    $ids_placeholder = implode( ',', array_fill( 0, count( $chapter_ids ), '%d' ) );
    $args = array_merge( [ $story_id ], $chapter_ids, [ $story_id ] );

    $wpdb->query(
      $wpdb->prepare(
        "UPDATE {$wpdb->posts}
        SET post_parent = %d
        WHERE post_type = 'fcn_chapter'
          AND ID IN ($ids_placeholder)
          AND post_parent <> %d",
        ...$args
      )
    );

    return (int) $wpdb->rows_affected;
  }

  /**
   * Get cached story data if fresh.
   *
   * @since 5.33.2
   *
   * @param int $story_id  ID of the story.
   *
   * @return array|null Story data or null.
   */

  protected static function get_cached_data_if_fresh( int $story_id ) : ?array {
    if ( ! defined( 'FICTIONEER_ENABLE_STORY_DATA_META_CACHE' ) || ! FICTIONEER_ENABLE_STORY_DATA_META_CACHE ) {
      return null;
    }

    $meta_cache = get_post_meta( $story_id, self::META_CACHE_KEY, true );

    if ( empty( $meta_cache ) || ! is_array( $meta_cache ) ) {
      return null;
    }

    $last_modified = strtotime(
      get_post_field( 'post_modified_gmt', $story_id, 'raw' ) ?: get_post_field( 'post_modified', $story_id, 'raw' )
    );

    if ( (int) ( $meta_cache['last_modified'] ?? 0 ) >= (int) $last_modified ) {
      return $meta_cache;
    }

    return null;
  }

  /**
   * Check whether comment count must be updated.
   *
   * @since 5.33.2
   *
   * @param array $cache  Cached story data (if any).
   * @param int   $now    Unix timestamp in seconds.
   * @param array $args   Array of additional arguments.
   *
   * @return bool True if refresh is required, false otherwise.
   */

  protected static function comment_count_refresh_required( array $cache, int $now, array $args ) : bool {
    $delay = (int) ( $cache['comment_count_timestamp'] ?? 0 ) + (int) FICTIONEER_STORY_COMMENT_COUNT_TIMEOUT;
    return $delay < $now || ! empty( $args['refresh_comment_count'] );
  }

  /**
   * Refresh comment count.
   *
   * @since 5.33.2
   *
   * @param int   $story_id  ID of the story.
   * @param array $cache     Cached story data (if any).
   * @param int   $now       Unix timestamp in seconds.
   *
   * @return array Cached story data with updated comment count.
   */

  protected static function refresh_comment_count( int $story_id, array $cache, int $now ) : array {
    $comment_count = (int) ( $cache['comment_count'] ?? 0 );
    $chapter_ids = $cache['chapter_ids'] ?? [];

    if ( $chapter_ids ) {
      $comment_count = fictioneer_get_story_comment_count( $story_id, $chapter_ids );
    }

    $cache['comment_count'] = $comment_count;
    $cache['comment_count_timestamp'] = $now;

    $story_comment_count = (int) ( get_approved_comments( $story_id, array( 'count' => true ) ) ?: 0 );
    $new_total_comment_count = (int) $comment_count + $story_comment_count;
    $old_total_comment_count = (int) get_post_field( 'comment_count', $story_id, 'raw' );

    if ( $old_total_comment_count !== $new_total_comment_count ) {
      fictioneer_sql_update_comment_count( $story_id, $new_total_comment_count );
    }

    if ( defined( 'FICTIONEER_ENABLE_STORY_DATA_META_CACHE' ) && FICTIONEER_ENABLE_STORY_DATA_META_CACHE ) {
      update_post_meta( $story_id, self::META_CACHE_KEY, $cache );
    }

    if ( function_exists( 'fictioneer_purge_post_cache' ) ) {
      fictioneer_purge_post_cache( $story_id );
    }

    return $cache;
  }

  /**
   * Aggregate story data.
   *
   * @since 5.33.2
   *
   * @param int $story_id  ID of the story.
   * @param int $now       Unix timestamp in seconds.
   *
   * @return array Story data.
   */

  protected static function build_story_data( int $story_id, int $now ) : array {
    $status = get_post_meta( $story_id, 'fictioneer_story_status', true );
    $icon = Utils::get_story_status_icon( $status );
    $chapter_ids = fictioneer_get_story_chapter_ids( $story_id );

    $queried_statuses = apply_filters( 'fictioneer_filter_get_story_data_queried_chapter_statuses', ['publish'], $story_id );
    $indexed_statuses = apply_filters( 'fictioneer_filter_get_story_data_indexed_chapter_statuses', ['publish'], $story_id );

    $chapters = $chapter_ids
      ? self::query_chapter_aggregates( $chapter_ids, $queried_statuses, $story_id )
      : [];

    $chapter_count = 0;
    $word_count = 0;
    $comment_count = 0;

    $visible_ids = [];
    $indexed_ids = [];

    foreach ( $chapter_ids as $cid ) {
      if ( empty( $chapters[ $cid ] ) ) {
        continue;
      }

      $c = $chapters[ $cid ];

      $is_hidden = ! empty( $c->is_hidden );
      $is_no_chapter = ! empty( $c->is_no_chapter );

      if ( ! $is_hidden ) {
        if ( ! $is_no_chapter ) {
          $chapter_count++;
          $word_count += (int) ( $c->word_count ?? 0 );
        }

        $visible_ids[] = (int) $cid;

        if ( in_array( $c->post_status, $indexed_statuses, true ) ) {
          $indexed_ids[] = (int) $cid;
        }
      }

      $comment_count += (int) ( $c->comment_count ?? 0 );
    }

    $word_count += (int) get_post_meta( $story_id, '_word_count', true );
    $modified_word_count = fictioneer_multiply_word_count( $word_count );

    $rating = get_post_meta( $story_id, 'fictioneer_story_rating', true ) ?: 'Everyone';

    $tags = get_the_tags( $story_id );
    $fandoms = get_the_terms( $story_id, 'fcn_fandom' );
    $characters = get_the_terms( $story_id, 'fcn_character' );
    $warnings = get_the_terms( $story_id, 'fcn_content_warning' );
    $genres = get_the_terms( $story_id, 'fcn_genre' );

    $last_modified = strtotime(
      get_post_field( 'post_modified_gmt', $story_id, 'raw' ) ?: get_post_field( 'post_modified', $story_id, 'raw' )
    );

    return array(
      'id' => $story_id,
      'chapter_count' => $chapter_count,
      'word_count_raw' => $word_count,
      'word_count' => $modified_word_count,
      'word_count_short' => fictioneer_shorten_number( $modified_word_count ),
      'status' => $status,
      'icon' => $icon,
      'has_taxonomies' => $fandoms || $characters || $genres,
      'tags' => $tags,
      'characters' => $characters,
      'fandoms' => $fandoms,
      'warnings' => $warnings,
      'genres' => $genres,
      'title' => fictioneer_get_safe_title( $story_id, 'utility-get-story-data' ), // Legacy context
      'rating' => $rating,
      'rating_letter' => $rating !== '' ? $rating[0] : '',
      'chapter_ids' => $visible_ids,
      'indexed_chapter_ids' => $indexed_ids,
      'last_modified' => $last_modified,
      'comment_count' => $comment_count,
      'comment_count_timestamp' => $now,
      'redirect' => get_post_meta( $story_id, 'fictioneer_story_redirect_link', true )
    );
  }

  /**
   * Query aggregated data of chapters (delegate).
   *
   * @since 5.33.2
   *
   * @param array $chapter_ids       Chapter IDs.
   * @param array $queried_statuses  Statuses to be queried.
   * @param int   $story_id          ID of the story.
   *
   * @return array Chapter data keyed by ID (unordered).
   */

  protected static function query_chapter_aggregates( array $chapter_ids, array $queried_statuses, int $story_id ) : array {
    if ( ! $queried_statuses ) {
      return [];
    }

    $threshold = (int) apply_filters( 'fictioneer_filter_get_story_data_switch_threshold', 225, $story_id );

    if ( count( $chapter_ids ) <= $threshold ) {
      return self::query_chapter_aggregates_small( $chapter_ids, $queried_statuses, $story_id );
    }

    return self::query_chapter_aggregates_large( $chapter_ids, $queried_statuses, $story_id );
  }

  /**
   * Query aggregated chapter data using multi-joins.
   *
   * @since 5.33.2
   *
   * @param array $chapter_ids       Chapter IDs.
   * @param array $queried_statuses  Statuses to be queried.
   * @param int   $story_id          ID of the story.
   *
   * @return array Chapter data keyed by ID (unordered).
   */

  protected static function query_chapter_aggregates_small(
    array $chapter_ids,
    array $queried_statuses,
    int $story_id
  ) : array {
    global $wpdb;

    $ids_placeholder = implode( ',', array_fill( 0, count( $chapter_ids ), '%d' ) );
    $status_placeholders = implode( ',', array_fill( 0, count( $queried_statuses ), '%s' ) );

    // Select c.ID first so OBJECT_K keys by ID
    $sql = $wpdb->prepare(
      "SELECT
        c.ID,
        c.comment_count,
        c.post_status,
        CAST(wc.meta_value AS UNSIGNED) AS word_count,
        h.meta_value AS is_hidden,
        nc.meta_value AS is_no_chapter
      FROM {$wpdb->posts} c
      LEFT JOIN {$wpdb->postmeta} wc ON wc.post_id = c.ID AND wc.meta_key = '_word_count'
      LEFT JOIN {$wpdb->postmeta} h  ON h.post_id  = c.ID AND h.meta_key  = 'fictioneer_chapter_hidden'
      LEFT JOIN {$wpdb->postmeta} nc ON nc.post_id = c.ID AND nc.meta_key = 'fictioneer_chapter_no_chapter'
      WHERE c.ID IN ($ids_placeholder)
        AND c.post_status IN ($status_placeholders)",
      ...$chapter_ids,
      ...$queried_statuses
    );

    $sql = apply_filters( 'fictioneer_filter_get_story_data_sql', $sql, $story_id, $chapter_ids, $queried_statuses );

    return $wpdb->get_results( $sql, OBJECT_K ) ?: [];
  }

  /**
   * Query aggregated chapter data using a chunked, ID-scoped query.
   *
   * @since 5.33.2
   *
   * @param array $chapter_ids       Chapter IDs.
   * @param array $queried_statuses  Statuses to be queried.
   * @param int   $story_id          ID of the story.
   *
   * @return array Chapter data keyed by ID (unordered).
   */

  protected static function query_chapter_aggregates_large(
    array $chapter_ids,
    array $statuses,
    int $story_id
  ) : array {
    global $wpdb;

    $limit = (int) apply_filters( 'fictioneer_filter_get_story_data_batch_limit', 800, $story_id );
    $limit = max( 100, min( 2000, $limit ) );

    $fetch_for_ids = static function( array $ids ) use ( $wpdb, $story_id, $statuses ) : array {
      if ( ! $ids ) {
        return [];
      }

      $ids = array_values( array_unique( array_map( 'intval', $ids ) ) );
      $ids_placeholder = implode( ',', array_fill( 0, count( $ids ), '%d' ) );
      $status_placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
      $args = array_merge( $ids, $ids, $statuses );

      // Select c.ID first so OBJECT_K keys by ID
      $sql = $wpdb->prepare(
        "SELECT
          c.ID,
          c.comment_count,
          c.post_status,
          CAST(pm.word_count AS UNSIGNED) AS word_count,
          pm.is_hidden,
          pm.is_no_chapter
        FROM {$wpdb->posts} c
        LEFT JOIN (
          SELECT
            post_id,
            MAX(CASE WHEN meta_key = '_word_count' THEN meta_value END) AS word_count,
            MAX(CASE WHEN meta_key = 'fictioneer_chapter_hidden' THEN meta_value END) AS is_hidden,
            MAX(CASE WHEN meta_key = 'fictioneer_chapter_no_chapter' THEN meta_value END) AS is_no_chapter
          FROM {$wpdb->postmeta}
          WHERE post_id IN ($ids_placeholder)
            AND meta_key IN ('_word_count','fictioneer_chapter_hidden','fictioneer_chapter_no_chapter')
          GROUP BY post_id
        ) pm ON pm.post_id = c.ID
        WHERE c.ID IN ($ids_placeholder)
          AND c.post_status IN ($status_placeholders)",
        ...$args
      );

      $sql = apply_filters( 'fictioneer_filter_get_story_data_sql', $sql, $story_id, $ids, $statuses );

      return $wpdb->get_results( $sql, OBJECT_K ) ?: [];
    };

    if ( count( $chapter_ids ) <= $limit ) {
      return $fetch_for_ids( $chapter_ids );
    }

    $result = [];

    foreach ( array_chunk( $chapter_ids, $limit ) as $chunk ) {
      $result += $fetch_for_ids( $chunk );
    }

    return $result;
  }

  /**
   * Cache and save story data.
   *
   * @since 5.33.2
   *
   * @param int   $story_id       ID of the story.
   * @param array $data           Story data.
   * @param bool  $show_comments  Whether the comment count is needed.
   */

  protected static function persist_story_data( int $story_id, array $data, bool $show_comments ) : void {
    // Cache
    if ( defined( 'FICTIONEER_ENABLE_STORY_DATA_META_CACHE' ) && FICTIONEER_ENABLE_STORY_DATA_META_CACHE ) {
      update_post_meta( $story_id, self::META_CACHE_KEY, $data );
    }

    // Update story total word count
    $new_total_words = (int) ( $data['word_count'] ?? 0 );
    $old_total_words = (int) get_post_meta( $story_id, 'fictioneer_story_total_word_count', true );

    if ( $old_total_words !== $new_total_words ) {
      update_post_meta( $story_id, 'fictioneer_story_total_word_count', $new_total_words );
    }

    // Update story total comment count
    if ( $show_comments ) {
      $comment_count = (int) ( $data['comment_count'] ?? 0 );
      $story_comment_count = (int) ( get_approved_comments( $story_id, array( 'count' => true ) ) ?: 0 );
      $new_total_comment_count = (int) $comment_count + $story_comment_count;
      $old_total_comment_count = (int) get_post_field( 'comment_count', $story_id, 'raw' );

      if ( $old_total_comment_count !== $new_total_comment_count ) {
        fictioneer_sql_update_comment_count( $story_id, $new_total_comment_count );
      }
    }
  }
}
