<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

final class CSS_Validator {
  protected $raw = '';
  protected $no_comments = '';
  protected $no_strings = '';
  protected $decoded = '';
  protected $normalized = '';
  protected $feedback = true;
  protected $rejection = '';

  /**
   * @since 5.34.0
   *
   * @param string $css       Raw CSS string.
   * @param bool   $feedback  Whether to return rejection feedback. Default true.
   */

  public function __construct( $css, $feedback = true ) {
    $this->feedback = (bool) $feedback;
    $this->raw = $this->sanitize( (string) $css );
    $this->rebuild_buffers();
  }

  /**
   * Return sanitized CSS, rejection feedback, or empty string.
   *
   * @since 5.34.0
   */

  public function result() : string {
    if ( $this->rejection !== '' ) {
      return $this->feedback ? $this->rejection : '';
    }

    return $this->raw;
  }

  /**
   * Whether validator has rejected the input.
   *
   * @since 5.34.0
   */

  public function rejected() : bool {
    return $this->rejection !== '';
  }

  /**
   * Set rejection message once.
   *
   * @since 5.34.0
   */

  protected function reject( $message ) : void {
    if ( $this->rejection === '' ) {
      $this->rejection = $message;
    }
  }

  /**
   * Basic pre-sanitize input.
   *
   * @since 5.34.0
   *
   * @param string $css  Raw CSS string.
   *
   * @return string Trimmed CSS without BOM or control chars.
   */

  protected function sanitize( $css ) : string {
    $css = (string) $css;

    $css = preg_replace( '/^\xEF\xBB\xBF/u', '', $css );
    $css = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $css );
    $css = trim( $css );

    if ( $css === '' ) {
      return '';
    }

    if ( stripos( $css, '@charset' ) !== false ) {
      if ( preg_match( '/^\s*@charset\b/i', $css ) !== 1 ) {
        $this->reject( '/* Rejected due to invalid @charset placement. */' );

        return '';
      }

      if ( preg_match( '/^\s*@charset\s+(?:"utf-8"|\'utf-8\'|utf-8)\s*;/i', $css ) !== 1 ) {
        $this->reject( '/* Rejected due to invalid @charset value. */' );

        return '';
      }

      $rest = preg_replace( '/^\s*@charset\s+(?:"utf-8"|\'utf-8\'|utf-8)\s*;\s*/i', '', $css, 1 );

      if ( $rest === null || stripos( $rest, '@charset' ) !== false ) {
        $this->reject( '/* Rejected due to duplicate @charset. */' );

        return '';
      }
    }

