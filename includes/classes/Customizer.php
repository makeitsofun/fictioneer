<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Customizer {
  /**
   * Return the CSS loaded from a snippet file.
   *
   * @since 5.11.1
   * @since 5.34.0 - Refactored and moved into Customizer class.
   *
   * @param string $snippet  Name of the snippet file without file ending.
   *
   * @return string CSS string from the file.
   */

  public static function get_css_snippet( $snippet ) : string {
    $snippet = sanitize_key( $snippet );

    if ( $snippet === '' ) {
      return '';
    }

    $filter = "fictioneer_filter_css_snippet_{$snippet}";
    $file = get_theme_file_path( 'css/customize/' . $snippet . '.css' );

    if ( ! is_readable( $file ) ) {
      error_log( '[Fictioneer] CSS snippet file not found or unreadable: ' . $file );

      return apply_filters( $filter, '', false );
    }

    $css = file_get_contents( $file );

    if ( $css === false ) {
      return apply_filters( $filter, '', false );
    }

    return apply_filters( $filter, $css, true );
  }

  /**
   * Return an eased fading linear-gradient value.
   *
   * @since 4.7.0
   * @since 5.34.0 - Moved into Customizer class.
   *
   * @param float  $start_opacity  Starting opacity of the gradient in percentage.
   * @param int    $start          Starting point of the gradient in percentage.
   * @param int    $end            Ending point of the gradient in percentage.
   * @param string $direction      Direction of the gradient with unit (e.g. '180deg').
   * @param string $hsl            HSL string used as color. Default '0 0% 0%'.
   *
   * @return string Linear-gradient value.
   */

  public static function get_fading_gradient( $start_opacity, $start, $end, $direction, $hsl = '0 0% 0%' ) : string {
    $alpha_values = [0.987, 0.951, 0.896, 0.825, 0.741, 0.648, 0.55, 0.45, 0.352, 0.259, 0.175, 0.104, 0.049, 0.013, 0];
    $num_stops = count( $alpha_values );

    $positions = array_map(
      function( $index ) use ( $start, $end, $num_stops ) {
        return $start + ( ( $end - $start ) / ( $num_stops - 1 ) * $index );
      },
      array_keys( $alpha_values )
    );

    $gradient = "linear-gradient({$direction}, ";

    foreach ( $alpha_values as $index => $alpha ) {
      $position = round( $positions[ $index ], 2 );
      $adjusted_alpha = round( $alpha * $start_opacity, 3 );
      $gradient .= "hsl({$hsl} / {$adjusted_alpha}%) {$position}%";

      if ( $index < $num_stops - 1 ) {
        $gradient .= ', ';
      }
    }

    return $gradient . ');';
  }

  /**
   * Return a high-precision CSS clamp.
   *
   * @since 4.7.0
   * @since 5.34.0 - Moved into Customizer class.
   *
   * @param int    $min   Minimum value.
   * @param int    $max   Maximum value.
   * @param int    $wmin  Minimum viewport value.
   * @param int    $wmax  Maximum viewport value.
   * @param string $unit  Relative clamp unit. Default 'vw'.
   *
   * @return string Calculated clamp.
   */

  public static function get_clamp( $min, $max, $wmin, $wmax, $unit = 'vw' ) : string {
    $vw = ( $min - $max ) / ( ( $wmin / 100 ) - ( $wmax / 100 ) );
    $offset = $min - $vw * ( $wmin / 100 );

    return "clamp({$min}px, {$vw}{$unit} + ({$offset}px), {$max}px)";
  }

  /**
   * Return CSS for dark mode :root properties.
   *
   * @since 5.34.0
   *
   * @return string CSS :root properties.
   */

  public static function get_dark_root_properties_css() : string {
    $css = '';

    $properties = array(
      '--site-title-heading-color' => Utils::get_hsl_font_code( Utils::get_theme_color( 'dark_header_title_color' ) ),
      '--site-title-tagline-color' => Utils::get_hsl_font_code( Utils::get_theme_color( 'dark_header_tagline_color' ) )
    );

    $header_bg_color_dark = Utils::get_theme_color( 'header_color_dark', 'transparent' );

    if ( $header_bg_color_dark && $header_bg_color_dark !== 'transparent' ) {
      $properties['--header-background-color'] = Utils::get_hsl_code( $header_bg_color_dark );
    }

    if ( get_theme_mod( 'use_custom_dark_mode' ) ) {
      foreach ( ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'] as $level ) {
        $properties["--bg-{$level}-free"] = Utils::get_hsl_code(
          Utils::get_theme_color( "dark_bg_{$level}" ),
          'free'
        );
      }

      foreach ( ['generic', 'moderator', 'admin', 'author', 'supporter', 'override'] as $level ) {
        $properties["--badge-{$level}-background"] = Utils::get_hsl_code(
          Utils::get_theme_color( "dark_badge_{$level}_background" )
        );
      }

      $dark_shade = Utils::hex_to_rgb(get_theme_mod( 'dark_shade', '000000' ) ) ?: [0, 0, 0];
      $properties['--dark-shade-rgb'] = implode( ' ', $dark_shade ); // No #-prefix

      $properties['--theme-color-base'] = Utils::get_hsl_code(
        Utils::get_theme_color( 'dark_theme_color_base' ),
        'values'
      );

      $properties['--navigation-background'] = Utils::get_hsl_code(
        Utils::get_theme_color( 'dark_navigation_background_sticky' )
      );

      $properties['--card-frame-border-color'] = Utils::get_hsl_code(
        Utils::get_theme_color( 'dark_card_frame' )
      );

      $properties['--primary-400'] = Utils::get_theme_color( 'dark_primary_400' );
      $properties['--primary-500'] = Utils::get_theme_color( 'dark_primary_500' );
      $properties['--primary-600'] = Utils::get_theme_color( 'dark_primary_600' );
      $properties['--red-400'] = Utils::get_theme_color( 'dark_red_400' );
      $properties['--red-500'] = Utils::get_theme_color( 'dark_red_600' );
      $properties['--red-600'] = Utils::get_theme_color( 'dark_red_600' );
      $properties['--green-400'] = Utils::get_theme_color( 'dark_green_400' );
      $properties['--green-500'] = Utils::get_theme_color( 'dark_green_400' );
      $properties['--green-600'] = Utils::get_theme_color( 'dark_green_400' );
      $properties['--bookmark-color-alpha'] = Utils::get_theme_color( 'dark_bookmark_color_alpha' );
      $properties['--bookmark-color-beta'] = Utils::get_theme_color( 'dark_bookmark_color_beta' );
      $properties['--bookmark-color-gamma'] = Utils::get_theme_color( 'dark_bookmark_color_gamma' );
      $properties['--bookmark-color-delta'] = Utils::get_theme_color( 'dark_bookmark_color_delta' );
      $properties['--bookmark-line'] = Utils::get_theme_color( 'dark_bookmark_line_color' );
      $properties['--ins-background'] = Utils::get_theme_color( 'dark_ins_background' );
      $properties['--del-background'] = Utils::get_theme_color( 'dark_del_background' );
    }

    $lines = [];

    foreach ( $properties as $name => $value ) {
      $lines[] = "{$name}:{$value};";
    }

    $css .= ':root{' . implode( '', $lines ) . '}';

    if ( get_theme_mod( 'use_custom_dark_mode' ) ) {
      $font_properties = [];

      foreach ( ['100', '200', '300', '400', '500', '600', '700', '800', '900', '950', 'tinted', 'inverted'] as $level ) {
        $font_properties["--fg-{$level}"] = Utils::get_hsl_font_code( Utils::get_theme_color( "dark_fg_{$level}" ) );
      }

      $lines = [];

      foreach ( $font_properties as $name => $value ) {
        $lines[] = "{$name}:{$value};";
      }

      $css .= ':root,:root .chapter-formatting{' . implode( '', $lines ) . '}';
    }

    if ( get_theme_mod( 'dark_mode_font_weight', 'adjusted' ) === 'normal' ) {
      $css .= ":root[data-font-weight=default]:is(html){--font-smoothing-webkit: subpixel-antialiased;--font-smoothing-moz: auto;--font-weight-normal: 400;--font-weight-semi-strong: 600;--font-weight-strong: 700;--font-weight-medium: 500;--font-weight-heading: 700;--font-weight-badge: 600;--font-weight-post-meta: 400;--font-weight-read-ribbon: 700;--font-weight-card-label: 600;--font-weight-navigation: 400;--font-letter-spacing-base: 0em;}";
    }

    return $css;
  }

  /**
   * Return CSS for light mode :root properties.
   *
   * @since 5.34.0
   *
   * @return string CSS :root properties.
   */

  public static function get_light_root_properties_css() : string {
    $css = '';

    $properties = array(
      '--site-title-heading-color' => Utils::get_hsl_font_code( Utils::get_theme_color( 'light_header_title_color' ) ),
      '--site-title-tagline-color' => Utils::get_hsl_font_code( Utils::get_theme_color( 'light_header_tagline_color' ) ),
      '--hue-offset' => get_theme_mod( 'hue_offset_light', 0 ) . 'deg',
      '--saturation-offset' => (float) get_theme_mod( 'saturation_offset_light', 0 ) / 100,
      '--lightness-offset' => (float) get_theme_mod( 'lightness_offset_light', 0 ) / 100,
      '--font-saturation-offset' => (float) get_theme_mod( 'font_saturation_offset_light', 0 ) / 100,
      '--font-lightness-offset' => (float) get_theme_mod( 'font_lightness_offset_light', 0 ) / 100
    );

    $header_bg_color_light = Utils::get_theme_color( 'header_color_light', 'transparent' );

    if ( $header_bg_color_light && $header_bg_color_light !== 'transparent' ) {
      $dark_properties['--header-background-color'] = Utils::get_hsl_code( $header_bg_color_light );
    }

    if ( get_theme_mod( 'use_custom_light_mode' ) ) {
      foreach ( ['50', '100', '200', '300', '400', '500', '600', '700', '800', '900', '950'] as $level ) {
        $properties["--bg-{$level}-free"] = Utils::get_hsl_code(
          Utils::get_theme_color( "light_bg_{$level}" ),
          'free'
        );
      }

      foreach ( ['generic', 'moderator', 'admin', 'author', 'supporter', 'override'] as $level ) {
        $properties["--badge-{$level}-background"] = Utils::get_hsl_code(
          Utils::get_theme_color( "light_badge_{$level}_background" )
        );
      }

      $properties['--theme-color-base'] = Utils::get_hsl_code(
        Utils::get_theme_color( 'light_theme_color_base' ),
        'values'
      );

      $properties['--navigation-background'] = Utils::get_hsl_code(
        Utils::get_theme_color( 'light_navigation_background_sticky' )
      );

      $properties['--card-frame-border-color'] = Utils::get_hsl_code(
        Utils::get_theme_color( 'light_card_frame' )
      );

      $properties['--primary-400'] = Utils::get_theme_color( 'light_primary_400' );
      $properties['--primary-500'] = Utils::get_theme_color( 'light_primary_500' );
      $properties['--primary-600'] = Utils::get_theme_color( 'light_primary_600' );
      $properties['--red-400'] = Utils::get_theme_color( 'light_red_400' );
      $properties['--red-500'] = Utils::get_theme_color( 'light_red_600' );
      $properties['--red-600'] = Utils::get_theme_color( 'light_red_600' );
      $properties['--green-400'] = Utils::get_theme_color( 'light_green_400' );
      $properties['--green-500'] = Utils::get_theme_color( 'light_green_400' );
      $properties['--green-600'] = Utils::get_theme_color( 'light_green_400' );
      $properties['--bookmark-color-alpha'] = Utils::get_theme_color( 'light_bookmark_color_alpha' );
      $properties['--bookmark-color-beta'] = Utils::get_theme_color( 'light_bookmark_color_beta' );
      $properties['--bookmark-color-gamma'] = Utils::get_theme_color( 'light_bookmark_color_gamma' );
      $properties['--bookmark-color-delta'] = Utils::get_theme_color( 'light_bookmark_color_delta' );
      $properties['--bookmark-line'] = Utils::get_theme_color( 'light_bookmark_line_color' );
      $properties['--ins-background'] = Utils::get_theme_color( 'light_ins_background' );
      $properties['--del-background'] = Utils::get_theme_color( 'light_del_background' );
    }

    $lines = [];

    foreach ( $properties as $name => $value ) {
      $lines[] = "{$name}:{$value};";
    }

    $css .= ':root[data-mode=light]{' . implode( '', $lines ) . '}';

    if ( get_theme_mod( 'use_custom_light_mode' ) ) {
      $font_properties = [];

      foreach ( ['100', '200', '300', '400', '500', '600', '700', '800', '900', '950', 'tinted', 'inverted'] as $level ) {
        $font_properties["--fg-{$level}"] = Utils::get_hsl_font_code( Utils::get_theme_color( "light_fg_{$level}" ) );
      }

      $lines = [];

      foreach ( $font_properties as $name => $value ) {
        $lines[] = "{$name}:{$value};";
      }

      $css .= ':root[data-mode=light],:root[data-mode=light] .chapter-formatting{' . implode( '', $lines ) . '}';
    }

    return $css;
  }

  /**
   * Build and save customization stylesheet.
   *
   * @since 5.34.0
   *
   * @param string|null $context  Optional. In which context the stylesheet created,
   *                              for example 'preview' for the Customizer.
   */

  public static function build_customizer_css( $context = null ) : void {
    if ( $context === 'preview' ) {
      $file_path = Utils::get_cache_dir( 'preview' ) . 'customize-preview.css';
    } else {
      $file_path = Utils::get_cache_dir( 'build_customize_css' ) . 'customize.css';
    }

    $css = self::get_customizer_css();
    $css = Utils::minify_css( $css );

    if ( $context !== 'preview' ) {
      update_option( 'fictioneer_customize_css_timestamp', time(), true );
    }

    file_put_contents( $file_path, $css );
  }

  /**
   * Return compiled customization styles.
   *
   * @since 5.34.0
   *
   * @return string Compiled customization styles.
   */

  public static function get_customizer_css() : string {
    $site_width = (int) get_theme_mod( 'site_width', FICTIONEER_DEFAULT_SITE_WIDTH );
    $header_style = get_theme_mod( 'header_style', 'default' );
    $header_image_style = get_theme_mod( 'header_image_style', 'default' );
    $page_style = get_theme_mod( 'page_style', 'default' );
    $content_list_style = get_theme_mod( 'content_list_style', 'default' );
    $content_list_collapse = get_theme_mod( 'content_list_collapse_style', 'default' );
    $card_style = get_theme_mod( 'card_style', 'default' );
    $card_frame = get_theme_mod( 'card_frame', 'default' );
    $footer_style = get_theme_mod( 'footer_style', 'default' );
    $sidebar_style = get_theme_mod( 'sidebar_style', 'none' );
    $css = '';

    // --- View transition style -------------------------------------------------

    if ( get_theme_mod( 'view_transition', 'none' ) === 'cross_fade' ) {
      $css .= '@media not (prefers-reduced-motion: reduce){@view-transition{navigation: auto;}}::view-transition-old(root),::view-transition-new(root){animation-duration:0.15s;}';
    }

    // --- Properties ------------------------------------------------------------

    $logo_min_height = (int) get_theme_mod( 'logo_min_height', 210 );
    $logo_max_height = (int) get_theme_mod( 'logo_height', 210 );

    if ( $logo_min_height < $logo_max_height ) {
      $logo_height = self::get_clamp( $logo_min_height, $logo_max_height, 320, $site_width );
    } else {
      $logo_height = $logo_max_height . 'px';
    }

    $card_box_shadow = get_theme_mod( 'card_shadow', 'var(--box-shadow-m)' );

    $base_properties = array(
      '--site-width' => $site_width . 'px',
      '--main-offset' => get_theme_mod( 'main_offset', 0 ) . 'px',
      '--sidebar-width' => get_theme_mod( 'sidebar_width', 256 ) . 'px',
      '--sidebar-gap' => get_theme_mod( 'sidebar_gap', 48 ) . 'px',
      '--hue-offset' => get_theme_mod( 'hue_offset', 0 ) . 'deg',
      '--saturation-offset' => (float) get_theme_mod( 'saturation_offset', 0 ) / 100, // No unit
      '--lightness-offset' => (float) get_theme_mod( 'lightness_offset', 0 ) / 100, // No unit
      '--font-saturation-offset' => (float) get_theme_mod( 'font_saturation_offset', 0 ) / 100, // No unit
      '--font-lightness-offset' => (float) get_theme_mod( 'font_lightness_offset', 0 ) / 100, // No unit
      '--header-image-height' => self::get_clamp(
        (int) get_theme_mod( 'header_image_height_min', 210 ),
        (int) get_theme_mod( 'header_image_height_max', 480 ),
        320,
        $site_width
      ),
      '--header-height' => 'calc('
        . self::get_clamp(
            (int) get_theme_mod( 'header_height_min', 190 ),
            (int) get_theme_mod( 'header_height_max', 380 ),
            320,
            $site_width
          )
        . ' - var(--page-inset-top, 0px))',
      '--header-logo-height' => $logo_height, // Unit already appended
      '--header-logo-min-height' => $logo_min_height, // No unit
      '--header-logo-max-height' => $logo_max_height, // No unit
      '--site-title-font-size' => self::get_clamp(
        (int) get_theme_mod( 'site_title_font_size_min', 32 ),
        (int) get_theme_mod( 'site_title_font_size_max', 60 ),
        320,
        $site_width
      ),
      '--site-title-tagline-font-size' => self::get_clamp(
        (int) get_theme_mod( 'site_tagline_font_size_min', 13 ),
        (int) get_theme_mod( 'site_tagline_font_size_max', 18 ),
        320,
        $site_width
      ),
      '--grid-columns-min' => get_theme_mod( 'card_grid_column_min', 308 ) . 'px',
      '--grid-columns-row-gap-multiplier' => get_theme_mod( 'card_grid_row_gap_mod', 1 ), // No unit
      '--grid-columns-col-gap-multiplier' => get_theme_mod( 'card_grid_column_gap_mod', 1 ), // No unit
      '--card-font-size-min-mod' => get_theme_mod( 'card_font_size_min_mod', 0 ) . 'px',
      '--card-font-size-grow-mod' => get_theme_mod( 'card_font_size_grow_mod', 0 ) . 'px',
      '--card-font-size-max-mod' => get_theme_mod( 'card_font_size_max_mod', 0 ) . 'px',
      '--card-cover-width-mod' => get_theme_mod( 'card_cover_width_mod', 1 ), // No unit
      '--card-box-shadow' => $card_box_shadow,
      '--card-drop-shadow' => str_replace( 'box-', 'drop-', $card_box_shadow ),
      '--story-cover-box-shadow' => get_theme_mod( 'story_cover_shadow', 'var(--box-shadow-xl)' ),
      '--recommendation-cover-box-shadow' => get_theme_mod( 'story_cover_shadow', 'var(--box-shadow-xl)' ),
      '--floating-cover-image-width' => self::get_clamp(
        56,
        (int) get_theme_mod( 'story_cover_width_offset', 0 ) + 200,
        320,
        768
      ),
      '--in-content-cover-image-width' => self::get_clamp(
        100,
        (int) get_theme_mod( 'story_cover_width_offset', 0 ) + 200,
        375,
        768
      ),
      '--chapter-group-background-after' => ( $content_list_collapse === 'edge' ) ? 'none' : '""',
      '--ff-base' => Utils::get_font_family( 'primary_font_family_value', 'var(--ff-system)', 'Open Sans' ),
      '--ff-note' => Utils::get_font_family( 'secondary_font_family_value', 'var(--ff-base)', 'Lato' ),
      '--ff-heading' => Utils::get_font_family( 'heading_font_family_value', 'var(--ff-base)', 'Open Sans' ),
      '--ff-site-title' => Utils::get_font_family( 'site_title_font_family_value', 'var(--ff-heading)', 'default' ),
      '--ff-story-title' => Utils::get_font_family( 'story_title_font_family_value', 'var(--ff-heading)', 'default' ),
      '--ff-chapter-title' => Utils::get_font_family( 'chapter_title_font_family_value', 'var(--ff-heading)', 'default' ),
      '--ff-card-title' => Utils::get_font_family( 'card_title_font_family_value', 'var(--ff-heading)', 'default' ),
      '--ff-card-body' => Utils::get_font_family( 'card_body_font_family_value', 'var(--ff-note)', 'default' ),
      '--ff-card-list-link' => Utils::get_font_family( 'card_list_link_font_family_value', 'var(--ff-note)', 'default' ),
      '--ff-nav-item' => Utils::get_font_family( 'nav_item_font_family_value', 'var(--ff-base)', 'default' ),
      '--ff-chapter-list-title' => Utils::get_font_family( 'chapter_list_title_font_family_value', 'var(--ff-base)', 'default' )
    );

    $lines = [];

    foreach ( $base_properties as $name => $value ) {
      $lines[] = "{$name}:{$value};";
    }

    $css .= ':root{' . implode( '', $lines ) . '}';

    if ( $card_box_shadow === 'none' ) {
      $css .= ".card{box-shadow:none!important;}";
    }

    // --- Dark mode -------------------------------------------------------------

    $css .= self::get_dark_root_properties_css();

    // --- Light mode ------------------------------------------------------------

    $css .= self::get_light_root_properties_css();

    // --- Layout ----------------------------------------------------------------

    $layout_properties = [];

    if ( $sidebar_style !== 'none' && ! get_theme_mod( 'use_custom_layout' ) ) {
      $layout_properties['--layout-spacing-horizontal'] = self::get_clamp( 20, 48, 480, $site_width );
      $layout_properties['--layout-spacing-horizontal-small'] = self::get_clamp( 10, 20, 320, 400 );
    }

    if ( get_theme_mod( 'use_custom_layout' ) ) {
      $horizontal_min = (int) get_theme_mod( 'horizontal_spacing_min', 20 );
      $horizontal_max = (int) get_theme_mod( 'horizontal_spacing_max', 80 );
      $horizontal_small_min = (int) get_theme_mod( 'horizontal_spacing_small_min', 10 );
      $horizontal_small_max = (int) get_theme_mod( 'horizontal_spacing_small_max', 20 );
      $content_list_gap = (int) get_theme_mod( 'content_list_gap', 4 );

      if ( $sidebar_style !== 'none' ) {
        $css .= sprintf(
          '.has-sidebar{%s;%s;}',
          '--layout-spacing-horizontal:' . self::get_clamp( $horizontal_min, $horizontal_max, 480, $site_width ),
          '--layout-spacing-horizontal-small:' . self::get_clamp( $horizontal_small_min, $horizontal_small_max, 320, 400 )
        );
      }

      $layout_properties = array_merge(
        $layout_properties,
        array(
          '--layout-spacing-vertical' => self::get_clamp(
            (int) get_theme_mod( 'vertical_spacing_min', 24 ),
            (int) get_theme_mod( 'vertical_spacing_max', 48 ),
            480,
            $site_width
          ),
          '--layout-spacing-horizontal' => self::get_clamp(
            $horizontal_min,
            $horizontal_max,
            480,
            $site_width,
            '%'
          ),
          '--layout-spacing-horizontal-small' => self::get_clamp(
            $horizontal_small_min,
            $horizontal_small_max,
            320,
            400,
            '%'
          ),
          '--layout-border-radius-large' => (int) get_theme_mod( 'large_border_radius', 4 ) . 'px',
          '--layout-border-radius-small' => (int) get_theme_mod( 'small_border_radius', 2 ) . 'px',
          '--layout-nested-border-radius-multiplier' => max( 0, get_theme_mod( 'nested_border_radius_multiplier', 1 ) ),
          '--chapter-list-gap' => "{$content_list_gap}px",
          '--content-list-gap' => "{$content_list_gap}px",
        )
      );
    }

    $lines = [];

    foreach ( $layout_properties as $name => $value ) {
      $lines[] = "{$name}:{$value};";
    }

    $css .= ':root{' . implode( '', $lines ) . '}';

    // --- Assets ----------------------------------------------------------------

    if (
      $header_image_style === 'polygon-battered' ||
      $page_style === 'polygon-battered' ||
      $page_style === 'polygon-mask-image-battered-ringbook'
    ) {
      $css .= self::get_css_snippet( 'polygon-battered' );
    }

    // --- Header styles ---------------------------------------------------------

    $header_style_map = array(
      'top' => 'header-style-top-split',
      'split' => 'header-style-top-split',
      'wide' => 'header-style-wide',
      'text_center' => 'header-style-text-center',
      'post_content' => 'header-style-post-content'
    );

    if ( isset( $header_style_map[ $header_style ] ) ) {
      $css .= self::get_css_snippet( $header_style_map[ $header_style ] );
    }

    // --- Header image style ----------------------------------------------------

    $header_image_style_map = array(
      'polygon-battered' => 'header-image-style-battered',
      'polygon-chamfered' => 'header-image-style-chamfered',
      'mask-grunge-frame-a-large' => 'header-image-style-grunge-frame-a-large',
      'mask-grunge-frame-a-small' => 'header-image-style-grunge-frame-a-small'
    );

    if ( isset( $header_image_style_map[ $header_image_style ] ) ) {
      $css .= self::get_css_snippet( $header_image_style_map[ $header_image_style ] );
    }

    // --- Fading header image ---------------------------------------------------

    $header_fading_start = Sanitizer::sanitize_integer( get_theme_mod( 'header_image_fading_start', 0 ), 0, 0, 99 );

    if ( $header_fading_start > 0 ) {
      $header_fading_breakpoint = Sanitizer::sanitize_integer( get_theme_mod( 'header_image_fading_breakpoint', 0 ), 0 );

      if ( $header_fading_breakpoint > 320 ) {
        $css .= "@media only screen and (min-width: {$header_fading_breakpoint}px) {
          :root {
            --header-fading-mask-image: " . self::get_fading_gradient( 100, $header_fading_start, 100, 'var(--header-fading-mask-image-rotation, 180deg)' ) . ";
          }
        }";
      } else {
        $css .= ":root {
          --header-fading-mask-image: " . self::get_fading_gradient( 100, $header_fading_start, 100, 'var(--header-fading-mask-image-rotation, 180deg)' ) . ";
        }";
      }

      $css .= '@media only screen and (min-width: 1024px) {
        .inset-header-image .header-background._style-default._fading-bottom._shadow .header-background__wrapper {
          margin-left: 4px;
          margin-right: 4px;
        }
      }';
    }

    // --- Inset header image ----------------------------------------------------

    if ( get_theme_mod( 'inset_header_image' ) ) {
      $css .= self::get_css_snippet( 'inset-header-image' );
    }

    // --- Page styles -----------------------------------------------------------

    $page_style_map = array(
      'polygon-mask-image-battered-ringbook' => ['page-style-battered', 'page-style-ringbook'],
      'polygon-battered' => ['page-style-battered'],
      'mask-image-ringbook' => ['page-style-ringbook'],
      'polygon-chamfered' => ['page-style-chamfered'],
      'polygon-interface-a' => ['page-style-interface-a'],
      'mask-image-wave-a' => ['page-style-wave-a'],
      'mask-image-layered-steps-a' => ['page-style-layered-steps-a'],
      'mask-image-layered-peaks-a' => ['page-style-layered-peaks-a'],
      'mask-image-grunge-a' => ['page-style-grunge-a'],
      'none' => ['.main__background{display:none !important;content-visibility:hidden;}']
    );

    foreach ( $page_style_map[ $page_style ] ?? [] as $snippet ) {
      $css .= str_starts_with( $snippet, '.' )
        ? $snippet
        : self::get_css_snippet( $snippet );
    }

    // --- Page shadow -----------------------------------------------------------

    if ( ! get_theme_mod( 'page_shadow', true ) ) {
      $css .= ':root.no-page-shadow{--minimal-page-box-shadow: none;--page-box-shadow: none;--page-drop-shadow: none;}';
    }

    // --- Card styles -----------------------------------------------------------

    $card_style_map = array(
      'unfolded' => ['card-style-unfolded-combined'],
      'combined' => ['card-style-unfolded-combined', 'card-style-combined']
    );

    foreach ( $card_style_map[ $card_style ] ?? array() as $snippet ) {
      $css .= self::get_css_snippet( $snippet );
    }

    // --- Card frames -----------------------------------------------------------

    if ( $card_frame !== 'default' ) {
      $css .= ':root:not(.minimal) .card{filter:var(--card-drop-shadow);}';
    }

    if ( in_array( $card_frame, ['stacked_right', 'stacked_left', 'stacked_random'] ) ) {
      $css .= self::get_css_snippet( 'card-frame-stacked' );

      if ( $card_frame === 'stacked_left' ) {
        $css .= '.card{--this-rotation-mod:-1;}';
      }
    }

    $card_frame_map = array(
      'border_2px' => ':root:not(.minimal) .card{--card-style-border-width: 2px;box-shadow: 0 0 0 var(--card-frame-border-thickness, 2px) var(--card-frame-border-color);}',
      'border_3px' => ':root:not(.minimal) .card{--card-style-border-width: 3px;box-shadow: 0 0 0 var(--card-frame-border-thickness, 3px) var(--card-frame-border-color);}',
      'chamfered' => 'card-frame-chamfered',
      'battered' => 'card-frame-battered'
    );

    if ( isset( $card_frame_map[ $card_frame ] ) ) {
      $value = $card_frame_map[ $card_frame ];

      $css .= str_starts_with( $value, ':' )
        ? $value
        : self::get_css_snippet( $value );
    }

    // --- Content list style ----------------------------------------------------

    $content_list_style_map = array(
      'full' => 'content-list-style-full',
      'free' => 'content-list-style-free',
      'lines' => 'content-list-style-lines'
    );

    if ( isset( $content_list_style_map[ $content_list_style ] ) ) {
      $css .= self::get_css_snippet( $content_list_style_map[ $content_list_style ] );
    }

    // --- Content list collapse style -------------------------------------------

    if ( $content_list_collapse === 'edge' ) {
      $css .= ".chapter-group._closed .chapter-group__list{opacity:1;}.chapter-group__name{padding:12px 6px;}";
    }

    // --- Footer style ----------------------------------------------------------

    if ( $footer_style === 'isolated' ) {
      $css .= self::get_css_snippet( 'footer-style-isolated' );
    }

    return $css;
  }
}
