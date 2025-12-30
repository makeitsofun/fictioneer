<?php

namespace Fictioneer\SEO;

defined( 'ABSPATH' ) OR exit;

final class Schema {
  /**
   * Initialize hooks.
   *
   * @since 5.34.0
   */

  public static function init() : void {
    if ( ! is_admin() ) {
      add_action( 'wp_head', [ self::class, 'render' ] );
    }

    add_action( 'save_post', [ self::class, 'delete_cache' ], 99, 2 );
  }

  /**
   * Render schema graph on selected pages.
   *
   * @since 5.34.0
   */

  public static function render() : void {
    if ( ! is_singular() || is_front_page() || is_home() || is_search() || is_archive() ) {
      return;
    }

    $post = get_post();

    if ( ! $post ) {
      return;
    }

    $schema = self::get( $post );

    if ( $schema && is_string( $schema ) ) {
      wp_print_inline_script_tag(
        $schema,
        array(
          'id' => 'fictioneer-schema-json',
          'type' => 'application/ld+json',
          'data-jetpack-boost' => 'ignore',
          'data-no-optimize' => '1',
          'data-no-defer' => '1',
          'data-no-minify' => '1'
        )
      );
    }
  }

  /**
   * Get schema graph.
   *
   * @since 4.0.0
   * @since 5.34.0 - Moved into Schema class.
   *
   * @param \WP_Post $post    Post object.
   * @param bool     $cached  Optional. Whether to use the cache. Default true.
   *
   * @return string Encoded JSON or an empty string.
   */

  public static function get( $post, $cached = true ) : string {
    if ( $cached ) {
      $cached_schema = self::get_cache( $post );

      if ( $cached_schema !== null ) {
        return $cached_schema;
      }
    }

    $schema = self::build( $post );

    if ( $cached && $schema !== '' ) {
      self::set_cache( $post, $schema );
    }

    return $schema;
  }

  /**
   * Build schema graph.
   *
   * @since 5.34.0
   *
   * @param \WP_Post $post  Post object.
   *
   * @return string Encoded JSON or an empty string.
   */

  public static function build( $post ) : string {
    $graph = [];
    $graph[] = Schema_Node::website();

    $image_data = Schema_Node::primary_image_data( $post );

    if ( $image_data ) {
      $graph[] = Schema_Node::primary_image( $post, $image_data );
    }

    $graph[] = Schema_Node::webpage( $post, $image_data );

    if ( $post->post_type === 'fcn_chapter' ) {
      $story = Schema_Node::chapter_story( $post );

      if ( ! empty( $story ) ) {
        $graph[] = $story;
      }
    }

    $template = (string) get_page_template_slug( $post->ID );

    if ( in_array( $template, ['chapters.php', 'stories.php', 'recommendations.php', 'collections.php'], true ) ) {
      $list = Schema_Node::item_list( $post );

      if ( ! empty( $list ) ) {
        $graph[] = $list;
      }
    } else {
      $graph[] = Schema_Node::article( $post, $image_data );
    }

    if ( $post->post_type === 'fcn_story' ) {
      $list = Schema_Node::chapter_list( $post );

      if ( $list ) {
        $graph[] = $list;
      }
    }

    $schema = Schema_Node::root();
    $schema['@graph'] = $graph;

    $schema = apply_filters( 'fictioneer_filter_seo_schema', $schema, $post, $image_data );

    $schema = wp_json_encode( $schema, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES );

    return $schema;
  }

  /**
   * Get cached schema graph.
   *
   * @since 5.34.0
   *
   * @param \WP_Post $post  Post object.
   *
   * @return string|null Encoded JSON or null if not cached.
   */

  private static function get_cache( $post ) {
    $meta = get_post_meta( $post->ID, 'fictioneer_schema', true );

    if (
      empty( $meta ) ||
      ! is_array( $meta ) ||
      ! isset( $meta['schema'], $meta['ttl'], $meta['v'] ) ||
      (int) $meta['v'] < 2 ||
      (int) $meta['ttl'] < time() ||
      ! is_string( $meta['schema'] ) ||
      $meta['schema'] === ''
    ) {
      return null;
    }

    return $meta['schema'];
  }

  /**
   * Cache schema graph.
   *
   * @since 5.34.0
   *
   * @param \WP_Post $post    Post object.
   * @param string   $schema  Encoded schema graph JSON.
   */

  private static function set_cache( $post, $schema ) : void {
    $meta = array(
      'v' => 2,
      'ttl' => time() + ( DAY_IN_SECONDS * 3 ),
      'schema' => $schema
    );

    update_post_meta( $post->ID, 'fictioneer_schema', $meta );
  }

  /**
   * Delete cached schema(s).
   *
   * @since 5.34.0
   *
   * @param int      $post_id  Post ID.
   * @param \WP_Post $post     Post object.
   */

  public static function delete_cache( $post_id, $post ) : void {
    if ( fictioneer_save_guard( $post_id ) ) {
      return;
    }

    delete_post_meta( $post_id, 'fictioneer_schema' );

    if ( $post->post_parent ) {
      delete_post_meta( $post->post_parent, 'fictioneer_schema' );
    }
  }
}
