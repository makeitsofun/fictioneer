<?php

use Fictioneer\Sanitizer;
use Fictioneer\Sanitizer_Admin;
use Fictioneer\Utils;
use Fictioneer\Utils_Admin;
use Fictioneer\Customizer;
use Fictioneer\Fonts;
use Fictioneer\Story;
use Fictioneer\User;

// =============================================================================
// SANITIZER DELEGATES
// =============================================================================

/**
 * [Deprecated] Sanitize an integer with options for default, minimum, and maximum.
 *
 * @since 4.0.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_integer() instead.
 *
 * @param mixed    $value    The value to be sanitized.
 * @param mixed    $default  Optional. Fallback value. Default 0.
 * @param int|null $min      Optional. Minimum value. Default is no minimum.
 * @param int|null $max      Optional. Maximum value. Default is no maximum.
 *
 * @return int The sanitized integer.
 */

function fictioneer_sanitize_integer( $value, $default = 0, $min = null, $max = null ) {
  return Sanitizer::sanitize_integer( $value, $default, $min, $max );
}

/**
 * [Deprecated] Sanitize integer to be 1 or more.
 *
 * @since 4.6.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_integer_one_up() instead.
 *
 * @param mixed $input  The input value to sanitize.
 *
 */

function fictioneer_sanitize_integer_one_up( $input ) {
  return Sanitizer::sanitize_integer_one_up( $input );
}

/**
 * [Deprecated] Sanitize the 'words per minute' setting with fallback.
 *
 * @since 4.0.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::fictioneer_sanitize_words_per_minute() instead.
 *
 * @param mixed $input  The input value to sanitize.
 *
 * @return int The sanitized integer.
 */

function fictioneer_sanitize_words_per_minute( $input ) {
  return Sanitizer::sanitize_integer_words_per_minute( $input );
}

/**
 * [Deprecated] Sanitize callback with float or default 0.
 *
 * @since 5.19.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_float() instead.
 *
 * @param mixed $value  The value to be sanitized.
 *
 * @return float The sanitized float.
 */

function fictioneer_sanitize_float( $value ) {
  return Sanitizer::sanitize_float( $value );
}

/**
 * [Deprecated] Sanitizes a float as positive number.
 *
 * @since 5.9.4
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_float_zero_positive() instead.
 *
 * @param mixed $value    The value to be sanitized.
 * @param float $default  Default value if an invalid float is provided. Default 0.0.
 *
 * @return float The sanitized float.
 */

function fictioneer_sanitize_positive_float( $value, $default = 0.0 ) {
  return Sanitizer::sanitize_float_zero_positive( $value,  $default );
}

/**
 * [Deprecated] Sanitize callback with positive float or default 1.0.
 *
 * @since 5.10.1
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_float_zero_positive_def1() instead.
 *
 * @param mixed $value  The value to be sanitized.
 *
 * @return float The sanitized positive float.
 */

function fictioneer_sanitize_positive_float_def1( $value ) {
  return Sanitizer::sanitize_float_zero_positive_def1( $value );
}

/**
 * [Deprecated] Sanitize a checkbox value into true or false.
 *
 * @since 4.7.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_bool() instead.
 *
 * @param string|boolean $value  The checkbox value to be sanitized.
 *
 * @return boolean True or false.
 */

function fictioneer_sanitize_checkbox( $value ) {
  return Sanitizer::sanitize_bool( $value, true );
}

/**
 * [Deprecated] Explode string into an array.
 *
 * @since 5.1.3
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::parse_list() in comma-mode instead.
 *
 * @param string $string  The string to explode.
 *
 * @return array The string content as array.
 */

function fictioneer_explode_list( $string ) {
  return Utils::parse_list( $string, null, 'comma' );
}

/**
 * [Deprecated] Sanitize (and transform) a comma-separated list into an array.
 *
 * @since 5.15.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::parse_list() in comma-mode instead.
 *
 * @param string $input  The comma-separated list.
 * @param array  $args   Deprecated. Optional flags ('unique', 'absint').
 *
 * @return array The comma-separated list turned array.
 */

function fictioneer_sanitize_list_into_array( $input, $args = []  ) {
  $list = Utils::parse_list( $input, ( $args['absint'] ?? 0 ) ? 'absint' : null, 'comma' );

  if ( $args['unique'] ?? 0 ) {
    $list = array_values( array_unique( $list ) );
  }

  return $list;
}

/**
 * [Deprecated] Sanitize comma-separated list of IDs.
 *
 * @since 5.32.0
 * @deprecated 5.34.0 - Use wp_parse_id_list() instead.
 *
 * @param string $input  List of IDs.
 *
 * @return string The sanitized list of IDs.
 */

function fictioneer_sanitize_comma_separated_ids( $input ) {
  return wp_parse_id_list( $input );
}

/**
 * [Deprecated] Sanitize a comma-separated Patreon ID list into a unique array.
 *
 * @since 5.15.0
 * @since 5.32.0 - Changed to alias for generic sanitizer.
 * @deprecated 5.34.0 - Use wp_parse_id_list() instead.
 *
 * @param string $input  The comma-separated list.
 *
 * @return array The comma-separated list turned array (unique items).
 */

function fictioneer_sanitize_global_patreon_tiers( $input ) {
  return wp_parse_id_list( $input );
}

/**
 * [Deprecated] Sanitize an URL.
 *
 * @since 5.19.1
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_url() instead.
 *
 * @param string      $url         The URL entered.
 * @param string|null $match       Optional. URL must start with this string.
 * @param string|null $preg_match  Optional. String for a preg_match() test.
 *
 * @return string The sanitized URL or an empty string if invalid.
 */

function fictioneer_sanitize_url( $url, $match = null, $preg_match = null ) {
  return Sanitizer::sanitize_url( $url, $match, $preg_match );
}

/**
 * [Deprecated] Sanitize a Patreon URL.
 *
 * @since 5.15.0
 * @since 5.19.1 - Split up into two functions.
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_url() instead.
 *
 * @param string $url  The URL entered.
 *
 * @return string The sanitized URL or an empty string if invalid.
 */

