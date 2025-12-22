<?php

namespace Fictioneer;

use Fictioneer\Role_Admin;

defined( 'ABSPATH' ) OR exit;

class Role {
  private static $type_to_plural = array(
    'post' => 'posts',
    'page' => 'pages',
    'fcn_story' => 'fcn_stories',
    'fcn_chapter' => 'fcn_chapters',
    'fcn_collection' => 'fcn_collections',
    'fcn_recommendation' => 'fcn_recommendations'
  );

  /**
   * Initialize theme roles and capabilities.
   *
   * @since 5.33.2
   */

  public static function initialize() : void {
    if ( is_admin() ) {
      \Fictioneer\Role_Admin::initialize();
    }

    add_filter( 'post_password_required', [ self::class, 'bypass_password' ], 10, 2 );

    if ( ! current_user_can( 'manage_options' ) ) {
      self::add_restrictions();
    }
  }

  /**
   * Bypass for post passwords.
   *
   * @since 5.12.3
   * @since 5.15.0 - Add Patreon checks.
   * @since 5.16.0 - Add Patreon unlock checks and static variable cache.
   * @since 5.33.2 - Moved into Role class.
   *
   * @param bool     $required  Whether the user needs to supply a password.
   * @param \WP_Post $post      Post object.
   *
   * @return bool True or false.
   */

