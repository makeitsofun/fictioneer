<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Post {
  /**
   * Get permalink via get_permalink() or custom build for post-like data.
   *
   * @since 5.34.0
   *
   * @param object $chapter    Post-like object with ->ID, ->post_name, ->post_type.
   * @param int    $story_id   Used to get story slug to fill %story_slug% (if enabled).
   * @param bool   $leavename  Optional. Keep %postname% placeholder. Default false.
   *
   * @return string Permalink.
   */

  public static function get_permalink( $chapter, $story_id, $leavename = false ) : string {
    if ( $chapter instanceof \WP_Post ) {
      return get_permalink( $chapter, $leavename );
    }

    $id = isset( $chapter->ID ) ? (int) $chapter->ID : 0;

    if ( $id < 1 ) {
      return '';
    }

    if ( ! get_option( 'permalink_structure' ) ) {
      return add_query_arg( 'p', $id, home_url( '/' ) );
    }

    $chapter_slug = $chapter->post_name ?? '';

    if ( $chapter_slug === '' ) {
      return get_permalink( $id );
    }

    if ( get_option( 'fictioneer_rewrite_chapter_permalinks' ) ) {
      $story_id = fictioneer_validate_id( $story_id, 'fcn_story' );

      if ( $story_id ) {
        $story_slug = get_post_field( 'post_name', $story_id );
      } else {
        return get_permalink( $id );
      }

      $path = 'story/' . rawurlencode( $story_slug ) . '/' . ( $leavename ? '%postname%' : rawurlencode( $chapter_slug ) );
    } else {
      $path = 'chapter/' . ( $leavename ? '%postname%' : rawurlencode( $chapter_slug ) );
    }

    $url = home_url( user_trailingslashit( $path ) );

    static $has_filter = null;

    if ( $has_filter === null ) {
      $has_filter = has_filter( 'fictioneer_filter_custom_permalink' );
    }

    if ( $has_filter ) {
      $url = apply_filters( 'fictioneer_filter_custom_permalink', $url, $chapter, $leavename );
    }

    return $url;
  }

  /**
   * Get post meta via get_post_meta() or from a post-like object.
   *
   * Note: Checks whether the given post is a WP_Post, otherwise
   * it checks whether the object responds to `->meta`.
   *
   * @since 5.34.0
   *
   * @param object     $post      Post-like object or WP_Post.
   * @param string     $key       Meta key.
   * @param mixed|null $default   Default value if meta does not exist.
   *
   * @return mixed Meta value (single).
   */

  public static function get_meta( $post, $key, $default = null ) {
    if ( ! ( $post instanceof \WP_Post ) && isset( $post->meta ) ) {
      if ( is_object( $post->meta ) && property_exists( $post->meta, $key ) ) {
        $value = $post->meta->{$key};

        if ( is_array( $value ) ) {
          return $value[0] ?? $default;
        }

        return $value;
      }

      if ( is_array( $post->meta ) && array_key_exists( $key, $post->meta ) ) {
        $value = $post->meta[ $key ];

        if ( is_array( $value ) ) {
          return $value[0] ?? $default;
        }

        return $value;
      }
    }

    $value = get_post_meta( $post->ID, $key, true );

    return $value !== '' && $value !== null ? $value : $default;
  }
}
