<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;
use Fictioneer\Utils_Admin;

defined( 'ABSPATH' ) OR exit;

class Utils {
  use Singleton_Trait;

  /**
   * Wrapper for wp_parse_list() with optional sanitizer.
   *
   * @since 5.34.0
   *
   * @param array|string $input_list  List of values.
   *
   * @return array Array of values.
   */

  public static function parse_list( array|string $input_list, string|null $sanitizer = null ) : array {
    $values = wp_parse_list( $input_list ?? '' );

    if ( $sanitizer && is_callable( $sanitizer ) ) {
      $values = array_map( $sanitizer, $values );
      $values = array_filter( $values, 'strlen' );
      $values = array_values( $values );
    }

    return $values;
  }

  /**
   * Extract aspect ratio values from string.
   *
   * @since 5.14.0
   * @since 5.34.0 - Moved into Utils class.
   *
   * @param mixed $value  Aspect-ratio value (e.g. '16/9, '1.5/1').
   *
   * @return array Tuple of numerator (0) and denominator (1).
   */

  public static function split_aspect_ratio( mixed $value ) : array {
    if ( ! is_string( $value ) || ! str_contains( $value, '/' ) ) {
      return [ 1.0, 1.0 ];
    }

    list( $num, $den ) = explode( '/', $value, 2 );

    $num = filter_var( $num, FILTER_VALIDATE_FLOAT );
    $den = filter_var( $den, FILTER_VALIDATE_FLOAT );

    if ( $num === false || $den === false || $num <= 0 || $den <= 0 ) {
      return [ 1.0, 1.0 ];
    }

    return [ (float) $num, (float) $den ];
  }

  /**
   * Return URL without query arguments or page number.
   *
   * @since 5.4.0
   * @since 5.34.0 - Moved into Utils class.
   *
   * @return string Clean URL.
   */

  public static function get_clean_url() : string {
    global $wp, $wp_rewrite;

    $url = home_url( $wp->request );
    $url = untrailingslashit( $url );
    $pagination_base = $wp_rewrite->pagination_base ?: 'page';
    $pattern = '#/' . preg_quote( $pagination_base, '#' ) . '/\d+$#';

    return preg_replace( $pattern, '', $url );
  }
}
