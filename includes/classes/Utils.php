<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Utils {
  use Singleton_Trait;

  /**
   * Return directory path of the theme cache.
   *
   * @since 5.23.1
   * @since 5.33.3 - Moved into Utils class.
   *
   * @param string|null $context  Optional. Context of the call. Default null.
   *
   * @return string Path of the cache directory.
   */

  public static function get_cache_dir( $context = null ) {
    static $checked = false;

    $dir = apply_filters(
      'fictioneer_filter_cache_dir',
      WP_CONTENT_DIR . '/cache/fictioneer/',
      $context
    );

    $dir = trailingslashit( $dir );

    if ( ! $checked ) {
      $checked = true;

      if ( ! is_dir( $dir ) ) {
        if ( ! wp_mkdir_p( $dir ) ) {
          error_log(
            sprintf(
              '[Fictioneer] Failed to create cache directory: %s (context: %s)',
              $dir,
              $context ?? 'none'
            )
          );
        }
      }
    }

    return $dir;
  }

  /**
   * Return theme cache URI.
   *
   * @since 5.23.1
   * @since 5.33.3 - Moved into Utils class.
   *
   * @param string|null $context  The context of the call. Default null.
   *
   * @return string Theme cache URI.
   */

  public static function get_cache_uri( $context = null ) : string {
    $uri = apply_filters(
      'fictioneer_filter_cache_uri',
      content_url( 'cache/fictioneer' ),
      $context
    );

    return trailingslashit( $uri );
  }

  /**
   * Wrapper for wp_parse_list() with optional sanitizer.
   *
   * @since 5.33.2
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
   * @since 5.33.2 - Moved into Utils class.
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
   * @since 5.33.2 - Moved into Utils class.
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

  /**
   * Return current main pagination page.
   *
   * @since 5.32.4
   * @since 5.33.2 - Moved into Utils class.
   *
   * @return int Current page or 1.
   */

  public static function get_global_page() : int {
    $paged = absint( get_query_var( 'paged' ) );
    $page = absint( get_query_var( 'page' ) );

    return max( 1, $paged, $page );
  }

  /**
   * Encrypt data.
   *
   * @since 5.19.0
   * @since 5.33.2 - Moved into Utils class.
   *
   * @param mixed $data  Data to encrypt.
   *
   * @return string|false Encrypted data or false on failure.
   */

  public static function encrypt( mixed $data ) : string|false {
    $plaintext = json_encode( $data );

    if ( $plaintext === false ) {
      return false;
    }

    $cipher = 'aes-256-gcm';

    if ( ! in_array( $cipher, openssl_get_cipher_methods(), true ) ) {
      return false;
    }

    $key = hash( 'sha256', wp_salt( 'auth' ), true );
    $iv = random_bytes( 12 );
    $tag = '';

    $cipher_text = openssl_encrypt(
      $plaintext,
      $cipher,
      $key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
      '',
      16
    );

    if ( $cipher_text === false || $tag === '' ) {
      return false;
    }

    return base64_encode( $iv . $tag . $cipher_text );
  }

  /**
   * Decrypt data.
   *
   * @since 5.19.0
   * @since 5.33.2 - Moved into Utils class.
   *
   * @param string $payload  Data to decrypt.
   *
   * @return mixed Decrypted data.
   */

  public static function decrypt( string $payload ) : mixed {
    $raw = base64_decode( $payload, true );

    if ( $raw === false ) {
      return false;
    }

    if ( strlen( $raw ) < 28 ) {
      return false;
    }

    $iv = substr( $raw, 0, 12 );
    $tag = substr( $raw, 12, 16 );
    $cipher_text = substr( $raw, 28 );

    $cipher = 'aes-256-gcm';

    if ( ! in_array( $cipher, openssl_get_cipher_methods(), true ) ) {
      return false;
    }

    $key = hash( 'sha256', wp_salt( 'auth' ), true );

    $plaintext = openssl_decrypt(
      $cipher_text,
      $cipher,
      $key,
      OPENSSL_RAW_DATA,
      $iv,
      $tag,
      ''
    );

    if ( $plaintext === false ) {
      return false;
    }

    return json_decode( $plaintext, true );
  }

  /**
   * Add class to element HTML string.
   *
   * @since 5.32.0
   * @since 5.33.2 - Moved into Utils class.
   *
   * @param string $html   HTML of the element.
   * @param string $class  CSS class string to be added.
   *
   * @return string Element HTML with the class added.
   */

  public static function add_class_to_element( string $html, string $class ) : string {
    if ( $html === '' || $class === '' ) {
      return $html;
    }

    $class = trim( $class );

    if ( preg_match( '/^<[^>]+\sclass=(["\'])/i', $html, $m ) ) {
      return preg_replace(
        '/\sclass=(["\'])/i',
        ' class=$1' . $class . ' ',
        $html,
        1
      );
    }

    return preg_replace(
      '/^<([a-z][a-z0-9:-]*)([^>]*)>/i',
      '<$1$2 class="' . $class . '">',
      $html,
      1
    );
  }

  /**
   * Return theme icon HTML set in the Customizer.
   *
   * @since 5.32.0
   * @since 5.33.2 - Moved into Utils class.
   *
   * @param string      $name     Name of the icon.
   * @param string|null $default  Optional. Fallback icon, defaults to empty string.
   * @param array|null  $args     Optional. Additional arguments. Supports:
   *   - 'class' (string) : CSS classes.
   *   - 'title' (string) : Title attribute.
   *   - 'data' (array) : Associative array of `data-*` attributes.
   *   - 'no_cache' (bool) : Skip caching if not needed.
   *
   * @return string The icon HTML.
   */

  public static function get_theme_icon( string $name, ?string $default = '', ?array $args = [] ) : string {
    static $cache = [];

    $id = isset( $args['id'] ) ? (string) $args['id'] : '';
    $class = isset( $args['class'] ) ? (string) $args['class'] : '';
    $title = isset( $args['title'] ) ? (string) $args['title'] : '';
    $data = isset( $args['data'] ) && is_array( $args['data'] ) ? $args['data'] : [];

    $attributes = '';

    if ( $title !== '' ) {
      $attributes .= ' title="' . esc_attr( $title ) . '"';
    }

    if ( $id !== '' ) {
      $attributes .= ' id="' . esc_attr( $id ) . '"';
    }

    if ( $data ) {
      foreach ( $data as $key => $value ) {
        if ( $key ) {
          $attributes .= ' data-' . $key . '="' . esc_attr( $value ) . '"';
        }
      }
    }

    $key = empty( $args['no_cache'] )
      ? md5( $name . '|' . (string) $default . '|' . $class . '|' . $attributes )
      : false;

    if ( $key && isset( $cache[ $key ] ) ) {
      return $cache[ $key ];
    }

    $icon = get_theme_mod( $name, $default ) ?: $default;
    $icon = Utils::add_class_to_element( $icon, $class );

    if ( $attributes !== '' ) {
      $p = strpos( $icon, 'class="' );

      if ( $p !== false ) {
        $icon = substr_replace( $icon, $attributes, $p, 0 );
      }
    }

    if ( $key ) {
      $cache[ $key ] = $icon;
    }

    return $icon;
  }

  /**
   * [Delegate] Return associative array of theme colors.
   *
   * Notes: Considers both parent and child theme.
   *
   * @since 5.21.2
   * @since 5.33.2 - Refactored and moved into Utils_Admin class.
   *
   * @return array Associative array of theme colors.
   */

  public static function get_theme_colors() : array {
    return Utils_Admin::get_theme_colors();
  }

  /**
   * [Delegate] Return theme color mod with default fallback.
   *
   * @since 5.12.0
   * @since 5.21.2 - Refactored with theme colors helper function.
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string      $mod      Requested theme color.
   * @param string|null $default  Optional. Default color code.
   *
   * @return string Requested color code or '#ff6347' (tomato) if not found.
   */

  public static function get_theme_color( string $mod, ?string $default = null ) : string {
    return Utils_Admin::get_theme_color( $mod, $default );
  }

  /**
   * [Delegate] Convert hex color to RGB array.
   *
   * @license MIT
   * @author Simon Waldherr https://github.com/SimonWaldherr
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   * @link https://github.com/SimonWaldherr/ColorConverter.php
   *
   * @param string $value  The to be converted hex (six digits).
   *
   * @return array|bool RGB values as array or false on failure.
   */

  public static function hex_to_rgb( string $value ) : array|bool {
    return Utils_Admin::hex_to_rgb( $value );
  }

  /**
   * [Delegate] Convert RGB color array to HSL.
   *
   * @license MIT
   * @author Simon Waldherr https://github.com/SimonWaldherr
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   * @link https://github.com/SimonWaldherr/ColorConverter.php
   *
   * @param array $value      To be converted RGB array (r, g, b).
   * @param int   $precision  Optional. Rounding precision. Default 0.
   *
   * @return array HSL values as array.
   */

  public static function rgb_to_hsl( array $value, int $precision = 0 ) : array {
    return Utils_Admin::rgb_to_hsl( $value, $precision );
  }

  /**
   * [Delegate] Convert a hex color to a Fictioneer HSL code.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $hex     Hex color.
   * @param string $output  Switch output style. Default 'default'.
   *
   * @return string Converted HSL code.
   */

  public static function get_hsl_code( string $hex, string $output = 'default' ) : string {
    return Utils_Admin::get_hsl_code( $hex, $output );
  }

  /**
   * [Delegate] Convert a hex color to an HSL font code.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $hex  Hex color.
   *
   * @return string Converted HSL font code.
   */

  public static function get_hsl_font_code( string $hex ) : string {
    return Utils_Admin::get_hsl_font_code( $hex );
  }

  /**
   * [Delegate] Return a font family value.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $option        Name of the theme mod.
   * @param string $font_default  Fallback font.
   * @param string $mod_default   Default for get_theme_mod().
   *
   * @return string Ready to use font family value.
   */

  public static function get_font_family( string $option, string $font_default, string $mod_default ) : string {
    return Utils_Admin::get_font_family( $option, $font_default, $mod_default );
  }

  /**
   * Return a CSS font-family value, quoted if required.
   *
   * @since 5.10.0
   * @since 5.33.3 - Moved into Utils class.
   *
   * @param string $font_value  Font family name (single family, no commas).
   * @param string $quote       Optional. Wrapping character. Default '"'.
   *
   * @return string Ready to use font-family value.
   */

  public static function get_font_family_value( string $font_value, string $quote = '"' ) : string {
    $font_value = trim( $font_value );

    if ( $font_value === '' ) {
      return '';
    }

    if ( str_contains( $font_value, ',' ) ) {
      return $font_value;
    }

    if ( preg_match( '/\s/', $font_value ) ) {
      return $quote . $font_value . $quote;
    }

    return $font_value;
  }

  /**
   * [Delegate] Return fonts data from a Google Fonts link.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $link  Google Fonts link.
   *
   * @return array|false|null Font data if successful, false if malformed,
   *                          null if not a valid Google Fonts link.
   */

  public static function extract_font_from_google_link( string $link ) : array|false|null {
    return Utils_Admin::extract_font_from_google_link( $link );
  }

  /**
   * [Delegate] Build bundled font stylesheet.
   *
   * @since 5.10.0
   * @since 5.33.3 - Moved into Utils_Admin class.
   */

  public static function bundle_fonts() : void {
    Utils_Admin::bundle_fonts();
  }

  /**
   * Return array of disabled font keys.
   *
   * @since 5.33.3
   *
   * @return array Disabled font keys.
   */

  public static function get_disabled_fonts() : array {
    $disabled_fonts = get_option( 'fictioneer_disabled_fonts', [] );

    if ( ! is_array( $disabled_fonts ) ) {
      update_option( 'fictioneer_disabled_fonts', [] );

      return [];
    }

    return $disabled_fonts;
  }

  /**
   * Return minified CSS.
   *
   * @license CC BY-SA 4.0
   * @author Qtax https://stackoverflow.com/users/107152/qtax
   * @author lots0logs https://stackoverflow.com/users/2639936/lots0logs
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils class.
   * @link https://stackoverflow.com/a/15195752/17140970
   * @link https://stackoverflow.com/a/44350195/17140970
   *
   * @param string $string  CSS to be minified.
   *
   * @return string Minified CSS.
   */

  public static function minify_css( string $css = '' ) : string {
    $comments = <<<'EOS'
    (?sx)
        # don't change anything inside of quotes
        ( "(?:[^"\\]++|\\.)*+" | '(?:[^'\\]++|\\.)*+' )
    |
        # comments
        /\* (?> .*? \*/ )
    EOS;

    $everything_else = <<<'EOS'
    (?six)
        # don't change anything inside of quotes
        ( "(?:[^"\\]++|\\.)*+" | '(?:[^'\\]++|\\.)*+' )
    |
        # spaces before and after ; and }
        \s*+ ; \s*+ ( } ) \s*+
    |
        # all spaces around meta chars/operators (excluding + and -)
        \s*+ ( [*$~^|]?+= | [{};,>~] | !important\b ) \s*+
    |
        # all spaces around + and - (in selectors only!)
        \s*([+-])\s*(?=[^}]*{)
    |
        # spaces right of ( [ :
        ( [[(:] ) \s++
    |
        # spaces left of ) ]
        \s++ ( [])] )
    |
        # spaces left (and right) of : (but not in selectors)!
        \s+(:)(?![^\}]*\{)
    |
        # spaces at beginning/end of string
        ^ \s++ | \s++ \z
    |
        # double spaces to single
        (\s)\s+
    EOS;

    $search_patterns  = array( "%{$comments}%", "%{$everything_else}%" );
    $replace_patterns = array( '$1', '$1$2$3$4$5$6$7$8' );

    return preg_replace( $search_patterns, $replace_patterns, $css );
  }
}
