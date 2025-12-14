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
   * Sanitize a float.
   *
   * @since 5.19.0
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $value    Value to be sanitized.
   * @param mixed $default  Optional. Default if invalid. Default 0.0.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float( mixed $value, mixed $default = 0.0 ) : float {
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
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $value    Value to be sanitized.
   * @param mixed $default  Optional. Default if invalid or negative. Default 0.0.
   *
   * @return float The sanitized float.
   */

  public static function sanitize_float_zero_positive( mixed $value, mixed $default = 0.0 ) : float {
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
   * @since 5.34.0 - Moved into Sanitizer class.
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
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $value            Value to be sanitized.
   * @param array $allowed_options  Allowed values to be checked against.
   * @param mixed $default          Optional. Default value as fallback.
   *
   * @return mixed The sanitized value or default, null if not provided.
   */

  public static function sanitize_selection( $value, $allowed_options, $default = null ) {
    if ( is_string( $value ) ) {
      $value = sanitize_text_field( $value );
    }

    $allowed = array_map(
      static fn( $v ) => is_string( $v ) ? sanitize_text_field( $v ) : $v,
      $allowed_options
    );

    return in_array( $value, $allowed, true ) ? $value : $default;
  }

  /**
   * Sanitize a CSS string.
   *
   * @since 5.7.4
   * @since 5.27.4 - Unslash string.
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param string $css  CSS to be sanitized. Expects slashed string.
   *
   * @return string The sanitized string.
   */

  public static function sanitize_css( string $css ) : string {
    $css = (string) ( $css ?? '' );
    $css = wp_unslash( $css );
    $css = wp_kses_no_null( $css );
    $css = preg_replace( '/[\x00-\x1F\x7F]/u', '', $css );
    $css = trim( $css );

    if ( $css === '' ) {
      return '';
    }

    $check = preg_replace( '#/\*.*?\*/#s', '', $css );
    $check = preg_replace( '/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/s', '', $check );

    if ( strpos( $check, '<' ) !== false ) {
      return '';
    }

    if ( preg_match( '/(?:expression\s*\(|-moz-binding\s*:|behavior\s*:|@import\b|javascript\s*:)/i', $check ) ) {
      return '';
    }

    if ( preg_match( '/url\s*\(\s*[^)]*javascript\s*:/i', $check ) ) {
      return '';
    }

    $open  = substr_count( $css, '{' );
    $close = substr_count( $css, '}' );

    if ( $open < 1 || $open !== $close ) {
      return '';
    }

    return $css;
  }

  /**
   * Sanitize a query variable.
   *
   * @since 5.14.0
   * @since 5.34.0 - Moved into Sanitizer class.
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
   * @since 5.34.0 - Moved into Sanitizer class.
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
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param string $content  Content to be sanitized.
   *
   * @return string Sanitized content.
   */

  public static function sanitize_meta_field_editor( string $content ) : string {
    if ( $content === null || $content === '' ) {
      return '';
    }

    $content = strip_shortcodes( $content );

    if ( strpos( $content, '<!-- wp:' ) !== false && function_exists( 'parse_blocks' ) ) {
      $out = '';

      foreach ( parse_blocks( $content ) as $block ) {
        if ( empty( $block['blockName'] ) && ! empty( $block['innerHTML'] ) ) {
          $out .= $block['innerHTML'];
        } elseif ( empty( $block['blockName'] ) && ! empty( $block['innerContent'] ) ) {
          $out .= implode( '', $block['innerContent'] );
        }
      }

      $content = $out;
    }

    return wp_kses_post( $content );
  }

  /**
   * Sanitize a CSS aspect ratio value.
   *
   * @since 5.14.0
   * @since 5.23.0 - Refactored to accept fractional values.
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed       $value    Value to be sanitized.
   * @param string|bool $default  Optional. Default value if invalid. Default false.
   *
   * @return string|bool Sanitized aspect-ratio or default.
   */

  public static function sanitize_css_aspect_ratio( mixed $value, string|bool $default = false ) : string|bool {
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
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param mixed $id  Image ID.
   *
   * @return int Image ID or 0 if not found.
   */

  public static function sanitize_image_id( mixed $id ) : int {
    $id = max( 0, (int) $id );

    return ( $id && wp_attachment_is_image( $id ) ) ? $id : 0;
  }

  /**
   * Return sanitized icon HTML.
   *
   * @since 5.32.0
   * @since 5.34.0 - Moved into Sanitizer class.
   *
   * @param string $html  Icon HTML.
   *
   * @return string Sanitized icon HTML.
   */

  public static function sanitize_icon_html( string $html ): string {
    $html = trim( wp_unslash( $html ?? '' ) );

    if ( $html === '' ) {
      return '';
    }

    if ( strpos( $html, '<!--' ) !== false ) {
      $html = preg_replace( '/<!--.*?-->/s', '', $html );
    }

    static $allowed = array(
      'i' => array(
        'class' => true,
        'title' => true,
        'role' => true,
        'aria-hidden' => true,
        'aria-label' => true,
      ),
      'span' => array(
        'class' => true,
        'role' => true,
        'aria-hidden' => true,
        'aria-label' => true,
        'title' => true,
      ),
      'div' => array(
        'class' => true,
        'role' => true,
        'aria-hidden' => true,
        'aria-label' => true,
        'title' => true,
      ),
      'svg' => array(
        'class' => true,
        'role' => true,
        'aria-hidden' => true,
        'aria-label' => true,
        'aria-labelledby' => true,
        'aria-describedby' => true,
        'focusable' => true,
        'width' => true,
        'height' => true,
        'viewBox' => true,
        'preserveAspectRatio' => true,
        'fill' => true,
        'stroke' => true,
        'stroke-width' => true,
        'xmlns' => true,
        'xmlns:xlink' => true,
      ),
      'g' => array(
        'class' => true,
        'fill' => true,
        'stroke' => true,
        'stroke-width' => true,
        'transform' => true,
      ),
      'path' => array(
        'class' => true,
        'd' => true,
        'fill' => true,
        'stroke' => true,
        'stroke-width' => true,
        'transform' => true,
        'vector-effect'=> true,
      ),
      'rect' => array(
        'x' => true, 'y' => true, 'width' => true, 'height' => true,
        'rx' => true, 'ry' => true, 'fill' => true, 'stroke' => true,
        'transform' => true,
      ),
      'circle' => array(
        'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true,
        'transform' => true,
      ),
      'line' => array(
        'x1' => true, 'y1' => true, 'x2' => true, 'y2' => true,
        'stroke' => true, 'stroke-width' => true, 'transform' => true,
      ),
      'polyline' => array(
        'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
        'transform' => true,
      ),
      'polygon' => array(
        'points' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true,
        'transform' => true,
      ),
      'symbol' => array( 'id' => true, 'viewBox' => true ),
      'defs' => [],
      'use' => array(
        'href' => true,
        'xlink:href' => true,
        'class' => true,
      ),
      'title' => [],
      'desc' => [],
    );

    $html = wp_kses( $html, $allowed );

    $html = preg_replace_callback(
      '/\s(?:xlink:)?href=(["\'])(.*?)\1/i',
      static function ( array $m ) : string {
        $val = trim( html_entity_decode( $m[2], ENT_QUOTES, 'UTF-8' ) );

        if ( $val !== '' && $val[0] === '#' ) {
          return ' href="' . esc_attr( $val ) . '"';
        }

        return '';
      },
      $html
    );

    return preg_replace( '/\s{2,}/', ' ', trim( $html ) );
  }
}
