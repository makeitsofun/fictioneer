<?php

use Fictioneer\Utils;
use Fictioneer\Utils_Admin;

// =============================================================================
// CUSTOM AVATAR
// =============================================================================

/**
 * Filter the avatar URL.
 *
 * @since 4.0.0
 *
 * @param string     $url          The default URL by WordPress.
 * @param int|string $id_or_email  User ID or email address.
 * @param WP_User    $args         Additional arguments.
 *
 * @return string The avatar URL.
 */

function fictioneer_get_avatar_url( $url, $id_or_email, $args ) {
  // Abort conditions...
  if ( ( $args['force_default'] ?? false ) || empty( $id_or_email ) ) {
    return $url;
  }

  // Setup
  $user = Utils::get_user_by_id_or_email( $id_or_email );
  $custom_avatar = \Fictioneer\User::get_custom_avatar_url( $user );

  // Check user and permissions
  if ( $user ) {
    $user_disabled = $user->fictioneer_disable_avatar;
    $admin_disabled = $user->fictioneer_admin_disable_avatar;

    if ( $user_disabled || $admin_disabled ) {
      return false;
    }
  } else {
    return $url;
  }

  // Return custom avatar if set
  if ( ! empty( $custom_avatar ) ) {
    return $custom_avatar;
  }

  // Return default avatar
  return $url;
}
add_filter( 'get_avatar_url', 'fictioneer_get_avatar_url', 10, 3 );

// =============================================================================
// FALLBACK AVATAR
// =============================================================================

if ( ! function_exists( 'fictioneer_get_default_avatar_url' ) ) {
  /**
   * Returns the default avatar URL
   *
   * @since 5.5.3
   *
   * @return string Default avatar URL.
   */

  function fictioneer_get_default_avatar_url() {
    $transient = get_transient( 'fictioneer_default_avatar' );

    // Check Transient
    if (
      empty( $transient ) ||
      ! is_array( $transient ) ||
      $transient['timestamp'] + DAY_IN_SECONDS < time()
    ) {
      // Get default avatar (and avoid infinite loop)
      remove_filter( 'get_avatar_url', 'fictioneer_get_avatar_url' );
      $default_url = get_avatar_url( 'nonexistentemail@example.com' );
      add_filter( 'get_avatar_url', 'fictioneer_get_avatar_url', 10, 3 );

      $transient = array(
        'url' => $default_url,
        'timestamp' => time()
      );

      set_transient( 'fictioneer_default_avatar', $transient );
    } else {
      $default_url = $transient['url'];
    }

    return $default_url;
  }
}

/**
 * Add fallback inline script to avatars
 *
 * @since 5.0.0
 *
 * @param string $avatar       HTML for the avatar.
 * @param string $id_or_email  ID or email of the user.
 *
 * @return string HTML with fallback script added.
 */

function fictioneer_avatar_fallback( $avatar, $id_or_email ) {
  $default_url = fictioneer_get_default_avatar_url();

  return str_replace( '<img', '<img onerror="this.src=\'' . $default_url . '\';this.srcset=\'\';this.onerror=\'\';"', $avatar );
}
add_filter( 'get_avatar', 'fictioneer_avatar_fallback', 1, 2 );

// =============================================================================
// GET COMMENT BADGE
// =============================================================================

if ( ! function_exists( 'fictioneer_get_comment_badge' ) ) {
  /**
   * Get HTML for comment badge
   *
   * @since 5.0.0
   *
   * @param WP_User|null    $user            The comment user.
   * @param WP_Comment|null $comment         Optional. The comment object.
   * @param int             $post_author_id  Optional. ID of the author of the post
   *                                         the comment is for.
   *
   * @return string Badge HTML or empty string.
   */

  function fictioneer_get_comment_badge( $user, $comment = null, $post_author_id = 0 ) {
    // Pre-setup
    $user_id = $user ? $user->ID : 0;
    $badge_body = '<div class="fictioneer-comment__badge %1$s">%2$s</div>';
    $filter_args = array(
      'comment' => $comment,
      'post_author_id' => $post_author_id
    );

    // Abort conditions...
    if ( empty( $user_id ) || get_the_author_meta( 'fictioneer_hide_badge', $user_id ) ) {
      $filter_args['class'] = 'is-guest';
      $filter_args['badge'] = '';
      $filter_args['body'] = $badge_body;

      return apply_filters( 'fictioneer_filter_comment_badge', '', $user, $filter_args );
    }

    // Setup
    $is_post_author = empty( $comment ) ? false : $comment->user_id == $post_author_id;
    $is_moderator = fictioneer_is_moderator( $user_id );
    $is_admin = fictioneer_is_admin( $user_id );
    $badge_class = '';
    $badge = '';
    $role_has_badge = user_can( $user_id, 'fcn_show_badge' );

    // Role badge
    if ( $role_has_badge ) {
      if ( $is_post_author ) {
        $badge = fcntr( 'author' );
        $badge_class = 'is-author';
      } elseif ( $is_admin ) {
        $badge = fcntr( 'admin' );
        $badge_class = 'is-admin';
      } elseif ( $is_moderator ) {
        $badge = fcntr( 'moderator' );
        $badge_class = 'is-moderator';
      } elseif ( ! empty( $user->roles ) ) {
        global $wp_roles;

        $role_slug = $user->roles[0] ?? '';
        $role = $wp_roles->roles[ $role_slug ];

        if ( ! empty( $role_slug ) ) {
          $badge = $role['name'];
          $badge_class = "is-{$role_slug}";
        }
      }
    }

    // Patreon badge (if no higher badge set yet)
    if ( empty( $badge ) ) {
      $badge = fictioneer_get_patreon_badge( $user );
      $badge_class = 'is-supporter';
    }

    // Custom badge (can override all)
    if (
      get_option( 'fictioneer_enable_custom_badges' ) &&
      ! get_the_author_meta( 'fictioneer_disable_badge_override', $user_id )
    ) {
      $custom_badge = fictioneer_get_override_badge( $user );

      if ( $custom_badge ) {
        $badge = $custom_badge;
        $badge_class = 'badge-override';
      }
    }

    // Apply filters
    $output = empty( $badge ) ? '' : sprintf( $badge_body, $badge_class, $badge );

    $filter_args['class'] = $badge_class;
    $filter_args['badge'] = $badge;
    $filter_args['body'] = $badge_body;

    $output = apply_filters( 'fictioneer_filter_comment_badge', $output, $user, $filter_args );

    // Return badge or empty sting
    return $output;
  }
}