function fictioneer_sanitize_patreon_url( $url ) {
  return Sanitizer::sanitize_url( $url, null, '#^https://(www\.)?patreon\.com(?:/|$)#i' );
}

/**
 * [Deprecated] Sanitize a selected option.
 *
 * @since 5.7.4
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_selection() instead.
 *
 * @param mixed $value            The selected value to be sanitized.
 * @param array $allowed_options  The allowed values to be checked against.
 * @param mixed $default          Optional. The default value as fallback.
 *
 * @return mixed The sanitized value or default, null if not provided.
 */

function fictioneer_sanitize_selection( $value, $allowed_options, $default = null ) {
  return Sanitizer::sanitize_selection( $value, $allowed_options, $default );
}

/**
 * [Deprecated] Sanitize a CSS string.
 *
 * @since 5.7.4
 * @since 5.27.4 - Unslash string.
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_css() instead.
 *
 * @param string $css  The CSS string to be sanitized. Expects slashed string.
 *
 * @return string The sanitized string.
 */

function fictioneer_sanitize_css( $css ) {
  return Sanitizer::sanitize_css( $css );
}

/**
 * [Deprecated] Sanitize a query variable.
 *
 * @since 5.14.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_query_var() instead.
 *
 * @param string      $var      Query variable to sanitize.
 * @param array       $allowed  Array of allowed string (lowercase).
 * @param string|null $default  Optional default value.
 * @param array       $args {
 *   Optional. An array of additional arguments.
 *
 *   @type bool $keep_case  Whether to transform the variable to lowercase. Default false.
 * }
 *
 *
 * @return string The sanitized (lowercase) query variable.
 */

function fictioneer_sanitize_query_var( $var, $allowed, $default = null, $args = [] ) {
  return Sanitizer::sanitize_query_var( $var, $allowed, $default, $args );
}

/**
 * [Deprecated] Sanitize a post type string.
 *
 * Note: Also associates simple strings like 'story' with their
 * registered post type, such as 'fcn_story'.
 *
 * @since 5.33.5
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_post_type() instead.
 *
 * @param string $post_type  The string to be sanitized.
 *
 * @return string The sanitized value.
 */

function fictioneer_sanitize_post_type( $post_type ) {
  return Sanitizer::sanitize_post_type( $post_type );
}

/**
 * [Deprecated] Sanitize editor content.
 *
 * Removes malicious HTML, magic quote slashes, shortcodes, and blocks.
 *
 * @since 5.7.4
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_meta_field_editor() instead.
 *
 * @param string $content  The content to be sanitized.
 *
 * @return string The sanitized content.
 */

function fictioneer_sanitize_editor( $content ) {
  return Sanitizer::sanitize_meta_field_editor( $content );
}

/**
 * [Deprecated] Return sanitized image ID that must exist.
 *
 * @since 5.30.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_image_id() instead.
 *
 * @param int|string $id  Image ID.
 *
 * @return int Image ID or 0 if not found.
 */

function fictioneer_sanitize_image_id( $id ) {
  return Sanitizer::sanitize_image_id( $id );
}

/**
 * [Deprecated] Return sanitized icon HTML.
 *
 * @since 5.32.0
 *
 * @param string $html  Icon HTML.
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_icon_html() instead.
 *
 * @return string Sanitized icon HTML.
 */

function fictioneer_sanitize_icon_html( $html ) {
  return Sanitizer::sanitize_icon_html( $html );
}

/**
 * [Deprecated] Return sanitized safe title.
 *
 * @since 5.7.1
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_safe_title() instead.
 *
 * @param string $title  Post title.
 * @param string $date   The date.
 * @param string $time   The time.
 *
 * @return string The sanitized title.
 */

function fictioneer_sanitize_safe_title( $title, $date, $time ) {
  return Sanitizer::sanitize_safe_title( $title, $date, $time );
}

/**
 * [Deprecated] Sanitize a page ID and checks whether it is valid.
 *
 * @since 4.6.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::sanitize_page_id() instead.
 *
 * @param mixed $input  The page ID to be sanitized.
 *
 * @return int The sanitized page ID or -1 if not a page.
 */

function fictioneer_sanitize_page_id( $input ) {
  return Sanitizer_Admin::sanitize_page_id( $input );
}

/**
 * [Deprecated] Sanitize with absint() unless it is an empty string.
 *
 * @since 5.15.0
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::sanitize_absint_or_empty_string() instead.
 *
 * @param mixed $input  The input value to sanitize.
 *
 * @return mixed The sanitized integer or an empty string.
 */

function fictioneer_sanitize_absint_or_empty_string( $input ) {
  return Sanitizer_Admin::sanitize_absint_or_empty_string( $input );
}

// =============================================================================
// CUSTOMIZER DELEGATES
// =============================================================================

if ( ! function_exists( 'fictioneer_hex_to_rgb' ) ) {
  /**
   * [Deprecated] Convert hex colors to RGB.
   *
   * @license MIT
   * @author Simon Waldherr https://github.com/SimonWaldherr
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::hex_to_rgb() instead.
   * @link https://github.com/SimonWaldherr/ColorConverter.php
   *
   * @param string $input  The to be converted hex (six digits).
   *
   * @return array RGB values as array.
   */

  function fictioneer_hex_to_rgb( $input ) {
    return Utils::hex_to_rgb( $input );
  }
}

if ( ! function_exists( 'fictioneer_rgb_to_hsl' ) ) {
  /**
   * [Deprecated] Convert RGB colors to HSL.
   *
   * @license MIT
   * @author Simon Waldherr https://github.com/SimonWaldherr
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::rgb_to_hsl() instead.
   * @link https://github.com/SimonWaldherr/ColorConverter.php
   *
   * @param array $input      The to be converted RGB.
   * @param int   $precision  Rounding precision. Default 0.
   *
   * @return array HSL values as array.
   */

  function fictioneer_rgb_to_hsl( $input, $precision = 0 ) {
    return Utils::rgb_to_hsl( $input, $precision );
  }
}