  public static function bypass_password( bool $required, \WP_Post $post ) : bool {
    // Already unlocked
    if ( ! $required ) {
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
    $patreon_user_data = fictioneer_get_user_patreon_data( $user->ID ); // Can be an empty array

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
   * @since 5.33.2
   */

  public static function add_restrictions() : void {
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
      add_action( 'admin_menu', [ self::class, 'remove_comments_menu_page' ] );
      add_action( 'wp_before_admin_bar_render', [ self::class, 'remove_comments_from_admin_bar' ] );
      add_action( 'current_screen', [ self::class, 'restrict_comment_screens' ] );
      add_filter( 'manage_posts_columns', [ self::class, 'remove_comments_column' ] );
      add_filter( 'manage_pages_columns', [ self::class, 'remove_comments_column' ] );
    }

    if ( current_user_can( 'moderate_comments' ) && current_user_can( 'fcn_only_moderate_comments' ) ) {
      add_filter( 'user_has_cap', [ self::class, 'edit_only_comments' ], 10, 3 );
      add_action( 'admin_menu', [ self::class, 'remove_post_menu_page' ], 99 );
      add_action( 'admin_bar_menu', [ self::class, 'remove_admin_bar_new_post' ], 99 );
    }

    // === MANAGE_OPTIONS ========================================================

    if ( ! current_user_can( 'manage_options' ) ) {
      add_action( 'admin_menu', [ self::class, 'reduce_admin_panel' ], 99 );
      add_action( 'current_screen', [ self::class, 'block_admin_only_screens' ] );
    }

    // === UPDATE_CORE ===========================================================

    if ( ! current_user_can( 'update_core' ) ) {
      add_action( 'admin_head', [ self::class, 'remove_update_notice' ] );
    }

    // === FCN_SHORTCODES ========================================================

    if ( ! current_user_can( 'fcn_shortcodes' ) ) {
      add_filter( 'wp_insert_post_data', [ self::class, 'strip_shortcodes_on_save' ], 1 );
    }

    // === EDIT_OTHERS_{POST_TYPE} ===============================================

    add_action( 'pre_get_posts', [ self::class, 'limit_posts_to_author' ] );
    add_filter( 'wp_count_posts', [ self::class, 'filter_counts_by_author' ], 10, 2 );

    // === FCN_READ_OTHERS_FILES =================================================

    if ( is_admin() && ! current_user_can( 'fcn_read_others_files' ) ) {
      add_action( 'admin_head', [ self::class, 'hide_inserter_media_tab_with_css' ] );
      add_action( 'pre_get_posts', [ self::class, 'limit_media_ajax_query_attachments' ] );
      add_action( 'pre_get_posts', [ self::class, 'limit_media_list_view' ] );
    }

    // === FCN_EDIT_OTHERS_FILES =================================================

    if ( ! current_user_can( 'fcn_edit_others_files' ) ) {
      add_filter( 'map_meta_cap', [ self::class, 'prevent_editing_others_attachments' ], 10, 4 );
    }

    // === FCN_DELETE_OTHERS_FILES ===============================================

    if ( ! current_user_can( 'fcn_delete_others_files' ) ) {
      add_filter( 'map_meta_cap', [ self::class, 'prevent_deleting_others_attachments' ], 9999, 4 );
    }

    // === FCN_PRIVACY_CLEARANCE =================================================

    if ( ! current_user_can( 'fcn_privacy_clearance' ) ) {
      add_filter( 'comment_email', '__return_false' );
      add_filter( 'get_comment_author_IP', '__return_empty_string' );
      add_filter( 'manage_users_columns', [ self::class, 'hide_users_columns' ] );
      add_filter( 'comment_row_actions', [ self::class, 'remove_comment_quick_edit' ] );
      add_action( 'admin_enqueue_scripts', [ self::class, 'hide_private_comment_data' ], 20 );
    }

    // === FCN_REDUCED_PROFILE ===================================================

    if ( current_user_can( 'fcn_reduced_profile' ) ) {
      add_filter( 'wp_is_application_passwords_available', '__return_false' );
      add_filter( 'user_contactmethods', '__return_empty_array' );
      add_action( 'admin_head', [ self::class, 'remove_profile_blocks' ] );
      remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
    }

    // === FCN_MAKE_STICKY =======================================================

    if ( ! current_user_can( 'fcn_make_sticky' ) ) {
      add_action( 'post_stuck', [ self::class, 'prevent_post_sticky' ] );
    }

    // === FCN_UPLOAD_LIMIT ======================================================

    if ( current_user_can( 'fcn_upload_limit' ) ) {
      add_filter( 'upload_size_limit', [ self::class, 'upload_size_limit' ] );
    }

    // === FCN_UPLOAD_RESTRICTION ================================================

    if ( current_user_can( 'fcn_upload_restrictions' ) ) {
      add_filter( 'wp_handle_upload_prefilter', [ self::class, 'upload_restrictions' ] );
    }

    // === FCN_ALL_BLOCKS ========================================================

    if ( ! current_user_can( 'fcn_all_blocks' ) ) {
      add_filter( 'allowed_block_types_all', [ self::class, 'restrict_block_types' ], 20 );
      add_filter( 'wp_insert_post_data', [ self::class, 'remove_restricted_block_content' ], 1 );
    }

    // === FCN_EDIT_PERMALINK ====================================================

    if ( ! current_user_can( 'fcn_edit_permalink' ) ) {
      add_action( 'admin_head', [ self::class, 'hide_permalink_with_css' ] );
      add_action( 'admin_footer', [ self::class, 'hide_permalink_with_js' ] );
      add_filter( 'wp_insert_post_data', [ self::class, 'prevent_permalink_edit' ], 99, 2 );
    }

    // === FCN_EDIT_DATE =========================================================

    if ( ! current_user_can( 'fcn_edit_date' ) ) {
      add_filter( 'wp_insert_post_data', [ self::class, 'prevent_publish_date_update' ], 1, 2 );
    }

    // === FCN_CLASSIC_EDITOR ====================================================

    if ( current_user_can( 'fcn_classic_editor' ) ) {
      add_filter( 'use_block_editor_for_post_type', '__return_false' );
      add_action( 'add_meta_boxes', [ self::class, 'restrict_classic_metaboxes' ] );
      add_action( 'admin_head', [ self::class, 'classic_editor_css_restrictions' ] );
      add_action( 'admin_footer', [ self::class, 'classic_editor_js_restrictions' ] );
    }
  }

  /**
   * Remove comment item from admin bar.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_comments_from_admin_bar() : void {
    global $wp_admin_bar;

    if ( $wp_admin_bar ) {
      $wp_admin_bar->remove_node( 'comments' );
    }
  }

  /**
   * Remove comments menu.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_comments_menu_page() : void {
    remove_menu_page( 'edit-comments.php' );
  }

  /**
   * Prevent direct access to comment admin screens.
   *
   * @since 5.33.2
   *
   * @param \WP_Screen $screen  Current screen object.
   */

  public static function restrict_comment_screens( $screen ) : void {
    if ( ! is_object( $screen ) || empty( $screen->id ) ) {
      return;
    }

    if ( $screen->id === 'edit-comments' || $screen->id === 'comment' ) {
      wp_die( 'Access denied.', 403 );
    }
  }

  /**
   * Remove comments column.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $columns  The table columns.
   *
   * @return array Modified table column.
   */

  public static function remove_comments_column( array $columns ) : array {
    if ( isset( $columns['comments'] ) ) {
      unset( $columns['comments'] );
    }

    return $columns;
  }

  /**
   * Restrict comment editing.
   *
   * @since 5.7.3
   * @since 5.33.2 - Renamed from fictioneer_edit_comments() and moved into Role class.
   *
   * @param array  $caps     Primitive capabilities required of the user.
   * @param string $cap      Capability being checked.
   * @param int    $user_id  The user ID.
   *
   * @return array The still allowed primitive capabilities of the user.
   */

  public static function disallow_edit_comment( array $caps, string $cap, int $user_id ) : array {
    if ( $cap !== 'edit_comment' ) {
      return $caps;
    }

    $user = get_userdata( $user_id );

    if ( $user && $user->has_cap( 'moderate_comments' ) ) {
      return $caps;
    }

    return ['do_not_allow'];
  }

  /**
   * Only allow editing of comments
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $all_caps  An array of all the user's capabilities.
   * @param array $caps      Primitive capabilities that are being checked.
   * @param array $args      Arguments passed to the capabilities check.
   *
   * @return array Modified capabilities array.
   */

  public static function edit_only_comments( array $all_caps, array $caps, array $args ) : array {
    if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
      return $all_caps;
    }

    $requested = $args[0] ?? '';

    if ( $requested !== 'edit_posts' ) {
      return $all_caps;
    }

    global $pagenow;

    $is_comment_screen = in_array( $pagenow, ['edit-comments.php', 'comment.php'], true );

    if ( ! $is_comment_screen ) {
      foreach ( $caps as $primitive ) {
        $all_caps[ $primitive ] = false;
      }
    }

    return $all_caps;
  }

