<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Sanitizer_Admin {
  use Singleton_Trait;

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
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param string $css  CSS to be sanitized.
   *
   * @return string The sanitized string.
   */

  public static function sanitize_css( string $css ) : string {
    $css = (string) ( $css ?? '' );
    $css = wp_kses_no_null( $css );
    $css = preg_replace( '/[\x00-\x1F\x7F]/u', '', $css );
    $css = trim( $css );

    if ( $css === '' ) {
      return '';
    }

    $no_comments = preg_replace( '#/\*.*?\*/#s', '', $css );
    $check = preg_replace( '/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/s', '', $no_comments );

    if ( strpos( $check, '<' ) !== false ) {
      return '';
    }

    if ( preg_match( '/(?:expression\s*\(|-moz-binding\s*:|behavior\s*:|javascript\s*:)/i', $check ) ) {
      return '';
    }

    if ( stripos( $no_comments, '@import' ) !== false ) {
      $import_regex = '/@import\s+(?:url\s*\(\s*)?(?:"([^"]+)"|\'([^\']+)\'|([^"\')\s]+))\s*\)?\s*;/i';

      if ( preg_match_all( $import_regex, $no_comments, $imports, PREG_SET_ORDER ) ) {
        foreach ( $imports as $match ) {
          // Check matches for double quotes, single quotes, and no quotes
          $url = trim( $match[1] ?: ( $match[2] ?: ( $match[3] ?? '' ) ) );

          $url_parts = wp_parse_url( $url );

          if ( ! is_array( $url_parts ) ) {
            return '';
          }

          $scheme = strtolower( $url_parts['scheme'] ?? '' );
          $host = strtolower( $url_parts['host'] ?? '' );
          $path = $url_parts['path'] ?? '';

          if ( $scheme !== 'https' || $host !== 'fonts.googleapis.com' || strpos( $path, '/css' ) !== 0 ) {
            return '';
          }
        }
      } else {
        return ''; // @import present in not recognized form
      }

      $stripped = preg_replace( $import_regex, '', $no_comments );

      if ( $stripped === null || stripos( $stripped, '@import' ) !== false ) {
        return '';
      }
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

  /**
   * Sanitize a page ID and check whether it is valid.
   *
   * @since 4.6.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $input  Page ID to be sanitized.
   *
   * @return int Valid page ID or -1 if invalid or not a page.
   */

  public static function sanitize_page_id( mixed $input ) : int {
    if ( ! is_scalar( $input ) ) {
      return -1;
    }

    $id = (int) $input;

    if ( $id <= 0 ) {
      return -1;
    }

    return ( get_post_type( $id ) === 'page' ) ? $id : -1;
  }

  /**
   * Sanitize with absint() unless it is an empty string.
   *
   * @since 5.15.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $input  Value to be sanitized.
   *
   * @return int|string Sanitized integer or an empty string.
   */

  public static function sanitize_absint_or_empty_string( mixed $input ) : int|string {
    if ( $input === '' ) {
      return '';
    }

    return absint( $input );
  }

  /**
   * Sanitize the phrase for the cookie consent banner.
   *
   * Checks whether the input is a string and has at least 32 characters,
   * otherwise a default is returned. The content is also cleaned of any
   * problematic HTML.
   *
   * @since 4.6.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $input  Content for the cookie consent banner.
   *
   * @return string Sanitized content for the cookie consent banner.
   */

  public static function sanitize_phrase_consent_banner( mixed $input ) : string {
    $default = __( 'We use cookies to enhance your browsing experience, serve personalized content, and analyze our traffic. Some features are not available without, but you can limit the site to strictly necessary cookies only. See <a href="[[privacy_policy_url]]" target="_blank" tabindex="1">Privacy Policy</a>.', 'fictioneer' );

    if ( ! is_string( $input ) ) {
      return $default;
    }

    $raw = trim( wp_unslash( $input ) );
    $visible = trim( wp_strip_all_tags( $raw ) );
    $len = function_exists( 'mb_strlen' ) ? mb_strlen( $visible ) : strlen( $visible );

    if ( $len < 32 ) {
      return $default;
    }

    $allowed = wp_kses_allowed_html( 'post' );

    $allowed['a'] = $allowed['a'] ?? [];
    $allowed['a']['tabindex'] = true;

    return wp_kses( $raw, $allowed ) ?: $default;
  }

  /**
   * Sanitize the textarea input for Google Fonts links.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value  Textarea string.
   *
   * @return string Sanitized textarea string.
   */

  public static function sanitize_google_fonts_links( mixed $value ) : string {
    $value = trim( wp_unslash( (string) ( $value ?? '' ) ) );

    if ( $value === '' ) {
      return '';
    }

    $lines = preg_split( "/\R/u", $value ) ?: [];
    $valid = [];

    foreach ( $lines as $line ) {
      $line = trim( $line );

      if ( $line === '' ) {
        continue;
      }

      if ( preg_match( '#^https://fonts\.googleapis\.com/css2(?:\?|$)#i', $line ) !== 1 ) {
        continue;
      }

      $url = esc_url_raw( $line );

      if ( $url !== '' ) {
        $valid[] = $url;
      }
    }

    return implode( "\n", array_values( array_unique( $valid ) ) );
  }

  /**
   * Sanitize the textarea input for preload font links.
   *
   * @since 5.31.0
   * @since 5.33.2 - Moved into Sanitizer class.
   *
   * @param mixed $value  Textarea string.
   *
   * @return string Sanitized textarea string.
   */

  public static function sanitize_preload_font_links( mixed $value ) : string {
    $value = trim( wp_unslash( (string) ( $value ?? '' ) ) );

    if ( $value === '' ) {
      return '';
    }

    $lines = preg_split( "/\R/u", $value ) ?: [];
    $valid_extensions = [ 'woff', 'woff2', 'ttf', 'otf', 'eot', 'fon' ];
    $valid = [];

    foreach ( $lines as $line ) {
      $line = trim( $line );

      if ( $line === '' ) {
        continue;
      }

      if ( str_contains( $line, "\0" ) || str_contains( $line, '\\' ) ) {
        continue;
      }

      $path = parse_url( $line, PHP_URL_PATH );

      if ( ! is_string( $path ) || $path === '' ) {
        continue;
      }

      $extension = strtolower( pathinfo( $path, PATHINFO_EXTENSION ) );

      if ( $extension === '' || ! in_array( $extension, $valid_extensions, true ) ) {
        continue;
      }

      if ( str_starts_with( $line, 'https://' ) ) {
        $url = esc_url_raw( $line );

        if ( $url !== '' && wp_http_validate_url( $url ) ) {
          $valid[] = $url;
        }

        continue;
      }

      if ( str_starts_with( $line, '/' ) ) {
        $decoded = rawurldecode( $line );

        if ( str_contains( $decoded, '..' ) || str_contains( $decoded, '://' ) ) {
          continue;
        }

        $rel = esc_url_raw( $line );

        if ( $rel !== '' && str_starts_with( $rel, '/' ) ) {
          $valid[] = $rel;
        }
      }
    }

    return implode( "\n", array_values( array_unique( $valid ) ) );
  }
}