if ( ! function_exists( 'fictioneer_get_css_clamp' ) ) {
  /**
   * [Deprecated] Generate a high-precision CSS clamp.
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Customizer::get_clamp() instead.
   *
   * @param int    $min   The minimum value.
   * @param int    $max   The maximum value.
   * @param int    $wmin  The minimum viewport value.
   * @param int    $wmax  The maximum viewport value.
   * @param string $unit  The relative clamp unit. Default 'vw'.
   *
   * @return string The calculated clamp.
   */

  function fictioneer_get_css_clamp( $min, $max, $wmin, $wmax, $unit = 'vw' ) {
    return Customizer::get_clamp( $min, $max, $wmin, $wmax, $unit );
  }
}

if ( ! function_exists( 'fictioneer_hsl_code' ) ) {
  /**
   * [Deprecated] Convert a hex color to a Fictioneer HSL code.
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_hsl_code() instead.
   *
   * @param string $hex     The color as hex.
   * @param string $output  Switch output style. Default 'default'.
   *
   * @return string The converted color.
   */

  function fictioneer_hsl_code( $hex, $output = 'default' ) {
    return Utils::get_hsl_code( $hex, $output );
  }
}

if ( ! function_exists( 'fictioneer_hsl_font_code' ) ) {
  /**
   * [Deprecated] Convert a hex color to a Fictioneer HSL font code.
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_hsl_font_code() instead.
   * @see fictioneer_hsl_code( $hex, $output )
   *
   * @param string $hex  The color as hex.
   *
   * @return string The converted color.
   */

  function fictioneer_hsl_font_code( $hex ) {
    return Utils::get_hsl_font_code( $hex );
  }
}

/**
 * [Deprecated] Helper that returns a font family value.
 *
 * @since 5.10.0
 * @deprecated 5.34.0 - Use \Fictioneer\Fonts::get_font_family() instead.
 *
 * @param string $option        Name of the theme mod.
 * @param string $font_default  Fallback font.
 * @param string $mod_default   Default for get_theme_mod().
 *
 * @return string Ready to use font family value.
 */

function fictioneer_get_custom_font( $option, $font_default, $mod_default ) {
  return Fonts::get_font_family( $option, $font_default, $mod_default );
}

/**
 * [Deprecated] Return the CSS loaded from a snippet file.
 *
 * @since 5.11.1
 * @deprecated 5.34.0 - Use \Fictioneer\Customizer::get_css_snippet() instead.
 *
 * @param string $snippet      Name of the snippet file without file ending.
 * @param string|null $filter  Optional. Part of the generated filter, defaulting
 *                             to the snippet name (lower case, underscores).
 *
 * @return string The CSS string from the file.
 */

function fictioneer_get_customizer_css_snippet( $snippet, $filter = null ) {
  return Customizer::get_css_snippet( $snippet );
}

/**
 * Return associative array of theme colors.
 *
 * Notes: Considers both parent and child theme.
 *
 * @since 5.21.2
 *
 * @return array Associative array of theme colors.
 */

function fictioneer_get_theme_colors_array() {
  return Utils::get_theme_colors();
}

/**
 * [Deprecated] Helper to get theme color mod with default fallback.
 *
 * @since 5.12.0
 * @since 5.21.2 - Refactored with theme colors helper function.
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_theme_color() instead.
 *
 * @param string $mod           The requested theme color.
 * @param string|null $default  Optional. Default color code.
 *
 * @return string The requested color code or '#ff6347' (tomato) if not found.
 */

function fictioneer_get_theme_color( $mod, $default = null ) {
  return Utils::get_theme_color( $mod, $default );
}

/**
 * [Deprecated] Build the customization stylesheet.
 *
 * @since 5.11.0
 * @deprecated 5.34.0 - Use \Fictioneer\Customizer::build_customizer_css() instead.
 *
 * @param string|null $context  Optional. In which context the stylesheet created,
 *                              for example 'preview' for the Customizer.
 */

function fictioneer_build_customize_css( $context = null ) {
  Customizer::build_customizer_css( $context );
}

if ( ! function_exists( 'fictioneer_get_fading_gradient' ) ) {
  /**
   * [Deprecated] Return an eased fading linear-gradient CSS.
   *
   * @since 5.11.0
   * @deprecated 5.34.0 - Use \Fictioneer\Customizer::get_fading_gradient() instead.
   *
   * @param float  $start_opacity  The starting opacity of the gradient in percentage.
   * @param int    $start          The starting point of the gradient in percentage.
   * @param int    $end            The ending point of the gradient in percentage.
   * @param string $direction      The direction of the gradient with unit (e.g. '180deg').
   * @param string $hsl            The HSL string used as color. Default '0 0% 0%'.
   *
   * @return string The linear-gradient CSS.
   */

  function fictioneer_get_fading_gradient( $start_opacity, $start, $end, $direction, $hsl = '0 0% 0%' ) {
    return Customizer::get_fading_gradient( $start_opacity, $start, $end, $direction, $hsl );
  }
}

/**
 * [Deprecated] Build bundled font stylesheet.
 *
 * @since 5.10.0
 * @deprecated 5.34.0 - Use \Fictioneer\Fonts::bundle_fonts() instead.
 */

function fictioneer_build_bundled_fonts() : void {
  Fonts::bundle_fonts();
}

/**
 * [Deprecated] Return fonts data from a Google Fonts link.
 *
 * @since 5.10.0
 * @deprecated 5.34.0 - Use \Fictioneer\Fonts::extract_font_from_google_link() instead.
 *
 * @param string $link  Google Fonts link.
 *
 * @return array|false|null Font data if successful, false if malformed,
 *                          null if not a valid Google Fonts link.
 */

function fictioneer_extract_font_from_google_link( $link ) {
  return Fonts::extract_font_from_google_link( $link );
}

/**
 * [Deprecated] Return fonts included by the theme.
 *
 * Note: If a font.json contains a { "remove": true } node, the font will not
 * be added to the result array and therefore removed from the site.
 *
 * @since 5.10.0
 * @deprecated 5.34.0 - Use \Fictioneer\Fonts::get_font_data() instead.
 *
 * @return array Array of font data. Keys: skip, chapter, version, key, name,
 *               family, type, styles, weights, charsets, formats, about, note,
 *               sources, css_path, css_file, and in_child_theme.
 */

