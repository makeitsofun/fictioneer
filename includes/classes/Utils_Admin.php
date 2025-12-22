<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Utils_Admin {
  /**
   * Return array of adjectives for randomized username generation.
   *
   * @since 5.19.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @return string[] Array of adjectives.
   */

  public static function get_username_adjectives() : array {
    static $adjectives = array(
      'Radical', 'Tubular', 'Gnarly', 'Epic', 'Electric', 'Neon', 'Bodacious', 'Rad',
      'Totally', 'Funky', 'Wicked', 'Fresh', 'Chill', 'Groovy', 'Vibrant', 'Flashy',
      'Buff', 'Hella', 'Motor', 'Cyber', 'Pixel', 'Holo', 'Stealth', 'Synthetic',
      'Enhanced', 'Synth', 'Bio', 'Laser', 'Virtual', 'Analog', 'Mega', 'Wave', 'Solo',
      'Retro', 'Quantum', 'Robotic', 'Digital', 'Hyper', 'Punk', 'Giga', 'Electro',
      'Chrome', 'Fusion', 'Vivid', 'Stellar', 'Galactic', 'Turbo', 'Atomic', 'Cosmic',
      'Artificial', 'Kinetic', 'Binary', 'Hypersonic', 'Runic', 'Data', 'Knightly',
      'Cryonic', 'Nebular', 'Golden', 'Silver', 'Red', 'Crimson', 'Augmented', 'Vorpal',
      'Ascended', 'Serious', 'Solid', 'Master', 'Prism', 'Spinning', 'Masked', 'Hardcore',
      'Somber', 'Celestial', 'Arcane', 'Luminous', 'Ionized', 'Lunar', 'Uncanny', 'Subatomic',
      'Luminary', 'Radiant', 'Ultra', 'Starship', 'Space', 'Starlight', 'Interstellar', 'Metal',
      'Bionic', 'Machine', 'Isekai', 'Warp', 'Neo', 'Alpha', 'Power', 'Unhinged', 'Ash',
      'Savage', 'Silent', 'Screaming', 'Misty', 'Rending', 'Horny', 'Dreadful', 'Bizarre',
      'Chaotic', 'Wacky', 'Twisted', 'Manic', 'Crystal', 'Infernal', 'Ruthless', 'Grim',
      'Mortal', 'Forsaken', 'Heretical', 'Cursed', 'Blighted', 'Scarlet', 'Delightful',
      'Nuclear', 'Azure', 'Emerald', 'Amber', 'Mystic', 'Ethereal', 'Enchanted', 'Valiant',
      'Fierce', 'Obscure', 'Enigmatic'
    );

    return apply_filters( 'fictioneer_random_username_adjectives', $adjectives );
  }

  /**
   * Return array of nouns for randomized username generation.
   *
   * @since 5.19.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @return string[] Array of nouns.
   */

  public static function get_username_nouns() : array {
    static $nouns = array(
      'Avatar', 'Cassette', 'Rubiks', 'Gizmo', 'Synthwave', 'Tron', 'Replicant', 'Warrior',
      'Hacker', 'Samurai', 'Cyborg', 'Runner', 'Mercenary', 'Shogun', 'Maverick', 'Glitch',
      'Byte', 'Matrix', 'Motion', 'Shinobi', 'Circuit', 'Droid', 'Virus', 'Vortex', 'Mech',
      'Codex', 'Hologram', 'Specter', 'Intelligence', 'Technomancer', 'Rider', 'Ghost',
      'Hunter', 'Hound', 'Wizard', 'Knight', 'Rogue', 'Scout', 'Ranger', 'Paladin', 'Sorcerer',
      'Mage', 'Artificer', 'Cleric', 'Tank', 'Fighter', 'Pilot', 'Necromancer', 'Neuromancer',
      'Barbarian', 'Streetpunk', 'Phantom', 'Shaman', 'Druid', 'Dragon', 'Dancer', 'Captain',
      'Pirate', 'Snake', 'Rebel', 'Kraken', 'Spark', 'Blitz', 'Alchemist', 'Dragoon', 'Geomancer',
      'Neophyte', 'Terminator', 'Tempest', 'Enigma', 'Automaton', 'Daemon', 'Juggernaut',
      'Paragon', 'Sentinel', 'Viper', 'Velociraptor', 'Spirit', 'Punk', 'Synth', 'Biomech',
      'Engineer', 'Pentagoose', 'Vampire', 'Soldier', 'Chimera', 'Lobotomy', 'Mutant',
      'Revenant', 'Wraith', 'Chupacabra', 'Banshee', 'Fae', 'Leviathan', 'Cenobite', 'Bob',
      'Ketchum', 'Collector', 'Student', 'Lover', 'Chicken', 'Alien', 'Titan', 'Sinner',
      'Nightmare', 'Bioplague', 'Annihilation', 'Elder', 'Priest', 'Guardian', 'Quagmire',
      'Berserker', 'Oblivion', 'Decimator', 'Devastation', 'Calamity', 'Doom', 'Ruin', 'Abyss',
      'Heretic', 'Armageddon', 'Obliteration', 'Inferno', 'Torment', 'Carnage', 'Purgatory',
      'Chastity', 'Angel', 'Raven', 'Star', 'Trinity', 'Idol', 'Eidolon', 'Havoc', 'Nirvana',
      'Digitron', 'Phoenix', 'Lantern', 'Warden', 'Falcon'
    );

    return apply_filters( 'fictioneer_random_username_nouns', $nouns );
  }

  /**
   * Return randomized username.
   *
   * @since 5.19.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param bool $unique  Optional. Whether the username must be unique. Default true.
   *
   * @return string Sanitized random username.
   */

  public static function get_random_username( $unique = true ) : string {
    $adjectives = Utils_Admin::get_username_adjectives();
    $nouns = Utils_Admin::get_username_nouns();

    shuffle( $adjectives );
    shuffle( $nouns );

    do {
      $username = $adjectives[ array_rand( $adjectives ) ] . $nouns[ array_rand( $nouns ) ] . rand( 1000, 9999 );
      $username = sanitize_user( $username, true );
    } while ( username_exists( $username ) && $unique );

    return $username;
  }

  /**
   * Return associative array of theme colors.
   *
   * Notes: Considers both parent and child theme.
   *
   * @since 5.21.2
   * @since 5.33.2 - Refactored and moved into Utils_Admin class.
   *
   * @return array Associative array of theme colors.
   */

  public static function get_theme_colors() : array {
    static $colors = null;

    if ( $colors !== null ) {
      return $colors;
    }

    $read_json = static function( string $file ) : array {
      if ( ! is_readable( $file ) ) {
        return [];
      }

      $raw = file_get_contents( $file );

      if ( $raw === false || $raw === '' ) {
        return [];
      }

      $data = json_decode( $raw, true );

      return is_array( $data ) ? $data : [];
    };

    $parent_file = get_parent_theme_file_path( 'includes/colors.json' );
    $child_file  = get_theme_file_path( 'includes/colors.json' );

    $parent = $read_json( $parent_file );

    if ( $child_file && $child_file !== $parent_file ) {
      $child = $read_json( $child_file );
    } else {
      $child = [];
    }

    $colors = array_merge( $parent, $child );

    return $colors;
  }

  /**
   * Return theme color mod with default fallback.
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

  public static function get_theme_color( $mod, $default = null ) : string {
    $colors = self::get_theme_colors();
    $default = $default ?? $colors[ $mod ]['hex'] ?? '#ff6347'; // Tomato

    return get_theme_mod( $mod, $default );
  }

  /**
   * Convert hex color to RGB array.
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

  public static function hex_to_rgb( $value ) {
    if ( substr( trim( $value ), 0, 1 ) === '#' ) {
      $value = substr( $value, 1 );
    }

    if ( ( strlen( $value ) < 2) || ( strlen( $value ) > 6 ) ) {
      return false;
    }

    $values = str_split( $value );

    if ( strlen( $value ) === 2 ) {
      $r = intval( $values[0] . $values[1], 16 );
      $g = $r;
      $b = $r;
    } else if ( strlen( $value ) === 3 ) {
      $r = intval( $values[0], 16 );
      $g = intval( $values[1], 16 );
      $b = intval( $values[2], 16 );
    } else if ( strlen( $value ) === 6 ) {
      $r = intval( $values[0] . $values[1], 16 );
      $g = intval( $values[2] . $values[3], 16 );
      $b = intval( $values[4] . $values[5], 16 );
    } else {
      return false;
    }

    return array( $r, $g, $b );
  }

  /**
   * Convert RGB color array to HSL.
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

  public static function rgb_to_hsl( $value, $precision = 0 ) : array {
    $r = max( min( intval( $value[0], 10 ) / 255, 1 ), 0 );
    $g = max( min( intval( $value[1], 10 ) / 255, 1 ), 0 );
    $b = max( min( intval( $value[2], 10 ) / 255, 1 ), 0 );
    $max = max( $r, $g, $b );
    $min = min( $r, $g, $b );
    $l = ( $max + $min ) / 2;

    if ( $max !== $min ) {
      $d = $max - $min;
      $s = $l > 0.5 ? $d / ( 2 - $max - $min ) : $d / ( $max + $min );
      if ( $max === $r ) {
        $h = ( $g - $b ) / $d + ( $g < $b ? 6 : 0 );
      } else if ( $max === $g ) {
        $h = ( $b - $r ) / $d + 2;
      } else {
        $h = ( $r - $g ) / $d + 4;
      }
      $h = $h / 6;
    } else {
      $h = $s = 0;
    }

    return [round( $h * 360, $precision ), round( $s * 100, $precision ), round( $l * 100, $precision )];
  }

  /**
   * Convert a hex color to a Fictioneer HSL code.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $hex     Hex color.
   * @param string $output  Switch output style. Default 'default'.
   *
   * @return string Converted HSL code.
   */

  public static function get_hsl_code( $hex, $output = 'default' ) : string {
    if ( ! is_string( $hex ) || ! preg_match( '/^#?(?:[a-fA-F0-9]{3}|[a-fA-F0-9]{6}|[a-fA-F0-9]{8})$/', $hex ) ) {
      return $hex;
    }

    $hsl_array = self::rgb_to_hsl( self::hex_to_rgb( $hex ) ?: [0, 0, 0], 2 );

    if ( $output == 'values' ) {
      return "$hsl_array[0] $hsl_array[1] $hsl_array[2]";
    }

    $deg = 'calc(' . $hsl_array[0] . 'deg + var(--hue-rotate))';
    $saturation = 'calc(' . $hsl_array[1] . '% * var(--saturation))';
    $min = max( 0, $hsl_array[2] * 0.5 );
    $max = $hsl_array[2] + (100 - $hsl_array[2]) / 2;
    $lightness = 'clamp('. $min . '%, ' . $hsl_array[2] . '% * var(--darken), ' . $max . '%)';

    if ( $output == 'free' ) {
      return "$deg $saturation $lightness";
    }

    return "hsl($deg $saturation $lightness)";
  }

  /**
   * Convert a hex color to an HSL font code.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $hex  Hex color.
   *
   * @return string Converted HSL font code.
   */

  public static function get_hsl_font_code( $hex ) : string {
    $hsl_array = self::rgb_to_hsl( self::hex_to_rgb( $hex ) ?: [0, 0, 0], 2 );

    $deg = 'calc(' . $hsl_array[0] . 'deg + var(--hue-rotate))';
    $saturation = 'max(calc(' . $hsl_array[1] . '% * (var(--font-saturation) + var(--saturation) - 1)), 0%)';
    $lightness = 'clamp(0%, calc(' . $hsl_array[2] . '% * var(--font-lightness, 1)), 100%)';

    return "hsl($deg $saturation $lightness)";
  }

  /**
   * Return a font family value.
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
    $selection = get_theme_mod( $option, $mod_default );
    $family = $font_default;

    switch ( $selection ) {
      case 'system':
        $family = 'var(--ff-system)';
        break;
      case 'default':
        $family = $font_default;
        break;
      default:
        $family = "'{$selection}', {$font_default}";
    }

    return $family;
  }

  /**
   * Return fonts data from a Google Fonts link.
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
    $link = trim( $link );

    if ( preg_match( '#^https://fonts\.googleapis\.com/css2(?:\?|$)#i', $link ) !== 1 ) {
      return null; // Not a Google Fonts link
    }

    $parts = wp_parse_url( $link );

    if ( ! is_array( $parts ) || empty( $parts['query'] ) ) {
      return false;
    }

    if ( preg_match_all( '/(?:^|&)family=([^&]+)/', (string) $parts['query'], $m ) !== 1 ) {
      return false; // Reject multiple 'family='
    }

    $family_raw = trim( (string) $m[1][0] );

    if ( $family_raw === '' ) {
      return false;
    }

    $family_decoded = rawurldecode( str_replace( '+', ' ', $family_raw ) );

    $name = trim( strtok( $family_decoded, ':' ) );

    if ( $name === '' ) {
      return false;
    }

    $font = array(
      'google_link' => $link,
      'skip' => true,
      'chapter' => true,
      'version' => '',
      'key' => sanitize_title( $name ),
      'name' => $name,
      'family' => $name,
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

    $weights = [];
    $is_italic = false;

    if ( preg_match( '/:ital,wght@([0-9,;]+)/', $family_decoded, $ital_weight_matches ) === 1 ) {
      foreach ( explode( ';', $ital_weight_matches[1] ) as $spec ) {
        $pair = explode( ',', $spec, 2 );

        if ( count( $pair ) !== 2 ) {
          continue;
        }

        list( $ital, $weight ) = $pair;

        if ( $ital === '1' ) {
          $is_italic = true;
        }

        $weights[ (string) $weight ] = true;
      }
    } elseif ( preg_match( '/:wght@([0-9;]+)/', $family_decoded, $ital_weight_matches ) === 1 ) {
      foreach ( explode( ';', $ital_weight_matches[1] ) as $weight ) {
        $weights[ (string) $weight ] = true;
      }
    }

    if ( $is_italic ) {
      $font['styles'][] = 'italic';
    }

    if ( $weights ) {
      $font['weights'] = array_keys( $weights );
    }

    return $font;
  }

  /**
   * Return fonts included by the theme.
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
    $extract_font_data = static function( string $font_dir, bool $in_child_theme ) : array {
      if ( ! is_dir( $font_dir ) ) {
        return [];
      }

      $out = [];

      foreach ( array_diff( scandir( $font_dir ), [ '.', '..' ] ) as $folder ) {
        $full_path = $font_dir . '/' . $folder;

        if ( ! is_dir( $full_path ) ) {
          continue;
        }

        $json_file = $full_path . '/font.json';
        $css_file = $full_path . '/font.css';

        if ( ! is_readable( $json_file ) || ! is_readable( $css_file ) ) {
          continue;
        }

        $raw = file_get_contents( $json_file );

        if ( $raw === false || $raw === '' ) {
          continue;
        }

        $data = json_decode( $raw, true );

        if ( ! is_array( $data ) || ! empty( $data['remove'] ) ) {
          continue;
        }

        if ( empty( $data['key'] ) || ! is_string( $data['key'] ) ) {
          continue;
        }

        $key = sanitize_key( $data['key'] );

        if ( $key === '' ) {
          continue;
        }

        $folder_name = basename( (string) $folder );

        $data['dir'] = "/fonts/{$folder_name}";
        $data['css_path'] = "/fonts/{$folder_name}/font.css";
        $data['css_file'] = $css_file;
        $data['in_child_theme'] = $in_child_theme;

        $out[ $key ] = $data;
      }

      return $out;
    };

    $parent_font_dir = trailingslashit( get_template_directory() ) . 'fonts';
    $child_font_dir = trailingslashit( get_stylesheet_directory() ) . 'fonts';

    $fonts = $extract_font_data( $parent_font_dir, false );

    if ( $child_font_dir !== $parent_font_dir ) {
      $fonts = array_merge( $fonts, $extract_font_data( $child_font_dir, true ) );
    }

    $google_fonts_links = get_option( 'fictioneer_google_fonts_links' );

    if ( is_string( $google_fonts_links ) && trim( $google_fonts_links ) !== '' ) {
      foreach ( preg_split( "/\R/u", trim( $google_fonts_links ) ) ?: [] as $link ) {
        $link = trim( $link );

        if ( $link === '' ) {
          continue;
        }

        $font = Utils::extract_font_from_google_link( $link );

        if ( is_array( $font ) && ! empty( $font['key'] ) ) {
          $fonts[ $font['key'] ] = $font;
        }
      }
    }

    return apply_filters( 'fictioneer_filter_font_data', $fonts );
  }

  /**
   * Build bundled font stylesheet.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @return array Font stack.
   */

  public static function bundle_fonts() : array {
    $fonts = apply_filters( 'fictioneer_filter_pre_build_bundled_fonts', self::get_font_data() );

    $disabled_fonts = Utils::get_disabled_fonts();
    $parent_uri = trailingslashit( get_template_directory_uri() );
    $child_uri = trailingslashit( get_stylesheet_directory_uri() );
    $combined_css = '';
    $font_stack = [];

    $base_file = get_parent_theme_file_path( 'css/fonts-base.css' );

    if ( is_readable( $base_file ) ) {
      $css = file_get_contents( $base_file );

      if ( $css !== false && $css !== '' ) {
        $combined_css .= str_replace( '../fonts/', $parent_uri . 'fonts/', $css );
      }
    }

    foreach ( $fonts as $key => $font ) {
      if ( in_array( $key, $disabled_fonts, true ) ) {
        continue;
      }

      if ( ! empty( $font['chapter'] ) ) {
        $font_stack[ $font['key'] ?? $key ] = array(
          'css' => Utils::get_font_family_value( $font['family'] ?? '' ),
          'name' => $font['name'] ?? '',
          'alt' => $font['alt'] ?? ''
        );
      }

      if ( ! empty( $font['skip'] ) || ! empty( $font['google_link'] ) ) {
        continue;
      }

      $css_file = $font['css_file'] ?? '';

      if ( ! is_string( $css_file ) || $css_file === '' || ! is_readable( $css_file ) ) {
        continue;
      }

      $css = file_get_contents( $css_file );

      if ( empty( $css ) ) {
        continue;
      }

      $uri = empty( $font['in_child_theme'] ) ? $parent_uri : $child_uri;
      $combined_css .= str_replace( '../fonts/', $uri . 'fonts/', $css );
    }

    update_option( 'fictioneer_chapter_fonts', $font_stack, true );
    update_option( 'fictioneer_bundled_fonts_timestamp', time(), true );

    $save_path = Utils::get_cache_dir( 'build_bundled_fonts' ) . 'bundled-fonts.css';

    if ( file_put_contents( $save_path, $combined_css ) === false ) {
      error_log( '[Fictioneer] Failed to write bundled fonts CSS: ' . $save_path );
    }

    return $font_stack;
  }

  /**
   * Check whether an URL exists.
   *
   * @since 4.0.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param string $url  The URL to check.
   *
   * @return bool True if the URL exists and false otherwise. Probably.
   */

  public static function url_exists( $url ) : bool {
    if ( empty( $url ) ) {
      return false;
    }

    $response = wp_remote_head( $url, array( 'timeout' => 2, 'redirection' => 0 ) );

    if ( is_wp_error( $response ) ) {
      return false;
    }

    $statusCode = wp_remote_retrieve_response_code( $response );

    return ( $statusCode >= 200 && $statusCode < 300 );
  }

  /**
   * Return word count of a post.
   *
   * @since 5.25.0
   * @since 5.30.0 - Fixed for accuracy (hopefully).
   * @since 5.33.2 - Moved into Utils_Admin class.
   *
   * @param int         $post_id  ID of the post to count the words of.
   * @param string|null $content  Optional. The post content. Queries the field by default.
   *
   * @return int The word count.
   */

  public static function count_words( $post_id, $content = null ) : int {
    // Prepare
    $content = $content ?? get_post_field( 'post_content', $post_id ) ?: '';
    $content = strip_shortcodes( $content );
    $content = strip_tags( $content );
    $content = html_entity_decode( $content, ENT_QUOTES | ENT_HTML5 );
    $content = preg_replace( '/[‐–—―‒−⁃]/u', ' - ', $content );

    preg_match_all(
      "/\b\p{L}[\p{L}\p{N}'’]*(?:-\p{L}[\p{L}\p{N}'’]*)*\b/u",
      $content,
      $matches
    );

    return count( $matches[0] );
  }
}
