<?php

use Fictioneer\Sanitizer;
use Fictioneer\Sanitizer_Admin;
use Fictioneer\Utils;

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
 * @deprecated 5.34.0 - Use wp_parse_list() instead.
 *
 * @param string $string  The string to explode.
 *
 * @return array The string content as array.
 */

function fictioneer_explode_list( $string ) {
  return wp_parse_list( $string );
}

/**
 * [Deprecated] Sanitize (and transform) a comma-separated list into an array.
 *
 * @since 5.15.0
 * @deprecated 5.34.0 - Use wp_parse_list() instead.
 *
 * @param string     $input  The comma-separated list.
 * @param array|null $args   Deprecated.
 *
 * @return array The comma-separated list turned array.
 */

function fictioneer_sanitize_list_into_array( $input, $args = []  ) {
  return wp_parse_list( $input );
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
// UTILITY DELEGATES
// =============================================================================

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
