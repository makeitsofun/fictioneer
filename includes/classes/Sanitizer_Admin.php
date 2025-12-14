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