function fictioneer_get_font_data() : array {
  return Fonts::get_font_data();
}

// =============================================================================
// UTILITY DELEGATES
// =============================================================================

/**
 * [Deprecated] Return directory path of the theme cache.
 *
 * @since 5.23.1
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_cache_dir() instead.
 *
 * @param string|null $context  Optional. Context of the call. Default null.
 *
 * @return string Path of the cache directory.
 */

function fictioneer_get_theme_cache_dir( $context = null ) {
  return Utils::get_cache_dir( $context );
}

/**
 * [Deprecated] Return theme cache URI.
 *
 * @since 5.23.1
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_cache_uri() instead.
 *
 * @param string|null $context  The context of the call. Default null.
 *
 * @return string Theme cache URI.
 */

function fictioneer_get_theme_cache_uri( $context = null ) {
  return Utils::get_cache_uri( $context );
}

/**
 * [Deprecated] Return aspect ratio values as tuple.
 *
 * @since 5.14.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::split_aspect_ratio() instead.
 *
 * @param string $css  Aspect-ratio CSS value.
 *
 * @return array Tuple of aspect-ratio values.
 */

function fictioneer_get_split_aspect_ratio( $css ) {
  return Utils::split_aspect_ratio( $css );
}

/**
 * [Deprecated] Return array of adjectives for randomized username generation.
 *
 * @since 5.19.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_username_adjectives() instead.
 *
 * @return array Array of adjectives.
 */

function fictioneer_get_username_adjectives() {
  return Utils_Admin::get_username_adjectives();
}

/**
 * [Deprecated] Return array of nouns for randomized username generation
 *
 * @since 5.19.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_username_nouns() instead.
 *
 * @return array Array of nouns.
 */

function fictioneer_get_username_nouns() {
  return Utils_Admin::get_username_nouns();
}

/**
 * [Deprecated] Return randomized username.
 *
 * @since 5.19.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_random_username() instead.
 *
 * @param bool $unique  Optional. Whether the username must be unique. Default true.
 *
 * @return string Sanitized random username.
 */

function fictioneer_get_random_username( $unique = true ) {
  return Utils_Admin::get_random_username( $unique );
}

if ( ! function_exists( 'fictioneer_get_clean_url' ) ) {
  /**
   * [Deprecated] Return URL without query arguments or page number.
   *
   * @since 5.4.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_clean_url() instead.
   *
   * @return string The clean URL.
   */

  function fictioneer_get_clean_url() {
    return Utils::get_clean_url();
  }
}

/**
 * [Deprecated] Encrypt data.
 *
 * @since 5.19.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::encrypt() instead.
 *
 * @param mixed $data  The data to encrypt.
 *
 * @return string|false The encrypted data or false on failure.
 */

function fictioneer_encrypt( $data ) {
  return Utils::encrypt( $data );
}

/**
 * [Deprecated] Decrypt data.
 *
 * @since 5.19.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::decrypt() instead.
 *
 * @param string $data  The data to decrypt.
 *
 * @return mixed The decrypted data.
 */

function fictioneer_decrypt( $data ) {
  return Utils::decrypt( $data );
}

/**
 * [Deprecated] Add or prepend class to element HTML string.
 *
 * @since 5.32.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::add_class_to_element() instead.
 *
 * @param string $html   HTML of the element.
 * @param string $class  CSS class string to be added.
 *
 * @return string Element HTML with the class added.
 */

function fictioneer_add_class_to_element( $html, $class ) {
  return Utils::add_class_to_element( $html, $class );
}

/**
 * [Deprecated] Return theme icon HTML set in the Customizer.
 *
 * @since 5.32.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_theme_icon() instead.
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

function fictioneer_get_theme_icon( $name, $default = '', $args = [] ) {
  return Utils::get_theme_icon( $name, $default, $args );
}

/**
 * [Deprecated] Return current main pagination page.
 *
 * @since 5.32.4
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_global_page() instead.
 *
 * @return int Current page or 1.
 */

function fictioneer_get_global_page() {
  return Utils::get_global_page();
}

/**
 * [Deprecated] Return a CSS font-family value, quoted if required.
 *
 * @since 5.10.0
 * @deprecated 5.34.0 - Use \Fictioneer\Fonts::get_font_family_value() instead.
 *
 * @param string $font_value  The font family value.
 * @param string $quote       Optional. The wrapping character. Default '"'.
 *
 * @return string Ready to use font family value.
 */

function fictioneer_font_family_value( $font_value, $quote = '"' ) {
  return Fonts::get_font_family_value( $font_value, $quote );
}

if ( ! function_exists( 'fictioneer_url_exists' ) ) {
  /**
   * [Deprecated] Check whether an URL exists.
   *
   * @since 4.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::url_exists() instead.
   *
   * @param string $url  The URL to check.
   *
   * @return bool True if the URL exists and false otherwise. Probably.
   */

  function fictioneer_url_exists( $url ) {
    return Utils_Admin::url_exists( $url );
  }
}

/**
 * [Deprecated] Check whether a JSON is valid.
 *
 * @since 4.0.0
 * @since 5.21.1 - Use json_validate() if on PHP 8.3 or higher.
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::json_validate() instead.
 *
 * @param string $data  JSON string hopeful.
 *
 * @return bool True if the JSON is valid, false if not.
 */

function fictioneer_is_valid_json( $data ) {
  return Utils::json_validate( $data );
}

if ( ! function_exists( 'fictioneer_get_user_by_id_or_email' ) ) {
  /**
   * [Deprecated] Get user by ID or email.
   *
   * @since 4.6.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_user_by_id_or_email() instead.
   *
   * @param mixed $id_or_email  User ID or email address.
   *
   * @return WP_User|false Returns the user or false if not found.
   */

  function fictioneer_get_user_by_id_or_email( $id_or_email ) {
    return Utils::get_user_by_id_or_email( $id_or_email );
  }
}

/**
 * [Deprecated] Unset the first occurrence of a value from an array.
 *
 * @since 5.7.5
 * @deprecated 5.34.0 - Use \Fictioneer\Utils::array_unset_by_value() instead.
 *
 * @param mixed $value   The value to look for.
 * @param array $array   The array to be modified.
 * @param bool  $strict  Whether to use strict comparison. Default false.
 *
 * @return array The modified array.
 */

