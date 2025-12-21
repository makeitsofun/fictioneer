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
   * Prevent users from seeing uploaded files of others.
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
   * Prevent users from seeing uploaded files of others in the Media list view.
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
}
