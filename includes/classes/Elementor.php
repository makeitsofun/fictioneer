<?php

namespace Fictioneer;

defined( 'ABSPATH' ) || exit;

use Fictioneer\Utils;

class Elementor {
  /**
   * Initialize hooks.
   *
   * @since 5.33.2
   */

  public static function init() : void {
    add_action( 'elementor/editor/after_enqueue_scripts', 'fictioneer_output_head_fonts', 5 );
    add_action( 'elementor/theme/register_locations', [ self::class, 'register_locations' ] );
    add_action( 'wp_enqueue_scripts', [ self::class, 'override_styles' ], 9999 );
    add_action( 'elementor/editor/after_enqueue_styles', [ self::class, 'override_editor_styles' ], 9999 );

    add_filter( 'elementor/fonts/groups', [ self::class, 'add_font_group' ] );
    add_filter( 'elementor/fonts/additional_fonts', [ self::class, 'add_additional_fonts' ] );
  }

  /**
   * Register Elementor locations.
   *
   * @since 5.20.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param object $elementor_theme_manager  The Elementor manager object.
   */

  public static function register_locations( $elementor_theme_manager ) : void {
    $elementor_theme_manager->register_location( 'header' );
    $elementor_theme_manager->register_location( 'footer' );

    $elementor_theme_manager->register_location(
      'nav_bar',
      array(
        'label' => esc_html__( 'Navigation Bar', 'fictioneer' ),
        'multiple' => false,
        'edit_in_content' => true
      )
    );

    $elementor_theme_manager->register_location(
      'nav_menu',
      array(
        'label' => esc_html__( 'Navigation Menu', 'fictioneer' ),
        'multiple' => false,
        'edit_in_content' => true
      )
    );

    $elementor_theme_manager->register_location(
      'mobile_nav_menu',
      array(
        'label' => esc_html__( 'Mobile Navigation Menu', 'fictioneer' ),
        'multiple' => false,
        'edit_in_content' => true
      )
    );

    $elementor_theme_manager->register_location(
      'story_header',
      array(
        'label' => esc_html__( 'Story Header', 'fictioneer' ),
        'multiple' => false,
        'edit_in_content' => true
      )
    );

    $elementor_theme_manager->register_location(
      'page_background',
      array(
        'label' => esc_html__( 'Page Background', 'fictioneer' ),
        'multiple' => false,
        'edit_in_content' => false
      )
    );
  }

  /**
   * Add override frontend styles for Elementor.
   *
   * @since 5.20.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function override_styles() : void {
    wp_register_style( 'fictioneer-elementor-override', false );
    wp_enqueue_style( 'fictioneer-elementor-override', false );

    $kit_id = get_option( 'elementor_active_kit' );

    if ( ! $kit_id  ) {
      return;
    }

    $css = "body.elementor-kit-{$kit_id} {
      --e-global-color-primary: var(--primary-500);
      --e-global-color-secondary: var(--fg-300);
      --e-global-color-text: var(--fg-500);
      --e-global-color-accent: var(--fg-700);
      --swiper-pagination-color: var(--fg-700);
      --swiper-pagination-bullet-inactive-opacity: .25;
    }";

    wp_add_inline_style( 'fictioneer-elementor-override', Utils::minify_css( $css ) );
  }

  /**
   * Add override editor styles for Elementor.
   *
   * @since 5.20.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function override_editor_styles() : void {
    wp_register_style( 'fictioneer-elementor-editor-override', false );
    wp_enqueue_style( 'fictioneer-elementor-editor-override', false );

    $css = '
      body {
        --primary-500: ' . Utils::get_theme_color( 'light_primary_500' ) . ';
        --fg-300: ' . Utils::get_theme_color( 'light_fg_300' ) . ';
        --fg-500: ' . Utils::get_theme_color( 'light_fg_500' ) . ';
        --fg-700: ' . Utils::get_theme_color( 'light_fg_700' ) . ';
      }

      .e-global__color[data-global-id="primary"] .e-global__color-preview-color {
        background-color: var(--primary-500) !important;
      }

      .e-global__color[data-global-id="primary"] .e-global__color-title::after {
        content: " ' . _x( '(--primary-500)', 'Elementor color override hint.', 'fictioneer' ) . '";
      }

      .e-global__popover-toggle--active + .pickr .pcr-button[style="--pcr-color: rgba(110, 193, 228, 1);"]::after {
        background: var(--primary-500) !important;
      }

      .e-global__color[data-global-id="secondary"] .e-global__color-preview-color {
        background-color: var(--fg-300) !important;
      }

      .e-global__color[data-global-id="secondary"] .e-global__color-title::after {
        content: " ' . _x( '(--fg-300)', 'Elementor color override hint.', 'fictioneer' ) . '";
      }

      .e-global__popover-toggle--active + .pickr .pcr-button[style="--pcr-color: rgba(84, 89, 95, 1);"]::after {
        background: var(--fg-300) !important;
      }

      .e-global__color[data-global-id="text"] .e-global__color-preview-color {
        background-color: var(--fg-500) !important;
      }

      .e-global__color[data-global-id="text"] .e-global__color-title::after {
        content: " ' . _x( '(--fg-500)', 'Elementor color override hint.', 'fictioneer' ) . '";
      }

      .e-global__popover-toggle--active + .pickr .pcr-button[style="--pcr-color: rgba(122, 122, 122, 1);"]::after {
        background: var(--fg-500) !important;
      }

      .e-global__color[data-global-id="accent"] .e-global__color-preview-color {
        background-color: var(--fg-700) !important;
      }

      .e-global__color[data-global-id="accent"] .e-global__color-title::after {
        content: " ' . _x( '(--fg-700)', 'Elementor color override hint.', 'fictioneer' ) . '";
      }

      .e-global__popover-toggle--active + .pickr .pcr-button[style="--pcr-color: rgba(97, 206, 112, 1);"]::after {
        background: var(--fg-700) !important;
      }

      .e-global__color .e-global__color-hex {
        display: none;
      }
    ';

    wp_add_inline_style( 'fictioneer-elementor-editor-override', Utils::minify_css( $css ) );
  }

  /**
   * Add Fictioneer font group.
   *
   * @since 5.20.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $groups  Array of font groups.
   *
   * @return array Updated font groups.
   */

  public static function add_font_group( $groups ) : array {
    $new_groups = array(
      'fictioneer' => __( 'Fictioneer', 'fictioneer' )
    );

    return array_merge( $new_groups, $groups );
  }

  /**
   * Add Fictioneer fonts to font group.
   *
   * @since 5.20.0
   *
   * @param array $fonts  Array of fonts.
   *
   * @return array Updated fonts.
   */

  public static function add_additional_fonts( $fonts ) : array {
    $theme_fonts = Utils::get_font_data();

    foreach ( $theme_fonts as $font ) {
      if ( $font['family'] ?? 0 ) {
        $fonts[ $font['family'] ] = 'fictioneer';
      }
    }

    return $fonts;
  }
}
