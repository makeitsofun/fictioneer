<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Utils {
  use Singleton_Trait;

  /**
   * Sanitize a date format string.
   *
   * @since 5.34.0
   * @link https://www.php.net/manual/en/datetime.format.php
   *
   * @param string $format  The string to be sanitized.
   *
   * @return string The sanitized value.
   */

  public static function sanitize_date_format( string $format ) : string {
    if ( ! $format ) {
      return '';
    }

    static $allowed = 'dDjlNSwzWFmMntLoYyaABgGhHisuvIeOTZcrU';

    $format = (string) $format;
    $len = strlen( $format );
    $output = '';

    for ( $i = 0; $i < $len; $i++ ) {
      $char = $format[ $i ];

      if ( $char === '\\' && isset( $format[ $i + 1 ] ) ) {
        $output .= '\\' . $format[ ++$i ];

        continue;
      }

      if ( strpos( $allowed, $char ) !== false ) {
        $output .= $char;

        continue;
      }

      $output .= $char;
    }

    return $output;
  }

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
}
