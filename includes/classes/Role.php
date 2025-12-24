<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Role {
  /**
   * Initialize theme roles and capabilities.
   *
   * @since 5.34.0
   */

  public static function initialize() : void {
    if ( is_admin() ) {
      \Fictioneer\Role_Admin::initialize();
    }

    add_filter( 'post_password_required', [ self::class, 'bypass_password' ], 10, 2 );

    if ( ! current_user_can( 'manage_options' ) ) {
      self::add_frontend_restrictions();
    }
  }

  /**
   * Bypass for post passwords.
   *
   * @since 5.12.3
   * @since 5.15.0 - Add Patreon checks.
   * @since 5.16.0 - Add Patreon unlock checks and static variable cache.
   * @since 5.34.0 - Moved into Role class.
   *
   * @param bool          $required  Whether the user needs to supply a password.
   * @param \WP_Post|null $post      Post object.
   *
   * @return bool True or false.
   */

  public static function bypass_password( $required, $post ) : bool {
    // Already unlocked
    if ( ! $required || ! $post ) {
      return $required;
    }

    // Ensure to skip search, list pages, and nested loops
    if (
      ( ! wp_doing_ajax() || ! ( $_GET['post_id'] ?? 0 ) ) &&
      ( $_REQUEST['action'] ?? 0 ) !== 'fictioneer_ajax_submit_comment' &&
      ! ( $_REQUEST['comment_post_ID'] ?? 0 )
    ) {
      if ( ! is_singular() || get_queried_object_id() != $post->ID ) {
        return $required;
      }
    }

    // Static variable cache
    static $cache = [];

    $cache_key = $post->ID . '_' . get_current_user_id() . '_' . (int) $required;

    if ( isset( $cache[ $cache_key ] ) ) {
      return $cache[ $cache_key ];
    }

    // Default
    remove_filter( 'post_password_required', [ self::class, 'bypass_password' ] );
    $required = post_password_required( $post );
    add_filter( 'post_password_required', [ self::class, 'bypass_password' ], 10, 2 );

    // Notify cache plugins to NOT cache the page regardless of access
    if ( $required ) {
      fictioneer_disable_caching( 'protected_post' );
    }

    // Always allow admins
    if ( current_user_can( 'manage_options' ) ) {
      $cache[ $cache_key ] = false;

      return false;
    }

    // Setup
    $user = wp_get_current_user();
    $patreon_user_data = \Fictioneer\User::get_user_patreon_data( $user->ID ); // Can be an empty array

    // Check capability per post type...
    switch ( $post->post_type ) {
      case 'post':
        $required = current_user_can( 'fcn_ignore_post_passwords' ) ? false : $required;
        break;
      case 'page':
        $required = current_user_can( 'fcn_ignore_page_passwords' ) ? false : $required;
        break;
      case 'fcn_story':
        $required = current_user_can( 'fcn_ignore_fcn_story_passwords' ) ? false : $required;
        break;
      case 'fcn_chapter':
        $required = current_user_can( 'fcn_ignore_fcn_chapter_passwords' ) ? false : $required;
        break;
      case 'fcn_collection':
        $required = current_user_can( 'fcn_ignore_fcn_collection_passwords' ) ? false : $required;
        break;
    }

    // Check Patreon tiers
    if ( $user && $required && get_option( 'fictioneer_enable_patreon_locks' ) && ( $patreon_user_data['valid'] ?? 0 ) ) {
      $patreon_post_data = fictioneer_get_post_patreon_data( $post );

      // If there is anything to check...
      if ( $patreon_post_data['gated'] ) {
        $patreon_check_amount_cents =
          $patreon_post_data['gate_cents'] < 1 ? 999999999999 : $patreon_post_data['gate_cents'];

        $patreon_check_lifetime_amount_cents =
          $patreon_post_data['gate_lifetime_cents'] < 1 ? 999999999999 : $patreon_post_data['gate_lifetime_cents'];

        foreach ( $patreon_user_data['tiers'] as $tier ) {
          $required = ! (
            in_array( $tier['id'], $patreon_post_data['gate_tiers'] ) ||
            ( $tier['amount_cents'] ?? -1 ) >= $patreon_check_amount_cents ||
            ( $patreon_user_data['lifetime_support_cents'] ?? -1 ) >= $patreon_check_lifetime_amount_cents
          );

          $required = apply_filters( 'fictioneer_filter_patreon_tier_unlock', $required, $post, $user, $patreon_post_data );

          if ( ! $required ) {
            break;
          }
        }
      }
    }

    // Check unlocked posts
    if ( $user && $required ) {
      $story_id = fictioneer_get_chapter_story_id( $post->ID );
      $unlocks = get_user_meta( $user->ID, 'fictioneer_post_unlocks', true ) ?: [];
      $unlocks = is_array( $unlocks ) ? $unlocks : [];

      $lock_gate_amount = get_option( 'fictioneer_patreon_global_lock_unlock_amount', 0 ) ?: 0;
      $allow_unlocks = ! ( get_option( 'fictioneer_enable_patreon_locks' ) && $lock_gate_amount );

      // Check Patreon unlock gate
      if ( ! $allow_unlocks && ( $patreon_user_data['valid'] ?? 0 ) ) {
        foreach ( $patreon_user_data['tiers'] as $tier ) {
          if ( ( $tier['amount_cents'] ?? -1 ) >= $lock_gate_amount ) {
            $allow_unlocks = true;
            break;
          }
        }
      }

      if ( $allow_unlocks && $unlocks && array_intersect( [ $post->ID, $story_id ], $unlocks ) ) {
        $required = false;
      }
    }

    // Cache
    $cache[ $cache_key ] = $required;

    // Continue filter
    return $required;
  }

  /**
   * Add capability restrictions.
   *
   * @since 5.34.0
   */

  public static function add_frontend_restrictions() : void {
    if ( current_user_can( 'manage_options' ) ) {
      return;
    }

    // === FCN_ADMINBAR_ACCESS ===================================================

    if ( ! current_user_can( 'fcn_adminbar_access' ) ) {
      add_filter( 'show_admin_bar', '__return_false' );
    }

    // === FCN_DASHBOARD_ACCESS ==================================================

    if ( current_user_can( 'fcn_admin_panel_access' ) && ! current_user_can( 'fcn_dashboard_access' ) ) {
      add_action( 'wp_before_admin_bar_render', [ self::class, 'remove_dashboard_from_admin_bar' ] );
    }

    // === MODERATE_COMMENTS =====================================================

    if ( ! current_user_can( 'moderate_comments' ) )  {
      add_action( 'wp_before_admin_bar_render', [ self::class, 'remove_comments_from_admin_bar' ] );
    }

    if ( current_user_can( 'moderate_comments' ) && current_user_can( 'fcn_only_moderate_comments' ) ) {
      add_action( 'admin_bar_menu', [ self::class, 'remove_admin_bar_new_post' ], 99 );
    }

    // === FCN_PRIVACY_CLEARANCE =================================================

    if ( ! current_user_can( 'fcn_privacy_clearance' ) ) {
      add_filter( 'comment_email', '__return_false' );
      add_filter( 'get_comment_author_IP', '__return_empty_string' );
    }

    // === FCN_REDUCED_PROFILE ===================================================

    if ( current_user_can( 'fcn_reduced_profile' ) ) {
      add_filter( 'wp_is_application_passwords_available', '__return_false' );
      add_filter( 'user_contactmethods', '__return_empty_array' );
    }

    // === FCN_UPLOAD_LIMIT ======================================================

    if ( current_user_can( 'fcn_upload_limit' ) ) {
      add_filter( 'upload_size_limit', [ self::class, 'upload_size_limit' ] );
    }

    // === FCN_UPLOAD_RESTRICTION ================================================

    if ( current_user_can( 'fcn_upload_restrictions' ) ) {
      add_filter( 'wp_handle_upload_prefilter', [ self::class, 'upload_restrictions' ] );
    }
  }

  /**
   * Remove comment item from admin bar.
   *
   * @since 5.6.0
   * @since 5.34.0 - Moved into Role class.
   */

  public static function remove_comments_from_admin_bar() : void {
    global $wp_admin_bar;

    if ( $wp_admin_bar ) {
      $wp_admin_bar->remove_node( 'comments' );
    }
  }

  /**
   * Remove post menu page.
   *
   * @since 5.34.0
   */

  public static function remove_admin_bar_new_post() : void {
    global $wp_admin_bar;

    if ( $wp_admin_bar ) {
      $wp_admin_bar->remove_node( 'new-post' );
    }
  }

  /**
   * Limit the upload size in MB (minimum 1 MB).
   *
   * @since 5.6.0
   * @since 5.34.0 - Respect WP limit and moved into Role class.
   *
   * @param int $bytes  Current limit in bytes.
   *
   * @return int Modified maximum upload file size in bytes.
   */

  public static function upload_size_limit( $bytes ) : int {
    $mb = (int) get_option( 'fictioneer_upload_size_limit', 5 );
    $mb = max( 1, $mb );

    $limit = $mb * 1024 * 1024;

    return min( $bytes, $limit );
  }

  /**
   * Restrict uploaded file types based on allowed MIME types.
   *
   * @since 5.6.0
   * @since 5.34.0 - Refactor and move into Role class.
   *
   * @param array $file  An array of data for a single uploaded file. Has keys
   *                     for 'name', 'type', 'tmp_name', 'error', and 'size'.
   *
   * @return array Potentially modified upload data.
   */

  public static function upload_restrictions( $file ) : array {
    if ( ! empty( $file['error'] ) || empty( $file['name'] ) ) {
      return $file;
    }

    $filetype = wp_check_filetype( (string) $file['name'] );
    $mime_type = $filetype['type'] ?? '';

    if ( $mime_type === '' ) {
      $file['error'] = __( 'You are not allowed to upload files of this type.', 'fictioneer' );

      return $file;
    }

    static $allowed = null;

    if ( $allowed === null ) {
      $allowed = get_option( 'fictioneer_upload_mime_types' ) ?: FICTIONEER_DEFAULT_UPLOAD_MIME_TYPE_RESTRICTIONS;
      $allowed = wp_parse_list( $allowed );
      $allowed = array_values( array_unique( array_filter( array_map( 'strval', $allowed ) ) ) );
    }

    if ( ! in_array( $mime_type, $allowed, true ) ) {
      $file['error'] = __( 'You are not allowed to upload files of this type.', 'fictioneer' );
    }

    return $file;
  }

  /**
   * Remove dashboard from admin bar dropdown.
   *
   * @since 5.6.0
   * @since 5.34.0 - Moved into Role class.
   */

  public static function remove_dashboard_from_admin_bar() : void {
    global $wp_admin_bar;

    if ( $wp_admin_bar ) {
      $wp_admin_bar->remove_menu( 'dashboard' );
    }
  }
}
