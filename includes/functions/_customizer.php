<?php

use Fictioneer\Utils;
use Fictioneer\Utils_Admin;

// =============================================================================
// GET FONT DATA
// =============================================================================

/**
 * Returns fonts included by the theme
 *
 * Note: If a font.json contains a { "remove": true } node, the font will not
 * be added to the result array and therefore removed from the site.
 *
 * @since 5.10.0
 *
 * @return array Array of font data. Keys: skip, chapter, version, key, name,
 *               family, type, styles, weights, charsets, formats, about, note,
 *               sources, css_path, css_file, and in_child_theme.
 */

function fictioneer_get_font_data() {
  // Setup
  $parent_font_dir = get_template_directory() . '/fonts';
  $child_font_dir = get_stylesheet_directory() . '/fonts';
  $parent_fonts = [];
  $child_fonts = [];
  $google_fonts = [];

  // Helper function
  $extract_font_data = function( $font_dir, &$fonts, $theme = 'parent' ) {
    if ( is_dir( $font_dir ) ) {
      $font_folders = array_diff( scandir( $font_dir ), ['..', '.'] );

      foreach ( $font_folders as $folder ) {
        $full_path = "{$font_dir}/{$folder}";
        $json_file = "$full_path/font.json";
        $css_file = "$full_path/font.css";

        if ( is_dir( $full_path ) && file_exists( $json_file ) && file_exists( $css_file ) ) {
          $folder_name = basename( $folder );
          $data = @json_decode( file_get_contents( $json_file ), true );

          if ( $data && json_last_error() === JSON_ERROR_NONE ) {
            if ( ! ( $data['remove'] ?? 0 ) ) {
              $data['dir'] = "/fonts/{$folder_name}";
              $data['css_path'] = "/fonts/{$folder_name}/font.css";
              $data['css_file'] = $css_file;
              $data['in_child_theme'] = $theme === 'child';

              $fonts[ $data['key'] ] = $data;
            }
          }
        }
      }

      return $fonts;
    }
  };

  // Parent theme
  $extract_font_data( $parent_font_dir, $parent_fonts );

  // Child theme (if any)
  if ( $parent_font_dir !== $child_font_dir ) {
    $extract_font_data( $child_font_dir, $child_fonts, 'child' );
  }

  // Google Fonts links (if any)
  $google_fonts_links = get_option( 'fictioneer_google_fonts_links' );

  if ( $google_fonts_links ) {
    $google_fonts_links = preg_split( '/\r\n|\r|\n/', $google_fonts_links );

    foreach ( $google_fonts_links as $link ) {
      $font = Utils::extract_font_from_google_link( $link );

      if ( $font ) {
        $google_fonts[] = $font;
      }
    }
  }

  // Merge finds
  $fonts = array_merge( $parent_fonts, $child_fonts, $google_fonts );

  // Apply filters
  $fonts = apply_filters( 'fictioneer_filter_font_data', $fonts );

  // Return complete font list
  return $fonts;
}
