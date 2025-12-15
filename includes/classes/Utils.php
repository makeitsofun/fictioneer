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
}
