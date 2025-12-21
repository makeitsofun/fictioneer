<?php

use Fictioneer\Utils;

// =============================================================================
// SETUP CAPABILITIES
// =============================================================================

define(
  'FICTIONEER_BASE_CAPABILITIES',
  array(
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
    'fcn_add_alerts'
  )
);

define(
  'FICTIONEER_TAXONOMY_CAPABILITIES',
  array(
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
    'assign_fcn_content_warnings'
  )
);

/**
 * Initialize user roles if not already done.
 *
 * @since 5.6.0
 *
 * @param boolean $force  Optional. Whether to force initialization.
 */

function fictioneer_initialize_roles( $force = false ) {
  // Only do this once...
  $administrator = get_role( 'administrator' );

  // If this capability is missing, the roles have not yet been initialized
  if (
    ( $administrator && ! in_array( 'fcn_edit_date', array_keys( $administrator->capabilities ) ) ) ||
    $force
  ) {
    fictioneer_setup_roles();
  }

  // If this capability is missing, the roles need to be updated
  if ( $administrator && ! in_array( 'fcn_add_alerts', array_keys( $administrator->capabilities ) ) ) {
    get_role( 'administrator' )->add_cap( 'fcn_custom_page_header' );
    get_role( 'administrator' )->add_cap( 'fcn_custom_epub_upload' );
    get_role( 'administrator' )->add_cap( 'fcn_unlock_posts' );
    get_role( 'administrator' )->add_cap( 'fcn_expire_passwords' );
    get_role( 'administrator' )->add_cap( 'fcn_crosspost' );
    get_role( 'administrator' )->add_cap( 'fcn_status_override' );
    get_role( 'administrator' )->add_cap( 'fcn_add_alerts' );

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
add_action( 'admin_init', 'fictioneer_initialize_roles' );

/**
 * Build user roles with custom capabilities.
 *
 * @since 5.6.0
 */

function fictioneer_setup_roles() {
  // Capabilities
  $all = array_merge(
    FICTIONEER_BASE_CAPABILITIES,
    FICTIONEER_TAXONOMY_CAPABILITIES,
    FICTIONEER_STORY_CAPABILITIES,
    FICTIONEER_CHAPTER_CAPABILITIES,
    FICTIONEER_COLLECTION_CAPABILITIES,
    FICTIONEER_RECOMMENDATION_CAPABILITIES
  );

  // Administrator
  $administrator = get_role( 'administrator' );

  $administrator->remove_cap( 'fcn_only_moderate_comments' );
  $administrator->remove_cap( 'fcn_reduced_profile' );
  $administrator->remove_cap( 'fcn_allow_self_delete' );
  $administrator->remove_cap( 'fcn_upload_limit' );
  $administrator->remove_cap( 'fcn_upload_restrictions' );

  foreach ( $all as $cap ) {
    $administrator->add_cap( $cap );
  }

  // Editor
  $editor = get_role( 'editor' );
  $editor_caps = array_merge(
    // Base
    array(
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
      'moderate_comments',         // Legacy restore
      'edit_comment',              // Legacy restore
      'edit_pages',                // Legacy restore
      'delete_pages',              // Legacy restore
      'delete_published_pages',    // Legacy restore
      'delete_published_posts',    // Legacy restore
      'delete_others_pages',       // Legacy restore
      'delete_others_posts',       // Legacy restore
      'publish_pages',             // Legacy restore
      'publish_posts',             // Legacy restore
      'manage_categories',         // Legacy restore
      'unfiltered_html',           // Legacy restore
      'manage_links',              // Legacy restore
    ),
    FICTIONEER_TAXONOMY_CAPABILITIES,
    FICTIONEER_STORY_CAPABILITIES,
    FICTIONEER_CHAPTER_CAPABILITIES,
    FICTIONEER_COLLECTION_CAPABILITIES,
    FICTIONEER_RECOMMENDATION_CAPABILITIES
  );

  foreach ( $editor_caps as $cap ) {
    $editor->add_cap( $cap );
  }

  // Author
  $author = get_role( 'author' );
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
    'assign_fcn_content_warnings'
  );

  $author->remove_cap( 'fcn_reduced_profile' );

  foreach ( $author_caps as $cap ) {
    $author->add_cap( $cap );
  }

  // Contributor
  $contributor = get_role( 'contributor' );
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
    'assign_fcn_content_warnings'
  );

  $contributor->remove_cap( 'fcn_reduced_profile' );

  foreach ( $contributor_caps as $cap ) {
    $contributor->add_cap( $cap );
  }

  // Moderator
  fictioneer_add_moderator_role();

  // Subscriber
  $subscriber = get_role( 'subscriber' );
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
    'read_fcn_recommendation'
  );

  foreach ( $subscriber_caps as $cap ) {
    $subscriber->add_cap( $cap );
  }
}

