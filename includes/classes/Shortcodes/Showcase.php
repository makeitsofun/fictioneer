<?php

namespace Fictioneer\Shortcodes;

use Fictioneer\Sanitizer;
use Fictioneer\Shortcodes\Base;

defined( 'ABSPATH' ) OR exit;

class Showcase {
  /**
   * Shortcode callback.
   *
   * @since 5.0.0
   * @since 5.34.0 - Moved into class.
   *
   * @param array|string $attr     Raw shortcode attributes.
   * @param string       $content  The enclosed content (if any).
   * @param string       $tag      The shortcode tag (name).
   *
   * @return string Shortcode HTML.
   */

  public static function render( $attr, $content = '', $tag = '' ) : string {
    $shortcode = $tag ?: 'fictioneer_showcase';
    $args = Attributes::parse( $attr, $shortcode, 4 );

    if ( empty( $args['for'] ) ) {
      return '';
    }

    $args['content'] = $content;
    $args['height'] = sanitize_text_field( $args['height'] ?? '' );
    $args['min_width'] = sanitize_text_field( $args['min_width'] ?? '' );
    $args['quality'] = sanitize_key( $args['quality'] ?? 'medium' );
    $args['no_cap'] = \Fictioneer\Utils::bool( $args['no_cap'] ?? null );

    switch ( $args['for'] ) {
      case 'collections':
        $args['post_type'] = 'fcn_collection';
        break;
      case 'chapters':
        $args['post_type'] = 'fcn_chapter';
        break;
      case 'stories':
        $args['post_type'] = 'fcn_story';
        break;
      case 'recommendations':
        $args['post_type'] = 'fcn_recommendation';
        break;
    }

    if ( ! isset( $args['post_type'] ) ) {
      return '';
    }

    if ( ! empty( $args['splide'] ) ) {
      $args['classes'] .= ' splide _splide-placeholder';
    }

    $transient_enabled = ! empty( $args['cache'] ) && Base::transients_enabled( $shortcode );

    if ( $transient_enabled ) {
      $transient_key = Base::transient_key( $shortcode, $args, $attr );
      $cached = get_transient( $transient_key );

      if ( is_string( $cached ) && $cached !== '' ) {
        return $cached;
      }
    }

    ob_start();

    fictioneer_get_template_part( 'partials/_showcase', null, $args );

    $html = fictioneer_minify_html( (string) ob_get_clean() );

    if (
      ! empty( $args['splide'] ) &&
      strpos( $args['classes'] ?? '', 'no-auto-splide' ) === false
    ) {
      $html = str_replace( '</section>', Base::splide_inline_script() . '</section>', $html );
    }

    if ( $transient_enabled ) {
      set_transient( $transient_key, $html, FICTIONEER_SHORTCODE_TRANSIENT_EXPIRATION );
    }

    return $html;
  }
}
