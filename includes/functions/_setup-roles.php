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



\Fictioneer\Role::initialize();
