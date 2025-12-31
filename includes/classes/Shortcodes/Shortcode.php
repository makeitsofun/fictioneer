<?php

namespace Fictioneer\Shortcodes;

defined( 'ABSPATH' ) OR exit;

class Shortcode {
  /**
   * Query result for shortcode.
   *
   * @since 5.4.9
   * @since 5.34.0 - Moved into Base class.
   *
   * @param array $args  Query arguments.
   *
   * @return \WP_Query The query result.
   */

  public static function query( $args ) : \WP_Query {
    $result = new \WP_Query( $args );

    if ( function_exists( 'update_post_thumbnail_cache' ) ) {
      update_post_thumbnail_cache( $result );
    }

    if (
      get_option( 'fictioneer_show_authors' ) &&
      ! empty( $result->posts ) &&
      function_exists( 'update_post_author_caches' )
    ) {
      update_post_author_caches( $result->posts );
    }

    return $result;
  }

  /**
   * Tax query argument for shortcode.
   *
   * @since 5.2.0
   * @since 5.34.0 - Refactored and moved into Base class.
   *
   * @param array $args  Arguments of the shortcode partial.
   *
   * @return array Tax query argument.
   */

  public static function tax_query_args( $args ) : array {
    $taxonomies = $args['taxonomies'] ?? [];

    if ( empty( $taxonomies ) || ! is_array( $taxonomies ) ) {
      return [];
    }

    $tax_query = [];

    $map = array(
      'tags' => 'post_tag',
      'categories' => 'category',
      'fandoms' => 'fcn_fandom',
      'characters' => 'fcn_character',
      'genres' => 'fcn_genre',
    );

    foreach ( $map as $key => $taxonomy ) {
      if ( empty( $taxonomies[ $key ] ) ) {
        continue;
      }

      $tax_query[] = array(
        'taxonomy' => $taxonomy,
        'field' => 'name',
        'terms' => $taxonomies[ $key ],
      );
    }

    if ( count( $tax_query ) > 1 ) {
      $tax_query['relation'] = ( $args['relation'] ?? 'AND' ) === 'OR' ? 'OR' : 'AND';
    }

    return $tax_query;
  }

  /**
   * Inline script to initialize Splide ASAP.
   *
   * Note: The script tag is only returned once in case multiple sliders
   * are active since only one is needed.
   *
   * @since 5.25.0
   * @since 5.26.1 - Use wp_print_inline_script_tag().
   * @since 5.34.0 - Moved into Base class.
   *
   * @return string Inline Splide script.
   */

  public static function splide_inline_script() : string {
    static $done = null;

    if ( $done ) {
      return '';
    }

    $done = true;

    return wp_get_inline_script_tag(
      'document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll(".splide:not(.no-auto-splide, .is-initialized)").forEach(e=>{e.querySelector(".splide__list")&&"undefined"!=typeof Splide&&(e.classList.remove("_splide-placeholder"),new Splide(e).mount())})});',
      array(
        'id' => 'fictioneer-iife-splide',
        'class' => 'temp-script',
        'type' => 'text/javascript',
        'data-jetpack-boost' => 'ignore',
        'data-no-optimize' => '1',
        'data-no-defer' => '1',
        'data-no-minify' => '1'
      )
    );
  }

  /**
   * Whether to enable transients for shortcodes.
   *
   * @since 5.6.3
   * @since 5.23.1 - Do not turn off with cache plugin.
   * @since 5.25.0 - Refactored with option.
   * @since 5.34.0 - Moved into Base class.
   *
   * @param string|null $shortcode  Optional. The shortcode in question.
   *
   * @return bool True if shortcode transients are enabled, false otherwise.
   */

  public static function transients_enabled( $shortcode = null ) : bool {
    global $pagenow;

    if ( is_customize_preview() || is_admin() || $pagenow === 'post.php' ) {
      return false;
    }

    $enabled = FICTIONEER_SHORTCODE_TRANSIENT_EXPIRATION > -1
      && ! get_option( 'fictioneer_disable_shortcode_transients' );

    return (bool) apply_filters( 'fictioneer_filter_enable_shortcode_transients', $enabled, $shortcode );
  }