  /**
   * Remove post menu page.
   *
   * @since 5.33.2
   */

  public static function remove_post_menu_page() : void {
    remove_menu_page( 'edit.php' );
  }

  /**
   * Remove post menu page.
   *
   * @since 5.33.2
   */

  public static function remove_admin_bar_new_post() : void {
    global $wp_admin_bar;

    if ( $wp_admin_bar ) {
      $wp_admin_bar->remove_node( 'new-post' );
    }
  }

  /**
   * Reduce admin panel.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function reduce_admin_panel() : void {
    remove_menu_page( 'tools.php' );
    remove_menu_page( 'plugins.php' );
    remove_menu_page( 'themes.php' );
  }

  /**
   * Restrict menu access for non-administrators.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param \WP_Screen $screen  Current screen object.
   */

  public static function block_admin_only_screens( \WP_Screen $screen ) : void {
    if ( ! is_object( $screen ) || empty( $screen->id ) ) {
      return;
    }

    static $blocked = array(
      'tools',
      'export',
      'import',
      'site-health',
      'export-personal-data',
      'erase-personal-data',
      'themes',
      'customize',
      'nav-menus',
      'theme-editor',
      'options-general',
    );

    if ( in_array( $screen->id, $blocked, true ) ) {
      wp_die( 'Access denied.', 403 );
    }
  }