    return $css;
  }

  /**
   * Rebuild scan buffers.
   *
   * @since 5.34.0
   */

  protected function rebuild_buffers() : void {
    if ( $this->raw === '' || $this->rejected() ) {
      return;
    }

    // Strip comments and charset
    $no_comments = preg_replace( '#/\*.*?\*/#s', '', $this->raw );

    if ( $no_comments === null ) {
      $this->reject( '/* Rejected due to regex error. */' );

      return;
    }

    $this->no_comments = preg_replace( '/^\s*@charset\s+(?:"utf-8"|\'utf-8\'|utf-8)\s*;\s*/i', '', $no_comments, 1 );

    // Strip strings
    $this->no_strings = preg_replace( '/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/s', '', $this->no_comments ) ?? '';

    if ( $this->no_strings === '' && $this->no_comments !== '' ) {
      $this->reject( '/* Rejected due to regex error. */' );

      return;
    }

    // Decode
    $this->decoded = $this->decode( $this->no_comments );

    // Normalize
    $this->normalized = $this->normalize( $this->decoded );
  }

  /**
   * Decode CSS hex escapes (\HHHHHH).
   *
   * @param string $css  CSS with comments removed.
   *
   * @return string Decoded CSS.
   */

  protected function decode( $css ) : string {
    $decoded = strtolower( $css );

    for ( $i = 0; $i < 5; $i++ ) {
      $next = preg_replace_callback(
        '/\\\\([0-9a-f]{1,6})\s?/i',
        function ( $m ) {
          $cp = hexdec( $m[1] );
          return ( $cp >= 0x20 && $cp <= 0x7E ) ? chr( $cp ) : '';
        },
        $decoded
      );

      if ( $next === null || $next === $decoded ) {
        break;
      }

      $decoded = $next;
    }

    return $decoded;
  }

  /**
   * Normalize decoded CSS.
   *
   * @param string $css  Decoded CSS.
   *
   * @return string Normalized CSS.
   */

  protected function normalize( $css ) : string {
    $normalized = preg_replace( '/["\'\s]+/', '', $css );

    if ( $normalized === null ) {
      return '';
    }

    $normalized = preg_replace( '/[^a-z0-9:]/', '', $normalized );

    return $normalized ?? '';
  }

  /**
   * Normalize a url() payload for scheme scanning.
   *
   * @param string $raw  Raw url() payload.
   *
   * @return string Normalized payload.
   */

  protected function normalize_url_payload( $raw ) : string {
    $normalized = strtolower( (string) $raw );

    for ( $i = 0; $i < 5; $i++ ) {
      $next = preg_replace_callback(
        '/\\\\([0-9a-f]{1,6})\s?/i',
        function ( $m ) {
          $hex = strtolower( (string) $m[1] );

          $cp = hexdec( $hex );

          if ( $cp >= 0x20 && $cp <= 0x7E ) {
            return chr( $cp );
          }

          if ( strlen( $hex ) > 2 ) {
            $first = substr( $hex, 0, 2 );
            $rest = substr( $hex, 2 );

            $cp2 = hexdec( $first );

            if ( $cp2 >= 0x20 && $cp2 <= 0x7E ) {
              return chr( $cp2 ) . $rest;
            }
          }

          return '';
        },
        $normalized
      );

      if ( $next === null || $next === $normalized ) {
        break;
      }

      $normalized = $next;
    }

    $normalized = preg_replace( '/["\'\s]+/', '', $normalized );

    if ( $normalized === null || $normalized === '' ) {
      return '';
    }

    $normalized = preg_replace( '/[^a-z0-9:]/', '', $normalized );

    return $normalized ?? '';
  }

  /**
   * Check for size limits.
   *
   * @param bool $unfiltered  Whether the input is unfiltered.
   * @param int $max_bytes    Optional. Maximum number of bytes. Default 10240.
   * @param int $max_lines    Optional. Maximum number of lines. Default 500.
   *
   * @return Css_Validator Instance.
   */

  public function reject_excess_size( $unfiltered, $max_bytes = 10240, $max_lines = 500 ) : self {
    if ( $this->rejected() || $unfiltered || $this->raw === '' ) {
      return $this;
    }

    if ( strlen( $this->raw ) > $max_bytes ) {
      $this->reject( '/* Rejected due to size. */' );

      return $this;
    }

    $lines = substr_count( str_replace( ["\r\n", "\r"], "\n", $this->raw ), "\n" ) + 1;

    if ( $lines > $max_lines ) {
      $this->reject( '/* Rejected due to too many lines. */' );
    }

    return $this;
  }

  /**
   * Check for opening HTML tags.
   *
   * @return Css_Validator Instance.
   */

  public function reject_html_open() : self {
    if ( ! $this->rejected() && strpos( $this->no_strings, '<' ) !== false ) {
      $this->reject( '/* Rejected due to HTML opening character. */' );
    }

    return $this;
  }

  /**
   * Check for dangerous tokens.
   *
   * @return Css_Validator Instance.
   */

  public function reject_danger_tokens() : self {
    if (
      ! $this->rejected() &&
      preg_match( '/(?:expression\s*\(|-moz-binding\s*:|behavior\s*:|javascript\s*:)/i', $this->decoded )
    ) {
      $this->reject( '/* Rejected due to dangerous expression or property. */' );
    }

    return $this;
  }

  /**
   * Check for invalid @import usage.
   *
   * @param bool $allow_fonts  Whether @import for fonts is allowed. Default false.
   *
   * @return Css_Validator Instance.
   */

  public function reject_invalid_imports( $allow_fonts = false ) : self {
    if ( $this->rejected() || $this->no_comments === '' ) {
      return $this;
    }

    $import_regex = '/@import\s+(?:url\s*\(\s*)?(?:"([^"]+)"|\'([^\']+)\'|([^"\')\s]+))\s*\)?\s*;/i';
    $has_import = stripos( $this->no_comments, '@import' ) !== false;

    if ( $has_import && ! $allow_fonts ) {
      $this->reject( '/* Rejected due to unallowed @import. */' );

      return $this;
    }

    if ( ! $has_import ) {
      return $this;
    }

    if ( preg_match_all( $import_regex, $this->no_comments, $imports, PREG_SET_ORDER ) ) {
      foreach ( $imports as $match ) {
        // Check matches for double quotes, single quotes, and no quotes
        $url = trim( $match[1] ?: ( $match[2] ?: ( $match[3] ?? '' ) ) );

        $url_parts = wp_parse_url( $url );

        if ( ! is_array( $url_parts ) ) {
          $this->reject( '/* Rejected due to unallowed @import. */' );

          return $this;
        }

        $scheme = strtolower( $url_parts['scheme'] ?? '' );
        $host = strtolower( $url_parts['host'] ?? '' );
        $path = $url_parts['path'] ?? '';

        if ( $scheme !== 'https' || $host !== 'fonts.googleapis.com' || strpos( $path, '/css' ) !== 0 ) {
          $this->reject( '/* Rejected due to unallowed @import. */' );

          return $this;
        }
      }
    } else {
      $this->reject( '/* Rejected due to unallowed @import. */' );

      return $this;
    }

    // Reject any unrecognized/leftover @import patterns.
    $import_stripped = preg_replace( $import_regex, '', $this->no_comments );

    if ( $import_stripped === null || stripos( $import_stripped, '@import' ) !== false ) {
      $this->reject( '/* Rejected due to unallowed @import. */' );

      return $this;
    }

    return $this;
  }

  /**
   * Get buffer without @import.
   *
   * @return string CSS with @import instances removed.
   */

  public function without_imports() : string {
    if ( $this->no_comments === '' ) {
      return '';
    }

    $import_regex = '/@import\s+(?:url\s*\(\s*)?(?:"([^"]+)"|\'([^\']+)\'|([^"\')\s]+))\s*\)?\s*;/i';
    $out = preg_replace( $import_regex, '', $this->no_comments );

    return $out === null ? '' : $out;
  }

  /**
   * Check for any unallowed url() usage.
   *
   * @param bool        $allow_url  Whether url() is allowed at all. Default false.
   * @param string|null $buffer     Optional. Scan buffer (defaults to `$this->without_imports()`).
   *
   * @return Css_Validator Instance.
   */

  public function reject_url( $allow_url = false, $buffer = null ) : self {
    if ( $this->rejected() || $allow_url ) {
      return $this;
    }

    $buffer = $buffer === null ? $this->without_imports() : (string) $buffer;

    if ( stripos( $buffer, 'url(' ) !== false ) {
      $this->reject( '/* Rejected due to use of url(). */' );
    }

    return $this;
  }

  /**
   * Check for blocked schemes inside any url().
   *
   * @param string|null $buffer   Optional. Scan buffer (defaults to `$this->without_imports()`).
   * @param array|null  $schemes  Optional. List of schemes to block. Defaults to
   *                              ['javascript:', 'vbscript:', 'file:'].
   *
   * @return Css_Validator Instance.
   */

  public function reject_blocked_url_schemes( $buffer = null, $schemes = null ) : self {
    if ( $this->rejected() ) {
      return $this;
    }

    if ( empty( $schemes ) ) {
      $schemes = ['javascript:', 'vbscript:', 'file:'];
    }

    $buffer = $buffer === null ? $this->without_imports() : (string) $buffer;

    if ( $buffer === '' || stripos( $buffer, 'url(' ) === false ) {
      return $this;
    }

    if ( ! preg_match_all( '/url\s*\(\s*([^)]+)\s*\)/i', $buffer, $matches, PREG_SET_ORDER ) ) {
      return $this;
    }

    foreach ( $matches as $m ) {
      $payload = (string) ( $m[1] ?? '' );

      if ( $payload === '' ) {
        continue;
      }

      $normalized = $this->normalize_url_payload( $payload );

      if ( $normalized === '' ) {
        continue;
      }

      foreach ( $schemes as $scheme ) {
        $scheme = strtolower( (string) $scheme );

        if ( $scheme !== '' && strpos( $normalized, $scheme ) !== false ) {
          $this->reject( '/* Rejected due to dangerous scheme inside url(). */' );

          return $this;
        }
      }
    }

    return $this;
  }

  /**
   * Reject unallowed at-rules.
   *
   * Note: Allowed rules are neutralized first.
   *
   * @param string|null $buffer   Optional. Scan buffer (defaults to `$this->without_imports()`).
   * @param array|null  $allowed  Allowed at-rules without @. Defaults to
   *                              ['media', 'container', 'keyframes', 'supports'].
   *
   * @return Css_Validator Instance.
   */

  public function reject_unallowed_at_rules( $buffer = null, $allowed = null ) : self {
    if ( $this->rejected() ) {
      return $this;
    }

    if ( empty( $allowed ) ) {
      $allowed = ['media', 'container', 'keyframes', 'supports'];
    }

    $buffer = $buffer === null ? $this->without_imports() : (string) $buffer;

    if ( $buffer === '' || strpos( $buffer, '@' ) === false ) {
      return $this;
    }

    $decoded = $this->decode( $buffer );

    $scan = preg_replace( '/"(?:\\\\.|[^"\\\\])*"|\'(?:\\\\.|[^\'\\\\])*\'/s', '', $decoded );

    if ( $scan === null ) {
      $this->reject( '/* Rejected due to regex error. */' );

      return $this;
    }

    if ( $allowed ) {
      $escaped = array_map(
        function( $r ) {
          return preg_quote( (string) $r, '/' );
        },
        $allowed
      );

      $scan = preg_replace( '/@\s*(?:' . implode( '|', $escaped ) . ')\b/i', '.dummy', $scan );

      if ( $scan === null ) {
        $this->reject( '/* Rejected due to regex error. */' );

        return $this;
      }
    }

    if ( strpos( $scan, '@' ) !== false ) {
      $this->reject( '/* Rejected due to unallowed @-rule. */' );
    }

    return $this;
  }

  /**
   * Check for brace balance.
   *
   * @return Css_Validator Instance.
   */

  public function reject_unbalanced_braces() : self {
    if ( $this->rejected() || $this->raw === '' ) {
      return $this;
    }

    $open  = substr_count( $this->raw, '{' );
    $close = substr_count( $this->raw, '}' );

    if ( $open < 1 || $open !== $close ) {
      $this->reject( '/* Rejected due to mismatched opening/closing braces. */' );
    }

    return $this;
  }
}
