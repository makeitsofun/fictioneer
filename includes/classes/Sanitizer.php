<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Sanitizer {
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
   * Sanitize an integer with options for default, minimum, and maximum.
   *
   * @since 4.0.0
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed    $value    The value to be sanitized.
   * @param mixed    $default  Optional. Fallback value. Default 0.
   * @param int|null $min      Optional. Minimum value. Default is no minimum.
   * @param int|null $max      Optional. Maximum value. Default is no maximum.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer( mixed $value, mixed $default = 0, ?int $min = null, ?int $max = null ) : int {
    if ( $default instanceof \WP_Customize_Setting ) {
      $default = $default->default;
    }

    $default = ( filter_var( $default, FILTER_VALIDATE_INT ) === false ) ? 0 : (int) $default;

    if ( is_string( $value ) ) {
      $value = trim( $value );
    }

    if ( $value === '' || filter_var( $value, FILTER_VALIDATE_INT ) === false ) {
      return $default;
    }

    $value = (int) $value;

    if ( $min !== null && $value < $min ) {
      return $min;
    }

    if ( $max !== null && $value > $max ) {
      return $max;
    }

    return $value;
  }

  /**
   * Sanitize an integer to be 1+ with options for default and maximum.
   *
   * @since 4.6.0
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed    $value    The value to be sanitized.
   * @param int      $default  Optional. Fallback value. Default 1.
   * @param int|null $max      Optional. Maximum value. Default is no maximum.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer_one_up( mixed $value, int $default = 1, ?int $max = null ) : int {
    return self::sanitize_integer( $value, max( 1, $default ), 1, $max  );
  }

  /**
   * Sanitize words per minute setting (min 200).
   *
   * @since 5.34.0
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $value  The value to be sanitized.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer_words_per_minute( mixed $value ) : int {
    return self::sanitize_integer( $value, 200, 200 );
  }

  /**
   * Sanitize a float as positive or zero number.
   *
   * @since 5.9.4
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $value    Value to be sanitized.
   * @param mixed $default  Optional. Default if invalid or negative. Default 0.0.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float_zero_positive( mixed $value, mixed $default = 0.0 ) : float {
    if ( $default instanceof \WP_Customize_Setting ) {
      $default = $default->default;
    }

    $default = filter_var( $default, FILTER_VALIDATE_FLOAT );
    $default = ( $default === false || $default < 0 ) ? 0.0 : (float) $default;

    if ( is_string( $value ) ) {
      $value = trim( $value );
    }

    if ( $value === '' || filter_var( $value, FILTER_VALIDATE_FLOAT ) === false ) {
      return $default;
    }

    $value = (float) $value;

    if ( ! is_finite( $value ) || $value < 0 ) {
      return $default;
    }

    return $value;
  }

  /**
   * Sanitize a float as positive or zero number with default 1.0.
   *
   * @since 5.10.1
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $value  Value to be sanitized.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float_zero_positive_def1( mixed $value ) : float {
    return self::sanitize_float_zero_positive( $value, 1.0 );
  }

  /**
   * Sanitize a boolean value.
   *
   * Note: Accepts common truthy/falsy representations and normalizes them.
   *
   * @since 4.7.0
   * @since 5.34.0 - Moved into Sanitizer class.
   * @link https://www.php.net/manual/en/function.filter-var.php
   *
   * @param mixed $value    Raw value.
   * @param bool  $numeric  Optional. Whether to return 1/0 instead of true/false. Default false.
   *
   * @return bool|int Sanitized boolean value.
   */

  public static function sanitize_bool( mixed $value, bool $numeric = false ) : int|bool {
    if ( is_string( $value ) ) {
      $value = trim( strtolower( $value ) );
    }

    $bool = filter_var( $value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE );
    $bool = ( $bool === true );

    return $numeric ? (int) $bool : $bool;
  }

  /**
   * Sanitize a boolean value to 0/1.
   *
   * @since 5.34.0
   *
   * @param mixed $value  Raw value.
   *
   * @return int Sanitized boolean value as 0/1.
   */

  public static function sanitize_bool_num( mixed $value ) : int {
    return self::sanitize_bool( $value, true );
  }

  /**
   * Sanitize an URL.
   *
   * @since 5.19.1
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param string|null $url      Raw URL value.
   * @param string|null $prefix   Optional. URL must start with this string.
   * @param string|null $pattern  Optional. Pattern the URL must match.
   *
   * @return string The sanitized URL or an empty string if invalid.
   */

  public static function sanitize_url( ?string $url, ?string $prefix = null, ?string $pattern = null ) : string {
    if ( $url === null || $url === '' ) {
      return '';
    }

    $url = esc_url_raw( $url );

    if ( ! $url || filter_var( $url, FILTER_VALIDATE_URL ) === false ) {
      return '';
    }

    if ( $prefix !== null && strncmp( $url, $prefix, strlen( $prefix ) ) !== 0 ) {
      return '';
    }

    if ( $pattern !== null && @preg_match( $pattern, $url ) !== 1 ) {
      return '';
    }

    return $url;
  }

  /**
   * Sanitize an URL starting with `https://`.
   *
   * @since 5.19.1
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param string|null $url      Raw URL value.
   * @param string|null $pattern  Optional. Pattern the URL must match.
   *
   * @return string The sanitized URL or an empty string if invalid.
   */

  public static function sanitize_url_https( ?string $url, ?string $pattern = null ) : string {
    return self::sanitize_url( $url, 'https://', $pattern );
  }

  /**
   * Sanitize a Patreon URL.
   *
   * @since 5.34.0
   *
   * @param string|null $url  Raw URL value.
   *
   * @return string The sanitized URL or an empty string if invalid.
   */

  public static function sanitize_url_patreon( ?string $url, ?string $pattern = null ) : string {
    return self::sanitize_url( $url, null, '#^https://(www\.)?patreon\.com(?:/|$)#i' );
  }
}