function fictioneer_unset_by_value( $value, $array, $strict = false ) {
  Utils::array_unset_by_value( $value, $array, $strict );
}

if ( ! function_exists( 'fictioneer_minify_css' ) ) {
  /**
   * [Deprecated] Minify CSS.
   *
   * @license CC BY-SA 4.0
   * @author Qtax https://stackoverflow.com/users/107152/qtax
   * @author lots0logs https://stackoverflow.com/users/2639936/lots0logs
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::minify_css() instead.
   * @link https://stackoverflow.com/a/15195752/17140970
   * @link https://stackoverflow.com/a/44350195/17140970
   *
   * @param string $string  The to be minified CSS string.
   *
   * @return string The minified CSS string.
   */

  function fictioneer_minify_css( $string ) {
    return Utils::minify_css( $string );
  }
}

if ( ! function_exists( 'fictioneer_get_fonts' ) ) {
  /**
   * [Deprecated] Return array of font items.
   *
   * Note: The css string can contain quotes in case of multiple words,
   * such as "Roboto Mono".
   *
   * @since 5.1.1
   * @since 5.10.0 - Refactor for font manager.
   * @since 5.12.5 - Add theme mod for chapter body font.
   * @deprecated 5.34.0 - Use \Fictioneer\Fonts::get_fonts() instead.
   *
   * @return array Font items (css, name, and alt).
   */

  function fictioneer_get_fonts() {
    return Fonts::get_fonts();
  }
}

if ( ! function_exists( 'fictioneer_get_story_data' ) ) {
  /**
   * [Deprecated] Get collection of a story's data.
   *
   * @since 4.3.0
   * @since 5.25.0 - Refactored with custom SQL query.
   * @deprecated 5.34.0 - Use \Fictioneer\Story::get_data() instead.
   *
   * @param int     $story_id       ID of the story.
   * @param boolean $show_comments  Optional. Whether the comment count is needed.
   *                                Default true.
   * @param array   $args           Optional array of arguments.
   *
   * @return array|bool Data of the story or false if invalid.
   */

  function fictioneer_get_story_data( $story_id, $show_comments = true, $args = [] ) {
    return Story::get_data( $story_id, $show_comments, $args );
  }
}

/**
 * [Deprecated] Return array of chapter posts for a story.
 *
 * @since 5.9.2
 * @since 5.22.3 - Refactored.
 * @deprecated 5.34.0 - Use \Fictioneer\Story::get_chapter_posts() instead.
 *
 * @param int        $story_id  ID of the story.
 * @param array|null $args      Optional. Additional query arguments.
 * @param bool|null  $full      Optional. Whether to not reduce the posts. Default false.
 * @param bool|null  $slow      Optional. Whether to skip the fast query (if enabled). Default false.
 *
 * @return array Array of chapter posts or empty.
 */

function fictioneer_get_story_chapter_posts( $story_id, $args = [], $full = false, $slow = false ) {
  return Story::get_chapter_posts( $story_id, $args, $full, $slow );
}

/**
 * [Deprecated] Group and prepares chapters for a specific story.
 *
 * Note: If chapter groups are disabled, all chapters will be
 * within the 'all_chapters' group.
 *
 * @since 5.25.0
 * @deprecated 5.34.0 - Use \Fictioneer\Story::prepare_chapter_groups() instead.
 *
 * @param int   $story_id  ID of the story.
 * @param array $chapters  Array of WP_Post or post-like objects.
 *
 * @return array The grouped and prepared chapters.
 */

function fictioneer_prepare_chapter_groups( $story_id, $chapters ) {
  return Story::prepare_chapter_groups( $story_id, $chapters );
}

/**
 * [Deprecated] Returns the comment count of all story chapters
 *
 * Note: Includes comments from hidden and non-chapter chapters.
 *
 * @since 5.22.2
 * @since 5.22.3 - Switched to SQL query.
 * @deprecated 5.34.0 - Use \Fictioneer\Story::get_story_comment_count() instead.
 *
 * @param int        $story_id     ID of the story.
 * @param array|null $chapter_ids  Optional. Array of chapter IDs.
 *
 * @return int Number of comments.
 */

function fictioneer_get_story_comment_count( $story_id, $chapter_ids = null ) {
  return Story::get_story_comment_count( $story_id, $chapter_ids );
}

if ( ! function_exists( 'fictioneer_count_words' ) ) {
  /**
   * [Deprecated] Return word count of a post.
   *
   * @since 5.25.0
   * @since 5.30.0 - Fixed for accuracy (hopefully).
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::count_words() instead.
   *
   * @param int         $post_id  ID of the post to count the words of.
   * @param string|null $content  Optional. The post content. Queries the field by default.
   *
   * @return int The word count.
   */

  function fictioneer_count_words( $post_id, $content = null ) {
    return Utils_Admin::count_words( $post_id, $content );
  }
}

/**
 * [Deprecated] Log a message to the theme log file.
 *
 * @since 5.0.0
 * @deprecated 5.34.0 - Use \Fictioneer\Log::add() instead.
 *
 * @param string       $message  What has been updated
 * @param WP_User|null $user     The user who did it. Defaults to current user.
 */

function fictioneer_log( $message, $current_user = null ) {
  \Fictioneer\Log::add( $message, $current_user );
}

/**
 * [Deprecated] Retrieve the log entries and returns an HTML representation.
 *
 * @since 5.0.0
 * @deprecated 5.34.0 - Use \Fictioneer\Log::get() instead.
 *
 * @return string The HTML representation of the log entries.
 */

function fictioneer_get_log() {
  return \Fictioneer\Log::get();
}

/**
 * [Deprecated] Retrieve the debug log entries and returns an HTML representation.
 *
 * @since 5.0.0
 * @deprecated 5.34.0 - Use \Fictioneer\Log::get_debug() instead.
 *
 * @return string HTML representation of the log entries.
 */

function fictioneer_get_wp_debug_log() {
  return \Fictioneer\Log::get_debug();
}

