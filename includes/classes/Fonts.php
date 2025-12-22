<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Fonts {
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

  public static function get_font_family( $option, $font_default, $mod_default ) : string {
    return Utils_Admin::get_font_family( $option, $font_default, $mod_default );
  }

  /**
   * Return a CSS font-family value, quoted if required.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils class.
   *
   * @param string $font_value  Font family name (single family, no commas).
   * @param string $quote       Optional. Wrapping character. Default '"'.
   *
   * @return string Ready to use font-family value.
   */

  public static function get_font_family_value( $font_value, $quote = '"' ) : string {
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

  public static function extract_font_from_google_link( $link ) {
    return Utils_Admin::extract_font_from_google_link( $link );
  }

  /**
   * [Delegate] Return fonts included by the theme.
   *
   * Note: If a font.json contains a { "remove": true } node, the font will not
   * be added to the result array and therefore removed from the site.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @return array Array of font data. Keys: skip, chapter, version, key, name,
   *               family, type, styles, weights, charsets, formats, about, note,
   *               sources, css_path, css_file, and in_child_theme.
   */

  public static function get_font_data() : array {
    return Utils_Admin::get_font_data();
  }

  /**
   * [Delegate] Build bundled font stylesheet.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   */

  public static function bundle_fonts() : void {
    Utils_Admin::bundle_fonts();
  }

  /**
   * Return array of font items.
   *
   * Note: The css string can contain quotes in case of multiple words,
   * such as "Roboto Mono".
   *
   * @since 5.1.1
   * @since 5.10.0 - Refactor for font manager.
   * @since 5.12.5 - Add theme mod for chapter body font.
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @return array Font items (css, name, and alt).
   */

  public static function get_fonts() : array {
    $custom_fonts = get_option( 'fictioneer_chapter_fonts' );

    if ( ! is_array( $custom_fonts ) ) {
      $custom_fonts = Utils::bundle_fonts();
    }

    $primary_css = Utils::get_font_family_value( FICTIONEER_PRIMARY_FONT_CSS );
    $primary_chapter_font = get_theme_mod( 'chapter_chapter_body_font_family_value', 'default' );

    $fonts = array(
      array( 'css' => $primary_css, 'name' => FICTIONEER_PRIMARY_FONT_NAME ),
      array( 'css' => '', 'name' => _x( 'System Font', 'Font name.', 'fictioneer' ) )
    );

    $seen = array( $primary_css => true, '' => true );

    foreach ( $custom_fonts as $custom_font ) {
      $css = $custom_font['css'];

      if ( isset( $seen[ $css ] ) ) {
        continue;
      }

      $seen[ $css ] = true;

      if (
        $primary_chapter_font !== 'default' &&
        strpos( $custom_font['css'], $primary_chapter_font ) !== false
      ) {
        array_unshift( $fonts, $custom_font );
      } else {
        $fonts[] = $custom_font;
      }
    }

    return apply_filters( 'fictioneer_filter_fonts', $fonts );
  }

  /**
   * Return array of disabled font keys.
   *
   * @since 5.33.2
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
}