  /**
   * Remove update notice.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_update_notice(){
    remove_action( 'admin_notices', 'update_nag', 3 );
  }

  /**
   * Strip shortcodes from content before saving to database.
   *
   * Note: The user can still use shortcodes on pages that already have them.
   * This is not ideal, but an edge case. Someone who cannot use shortcodes
   * usually also cannot edit others posts.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $data  Array of slashed, sanitized, and processed post data.
   *
   * @return array Modified post data with shortcodes removed.
   */

  public static function strip_shortcodes_on_save( array $data ) : array {
    if (
      current_user_can( 'fcn_shortcodes' ) ||
      get_current_user_id() !== (int) ( $data['post_author'] ?? 0 )
    ) {
      return $data;
    }

    if ( empty( $data['post_content'] ) || strpos( $data['post_content'], '[' ) === false ) {
      return $data;
    }

    add_filter( 'strip_shortcodes_tagnames', [ self::class, 'exempt_shortcodes_from_removal' ] );

    $data['post_content'] = strip_shortcodes( $data['post_content'] );

    // Only do this for the trigger post or bad things can happen!
    remove_filter( 'wp_insert_post_data', [ self::class, 'strip_shortcodes_on_save' ], 1 );
    remove_filter( 'strip_shortcodes_tagnames', [ self::class, 'exempt_shortcodes_from_removal' ] );

    return $data;
  }

  /**
   * Exempt shortcodes from being removed by strip_shortcodes().
   *
   * @since 5.14.0
   * @since 5.25.0 - Allowed 'fcnt' shortcode to pass.
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $tags_to_remove  Tags that strip_shortcodes() would remove.
   *
   * @return array Updated tags to be removed.
   */

  public static function exempt_shortcodes_from_removal( array $tags_to_remove ) : array {
    $exempt = ['fictioneer_fa', 'fcnt'];

    foreach ( $exempt as $tag ) {
      if ( ( $key = array_search( $tag, $tags_to_remove, true ) ) !== false ) {
        unset( $tags_to_remove[ $key ] );
      }
    }

    return $tags_to_remove;
  }

  /**
   * Limit admin post list-table to only include own posts.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param \WP_Query $query The WP_Query instance (passed by reference).
   */

  public static function limit_posts_to_author( \WP_Query $query ) : void {
    global $pagenow;

    if ( ! is_admin() || ! $query->is_main_query() || $pagenow !== 'edit.php' ) {
      return;
    }

    $post_type = (string) $query->get( 'post_type' );

    if ( ! isset( self::$type_to_plural[ $post_type ] ) ) {
      return;
    }

    if ( ! current_user_can( 'edit_others_' . self::$type_to_plural[ $post_type ] ) ) {
      $query->set( 'author', get_current_user_id() );
    }
  }

  /**
   * Filter list-table counts (All/Published/Trash) to only include own posts.
   *
   * @since 5.33.2
   *
   * @param \stdClass $counts  Post counts.
   * @param string    $type    Post type.
   *
   * @return \stdClass Filtered counts.
   */

  public static function filter_counts_by_author( $counts, $type ) {
    global $pagenow, $wpdb;

    if ( ! is_admin() || $pagenow !== 'edit.php' ) {
      return $counts;
    }

    if ( ! isset( self::$type_to_plural[ $type ] ) ) {
      return $counts;
    }

    if ( current_user_can( 'edit_others_' . self::$type_to_plural[ $type ] ) ) {
      return $counts;
    }

    $author = get_current_user_id();

    $rows = $wpdb->get_results(
      $wpdb->prepare(
        "SELECT post_status, COUNT(*) AS num_posts
        FROM {$wpdb->posts}
        WHERE post_type = %s
          AND post_author = %d
        GROUP BY post_status",
        $type,
        $author
      ),
      OBJECT_K
    );

    $out = new \stdClass();

    foreach ( get_object_vars( $counts ) as $status => $n ) {
      $out->$status = isset( $rows[ $status ] ) ? (int) $rows[ $status ]->num_posts : 0;
    }

    foreach ( $rows as $status => $row ) {
      if ( ! property_exists( $out, $status ) ) {
        $out->$status = (int) $row->num_posts;
      }
    }

    return $out;
  }

