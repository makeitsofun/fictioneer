<?php

namespace Fictioneer;

use Fictioneer\Traits\Singleton_Trait;

defined( 'ABSPATH' ) OR exit;

class Utils_Admin {
  use Singleton_Trait;

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

  public static function get_random_username( bool $unique = true ) : string {
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

  public static function get_theme_color( string $mod, ?string $default = null ) : string {
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

  public static function hex_to_rgb( string $value ) : array|bool {
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

  public static function rgb_to_hsl( array $value, int $precision = 0 ) : array {
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

  public static function get_hsl_code( string $hex, string $output = 'default' ) : string {
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

  public static function get_hsl_font_code( string $hex ) : string {
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

  public static function get_font_family( string $option, string $font_default, string $mod_default ) : string {
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
   * Build bundled font stylesheet.
   *
   * @since 5.10.0
   * @since 5.33.2 - Moved into Utils_Admin class.
   */

  public static function bundle_fonts() : void {
    $fonts = apply_filters( 'fictioneer_filter_pre_build_bundled_fonts', fictioneer_get_font_data() );

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
          'css' => fictioneer_font_family_value( $font['family'] ?? '' ),
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
  }
}
