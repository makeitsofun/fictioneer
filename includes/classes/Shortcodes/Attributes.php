<?php
namespace Fictioneer\Shortcodes;

use Fictioneer\Sanitizer;
use Fictioneer\Utils;

defined( 'ABSPATH' ) || exit;

final class Attributes {
  private static $defaults = null;
  private static $card_image_style = null;

  /**
   * Cast a value to boolean with an optional default.
   *
   * @since 5.34.0
   *
   * @param mixed $value    Raw value.
   * @param bool  $default  Optional. Default if value is empty.
   *
   * @return bool Parsed boolean value.
   */

  private static function bool( $value, $default = false ) : bool {
    if ( $value === null || $value === '' ) {
      return $default;
    }

    return filter_var( $value, FILTER_VALIDATE_BOOLEAN );
  }

  /**
   * Return the card image style theme mod (cached).
   *
   * @since 5.34.0
   *
   * @return string Card image style.
   */

  private static function card_image_style() : string {
    if ( self::$card_image_style === null ) {
      self::$card_image_style = (string) get_theme_mod( 'card_image_style', 'default' );
    }

    return self::$card_image_style;
  }

  /**
   * Parse and validate Splide configuration JSON.
   *
   * @since 5.34.0
   *
   * @param string $raw  Raw Splide JSON string.
   *
   * @return string|false JSON-encoded configuration or false on failure.
   */

  private static function parse_splide( $raw ) {
    $raw = trim( $raw );

    if ( $raw === '' ) {
      return '';
    }

    $raw = str_replace( "'", '"', $raw );

    if ( ! Utils::json_validate( $raw ) ) {
      return false;
    }

    $splide = json_decode( $raw, true );

    if ( ! is_array( $splide ) ) {
      return false;
    }

    if ( ! isset( $splide['arrows'] ) ) {
      $splide['arrows'] = false;
    }

    if ( ! isset( $splide['arrowPath'] ) ) {
      $splide['arrowPath'] =
        'M31.89 18.24c0.98 0.98 0.98 2.56 0 3.54l-15 15c-0.98 0.98-2.56 0.98-3.54 0s-0.98-2.56 0-3.54L26.45 20 13.23 6.76c-0.98-0.98-0.98-2.56 0-3.54s2.56-0.98 3.54 0l15 15';
    }

    return wp_json_encode( $splide );
  }

  /**
   * Default attribute pairs for shortcode_atts().
   *
   * @since 5.34.0
   *
   * @return array Default attribute pairs.
   */

  public static function defaults() : array {
    if ( self::$defaults !== null ) {
      return self::$defaults;
    }

    self::$defaults = array(
      'uid' => '',
      'type' => 'default',
      'simple' => false,
      'single' => false,
      'spoiler' => false,
      'spotlight' => false,
      'count' => -1,
      'offset' => 0,
      'order' => '',
      'orderby' => '',
      'page' => 1,
      'per_page' => (int) get_option( 'posts_per_page' ),
      'posts_per_page' => (int) get_option( 'posts_per_page' ),
      'post_status' => 'publish',
      'post_ids' => '',
      'author' => '',
      'author_ids' => '',
      'excluded_authors' => '',
      'excluded_tags' => '',
      'excluded_cats' => '',
      'tags' => '',
      'categories' => '',
      'fandoms' => '',
      'genres' => '',
      'characters' => '',
      'taxonomies' => '',
      'rel' => 'AND',
      'relation' => 'AND',
      'ignore_sticky' => false,
      'ignore_protected' => false,
      'only_protected' => false,
      'vertical' => false,
      'seamless' => ( self::card_image_style() === 'seamless' ),
      'aspect_ratio' => '',
      'thumbnail' => ( self::card_image_style() !== 'none' ),
      'lightbox' => true,
      'words' => true,
      'date' => true,
      'date_format' => '',
      'nested_date_format' => '',
      'footer' => true,
      'footer_author' => true,
      'footer_chapters' => true,
      'footer_words' => true,
      'footer_date' => true,
      'footer_comments' => true,
      'footer_status' => true,
      'footer_rating' => true,
      'classes' => '',
      'class' => '',
      'infobox' => true,
      'source' => true,
      'splide' => '',
      'cache' => true,
      'terms' => 'inline',
      'max_terms' => 10,
      'height' => '',
      'min_width' => '',
      'quality' => '',
      'no_cap' => '',
      'for' => ''
    );

    return self::$defaults;
  }

  /**
   * Extract taxonomies from shortcode attributes.
   *
   * @since 5.2.0
   * @since 5.34.0 - Refactored and moved into Attributes class.
   *
   * @param array $attr  Raw shortcode attributes.
   *
   * @return array Array of found taxonomies.
   */

  public static function get_shortcode_taxonomies( $attr ) : array {
    $taxonomies = [];

    foreach ( ['tags', 'categories', 'fandoms', 'characters', 'genres'] as $key ) {
      if ( ! empty( $attr[ $key ] ) ) {
        $taxonomies[ $key ] = Utils::parse_list( $attr[ $key ], 'sanitize_text_field', 'comma' );
      }
    }

    return $taxonomies;
  }