/**
 * Add custom moderator role
 *
 * @since 5.0.0
 */

function fictioneer_add_moderator_role() {
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
    'manage_fcn_content_warnings',
    'assign_categories' => true,
    'assign_post_tags' => true,
    'assign_fcn_genres' => true,
    'assign_fcn_fandoms' => true,
    'assign_fcn_characters' => true,
    'assign_fcn_content_warnings' => true
  );

  if ( $moderator ) {
    $caps = array_keys( $caps );

    foreach ( $caps as $cap ) {
      $moderator->add_cap( $cap );
    }

    // Already exists
    return null;
  }

  // Add
  return add_role(
    'fcn_moderator',
    __( 'Moderator', 'fictioneer' ),
    $caps
  );
}

// =============================================================================
// APPLY CAPABILITY RULES
// =============================================================================

/**
 * Exceptions for post passwords
 *
 * @since 5.12.3
 * @since 5.15.0 - Add Patreon checks.
 * @since 5.16.0 - Add Patreon unlock checks and static variable cache.
 *
 * @param bool    $required  Whether the user needs to supply a password.
 * @param WP_Post $post      Post object.
 *
 * @return bool True or false.
 */

function fictioneer_bypass_password( $required, $post ) {
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
  remove_filter( 'post_password_required', 'fictioneer_bypass_password' );
  $required = post_password_required( $post );
  add_filter( 'post_password_required', 'fictioneer_bypass_password', 10, 2 );

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
add_filter( 'post_password_required', 'fictioneer_bypass_password', 10, 2 );

if ( ! current_user_can( 'manage_options' ) ) {
  \Fictioneer\Role::add_restrictions();
}

// No restriction can be applied to administrators
if ( ! current_user_can( 'manage_options' ) ) {
  // === FCN_EDIT_DATE =========================================================

  /**
   * Prevents the update of the publish date
   *
   * Note: The date can be edited until the post has been published once, so you
   * can still schedule a post or change the target date. But once it is published,
   * the date cannot be changed.
   *
   * @param array $data     An array of slashed, sanitized, and processed post data.
   * @param array $postarr  An array of sanitized (and slashed) but otherwise unmodified post data.
   *
   * @return array The potentially modified post data.
   */

  function fictioneer_prevent_publish_date_update( $data, $postarr ) {
    // New post?
    if ( empty( $postarr['ID'] ) || $postarr['post_status'] === 'auto-draft' || empty( $postarr['post_date_gmt'] ) ) {
      return $data;
    }

    // Setup
    $current_post_date_gmt = get_post_time( 'Y-m-d H:i:s', 1, $postarr['ID'] );

    // Remove from update array if already published once
    if ( $current_post_date_gmt !== $data['post_date_gmt'] ) {
      unset( $data['post_date'] );
      unset( $data['post_date_gmt'] );
    }

    // Continue filter
    return $data;
  }

  if ( ! current_user_can( 'fcn_edit_date' ) ) {
    add_filter( 'wp_insert_post_data', 'fictioneer_prevent_publish_date_update', 1, 2 );
  }

  // === FCN_CLASSIC_EDITOR ====================================================

  /**
   * Inject CSS for the classic editor
   *
   * @since 5.6.2
   */

  function fictioneer_classic_editor_css_restrictions() {
    global $pagenow;

    if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) {
      return;
    }

    echo '<style type="text/css">.selectit[for="ping_status"], #add-new-comment {display: none !important;}</style>';
  }

  /**
   * Inject JavaScript for the classic editor
   *
   * @since 5.6.2
   * @since 5.26.1 - Use wp_print_inline_script_tag().
   */

  function fictioneer_classic_editor_js_restrictions() {
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
   * Restrict metaboxes in the classic editor
   *
   * @since 5.6.2
   */

  function fictioneer_restrict_classic_metaboxes() {
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

  if ( current_user_can( 'fcn_classic_editor' ) ) {
    add_filter( 'use_block_editor_for_post_type', '__return_false' );
    add_action( 'add_meta_boxes', 'fictioneer_restrict_classic_metaboxes' );
    add_action( 'admin_head', 'fictioneer_classic_editor_css_restrictions' );
    add_action( 'admin_footer', 'fictioneer_classic_editor_js_restrictions' );
  }
}
