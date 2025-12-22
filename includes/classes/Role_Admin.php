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

  protected const TYPE_TO_PLURAL = array(
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
    add_action( 'admin_init', array( self::class, 'initialize_roles' ) );

    if ( ! current_user_can( 'manage_options' ) ) {
      self::add_admin_restrictions();
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

  public static function initialize_roles( $force = false ) : void {
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

  public static function add_admin_restrictions() : void {
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

    // === MODERATE_COMMENTS =====================================================

    if ( ! current_user_can( 'moderate_comments' ) )  {
      add_action( 'admin_menu', [ self::class, 'remove_comments_menu_page' ] );
      add_action( 'current_screen', [ self::class, 'restrict_comment_screens' ] );
      add_filter( 'manage_posts_columns', [ self::class, 'remove_comments_column' ] );
      add_filter( 'manage_pages_columns', [ self::class, 'remove_comments_column' ] );
    }

    if ( current_user_can( 'moderate_comments' ) && current_user_can( 'fcn_only_moderate_comments' ) ) {
      add_filter( 'user_has_cap', [ self::class, 'edit_only_comments' ], 10, 3 );
      add_action( 'admin_menu', [ self::class, 'remove_post_menu_page' ], 99 );
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
      add_filter( 'manage_users_columns', [ self::class, 'hide_users_columns' ] );
      add_filter( 'comment_row_actions', [ self::class, 'remove_comment_quick_edit' ] );
      add_action( 'admin_enqueue_scripts', [ self::class, 'hide_private_comment_data' ], 20 );
    }

    // === FCN_REDUCED_PROFILE ===================================================

    if ( current_user_can( 'fcn_reduced_profile' ) ) {
      add_action( 'admin_head', [ self::class, 'remove_profile_blocks' ] );
      remove_action( 'admin_color_scheme_picker', 'admin_color_scheme_picker' );
    }

    // === FCN_MAKE_STICKY =======================================================

    if ( ! current_user_can( 'fcn_make_sticky' ) ) {
      add_action( 'post_stuck', [ self::class, 'prevent_post_sticky' ] );
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

  public static function prevent_publish_date_update( $data, $postarr ) : array {
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

  public static function prevent_permalink_edit( $data, $postarr ) : array {
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
   * Remove restricted Gutenberg blocks content on save.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $data  Array of slashed, sanitized, and processed post data.
   *
   * @return array Modified post data.
   */

  public static function remove_restricted_block_content( $data ) : array {
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
   * Prevent making posts sticky.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param int $post_id The post ID.
   */

  public static function prevent_post_sticky( $post_id ) : void {
    unstick_post( $post_id );

    remove_action( 'post_stuck', [ self::class, 'prevent_post_sticky' ] );
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
   * Remove email and name columns from user table.
   *
   * @since 4.7.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param array $column_headers  Columns to show in the user table.
   *
   * @return array Reduced columns.
   */

  public static function hide_users_columns( $column_headers ) : array {
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

  public static function remove_comment_quick_edit( $actions ) : array {
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

  public static function prevent_deleting_others_attachments( $caps, $cap, $user_id, $args ) : array {
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

  public static function prevent_editing_others_attachments( $caps, $cap, $user_id, $args ) : array {
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
   * Prevent users from seeing attachments uploaded by others.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param \WP_Query $query  The queried attachments.
   */

  public static function limit_media_ajax_query_attachments( $query ) : void {
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

  public static function limit_media_list_view( $query ) : void {
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
   * Limit admin post list-table to only include own posts.
   *
   * @since 5.6.0
   * @since 5.33.2 - Moved into Role class.
   *
   * @param \WP_Query $query The WP_Query instance (passed by reference).
   */

  public static function limit_posts_to_author( $query ) : void {
    global $pagenow;

    if ( ! is_admin() || ! $query->is_main_query() || $pagenow !== 'edit.php' ) {
      return;
    }

    $post_type = (string) $query->get( 'post_type' );

    if ( ! isset( self::TYPE_TO_PLURAL[ $post_type ] ) ) {
      return;
    }

    if ( ! current_user_can( 'edit_others_' . self::TYPE_TO_PLURAL[ $post_type ] ) ) {
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

    if ( ! isset( self::TYPE_TO_PLURAL[ $type ] ) ) {
      return $counts;
    }

    if ( current_user_can( 'edit_others_' . self::TYPE_TO_PLURAL[ $type ] ) ) {
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

  public static function strip_shortcodes_on_save( $data ) : array {
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

  public static function exempt_shortcodes_from_removal( $tags_to_remove ) : array {
    $exempt = ['fictioneer_fa', 'fcnt'];

    foreach ( $exempt as $tag ) {
      if ( ( $key = array_search( $tag, $tags_to_remove, true ) ) !== false ) {
        unset( $tags_to_remove[ $key ] );
      }
    }

    return $tags_to_remove;
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

  public static function prevent_parent_and_order_update( $data ) : array {
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

  public static function disallow_page_template_select( $templates ) : array {
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

  public static function prevent_page_template_update( $check, $object_id, $meta_key, $meta_value ) {
    if ( $meta_key !== '_wp_page_template' ) {
      return $check;
    }

    if ( isset( FICTIONEER_ALLOWED_PAGE_TEMPLATES[ (string) $meta_value ] ) ) {
      return $check;
    }

    return false;
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

  public static function remove_comments_column( $columns ) : array {
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

  public static function disallow_edit_comment( $caps, $cap, $user_id ) : array {
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

  public static function edit_only_comments( $all_caps, $caps, $args ) : array {
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

  public static function block_admin_only_screens( $screen ) : void {
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
}
