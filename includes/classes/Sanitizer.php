<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Sanitizer {
  use Singleton_Trait;

  /**
   * Sanitize a date format string.
   *
   * @since 5.33.2
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
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed    $value    The value to be sanitized.
   * @param mixed    $default  Optional. Fallback value. Default 0.
   * @param int|null $min      Optional. Minimum value. Default is no minimum.
   * @param int|null $max      Optional. Maximum value. Default is no maximum.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer( $value, $default = 0, ?int $min = null, ?int $max = null ) : int {
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
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed    $value    The value to be sanitized.
   * @param int      $default  Optional. Fallback value. Default 1.
   * @param int|null $max      Optional. Maximum value. Default is no maximum.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer_one_up( $value, int $default = 1, ?int $max = null ) : int {
    return self::sanitize_integer( $value, max( 1, $default ), 1, $max  );
  }

  /**
   * Sanitize words per minute setting (min 200).
   *
   * @since 5.33.2
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value  The value to be sanitized.
   *
   * @return int The sanitized integer.
   */

  public static function sanitize_integer_words_per_minute( $value ) : int {
    return self::sanitize_integer( $value, 200, 200 );
  }

  /**
   * Sanitize a float.
   *
   * @since 5.19.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value    Value to be sanitized.
   * @param mixed $default  Optional. Default if invalid. Default 0.0.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float( $value, $default = 0.0 ) : float {
    if ( $default instanceof \WP_Customize_Setting ) {
      $default = $default->default;
    }

    $default = filter_var( $default, FILTER_VALIDATE_FLOAT );
    $default = ( $default === false ) ? 0.0 : (float) $default;

    if ( is_string( $value ) ) {
      $value = trim( $value );
    }

    $validated = filter_var( $value, FILTER_VALIDATE_FLOAT );

    if ( $validated === false ) {
      return $default;
    }

    $value = (float) $validated;

    if ( ! is_finite( $value ) ) {
      return $default;
    }

    return $value;
  }

  /**
   * Sanitize a float as positive or zero number.
   *
   * @since 5.9.4
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value    Value to be sanitized.
   * @param mixed $default  Optional. Default if invalid or negative. Default 0.0.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float_zero_positive( $value, $default = 0.0 ) : float {
    $default = self::sanitize_float( $default, 0.0 );

    if ( $default < 0 ) {
      $default = 0.0;
    }

    $value = self::sanitize_float( $value, $default );

    return ( $value < 0 ) ? $default : $value;
  }

  /**
   * Sanitize a float as positive or zero number with default 1.0.
   *
   * @since 5.10.1
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value  Value to be sanitized.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float_zero_positive_def1( $value ) : float {
    return self::sanitize_float_zero_positive( $value, 1.0 );
  }

  /**
   * Sanitize a boolean value.
   *
   * Note: Accepts common truthy/falsy representations and normalizes them.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Sanitizer class.
   * @link https://www.php.net/manual/en/function.filter-var.php
   *
   * @param mixed $value    Raw value.
   * @param bool  $numeric  Optional. Whether to return 1/0 instead of true/false. Default false.
   *
   * @return bool|int Sanitized boolean value.
   */

  public static function sanitize_bool( $value, bool $numeric = false ) {
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
   * @since 5.33.2
   *
   * @param mixed $value  Raw value.
   *
   * @return int Sanitized boolean value as 0/1.
   */

  public static function sanitize_bool_num( $value ) : int {
    return self::sanitize_bool( $value, true );
  }

  /**
   * Sanitize an URL.
   *
   * @since 5.19.1
   * @since 5.33.2 - Moved into Sanitizer class.
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

    if ( ! $url || ! wp_http_validate_url( $url ) ) {
      return '';
    }

    if ( $prefix !== null ) {
      $prefix = esc_url_raw( trim( $prefix ) );

      if ( ! $prefix || strncmp( $url, $prefix, strlen( $prefix ) ) !== 0 ) {
        return '';
      }
    }

    if ( $pattern !== null && preg_match( $pattern, $url ) !== 1 ) {
      return '';
    }

    return $url;
  }

  /**
   * Sanitize an URL starting with `https://`.
   *
   * @since 5.19.1
   * @since 5.33.2 - Moved into Sanitizer class.
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
   * @since 5.33.2
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string|null $url  Raw URL value.
   *
   * @return string The sanitized URL or an empty string if invalid.
   */

  public static function sanitize_url_patreon( ?string $url, ?string $pattern = null ) : string {
    return self::sanitize_url( $url, null, '#^https://(www\.)?patreon\.com(?:/|$)#i' );
  }

  /**
   * Sanitize a selected option.
   *
   * @since 5.7.4
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value            Value to be sanitized.
   * @param array $allowed_options  Allowed values to be checked against.
   * @param mixed $default          Optional. Default value as fallback.
   *
   * @return mixed The sanitized value or default, null if not provided.
   */

  public static function sanitize_selection( $value, array $allowed_options, $default = null ) {
    return Sanitizer_Admin::sanitize_selection( $value, $allowed_options, $default );
  }

  /**
   * Sanitize a CSS string.
   *
   * @since 5.7.4
   * @since 5.27.4 - Unslash string.
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string $css  CSS to be sanitized. Expects slashed string.
   *
   * @return string The sanitized string.
   */

  public static function sanitize_css( string $css ) : string {
    return Sanitizer_Admin::sanitize_css( $css );
  }

  /**
   * Sanitize a query variable.
   *
   * @since 5.14.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string      $var      Query variable to sanitize.
   * @param string[]    $allowed  Array of allowed strings (lowercase).
   * @param string|null $default  Optional. Default value.
   * @param array       $args     Optional. Additional arguments.
   *
   * @return string The sanitized (lowercase) query variable.
   */

  public static function sanitize_query_var(
    string $var, array $allowed, ?string $default = null, array $args = [] ) : ?string
  {
    if ( ! is_scalar( $var ) ) {
      return $default;
    }

    $value = (string) $var;

    if ( empty( $args['keep_case'] ) ) {
      $value = strtolower( $value );
      $allowed = array_map( 'strtolower', $allowed );
    }

    return in_array( $value, $allowed, true ) ? $value : $default;
  }

  /**
   * Sanitize a post type.
   *
   * Note: Also associates simple strings like 'story' with their
   * registered post type, such as 'fcn_story'.
   *
   * @since 5.33.5
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string $post_type  Post type to be sanitized.
   *
   * @return string The sanitized post type.
   */

  public static function sanitize_post_type( string $post_type ) : string {
    $post_type = sanitize_key( $post_type );

    static $types = array(
      'story' => 'fcn_story',
      'stories' => 'fcn_story',
      'chapter' => 'fcn_chapter',
      'chapters' => 'fcn_chapter',
      'collection' => 'fcn_collection',
      'collections' => 'fcn_collection',
      'recommendation' => 'fcn_recommendation',
      'recommendations' => 'fcn_recommendation'
    );

    return $types[ $post_type ] ?? $post_type;
  }

  /**
   * Sanitize meta field editor content.
   *
   * Removes malicious HTML, shortcodes, and blocks.
   *
   * @since 5.7.4
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string $content  Content to be sanitized.
   *
   * @return string Sanitized content.
   */

  public static function sanitize_meta_field_editor( string $content ) : string {
    return Sanitizer_Admin::sanitize_meta_field_editor( $content );
  }

  /**
   * Sanitize a CSS aspect ratio value.
   *
   * @since 5.14.0
   * @since 5.23.0 - Refactored to accept fractional values.
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed       $value    Value to be sanitized.
   * @param string|bool $default  Optional. Default value if invalid. Default false.
   *
   * @return string|bool Sanitized aspect-ratio or default.
   */

  public static function sanitize_css_aspect_ratio( $value, $default = false ) {
    if ( $default instanceof \WP_Customize_Setting ) {
      $default = (string) $default->default;
    }

    if ( $value === null ) {
      return $default;
    }

    $value = trim( (string) $value );

    if ( ! preg_match( '/^\d+(?:\.\d+)?\/\d+(?:\.\d+)?$/', $value ) ) {
      return $default;
    }

    list( $num, $den ) = explode( '/', $value, 2 );

    $num = (float) $num;
    $den = (float) $den;

    if ( $num <= 0 || $den <= 0 || ! is_finite( $num ) || ! is_finite( $den ) ) {
      return $default;
    }

    $num = rtrim( rtrim( (string) $num, '0' ), '.' );
    $den = rtrim( rtrim( (string) $den, '0' ), '.' );

    return $num . '/' . $den;
  }

  /**
   * Return sanitized and existing image ID or 0.
   *
   * @since 5.30.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $id  Image ID.
   *
   * @return int Image ID or 0 if not found.
   */

  public static function sanitize_image_id( $id ) : int {
    $id = max( 0, (int) $id );

    return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
  }

  /**
   * Return sanitized icon HTML.
   *
   * @since 5.32.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string $html  Icon HTML.
   *
   * @return string Sanitized icon HTML.
   */

  public static function sanitize_icon_html( string $html ): string {
    return Sanitizer_Admin::sanitize_icon_html( $html );
  }

  /**
   * Return sanitized safe title.
   *
   * @since 5.7.1
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string $title  Post title.
   * @param string $date   The date.
   * @param string $time   The time.
   *
   * @return string The sanitized title.
   */

  public static function sanitize_safe_title( string $title, string $date, string $time ) : string {
    $title = wp_strip_all_tags( $title );

    if ( empty( $title ) ) {
      $title = sprintf(
        _x( '%1$s — %2$s', '[Date] — [Time] if post title is missing.', 'fictioneer' ),
        $date,
        $time
      );
    }

    return $title;
  }
}