if ( ! function_exists( 'fictioneer_get_validated_ajax_user' ) ) {
  /**
   * [Deprecated] Get the current user after performing AJAX validations
   *
   * @since 5.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_validated_ajax_user() instead.
   *
   * @param string $nonce_name   Optional. The name of the nonce. Default 'nonce'.
   * @param string $nonce_value  Optional. The value of the nonce. Default 'fictioneer_nonce'.
   *
   * @return boolean|WP_User False if not valid, the current user object otherwise.
   */

  function fictioneer_get_validated_ajax_user( $nonce_name = 'nonce', $nonce_value = 'fictioneer_nonce' ) {
    return Utils_Admin::get_validated_ajax_user( $nonce_name, $nonce_value );
  }
}

if ( ! function_exists( 'fictioneer_bulk_update_post_meta' ) ) {
  /**
   * [Deprecated] Update post meta fields in bulk for a post.
   *
   * If the meta value is truthy, the meta field is updated as normal.
   * If not, the meta field is deleted instead to keep the database tidy.
   * Fires default WP hooks where possible.
   *
   * @since 5.27.4
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::bulk_update_post_meta() instead.
   * @link https://developer.wordpress.org/reference/functions/update_metadata/
   * @link https://developer.wordpress.org/reference/functions/add_metadata/
   * @link https://developer.wordpress.org/reference/functions/delete_metadata/
   *
   * @param int   $post_id  Post ID.
   * @param array $fields   Associative array of field keys and sanitized (!) values.
   */

  function fictioneer_bulk_update_post_meta( $post_id, $fields ) {
    Utils_Admin::bulk_update_post_meta( $post_id, $fields );
  }
}

if ( ! function_exists( 'fictioneer_sql_has_new_story_chapters' ) ) {
  /**
   * [Deprecated] Check whether there any added chapters are to be considered "new".
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::has_new_story_chapters() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int   $story_id              Story ID.
   * @param int[] $chapter_ids           Current array of chapter IDs.
   * @param int[] $previous_chapter_ids  Previous array of chapter IDs.
   *
   * @return bool True if new chapters, false otherwise.
   */

  function fictioneer_sql_has_new_story_chapters( $story_id, $chapter_ids, $previous_chapter_ids ) {
    return Utils_Admin::has_new_story_chapters( $story_id, $chapter_ids, $previous_chapter_ids );
  }
}

if ( ! function_exists( 'fictioneer_sql_get_co_authored_story_ids' ) ) {
  /**
   * [Deprecated] Return story IDs where the user is a co-author.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_co_authored_story_ids() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int $author_id  User ID.
   *
   * @return int[] Array of story IDs.
   */

  function fictioneer_sql_get_co_authored_story_ids( $author_id ) {
    return Utils_Admin::get_co_authored_story_ids( $author_id );
  }
}

if ( ! function_exists( 'fictioneer_sql_get_chapter_story_selection' ) ) {
  /**
   * [Deprecated] Return selectable stories for chapter assignments.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_chapter_story_selection() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int $post_author_id     Author ID of the current post.
   * @param int $current_story_id   ID of the currently selected story (if any).
   *
   * @return array Associative array with 'stories' (array), 'other_author' (bool), 'co_author' (bool).
   */

  function fictioneer_sql_get_chapter_story_selection( $post_author_id, $current_story_id = 0 ) {
    return Utils_Admin::get_chapter_story_selection( $post_author_id, $current_story_id );
  }
}

if ( ! function_exists( 'fictioneer_sql_get_story_chapter_relationship_data' ) ) {
  /**
   * [Deprecated] Return chapter objects for a story.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::get_story_chapter_relationship_data() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int $story_id  Story ID.
   *
   * @return object[] Array of chapter data object similar to WP_Post.
   */

  function fictioneer_sql_get_story_chapter_relationship_data( $story_id ) {
    return Utils_admin::get_story_chapter_relationship_data( $story_id );
  }
}

/**
 * [Deprecated] Update the comment count of a post.
 *
 * @since 5.26.0
 * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::update_comment_count() instead.
 *
 * @global wpdb $wpdb  WordPress database object.
 *
 * @param int $post_id  Post ID.
 * @param int $count    Comment count.
 */

function fictioneer_sql_update_comment_count( $post_id, $count ) {
  Utils_Admin::update_comment_count( $post_id, $count );
}

if ( ! function_exists( 'fictioneer_get_user_fingerprint' ) ) {
  /**
   * [Deprecated] Return an unique-enough MD5 hash for the user.
   *
   * In order to differentiate users on the frontend even if they have the same
   * display name (which is possible) but without exposing any sensitive data,
   * a simple cryptic hash is calculated.
   *
   * @since 4.7.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils::get_user_fingerprint() instead.
   *
   * @param int $user_id  User ID to get the hash for.
   *
   * @return string The unique fingerprint hash or empty string if not found.
   */

  function fictioneer_get_user_fingerprint( $user_id ) {
    return Utils::get_user_fingerprint( $user_id );
  }
}

if ( ! function_exists( 'fictioneer_soft_delete_user_comments' ) ) {
  /**
   * [Deprecated] Soft delete a user's comments
   *
   * Replace the content and meta data of a user's comments with junk
   * but leave the comment itself in the database. This preserves the
   * structure of comment threads.
   *
   * @since 5.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\Utils_Admin::soft_delete_user_comments() instead.
   *
   * @param int $user_id  User ID to soft delete the comments for.
   *
   * @return array|false Detailed results about the database update. Accounts
   *                     for completeness, partial success, and errors. Includes
   *                     'complete' (boolean), 'failure' (boolean), 'success' (boolean),
   *                     'comment_count' (int), and 'updated_count' (int). Or false.
   */

  function fictioneer_soft_delete_user_comments( $user_id ) {
    return Utils_Admin::soft_delete_user_comments( $user_id );
  }
}

// =============================================================================
// SEARCH DELEGATES
// =============================================================================