  /**
   * Parse, sanitize, and normalize shortcode attributes.
   *
   * @since 5.7.3
   * @since 5.34.0 - Refactored and moved into Attributes class.
   *
   * @param array  $attr       Raw shortcode attributes.
   * @param string $shortcode  Shortcode name for context.
   * @param int    $count      Optional fallback. Default -1 (all).
   *
   * @return array Parsed and sanitized arguments.
   */

  public static function parse( $attr, $shortcode, $count = -1 ) : array {
    $defaults = self::defaults();
    $attr = is_array( $attr ) ? $attr : [];
    $uid = wp_unique_id( 'shortcode-id-' );

    $sanitized = array(
      'uid' => $uid,
      'type' => sanitize_key( $attr['type'] ?? 'default' ),
      'simple' => self::bool( $attr['simple'] ?? null ),
      'count' => max( -1, (int) ( $attr['count'] ?? $count ) ),
      'offset' => max( 0, (int) ( $attr['offset'] ?? 0 ) ),
      'order' => sanitize_key( $attr['order'] ?? '' ),
      'orderby' => sanitize_key( $attr['orderby'] ?? '' ),
      'page' => max( 1, absint( get_query_var( 'page' ) ) ?: absint( get_query_var( 'paged' ) ) ?: 1 ),
      'posts_per_page' => absint( $attr['posts_per_page'] ?? 0 )
        ?: absint( $attr['per_page'] ?? 0 ) ?: (int) get_option( 'posts_per_page' ),
      'post_status' => sanitize_key( $attr['post_status'] ?? 'publish' ),
      'post_ids' => wp_parse_id_list( $attr['post_ids'] ?? '' ),
      'author' => sanitize_title( $attr['author'] ?? '' ),
      'author_ids' => wp_parse_id_list( $attr['author_ids'] ?? '' ),
      'excluded_authors' => wp_parse_id_list( $attr['exclude_author_ids'] ?? '' ),
      'excluded_tags' => wp_parse_id_list( $attr['exclude_tag_ids'] ?? '' ),
      'excluded_cats' => wp_parse_id_list( $attr['exclude_cat_ids'] ?? '' ),
      'taxonomies' => self::get_shortcode_taxonomies( $attr ),
      'relation' => strtolower( (string) ( $attr['rel'] ?? $attr['relation'] ?? 'and' ) ) === 'or' ? 'OR' : 'AND',
      'ignore_sticky' => self::bool( $attr['ignore_sticky'] ?? null ),
      'ignore_protected' => self::bool( $attr['ignore_protected'] ?? null ),
      'only_protected' => self::bool( $attr['only_protected'] ?? null ),
      'vertical' => self::bool( $attr['vertical'] ?? null ),
      'seamless' => self::bool( $attr['seamless'] ?? null, $defaults['seamless'] ),
      'thumbnail' => self::bool( $attr['thumbnail'] ?? null, $defaults['thumbnail'] ),
      'aspect_ratio' => Sanitizer::sanitize_css_aspect_ratio( $attr['aspect_ratio'] ?? '' ),
      'spoiler' => self::bool( $attr['spoiler'] ?? null ),
      'lightbox' => self::bool( $attr['lightbox'] ?? null, true ),
      'words' => self::bool( $attr['words'] ?? null, true ),
      'date' => self::bool( $attr['date'] ?? null, true ),
      'date_format' => Sanitizer::sanitize_date_format( $attr['date_format'] ?? '' ),
      'nested_date_format' => Sanitizer::sanitize_date_format( $attr['nested_date_format'] ?? '' ),
      'footer' => self::bool( $attr['footer'] ?? null, true ),
      'footer_author' => self::bool( $attr['footer_author'] ?? null, true ),
      'footer_chapters' => self::bool( $attr['footer_chapters'] ?? null, true ),
      'footer_comments' => self::bool( $attr['footer_comments'] ?? null, true ),
      'footer_date' => self::bool( $attr['footer_date'] ?? null, true ),
      'footer_rating' => self::bool( $attr['footer_rating'] ?? null, true ),
      'footer_status' => self::bool( $attr['footer_status'] ?? null, true ),
      'footer_words' => self::bool( $attr['footer_words'] ?? null, true ),
      'classes' => esc_attr( wp_strip_all_tags( $attr['classes'] ?? $attr['class'] ?? '' ) ) . " {$uid}",
      'infobox' => self::bool( $attr['infobox'] ?? null, true ),
      'source' => self::bool( $attr['source'] ?? null, true ),
      'splide' => self::parse_splide( (string) ( $attr['splide'] ?? '' ) ),
      'cache' => self::bool( $attr['cache'] ?? null, true ),
      'spotlight' => self::bool( $attr['spotlight'] ?? null )
    );

    if ( ! empty( $sanitized['post_ids'] ) ) {
      $sanitized['count'] = count( $sanitized['post_ids'] );
    }

    return apply_filters(
      'fictioneer_filter_default_shortcode_args',
      shortcode_atts( $defaults, $sanitized + $attr, $shortcode ),
      $count
    );
  }
}