// =============================================================================
// GET CUSTOM BADGE
// =============================================================================

if ( ! function_exists( 'fictioneer_get_override_badge' ) ) {
  /**
   * Get a user's custom badge (if any)
   *
   * @since 4.0.0
   *
   * @param WP_User        $user     The user.
   * @param string|boolean $default  Default value or false.
   *
   * @return string|boolean The badge label, default, or false.
   */

  function fictioneer_get_override_badge( $user, $default = false ) {
    // Abort conditions...
    if (
      ! $user ||
      get_the_author_meta( 'fictioneer_hide_badge', $user->ID )
    ) {
      return $default;
    }

    // Setup
    $badge = get_the_author_meta( 'fictioneer_badge_override', $user->ID );

    // Return badge of found and not disabled
    if ( $badge && ! get_the_author_meta( 'fictioneer_disable_badge_override', $user->ID ) ) {
      return $badge;
    }

    // Return default
    return $default;
  }
}

// =============================================================================
// PATREON
// =============================================================================

if ( ! function_exists( 'fictioneer_get_patreon_badge' ) ) {
  /**
   * Get a user's Patreon badge (if any)
   *
   * @since 5.0.0
   *
   * @param WP_User        $user     The user.
   * @param string|boolean $default  Default value or false.
   *
   * @return string|boolean The badge label, default, or false.
   */

  function fictioneer_get_patreon_badge( $user, $default = false ) {
    // Abort conditions...
    if ( ! $user ) {
      return $default;
    }

    // Check if still valid if not empty
    if ( fictioneer_patreon_tiers_valid( $user ) ) {
      $label = get_option( 'fictioneer_patreon_label' );

      return empty( $label ) ? _x( 'Patron', 'Default Patreon supporter badge label.', 'fictioneer' ) : $label;
    }

    return $default;
  }
}

/**
 * Checks whether the user's Patreon data is still valid
 *
 * Note: Patreon data expires after a set amount of time, one week
 * by default defined as FICTIONEER_PATREON_EXPIRATION_TIME.
 *
 * @since 5.15.0
 *
 * @param int|WP_User|null $user  The user object or user ID. Defaults to current user.
 *
 * @return boolean True if still valid, false if expired.
 */

function fictioneer_patreon_tiers_valid( $user = null ) {
  // Setup
  $user = $user ?? wp_get_current_user();
  $user_id = is_numeric( $user ) ? $user : $user->ID;

  // Abort conditions...
  if ( ! $user_id ) {
    return apply_filters( 'fictioneer_filter_user_patreon_validation', false, $user_id, [] );
  }

  // Setup
  $patreon_tiers = get_user_meta( $user_id, 'fictioneer_patreon_tiers', true );
  $patreon_tiers = is_array( $patreon_tiers ) ? $patreon_tiers : [];
  $last_updated = empty( $patreon_tiers ) ? 0 : ( reset( $patreon_tiers )['timestamp'] ?? 0 );

  // Check
  $valid = current_time( 'U', true ) <= ( $last_updated + FICTIONEER_PATREON_EXPIRATION_TIME );

  // Filter and return
  return apply_filters( 'fictioneer_filter_user_patreon_validation', $valid, $user_id, $patreon_tiers );
}

/**
 * Returns Patreon data of the user
 *
 * @since 5.17.0
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
  // Setup
  $user = $user ?? wp_get_current_user();
  $user_id = is_numeric( $user ) ? $user : $user->ID;

  // Get meta data
  $membership = get_user_meta( $user_id, 'fictioneer_patreon_membership', true );
  $membership = is_array( $membership ) ? $membership : [];

  if ( $membership ) {
    $tiers = get_user_meta( $user_id, 'fictioneer_patreon_tiers', true );
    $membership['tiers'] = is_array( $tiers ) ? $tiers : [];
    $membership['valid'] = fictioneer_patreon_tiers_valid( $user_id );
  }

  // Return
  return $membership;
}