if ( ! function_exists( 'fcn_keyword_search_taxonomies_input' ) ) {
  /**
   * [Deprecated] Render taxonomies keyword input field for search form.
   *
   * @since 5.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\Search::render_search_taxonomies() instead.
   *
   * @param array  $taxonomies  Array of WP_Term objects.
   * @param string $type        The taxonomy type.
   * @param string $query_var   Name of the submitted collection field.
   * @param string $and_var     Name of the submitted operator field.
   * @param string $singular    Singular display name of taxonomy.
   * @param string $plural      Plural display name of taxonomy.
   * @param array  $args        Optional arguments.
   */

  function fcn_keyword_search_taxonomies_input( $taxonomies, $type, $query_var, $and_var, $singular, $plural, $args = [] ) {
    \Fictioneer\Search::render_search_taxonomies( $taxonomies, $type, $query_var, $and_var, $singular, $plural, $args );
  }
}

if ( ! function_exists( 'fcn_keyword_search_authors_input' ) ) {
  /**
   * [Deprecated] Render authors keyword input field for search form.
   *
   * @since 5.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\Search::render_search_authors() instead.
   *
   * @param array  $authors    Array of WP_User objects.
   * @param string $query_var  Name of the submitted collection field.
   * @param string $singular   Singular display name of taxonomy.
   * @param string $plural     Plural display name of taxonomy.
   * @param array  $args       Optional arguments.
   */

  function fcn_keyword_search_authors_input( $authors, $query_var, $singular, $plural, $args = [] ) {
     \Fictioneer\Search::render_search_authors( $authors, $query_var, $singular, $plural, $args );
  }
}

// =============================================================================
// SQL DELEGATES
// =============================================================================

if ( ! function_exists( 'fictioneer_sql_filter_valid_chapter_ids' ) ) {
  /**
   * [Deprecated] Filter out non-valid chapter array IDs.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::filter_valid_chapter_ids() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int   $story_id     Story ID.
   * @param int[] $chapter_ids  Array of chapter IDs.
   *
   * @return int[] Filtered and validated array of IDs.
   */

  function fictioneer_sql_filter_valid_chapter_ids( $story_id, $chapter_ids ) {
    return Sanitizer_Admin::filter_valid_chapter_ids( $story_id, $chapter_ids );
  }
}

if ( ! function_exists( 'fictioneer_sql_filter_valid_page_ids' ) ) {
  /**
   * [Deprecated] Filter out non-valid story page array IDs.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::filter_valid_page_ids() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int   $author_id  Author ID for the pages.
   * @param int[] $page_ids   Array of page IDs.
   *
   * @return int[] Filtered and validated array of IDs.
   */

  function fictioneer_sql_filter_valid_page_ids( $author_id, $page_ids ) {
    return Sanitizer_Admin::filter_valid_page_ids( $author_id, $page_ids );
  }
}

if ( ! function_exists( 'fictioneer_sql_filter_valid_collection_ids' ) ) {
  /**
   * [Deprecated] Filter out non-valid story page array IDs.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::filter_valid_collection_ids() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int[] $item_ids  Array of collection item IDs.
   *
   * @return int[] Filtered and validated array of IDs.
   */

  function fictioneer_sql_filter_valid_collection_ids( $item_ids ) {
    return Sanitizer_Admin::filter_valid_collection_ids( $item_ids );
  }
}

if ( ! function_exists( 'fictioneer_sql_filter_valid_featured_ids' ) ) {
  /**
   * [Deprecated] Filter out non-valid featured array IDs.
   *
   * @since 5.26.0
   * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::filter_valid_featured_ids() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int[] $post_ids  Array of featured post IDs.
   *
   * @return int[] Filtered and validated array of IDs.
   */

  function fictioneer_sql_filter_valid_featured_ids( $post_ids ) {
    return Sanitizer_Admin::filter_valid_featured_ids( $post_ids );
  }
}

if ( ! function_exists( 'fictioneer_sql_filter_valid_blog_story_ids' ) ) {
  /**
   * [Deprecated] Filter out non-valid blog story array IDs.
   *
   * @since 5.26.0
   * @since 5.30.0 - Refactored for optional author.
   * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer_Admin::filter_valid_blog_story_ids() instead.
   *
   * @global wpdb $wpdb  WordPress database object.
   *
   * @param int[]    $story_blogs      Array of story blog IDs.
   * @param int|null $story_author_id  Optional. Author ID of the story.
   *
   * @return int[] Filtered and validated array of IDs.
   */

  function fictioneer_sql_filter_valid_blog_story_ids( $story_blogs, $story_author_id = null ) {
    return Sanitizer_Admin::filter_valid_blog_story_ids( $story_blogs, $story_author_id );
  }
}

// =============================================================================
// USER HELPER DELEGATES
// =============================================================================

if ( ! function_exists( 'fictioneer_get_custom_avatar_url' ) ) {
  /**
   * [Deprecated] Get custom avatar URL.
   *
   * @since 4.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\User::get_custom_avatar_url() instead.
   *
   * @param WP_User $user The user to get the avatar for.
   *
   * @return string|boolean The custom avatar URL or false.
   */

  function fictioneer_get_custom_avatar_url( $user ) {
    return User::get_custom_avatar_url( $user );
  }
}

if ( ! function_exists( 'fictioneer_get_default_avatar_url' ) ) {
  /**
   * [Deprecated] Get default avatar URL.
   *
   * @since 5.5.3
   * @deprecated 5.34.0 - Use \Fictioneer\User::get_default_avatar_url() instead.
   *
   * @return string Default avatar URL.
   */

  function fictioneer_get_default_avatar_url() {
    return User::get_default_avatar_url();
  }
}

if ( ! function_exists( 'fictioneer_get_comment_badge' ) ) {
  /**
   * [Deprecated] Get HTML for comment badge.
   *
   * @since 5.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\User::get_comment_badge() instead.
   *
   * @param WP_User|null    $user            The comment user.
   * @param WP_Comment|null $comment         Optional. The comment object.
   * @param int             $post_author_id  Optional. ID of the author of the post
   *                                         the comment is for.
   *
   * @return string Badge HTML or empty string.
   */

  function fictioneer_get_comment_badge( $user, $comment = null, $post_author_id = 0 ) {
    return User::get_comment_badge( $user, $comment, $post_author_id );
  }
}

