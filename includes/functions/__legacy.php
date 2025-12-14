<?php

use Fictioneer\Sanitizer;

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
 * [Deprecated] Sanitizes a float as positive number.
 *
 * @since 5.9.4
 * @deprecated 5.34.0 - Use \Fictioneer\Sanitizer::sanitize_float_positive() instead.
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
 * Sanitize an URL.
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
 * Sanitize a Patreon URL.
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
