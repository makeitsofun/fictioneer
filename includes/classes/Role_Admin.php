<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Role_Admin {
  public const BASE_CAPABILITIES = array(
    'fcn_read_others_files',
    'fcn_edit_others_files',
    'fcn_delete_others_files',
    'fcn_select_page_template',
    'fcn_admin_panel_access',
    'fcn_adminbar_access',
    'fcn_dashboard_access',
    'fcn_privacy_clearance',
    'fcn_shortcodes',
    'fcn_simple_comment_html',
    'fcn_custom_page_header',
    'fcn_custom_page_css',
    'fcn_custom_epub_css',
    'fcn_custom_epub_upload',
    'fcn_seo_meta',
    'fcn_make_sticky',
    'fcn_show_badge',
    'fcn_edit_permalink',
    'fcn_all_blocks',
    'fcn_story_pages',
    'fcn_edit_date',
    'fcn_assign_patreon_tiers',
    'fcn_moderate_post_comments',
    'fcn_ignore_post_passwords',
    'fcn_ignore_page_passwords',
    'fcn_ignore_fcn_story_passwords',
    'fcn_ignore_fcn_chapter_passwords',
    'fcn_ignore_fcn_collection_passwords',
    'fcn_unlock_posts',
    'fcn_expire_passwords',
    'fcn_crosspost',
    'fcn_status_override',
    'fcn_add_alerts',
  );

  public const TAXONOMY_CAPABILITIES = array(
    // Categories
    'manage_categories',
    'edit_categories',
    'delete_categories',
    'assign_categories',
    // Tags
    'manage_post_tags',
    'edit_post_tags',
    'delete_post_tags',
    'assign_post_tags',
    // Genres
    'manage_fcn_genres',
    'edit_fcn_genres',
    'delete_fcn_genres',
    'assign_fcn_genres',
    // Fandoms
    'manage_fcn_fandoms',
    'edit_fcn_fandoms',
    'delete_fcn_fandoms',
    'assign_fcn_fandoms',
    // Characters
    'manage_fcn_characters',
    'edit_fcn_characters',
    'delete_fcn_characters',
    'assign_fcn_characters',
    // Warnings
    'manage_fcn_content_warnings',
    'edit_fcn_content_warnings',
    'delete_fcn_content_warnings',
    'assign_fcn_content_warnings',
  );

  /**
   * Initialize theme roles and capabilities.
   *
   * @since 5.33.2
   */

  public static function initialize() : void {
    add_action( 'admin_init', array( self::class, 'initialize_roles' ) );

    if ( ! current_user_can( 'manage_options' ) ) {
      self::add_restrictions();
    }
  }

  /**
   * Initialize user roles if not already done.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param bool $force  Optional. Whether to force initialization.
   */

  public static function initialize_roles( ?bool $force = false ) : void {
    $administrator = get_role( 'administrator' );

    // If this capability is missing, the roles have not yet been initialized.
    if ( $force || ( $administrator && ! isset( $administrator->capabilities['fcn_edit_date'] ) ) ) {
      self::setup_roles();

      $administrator = get_role( 'administrator' );
    }

    // If this capability is missing, the roles need to be updated.
    if ( $administrator && ! isset( $administrator->capabilities['fcn_add_alerts'] ) ) {
      $administrator->add_cap( 'fcn_custom_page_header' );
      $administrator->add_cap( 'fcn_custom_epub_upload' );
      $administrator->add_cap( 'fcn_unlock_posts' );
      $administrator->add_cap( 'fcn_expire_passwords' );
      $administrator->add_cap( 'fcn_crosspost' );
      $administrator->add_cap( 'fcn_status_override' );
      $administrator->add_cap( 'fcn_add_alerts' );

      if ( $editor = get_role( 'editor' ) ) {
        $editor->add_cap( 'fcn_custom_page_header' );
        $editor->add_cap( 'fcn_custom_epub_upload' );
      }

      if ( $moderator = get_role( 'fcn_moderator' ) ) {
        $moderator->add_cap( 'fcn_only_moderate_comments' );
        $moderator->add_cap( 'fcn_custom_epub_upload' );
      }

      if ( $author = get_role( 'author' ) ) {
        $author->add_cap( 'fcn_custom_epub_upload' );
      }
    }
  }

  /**
   * Build user roles with custom capabilities.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   */

  public static function setup_roles() : void {
    // Capabilities
    $all = array_merge(
      self::BASE_CAPABILITIES,
      self::TAXONOMY_CAPABILITIES,
      FICTIONEER_STORY_CAPABILITIES,
      FICTIONEER_CHAPTER_CAPABILITIES,
      FICTIONEER_COLLECTION_CAPABILITIES,
      FICTIONEER_RECOMMENDATION_CAPABILITIES
    );

    // === Administrator ========================================================

    if ( $administrator = get_role( 'administrator' ) ) {
      $administrator->remove_cap( 'fcn_only_moderate_comments' );
      $administrator->remove_cap( 'fcn_reduced_profile' );
      $administrator->remove_cap( 'fcn_allow_self_delete' );
      $administrator->remove_cap( 'fcn_upload_limit' );
      $administrator->remove_cap( 'fcn_upload_restrictions' );

      foreach ( $all as $cap ) {
        $administrator->add_cap( $cap );
      }
    }

    // === Editor ==============================================================

    if ( $editor = get_role( 'editor' ) ) {
      $editor_caps = array_merge(
        array(
          // Base
          'fcn_read_others_files',
          'fcn_edit_others_files',
          'fcn_delete_others_files',
          'fcn_admin_panel_access',
          'fcn_adminbar_access',
          'fcn_dashboard_access',
          'fcn_seo_meta',
          'fcn_make_sticky',
          'fcn_edit_permalink',
          'fcn_all_blocks',
          'fcn_story_pages',
          'fcn_edit_date',
          'fcn_custom_page_header',
          'fcn_custom_epub_upload',
          // Legacy restore
          'moderate_comments',
          'edit_comment',
          'edit_pages',
          'delete_pages',
          'delete_published_pages',
          'delete_published_posts',
          'delete_others_pages',
          'delete_others_posts',
          'publish_pages',
          'publish_posts',
          'manage_categories',
          'unfiltered_html',
          'manage_links',
        ),
        self::TAXONOMY_CAPABILITIES,
        FICTIONEER_STORY_CAPABILITIES, // Defined in custom post type setup
        FICTIONEER_CHAPTER_CAPABILITIES, // Defined in custom post type setup
        FICTIONEER_COLLECTION_CAPABILITIES, // Defined in custom post type setup
        FICTIONEER_RECOMMENDATION_CAPABILITIES // Defined in custom post type setup
      );

      foreach ( $editor_caps as $cap ) {
        $editor->add_cap( $cap );
      }
    }

    // === Author ==============================================================

    if ( $author = get_role( 'author' ) ) {
      $author_caps = array(
        // Base
        'fcn_admin_panel_access',
        'fcn_adminbar_access',
        'fcn_allow_self_delete',
        'fcn_upload_limit',
        'fcn_upload_restrictions',
        'fcn_story_pages',
        'fcn_custom_epub_upload',
        // Stories
        'read_fcn_story',
        'edit_fcn_stories',
        'publish_fcn_stories',
        'delete_fcn_stories',
        'delete_published_fcn_stories',
        'edit_published_fcn_stories',
        // Chapters
        'read_fcn_chapter',
        'edit_fcn_chapters',
        'publish_fcn_chapters',
        'delete_fcn_chapters',
        'delete_published_fcn_chapters',
        'edit_published_fcn_chapters',
        // Collections
        'read_fcn_collection',
        'edit_fcn_collections',
        'publish_fcn_collections',
        'delete_fcn_collections',
        'delete_published_fcn_collections',
        'edit_published_fcn_collections',
        // Recommendations
        'read_fcn_recommendation',
        'edit_fcn_recommendations',
        'publish_fcn_recommendations',
        'delete_fcn_recommendations',
        'delete_published_fcn_recommendations',
        'edit_published_fcn_recommendations',
        // Taxonomies
        'manage_categories',
        'manage_post_tags',
        'manage_fcn_genres',
        'manage_fcn_fandoms',
        'manage_fcn_characters',
        'manage_fcn_content_warnings',
        'assign_categories',
        'assign_post_tags',
        'assign_fcn_genres',
        'assign_fcn_fandoms',
        'assign_fcn_characters',
        'assign_fcn_content_warnings',
      );

      $author->remove_cap( 'fcn_reduced_profile' );

      foreach ( $author_caps as $cap ) {
        $author->add_cap( $cap );
      }
    }

    // === Contributor =========================================================

    if ( $contributor = get_role( 'contributor' ) ) {
      $contributor_caps = array(
        // Base
        'fcn_admin_panel_access',
        'fcn_adminbar_access',
        'fcn_allow_self_delete',
        'fcn_upload_limit',
        'fcn_upload_restrictions',
        'fcn_story_pages',
        // Stories
        'read_fcn_story',
        'edit_fcn_stories',
        'delete_fcn_stories',
        'edit_published_fcn_stories',
        // Chapters
        'read_fcn_chapter',
        'edit_fcn_chapters',
        'delete_fcn_chapters',
        'edit_published_fcn_chapters',
        // Collections
        'read_fcn_collection',
        'edit_fcn_collections',
        'delete_fcn_collections',
        'edit_published_fcn_collections',
        // Recommendations
        'read_fcn_recommendation',
        'edit_fcn_recommendations',
        'delete_fcn_recommendations',
        'edit_published_fcn_recommendations',
        // Taxonomies
        'manage_categories',
        'manage_post_tags',
        'manage_fcn_genres',
        'manage_fcn_fandoms',
        'manage_fcn_characters',
        'manage_fcn_content_warnings',
        'assign_categories',
        'assign_post_tags',
        'assign_fcn_genres',
        'assign_fcn_fandoms',
        'assign_fcn_characters',
        'assign_fcn_content_warnings',
      );

      $contributor->remove_cap( 'fcn_reduced_profile' );

      foreach ( $contributor_caps as $cap ) {
        $contributor->add_cap( $cap );
      }
    }

    // === Moderator ============================================================

    self::add_moderator_role();

    // === Subscriber ===========================================================

    if ( $subscriber = get_role( 'subscriber' ) ) {
      $subscriber_caps = array(
        // Base
        'fcn_admin_panel_access',
        'fcn_reduced_profile',
        'fcn_allow_self_delete',
        'fcn_upload_limit',
        'fcn_upload_restrictions',
        // Stories
        'read_fcn_story',
        // Chapters
        'read_fcn_chapter',
        // Collections
        'read_fcn_collection',
        // Recommendations
        'read_fcn_recommendation',
      );

      foreach ( $subscriber_caps as $cap ) {
        $subscriber->add_cap( $cap );
      }
    }
  }

  /**
   * Add/Update custom moderator role.
   *
   * @since 5.0.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @return \WP_Role|\WP_Error|null
   */

  public static function add_moderator_role() {
    $moderator = get_role( 'fcn_moderator' );

    $caps = array(
      // Base
      'read' => true,
      'edit_posts' => true,
      'edit_others_posts' => true,
      'edit_published_posts' => true,
      'moderate_comments' => true,
      'edit_comment' => true,
      'delete_posts' => true,
      'delete_others_posts' => true,
      'fcn_admin_panel_access' => true,
      'fcn_adminbar_access' => true,
      'fcn_only_moderate_comments' => true,
      'fcn_upload_limit' => true,
      'fcn_upload_restrictions' => true,
      'fcn_show_badge' => true,
      'fcn_story_pages' => true,
      'fcn_custom_epub_upload' => true,
      // Stories
      'read_fcn_story' => true,
      'edit_fcn_stories' => true,
      'publish_fcn_stories' => true,
      'delete_fcn_stories' => true,
      'delete_published_fcn_stories' => true,
      'edit_published_fcn_stories' => true,
      'edit_others_fcn_stories' => true,
      // Chapters
      'read_fcn_chapter' => true,
      'edit_fcn_chapters' => true,
      'publish_fcn_chapters' => true,
      'delete_fcn_chapters' => true,
      'delete_published_fcn_chapters' => true,
      'edit_published_fcn_chapters' => true,
      'edit_others_fcn_chapters' => true,
      // Collections
      'read_fcn_collection' => true,
      'edit_fcn_collections' => true,
      'publish_fcn_collections' => true,
      'delete_fcn_collections' => true,
      'delete_published_fcn_collections' => true,
      'edit_published_fcn_collections' => true,
      'edit_others_fcn_collections' => true,
      // Recommendations
      'read_fcn_recommendation' => true,
      'edit_fcn_recommendations' => true,
      'publish_fcn_recommendations' => true,
      'delete_fcn_recommendations' => true,
      'delete_published_fcn_recommendations' => true,
      'edit_published_fcn_recommendations' => true,
      'edit_others_fcn_recommendations' => true,
      // Taxonomies
      'manage_categories' => true,
      'manage_post_tags' => true,
      'manage_fcn_genres' => true,
      'manage_fcn_fandoms' => true,
      'manage_fcn_characters' => true,
      'manage_fcn_content_warnings' => true,
      'assign_categories' => true,
      'assign_post_tags' => true,
      'assign_fcn_genres' => true,
      'assign_fcn_fandoms' => true,
      'assign_fcn_characters' => true,
      'assign_fcn_content_warnings' => true
    );

    if ( $moderator ) {
      foreach ( array_keys( $caps ) as $cap ) {
        $moderator->add_cap( $cap );
      }

      return null;
    }

    return add_role(
      'fcn_moderator',
      __( 'Moderator', 'fictioneer' ),
      $caps
    );
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

    // === FCN_ADMIN_PANEL_ACCESS ================================================

    add_action( 'admin_init', [ self::class, 'restrict_admin_panel' ] );

    // === FCN_DASHBOARD_ACCESS ==================================================

    if ( current_user_can( 'fcn_admin_panel_access' ) && ! current_user_can( 'fcn_dashboard_access' ) ) {
      add_action( 'wp_dashboard_setup', [ self::class, 'remove_dashboard_widgets' ] );
      add_action( 'admin_menu', [ self::class, 'remove_dashboard_menu' ] );
      add_action( 'admin_init', [ self::class, 'skip_dashboard' ] );
    }

    // === FCN_SELECT_PAGE_TEMPLATE ==============================================

    if ( ! current_user_can( 'fcn_select_page_template' ) ) {
      add_filter( 'update_post_metadata', [ self::class, 'prevent_page_template_update' ], 1, 4 );
      add_filter( 'theme_templates', [ self::class, 'disallow_page_template_select' ], 1 );
      add_filter( 'wp_insert_post_data', [ self::class, 'prevent_parent_and_order_update' ], 1 );
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
}