if ( ! function_exists( 'fictioneer_get_override_badge' ) ) {
  /**
   * [Deprecated] Get a user's custom badge (if any).
   *
   * @since 4.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\User::get_override_badge() instead.
   *
   * @param WP_User        $user     The user.
   * @param string|boolean $default  Default value or false.
   *
   * @return string|boolean The badge label, default, or false.
   */

  function fictioneer_get_override_badge( $user, $default = false ) {
    return User::get_override_badge( $user, $default );
  }
}

if ( ! function_exists( 'fictioneer_get_patreon_badge' ) ) {
  /**
   * [Deprecated] Get a user's Patreon badge (if any).
   *
   * @since 5.0.0
   * @deprecated 5.34.0 - Use \Fictioneer\User::get_patreon_badge() instead.
   *
   * @param WP_User        $user     The user.
   * @param string|boolean $default  Default value or false.
   *
   * @return string|boolean The badge label, default, or false.
   */

  function fictioneer_get_patreon_badge( $user, $default = false ) {
    return User::get_patreon_badge( $user, $default );
  }
}

/**
 * [Deprecated] Check whether the user's Patreon data is still valid.
 *
 * Note: Patreon data expires after a set amount of time, one week
 * by default defined as FICTIONEER_PATREON_EXPIRATION_TIME.
 *
 * @since 5.15.0
 * @deprecated 5.34.0 - Use \Fictioneer\User::patreon_tiers_valid() instead.
 *
 * @param int|WP_User|null $user  The user object or user ID. Defaults to current user.
 *
 * @return bool True if still valid, false if expired.
 */

function fictioneer_patreon_tiers_valid( $user = null ) {
  return User::patreon_tiers_valid( $user );
}

/**
 * [Deprecated] Return Patreon data of the user.
 *
 * @since 5.17.0
 * @deprecated 5.34.0 - Use \Fictioneer\User::get_user_patreon_data() instead.
 *
 * @param int|WP_User|null $user  The user object or user ID. Defaults to current user.
 *
 * @return array Empty array if not a patron, associative array otherwise. Includes the
 *               keys 'valid', 'lifetime_support_cents', 'last_charge_date',
 *               'last_charge_status', 'next_charge_date', 'patron_status', and 'tiers'.
 *               Tiers is an array of tiers with the keys 'id', 'title', 'description',
 *               'published', 'amount_cents', and 'timestamp'.
 */

function fictioneer_get_user_patreon_data( $user = null ) {
  return User::get_user_patreon_data( $user );
}

// =============================================================================
// SHORTCODE DELEGATES
// =============================================================================

/**
 * [Deprecated] Whether to enable Transients for shortcodes.
 *
 * @since 5.6.3
 * @since 5.23.1 - Do not turn off with cache plugin.
 * @since 5.25.0 - Refactored with option.
 * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Base::transients_enabled() instead.
 *
 * @param string $shortcode  The shortcode in question.
 *
 * @return boolean Either true or false.
 */

function fictioneer_enable_shortcode_transients( $shortcode = null ) {
  Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Base::transients_enabled()' );

  return \Fictioneer\Shortcodes\Base::transients_enabled( $shortcode );
}

/**
 * [Deprecated] Default attribute pairs for shortcode_atts().
 *
 * @since 5.33.0
 * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Attributes::defaults() instead.
 *
 * @return array Default attribute pairs.
 */

function fictioneer_get_shortcode_default_attribute_pairs() {
  Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Attributes::defaults()' );

  return \Fictioneer\Shortcodes\Attributes::defaults();
}

/**
 * [Deprecated] Parse, sanitize, and normalize shortcode attributes.
 *
 * @since 5.7.3
 * @since 5.33.0 - Added filter.
 * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Attributes::parse() instead.
 *
 * @param array  $attr       Attributes passed to the shortcode.
 * @param int    $def_count  Optional. Default for the 'count' argument. Default -1.
 *
 * @return array Parsed and sanitized arguments.
 */

function fictioneer_get_default_shortcode_args( $attr, $def_count = -1 ) {
  Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Attributes::parse()' );

  return \Fictioneer\Shortcodes\Attributes::parse( $attr, 'deprecated_call', $def_count );
}

/**
 * [Deprecated] Extract taxonomies from shortcode attributes.
 *
 * @since 5.2.0
 * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Attributes::get_shortcode_taxonomies() instead.
 *
 * @param array $attr  Attributes of the shortcode.
 *
 * @return array Array of found taxonomies.
 */

function fictioneer_get_shortcode_taxonomies( $attr ) {
  Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Attributes::get_shortcode_taxonomies()' );

  return \Fictioneer\Shortcodes\Attributes::get_shortcode_taxonomies( $attr );
}

if ( ! function_exists( 'fictioneer_shortcode_query' ) ) {
  /**
   * [Deprecated] Return query result for shortcode.
   *
   * @since 5.4.9
   * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Base::query() instead.
   *
   * @param array $args  Query arguments.
   *
   * @return WP_Query The query result.
   */

  function fictioneer_shortcode_query( $args ) {
    Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Base::query()' );

    return \Fictioneer\Shortcodes\Base::query( $args );
  }
}

/**
 * [Deprecated] Tax query argument for shortcode.
 *
 * @since 5.2.0
 * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Base::tax_query_args() instead.
 *
 * @param array $args  Arguments of the shortcode partial.
 *
 * @return array Tax query argument.
 */

function fictioneer_get_shortcode_tax_query( $args ) {
  Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Base::tax_query_args()' );

  return \Fictioneer\Shortcodes\Base::tax_query_args( $args );
}

/**
 * [Deprecated] Inline script to initialize Splide ASAP.
 *
 * Note: The script tag is only returned once in case multiple sliders
 * are active since only one is needed.
 *
 * @since 5.25.0
 * @since 5.26.1 - Use wp_print_inline_script_tag().
 * @deprecated 5.34.0 - Use \Fictioneer\Shortcodes\Base::splide_inline_script() instead.
 *
 * @return string Inline Splide script.
 */

function fictioneer_get_splide_inline_init() {
  Utils::deprecated( __FUNCTION__, '5.34.0', '\Fictioneer\Shortcodes\Base::splide_inline_script()' );

  return \Fictioneer\Shortcodes\Base::splide_inline_script();
}