  /**
   * Prevent users from seeing attachments uploaded by others.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param \WP_Query $query  The queried attachments.
   */

  public static function limit_media_ajax_query_attachments( \WP_Query $query ) : void {
    global $pagenow;

    if ( $pagenow !== 'admin-ajax.php' ) {
      return;
    }

    if ( ( $_REQUEST['action'] ?? '' ) !== 'query-attachments' ) {
      return;
    }

    if ( ( $query->get( 'post_type' ) ?: 'attachment' ) !== 'attachment' ) {
      return;
    }

    $query->set( 'author', get_current_user_id() );
  }

  /**
   * Prevent users from seeing attachments uploaded by others in the Media list view.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param \WP_Query $query  The current WP_Query.
   */

  public static function limit_media_list_view( \WP_Query $query ) : void {
    if ( ! $query->is_main_query() ) {
      return;
    }

    global $pagenow;

    if ( $pagenow !== 'upload.php' ) {
      return;
    }

    if ( (string) $query->get( 'post_type' ) !== 'attachment' ) {
      return;
    }

    $query->set( 'author', get_current_user_id() );
  }

  /**
   * Hide inserter media tab with CSS.
   *
   * @since 5.27.3
   * @since 5.33.2 - Moved into Role class.
   */

  public static function hide_inserter_media_tab_with_css() : void {
    global $pagenow;

    if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
      return;
    }

