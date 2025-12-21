<?php

namespace Fictioneer;

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

    // === FCN_ADMIN_PANEL_ACCESS ================================================

    add_action( 'admin_init', [ self::class, 'restrict_admin_panel' ] );

    // === FCN_DASHBOARD_ACCESS ==================================================

    if ( current_user_can( 'fcn_admin_panel_access' ) && ! current_user_can( 'fcn_dashboard_access' ) ) {
      add_action( 'wp_dashboard_setup', [ self::class, 'remove_dashboard_widgets' ] );
      add_action( 'admin_menu', [ self::class, 'remove_dashboard_menu' ] );
      add_action( 'admin_init', [ self::class, 'skip_dashboard' ] );
      add_action( 'wp_before_admin_bar_render', [ self::class, 'remove_dashboard_from_admin_bar' ] );
    }

    // === FCN_SELECT_PAGE_TEMPLATE ==============================================

    if ( ! current_user_can( 'fcn_select_page_template' ) ) {
      add_filter( 'update_post_metadata', [ self::class, 'prevent_page_template_update' ], 1, 4 );
      add_filter( 'theme_templates', [ self::class, 'disallow_page_template_select' ], 1 );
      add_filter( 'wp_insert_post_data', [ self::class, 'prevent_parent_and_order_update' ], 1 );
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
  }

  /**
   * Prevent access to the admin panel.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function restrict_admin_panel() : void {
    if ( ! is_user_logged_in() ) {
      return;
    }

    if ( wp_doing_ajax() || wp_doing_cron() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
      return;
    }

    global $pagenow;

    if ( in_array( $pagenow, ['admin-post.php', 'async-upload.php'], true ) ) {
      return;
    }

    if ( ! current_user_can( 'fcn_admin_panel_access' ) ) {
      wp_safe_redirect( home_url( '/' ) );
      exit;
    }
  }

  /**
   * Remove admin dashboard widgets.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_dashboard_widgets() : void {
    global $wp_meta_boxes;

    if ( isset( $wp_meta_boxes['dashboard']['normal']['core'] ) ) {
      $wp_meta_boxes['dashboard']['normal']['core'] = [];
    }

    if ( isset( $wp_meta_boxes['dashboard']['side']['core'] ) ) {
      $wp_meta_boxes['dashboard']['side']['core'] = [];
    }

    remove_action( 'welcome_panel', 'wp_welcome_panel' );
    remove_meta_box( 'dashboard_plugins', 'dashboard', 'normal' );
  }

  /**
   * Remove the dashboard menu page.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function remove_dashboard_menu() : void {
    remove_menu_page( 'index.php' );
  }

  /**
   * Redirect from dashboard to user profile.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function skip_dashboard() : void {
    global $pagenow;

    if ( $pagenow !== 'index.php' || wp_doing_ajax() ) {
      return;
    }

    wp_safe_redirect( admin_url( 'profile.php' ) );
    exit;
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

  /**
   * Prevent parent and menu order from being updated.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $data  Array of slashed, sanitized, and processed post data.
   *
   * @return array Potentially modified post data.
   */

  public static function prevent_parent_and_order_update( array $data ) : array {
    unset( $data['post_parent'], $data['menu_order'] );

    return $data;
  }

  /**
   * Filter the page template selection list.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $templates  Array of templates ('name' => 'Display Name').
   *
   * @return array Allowed templates.
   */

  public static function disallow_page_template_select( array $templates ) : array {
    return array_intersect_key( $templates, FICTIONEER_ALLOWED_PAGE_TEMPLATES ) ?: [];
  }

  /**
   * Prevent update of page template based on conditions.
   *
   * Note: If the user lacks permission and the selected template is not
   * allowed for everyone, block the meta update.
   *
   * @since 5.6.2
   * @since 5.33.2 - Moved into Role class.
   *
   * @param mixed  $check       Null if allowed, anything else blocks update.
   * @param int    $object_id   ID of the object metadata is for.
   * @param string $meta_key    Metadata key.
   * @param mixed  $meta_value  Metadata value.
   *
   * @return mixed Null if allowed (yes), literally anything else if not.
   */

  public static function prevent_page_template_update( $check, int $object_id, string $meta_key, $meta_value ) {
    if ( $meta_key !== '_wp_page_template' ) {
      return $check;
    }

    if ( isset( FICTIONEER_ALLOWED_PAGE_TEMPLATES[ (string) $meta_value ] ) ) {
      return $check;
    }

    return false;
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
}
