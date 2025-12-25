<?php

namespace Fictioneer\Shortcodes;

use Fictioneer\Sanitizer;
use Fictioneer\Shortcodes\Base;

defined( 'ABSPATH' ) OR exit;

class Latest_Chapters {
  /**
   * Shortcode callback.
   *
   * @since 3.0
   * @since 5.34.0 - Moved into class.
   *
   * @param array|string $attr     Raw shortcode attributes.
   * @param string       $content  The enclosed content (if any).
   * @param string       $tag      The shortcode tag (name).
   *
   * @return string Shortcode HTML.
   */

  public static function render( $attr, $content = '', $tag = '' ) : string {
    $shortcode = $tag ?: 'fictioneer_latest_chapters';
    $args = Attributes::parse( $attr, $shortcode, 4 );

    $args['content'] = $content;
    $args['simple'] = false;

    $args['terms'] = Sanitizer::sanitize_query_var(
      $args['terms'] ?? 'inline',
      ['inline', 'pills', 'none', 'false'],
      'inline'
    );

    if ( ! empty( $args['splide'] ) ) {
      $args['classes'] .= ' splide _splide-placeholder';
    }

    $transient_enabled = ! empty( $args['cache'] ) && Base::transients_enabled( 'fictioneer_latest_stories' );

    if ( $transient_enabled ) {
      $transient_key = Base::transient_key( $shortcode, $args, $attr );
      $cached = get_transient( $transient_key );

      if ( is_string( $cached ) && $cached !== '' ) {
        return $cached;
      }
    }

    ob_start();

    switch ( $args['type'] ?? 'default' ) {
      case 'compact':
        fictioneer_get_template_part( 'partials/_latest-chapters-compact', null, $args );
        break;
      case 'list':
        fictioneer_get_template_part( 'partials/_latest-chapters-list', null, $args );
        break;
      default:
        $args['simple'] = ( $args['type'] === 'simple' );
        fictioneer_get_template_part( 'partials/_latest-chapters', null, $args );
    }

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