    echo '<style type="text/css">.block-editor-tabbed-sidebar #tabs-1-media{display:none!important;}</style>';
  }

  /**
   * Prevent users from editing attachments uploaded by others.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array  $caps     Primitive capabilities required of the user.
   * @param string $cap      Capability being checked.
   * @param int    $user_id  The user ID.
   * @param array  $args     Context (typically starts with an object ID).
   *
   * @return array Modified primitive caps.
   */

  public static function prevent_editing_others_attachments( array $caps, string $cap, int $user_id, array $args ) : array {
    if ( $cap !== 'edit_post' ) {
      return $caps;
    }

    $post_id = (int) ( $args[0] ?? 0 );

    if ( $post_id < 1 ) {
      return $caps;
    }

    $post = get_post( $post_id );

    if ( ! $post || $post->post_type !== 'attachment' ) {
      return $caps;
    }

    if ( (int) $post->post_author === $user_id ) {
      return $caps;
    }

    return ['do_not_allow'];
  }

  /**
   * Prevent users from deleting attachments uploaded by others.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array  $caps     Primitive capabilities required of the user.
   * @param string $cap      Capability being checked.
   * @param int    $user_id  The user ID.
   * @param array  $args     Context (typically starts with an object ID).
   *
   * @return array Modified primitive caps.
   */

  public static function prevent_deleting_others_attachments( array $caps, string $cap, int $user_id, array $args ) : array {
    if ( $cap !== 'delete_post' ) {
      return $caps;
    }

    $post_id = (int) ( $args[0] ?? 0 );

    if ( $post_id < 1 ) {
      return $caps;
    }

    $post = get_post( $post_id );

    if ( ! $post || $post->post_type !== 'attachment' ) {
      return $caps;
    }

    if ( (int) $post->post_author === $user_id ) {
      return $caps;
    }

    return ['do_not_allow'];
  }

  /**
   * Remove email and name columns from user table.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $column_headers  Columns to show in the user table.
   *
   * @return array Reduced columns.
   */

  public static function hide_users_columns( array $column_headers ) : array {
    unset( $column_headers['email'], $column_headers['name'] );

    return $column_headers;
  }

  /**
   * Remove quick edit from comments table.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $actions  Actions per row in the comments table.
   *
   * @return array Reduced actions per row.
   */

  public static function remove_comment_quick_edit( array $actions ) : array {
    unset( $actions['quickedit'] );

    return $actions;
  }

  /**
   * Hide URL and email fields from comment edit page.
   *
   * Note: Best we can do, unfortunately.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function hide_private_comment_data() : void {
    $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

    if ( ! $screen || $screen->id !== 'comment' ) {
      return;
    }

    wp_add_inline_script(
      'fictioneer-admin-script',
      "jQuery(function($){
        $('.editcomment tr:nth-child(3)').remove();
        $('.editcomment tr:nth-child(2)').remove();
      });"
    );
  }

  /**
   * Hide subscriber profile blocks in admin panel.
   *
   * @since 5.6.0
   * @since 5.26.1 - Use wp_print_inline_script_tag().
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_profile_blocks() : void {
    global $pagenow;

    if ( $pagenow !== 'profile.php' ) {
      return;
    }

    echo '<style type="text/css">.user-url-wrap, .user-description-wrap, .user-first-name-wrap, .user-last-name-wrap, .user-language-wrap, .user-admin-bar-front-wrap, #contextual-help-link-wrap, #your-profile > h2:first-of-type { display: none; }</style>';

    if ( ! get_option( 'fictioneer_show_wp_login_link' ) ) {
      wp_print_inline_script_tag(
        'document.addEventListener("DOMContentLoaded",()=>{document.querySelectorAll(".user-pass1-wrap,.user-pass2-wrap,.pw-weak,.user-generate-reset-link-wrap").forEach(el=>{el.remove();});});',
        array(
          'id' => 'fictioneer-iife-remove-admin-profile-blocks',
          'type' => 'text/javascript',
          'data-jetpack-boost' => 'ignore',
          'data-no-optimize' => '1',
          'data-no-defer' => '1',
          'data-no-minify' => '1',
        )
      );
    }
  }

  /**
   * Prevent making posts sticky.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param int $post_id The post ID.
   */

  public static function prevent_post_sticky( int $post_id ) : void {
    unstick_post( $post_id );

    remove_action( 'post_stuck', [ self::class, 'prevent_post_sticky' ] );
  }

  /**
   * Limit the upload size in MB (minimum 1 MB).
   *
   * @since 5.6.0
   * @since 5.33.2 - Respect WP limit and moved into Role class.
   *
   * @param int $bytes  Current limit in bytes.
   *
   * @return int Modified maximum upload file size in bytes.
   */

  public static function upload_size_limit( int $bytes ) : int {
    $mb = (int) get_option( 'fictioneer_upload_size_limit', 5 );
    $mb = max( 1, $mb );

    $limit = $mb * 1024 * 1024;

    return min( $bytes, $limit );
  }

  /**
   * Restrict uploaded file types based on allowed MIME types.
   *
   * @since 5.6.0
   * @since 5.33.2 - Refactor and move into Role class.
   *
   * @param array $file  An array of data for a single uploaded file. Has keys
   *                     for 'name', 'type', 'tmp_name', 'error', and 'size'.
   *
   * @return array Potentially modified upload data.
   */

  public static function upload_restrictions( array $file ) : array {
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
   * Remove restricted Gutenberg blocks content on save.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $data  Array of slashed, sanitized, and processed post data.
   *
   * @return array Modified post data.
   */

  public static function remove_restricted_block_content( array $data ) : array {
    if ( empty( $data['post_content'] ) || ! is_string( $data['post_content'] ) ) {
      // Only do this for the trigger post or bad things can happen!
      remove_filter( 'wp_insert_post_data', [ self::class, 'remove_restricted_block_content' ], 1 );

      return $data;
    }

    $forbidden_patterns = array(
      '/<!--\s*wp:buttons.*?-->(.*?)<!--\s*\/wp:buttons.*?\s*-->/s',
      '/<!--\s*wp:button.*?-->(.*?)<!--\s*\/wp:button.*?\s*-->/s',
      '/<!--\s*wp:audio.*?-->(.*?)<!--\s*\/wp:audio.*?\s*-->/s',
      '/<!--\s*wp:video.*?-->(.*?)<!--\s*\/wp:video.*?\s*-->/s',
      '/<!--\s*wp:file.*?-->(.*?)<!--\s*\/wp:file.*?\s*-->/s',
      '/<!--\s*wp:jetpack.*?-->(.*?)<!--\s*\/wp:jetpack.*?\s*-->/s' // Because it's common enough
    );

    foreach ( $forbidden_patterns as $pattern ) {
      $data['post_content'] = preg_replace( $pattern, '', $data['post_content'] );
    }

    // Only do this for the trigger post or bad things can happen!
    remove_filter( 'wp_insert_post_data', [ self::class, 'remove_restricted_block_content' ], 1 );

    return $data;
  }

  /**
   * Restrict block types available in the editor.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @return array Allowed block types.
   */

  public static function restrict_block_types() : array {
    $allowed = array(
      'core/image',
      'core/paragraph',
      'core/heading',
      'core/columns',
      'core/list',
      'core/list-item',
      'core/gallery',
      'core/quote',
      'core/pullquote',
      'core/verse',
      'core/table',
      'core/code',
      'core/preformatted',
      'core/html',
      'core/separator',
      'core/spacer',
      'core/more',
      'core/embed',
      'core-embed/youtube',
      'core-embed/soundcloud',
      'core-embed/spotify',
      'core-embed/vimeo',
      'core-embed/twitter',
    );

    if ( current_user_can( 'fcn_shortcodes' ) ) {
      $allowed[] = 'core/shortcode';
    }

    return $allowed;
  }

  /**
   * Prevent user edit of permalink.
   *
   * @since 5.6.0
   * @since 5.8.6 - Fixed duplicate permalinks.
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $data     Array of slashed, sanitized, and processed post data.
   * @param array $postarr  Array of sanitized (and slashed) but otherwise unmodified post data.
   *
   * @return array Post data with enforced permalink.
   */

  public static function prevent_permalink_edit( array $data, array $postarr ) : array {
    $post_id = (int) ( $postarr['ID'] ?? 0 );

    if ( $post_id < 1 || empty( $data['post_name'] ) ) {
      return $data;
    }

    $current = (string) get_post_field( 'post_name', $post_id );

    if ( $data['post_name'] === $current ) {
      return $data;
    }

    $data['post_name'] = wp_unique_post_slug(
      sanitize_title( (string) ( $data['post_title'] ?? '' ) ),
      $post_id,
      (string) ( $data['post_status'] ?? 'draft' ),
      (string) ( $data['post_type'] ?? 'post' ),
      (int) ( $data['post_parent'] ?? 0 )
    );

    return $data;
  }

  /**
   * Hide the permalink field with CSS.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function hide_permalink_with_css() : void {
    global $pagenow;

    if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
      return;
    }

    echo '<style type="text/css" id="fictioneer-hide-permalink">.edit-post-post-url, #edit-slug-buttons {display: none !important;}</style>';
  }

  /**
   * Hide the permalink field with JS.
   *
   * @since 5.6.2
   * @since 5.26.1 - Use wp_print_inline_script_tag().
   * @since 5.33.2 - Moved into Role class.
   */

  public static function hide_permalink_with_js() : void {
    global $pagenow;

    if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
      return;
    }

    wp_print_inline_script_tag(
      'document.querySelectorAll("#edit-slug-buttons").forEach(element => {element.remove();});',
      array(
        'id' => 'fictioneer-iife-hide-permalink-in-editor',
        'type' => 'text/javascript',
        'data-jetpack-boost' => 'ignore',
        'data-no-optimize' => '1',
        'data-no-defer' => '1',
        'data-no-minify' => '1'
      )
    );
  }

  /**
   * Prevent updating the publish date after the post has been published once.
   *
   * Note: The date can be edited until the post has been published once, so you
   * can still schedule a post or change the target date. But once it is published,
   * the date cannot be changed.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $data     Array of slashed, sanitized, and processed post data.
   * @param array $postarr  Array of sanitized (and slashed) but otherwise unmodified post data.
   *
   * @return array Potentially modified post data.
   */

  public static function prevent_publish_date_update( array $data, array $postarr ) : array {
    $post_id = (int) ( $postarr['ID'] ?? 0 );

    if ( $post_id < 1 || ( $postarr['post_status'] ?? '' ) === 'auto-draft' || empty( $postarr['post_date_gmt'] ) ) {
      return $data;
    }

    $current_gmt = get_post_time( 'Y-m-d H:i:s', true, $post_id );

    if ( isset( $data['post_date_gmt'] ) && $current_gmt !== $data['post_date_gmt'] ) {
      unset( $data['post_date'], $data['post_date_gmt'] );
    }

    return $data;
  }

  /**
   * Inject CSS for the classic editor.
   *
   * @since 5.6.2
   * @since 5.33.2 - Moved into Role class.
   */

  public static function classic_editor_css_restrictions() : void {
    global $pagenow;

    if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
      return;
    }

    echo '<style type="text/css">.selectit[for="ping_status"], #add-new-comment {display: none !important;}</style>';
  }

  /**
   * Inject JavaScript for the classic editor.
   *
   * @since 5.6.2
   * @since 5.26.1 - Use wp_print_inline_script_tag().
   * @since 5.33.2 - Moved into Role class.
   */

  public static function classic_editor_js_restrictions() : void {
    global $pagenow;

    if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
      return;
    }

    wp_print_inline_script_tag(
      'document.querySelectorAll(".selectit[for=ping_status], #add-new-comment").forEach(element => {element.remove();});',
      array(
        'id' => 'fictioneer-iife-classic-editor-restrictions',
        'type' => 'text/javascript',
        'data-jetpack-boost' => 'ignore',
        'data-no-optimize' => '1',
        'data-no-defer' => '1',
        'data-no-minify' => '1'
      )
    );
  }

  /**
   * Restrict metaboxes in the classic editor.
   *
   * @since 5.6.2
   * @since 5.33.2 - Moved into Role class.
   */

  public static function restrict_classic_metaboxes() : void {
    $post_types = ['post', 'page', 'fcn_story', 'fcn_chapter', 'fcn_collection', 'fcn_recommendation'];

    // Trackbacks
    remove_meta_box( 'trackbacksdiv', $post_types, 'normal' );

    // Tags
    if ( ! current_user_can( 'assign_post_tags' ) ) {
      remove_meta_box( 'tagsdiv-post_tag', $post_types, 'side' );
    }

    // Categories
    if ( ! current_user_can( 'assign_categories' ) ) {
      remove_meta_box( 'categorydiv', $post_types, 'side' );
    }

    // Genres
    if ( ! current_user_can( 'assign_fcn_genres' ) ) {
      remove_meta_box( 'fcn_genrediv', $post_types, 'side' );
    }

    // Fandoms
    if ( ! current_user_can( 'assign_fcn_fandoms' ) ) {
      remove_meta_box( 'fcn_fandomdiv', $post_types, 'side' );
    }

    // Characters
    if ( ! current_user_can( 'assign_fcn_characters' ) ) {
      remove_meta_box( 'fcn_characterdiv', $post_types, 'side' );
    }

    // Content Warnings
    if ( ! current_user_can( 'assign_fcn_content_warnings' ) ) {
      remove_meta_box( 'fcn_content_warningdiv', $post_types, 'side' );
    }

    // Permalink
    if ( ! current_user_can( 'fcn_edit_permalink' ) ) {
      remove_meta_box( 'slugdiv', $post_types, 'normal' );
    }

    // Page template
    if ( ! current_user_can( 'fcn_select_page_template' ) ) {
      remove_meta_box( 'pageparentdiv', $post_types, 'side' );
    }
  }

  /**
   * Remove dashboard from admin bar dropdown.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_dashboard_from_admin_bar() : void {
    global $wp_admin_bar;

    if ( $wp_admin_bar ) {
      $wp_admin_bar->remove_menu( 'dashboard' );
    }
  }
}