  /**
   * Transient key for a shortcode.
   *
   * @since 5.34.0
   *
   * @param array       $args  Parsed args (sanitized).
   * @param array|mixed $attr  Raw attributes as passed to shortcode callback.
   *
   * @return string Transient key.
   */

  public static function transient_key( $shortcode, $args, $attr ) : string {
    unset( $args['content'] ); // Could be large; UID is enough

    $raw = is_array( $attr ) ? $attr : array();
    $base = wp_json_encode( array( 'args' => $args, 'attr' => $raw ) );
    $hash = md5( (string) $base );

    return 'fictioneer_shortcode_' . $shortcode . '_' . ( $args['type'] ?? 'default' ) . '_html_' . $hash;
  }

  /**
   * Register shortcodes.
   *
   * @since 5.34.0
   */

  public static function register() : void {
    add_shortcode( 'fictioneer_latest_stories', [ self::class, 'latest_stories' ] );
    add_shortcode( 'fictioneer_latest_chapters', [ self::class, 'latest_chapters' ] );
    add_shortcode( 'fictioneer_latest_updates', [ self::class, 'latest_updates' ] );
    add_shortcode( 'fictioneer_latest_recommendations', [ self::class, 'latest_recommendations' ] );
    add_shortcode( 'fictioneer_showcase', [ self::class, 'showcase' ] );
  }

  /**
   * Shortcode delegate callback for latest stories.
   *
   * @since 5.34.0
   *
   * @param array|string $atts     Raw shortcode attributes.
   * @param string       $content  Enclosed content (if any).
   * @param string       $tag      Shortcode tag name.
   *
   * @return string Shortcode HTML.
   */

  public static function latest_stories( $atts = [], $content = '', $tag = '' ) : string {
    return Latest_Stories::render( $atts, $content, $tag );
  }

   /**
   * Shortcode delegate callback for latest chapters.
   *
   * @since 5.34.0
   *
   * @param array|string $atts     Raw shortcode attributes.
   * @param string       $content  Enclosed content (if any).
   * @param string       $tag      Shortcode tag name.
   *
   * @return string Shortcode HTML.
   */

  public static function latest_chapters( $atts = [], $content = '', $tag = '' ) : string {
    return Latest_Chapters::render( $atts, $content, $tag );
  }

   /**
   * Shortcode delegate callback for latest updates.
   *
   * @since 5.34.0
   *
   * @param array|string $atts     Raw shortcode attributes.
   * @param string       $content  Enclosed content (if any).
   * @param string       $tag      Shortcode tag name.
   *
   * @return string Shortcode HTML.
   */

  public static function latest_updates( $atts = [], $content = '', $tag = '' ) : string {
    return Latest_Updates::render( $atts, $content, $tag );
  }

   /**
   * Shortcode delegate callback for showcases.
   *
   * @since 5.34.0
   *
   * @param array|string $atts     Raw shortcode attributes.
   * @param string       $content  Enclosed content (if any).
   * @param string       $tag      Shortcode tag name.
   *
   * @return string Shortcode HTML.
   */

  public static function showcase( $atts = [], $content = '', $tag = '' ) : string {
    return Showcase::render( $atts, $content, $tag );
  }

   /**
   * Shortcode delegate callback for latest recommendations.
   *
   * @since 5.34.0
   *
   * @param array|string $atts     Raw shortcode attributes.
   * @param string       $content  Enclosed content (if any).
   * @param string       $tag      Shortcode tag name.
   *
   * @return string Shortcode HTML.
   */

  public static function latest_recommendations( $atts = [], $content = '', $tag = '' ) : string {
    return Latest_Recommendations::render( $atts, $content, $tag );
  }
}
