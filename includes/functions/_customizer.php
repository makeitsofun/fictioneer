<?php

use Fictioneer\Utils;

// =============================================================================
// EXTRACT FONT DATA FROM GOOGLE FONTS LINK
// =============================================================================

/**
 * Returns fonts data from a Google Fonts link
 *
 * @since 5.10.0
 *
 * @param string $link  The Google Fonts link.
 *
 * @return array|false|null The font data if successful, false if malformed,
 *                          null if not a valid Google Fonts link.
 */

function fictioneer_extract_font_from_google_link( $link ) {
  // Validate
  if ( preg_match( '#^https://fonts\.googleapis\.com/css2(?:\?|$)#i', $link ) !== 1 ) {
    // Not Google Fonts link
    return null;
  }

  // Setup
  $font = array(
    'google_link' => $link,
    'skip' => true,
    'chapter' => true,
    'version' => '',
    'key' => '',
    'name' => '',
    'family' => '',
    'type' => '',
    'styles' => ['normal'],
    'weights' => [],
    'charsets' => [],
    'formats' => [],
    'about' => __( 'This font is loaded via the Google Fonts CDN, see source for additional information.', 'fictioneer' ),
    'note' => '',
    'sources' => array(
      'googleFontsCss' => array(
        'name' => 'Google Fonts CSS File',
        'url' => $link
      )
    )
  );

  // Name?
  preg_match( '/family=([^:]+)/', $link, $name_matches );

  if ( ! empty( $name_matches ) ) {
    $font['name'] = str_replace( '+', ' ', $name_matches[1] );
    $font['family'] = $font['name'];
    $font['key'] = sanitize_title( $font['name'] );
  } else {
    // Link malformed
    return false;
  }

  // Italic? Weights?
  preg_match( '/ital,wght@([0-9,;]+)/', $link, $ital_weight_matches );

  if ( ! empty( $ital_weight_matches ) ) {
    $specifications = explode( ';', $ital_weight_matches[1] );
    $weights = [];
    $is_italic = false;

    foreach ( $specifications as $spec ) {
      list( $ital, $weight ) = explode( ',', $spec );

      if ( $ital == '1' ) {
        $is_italic = true;
      }

      $weights[ $weight ] = true;
    }

    if ( $is_italic ) {
      $font['styles'][] = 'italic';
    }

    $font['weights'] = array_keys( $weights );
  }

  // Done
  return $font;
}

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
      $font = fictioneer_extract_font_from_google_link( $link );

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

// =============================================================================
// BUILD BUNDLED FONT CSS FILE
// =============================================================================

/**
 * Build bundled font stylesheet
 *
 * @since 5.10.0
 */

function fictioneer_build_bundled_fonts() {
  // Setup
  $base_fonts = WP_CONTENT_DIR . '/themes/fictioneer/css/fonts-base.css';
  $fonts = fictioneer_get_font_data();
  $disabled_fonts = get_option( 'fictioneer_disabled_fonts', [] );
  $disabled_fonts = is_array( $disabled_fonts ) ? $disabled_fonts : [];
  $combined_font_css = '';
  $font_stack = [];

  // Apply filters
  $fonts = apply_filters( 'fictioneer_filter_pre_build_bundled_fonts', $fonts );

  // Build
  if ( file_exists( $base_fonts ) ) {
    $css = file_get_contents( $base_fonts );
    $css = str_replace( '../fonts/', get_template_directory_uri() . '/fonts/', $css );

    $combined_font_css .= $css;
  }

  foreach ( $fonts as $key => $font ) {
    if ( in_array( $key, $disabled_fonts ) ) {
      continue;
    }

    if ( $font['chapter'] ?? 0 ) {
      $font_stack[ $font['key'] ] = array(
        'css' => fictioneer_font_family_value( $font['family'] ?? '' ),
        'name' => $font['name'] ?? '',
        'alt' => $font['alt'] ?? ''
      );
    }

    if ( ! ( $font['skip'] ?? 0 ) && ! ( $font['google_link'] ?? 0 ) ) {
      $css = file_get_contents( $font['css_file'] );

      if ( $font['in_child_theme'] ?? 0 ) {
        $css = str_replace( '../fonts/', get_stylesheet_directory_uri() . '/fonts/', $css );
      } else {
        $css = str_replace( '../fonts/', get_template_directory_uri() . '/fonts/', $css );
      }

      $combined_font_css .= $css;
    }
  }

  // Update options
  update_option( 'fictioneer_chapter_fonts', $font_stack, true );
  update_option( 'fictioneer_bundled_fonts_timestamp', time(), true );

  // Save
  file_put_contents(
    Utils::get_cache_dir( 'build_bundled_fonts' ) . '/bundled-fonts.css',
    $combined_font_css
  );
}
