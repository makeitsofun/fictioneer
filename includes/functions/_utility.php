<?php

use Fictioneer\Sanitizer;
use Fictioneer\Utils;
use Fictioneer\Story;
use Fictioneer\Utils_Admin;

// =============================================================================
// CHECK FOR ACTIVE PLUGINS
// =============================================================================

/**
 * Check whether a plugin is active for the entire network.
 *
 * @since 5.20.2
 * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/
 *
 * @param string $path  Relative path to the plugin.
 *
 * @return boolean True if the plugin is active, otherwise false.
 */

function fictioneer_is_network_plugin_active( $path ) {
  if ( ! is_multisite() ) {
    return false;
  }

  $plugins = get_site_option( 'active_sitewide_plugins' );

  if ( isset( $plugins[ $path ] ) ) {
    return true;
  }

  return false;
}

/**
 * Checks whether a plugin is active
 *
 * @since 4.0.0
 * @since 5.20.2 - Changed to copy of is_plugin_active().
 * @link https://developer.wordpress.org/reference/functions/is_plugin_active/
 *
 * @param string $path  Relative path to the plugin.
 *
 * @return boolean True if the plugin is active, otherwise false.
 */

function fictioneer_is_plugin_active( $path ) {
  return in_array( $path, (array) get_option( 'active_plugins', [] ), true ) || fictioneer_is_network_plugin_active( $path );
}

if ( ! function_exists( 'fictioneer_seo_plugin_active' ) ) {
  /**
   * Check whether any SEO plugin known to the theme is active.
   *
   * The theme's SEO features are inherently incompatible with SEO plugins, which
   * may be more sophisticated but do not understand the theme's content structure.
   * At all. Regardless, if the user want to use a SEO plugin, it's better to turn
   * off the theme's own SEO features. This function detects some of them.
   *
   * @since 4.0.0
   *
   * @return boolean True if a known SEO is active, otherwise false.
   */

  function fictioneer_seo_plugin_active() {
    return fictioneer_is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
      fictioneer_is_plugin_active( 'wordpress-seo-premium/wp-seo-premium.php' ) ||
      function_exists( 'aioseo' );
  }
}

// =============================================================================
// SHORTEN NUMBER WITH LETTER
// =============================================================================

if ( ! function_exists( 'fictioneer_shorten_number' ) ) {
  /**
   * Shorten a number to a fractional with a letter.
   *
   * @since 4.5.0
   * @since 5.27.4 - Localize numbers and made letters translatable.
   *
   * @param int $number     The number to be shortened.
   * @param int $precision  Precision of the fraction. Default 1.
   *
   * @return string The minified number string.
   */

  function fictioneer_shorten_number( $number, $precision = 1 ) {
    $number = (int) $number;

    // The letters are prefixed by a HAIR SPACE (&hairsp;)
    if ( $number < 1000 ) {
      return strval( $number );
    } else if ( $number < 1000000 ) {
      return sprintf(
        _x( '%s K', 'Abbreviation for thousand, separated by HAIR SPACE (&hairsp;).', 'fictioneer' ),
        number_format_i18n( $number / 1000, $precision )
      );
    } else if ( $number < 1000000000 ) {
      return sprintf(
        _x( '%s M', 'Abbreviation for million, separated by HAIR SPACE (&hairsp;).', 'fictioneer' ),
        number_format_i18n( $number / 1000000, $precision )
      );
    } else {
      return sprintf(
        _x( '%s B', 'Abbreviation for billion, separated by HAIR SPACE (&hairsp;).', 'fictioneer' ),
        number_format_i18n( $number / 1000000000, $precision )
      );
    }
  }
}

// =============================================================================
// VALIDATE ID
// =============================================================================

if ( ! function_exists( 'fictioneer_validate_id' ) ) {
  /**
   * Ensures an ID is a valid integer and positive; optionally checks whether the
   * associated post is of a certain types (or among an array of types)
   *
   * @since 4.7.0
   *
   * @param int          $id        The ID to validate.
   * @param string|array $for_type  Optional. The expected post type(s).
   *
   * @return int|boolean The validated ID or false if invalid.
   */

  function fictioneer_validate_id( $id, $for_type = [] ) {
    $safe_id = (int) $id;
    $types = is_array( $for_type ) ? $for_type : [ $for_type ];

    if ( empty( $safe_id ) || $safe_id < 0 ) {
      return false;
    }

    if ( ! empty( $for_type ) && ! in_array( get_post_type( $safe_id ), $types ) ) {
      return false;
    }

    return $safe_id;
  }
}

// =============================================================================
// VALIDATE NONCE PLAUSIBILITY
// =============================================================================

if ( ! function_exists( 'fictioneer_nonce_plausibility' ) ) {
  /**
   * Checks nonce to be plausible
   *
   * This helps to evaluate whether a nonce has been malformed, for example
   * through a dynamic update from a cache plugin not working properly.
   *
   * @since 5.9.4
   *
   * @param string $nonce  The nonce to check.
   *
   * @return boolean Whether the nonce is plausible.
   */

  function fictioneer_nonce_plausibility( $nonce ) {
    if ( preg_match( '/^[a-f0-9]{10}$/i', $nonce ) === 1 ) {
      return true;
    }

    return false;
  }
}

// =============================================================================
// KEY/VALUE STRING REPLACEMENT
// =============================================================================

if ( ! function_exists( 'fictioneer_replace_key_value' ) ) {
  /**
   * Replaces key/value pairs in a string
   *
   * @since 5.0.0
   *
   * @param string $text     Text that has key/value pairs to be replaced.
   * @param array  $args     The key/value pairs.
   * @param string $default  Optional. To be used if the text is empty.
   *                         Default is an empty string.
   *
   * @return string The modified text.
   */

  function fictioneer_replace_key_value( $text, $args, $default = '' ) {
    // Check if text exists
    if ( empty( $text ) ) {
      $text = $default;
    }

    // Check args
    $args = is_array( $args ) ? $args : [];

    // Filter args
    $args = array_filter( $args, 'is_scalar' );

    // Return modified text
    return trim( strtr( $text, $args ) );
  }
}

// =============================================================================
// CHECK USER CAPABILITIES
// =============================================================================

if ( ! function_exists( 'fictioneer_is_admin' ) ) {
  /**
   * Checks if a user is an administrator
   *
   * @since 5.0.0
   *
   * @param int $user_id  The user ID to check.
   *
   * @return boolean To be or not to be.
   */

  function fictioneer_is_admin( $user_id ) {
    // Abort conditions
    if ( ! $user_id ) {
      return false;
    }

    // Check capabilities
    $check = user_can( $user_id, 'administrator' );

    // Filter
    $check = apply_filters( 'fictioneer_filter_is_admin', $check, $user_id );

    // Return result
    return $check;
  }
}

if ( ! function_exists( 'fictioneer_is_author' ) ) {
  /**
   * Checks if a user is an author
   *
   * @since 5.0.0
   *
   * @param int $user_id  The user ID to check.
   *
   * @return boolean To be or not to be.
   */

  function fictioneer_is_author( $user_id ) {
    // Abort conditions
    if ( ! $user_id ) {
      return false;
    }

    // Check capabilities
    $check = user_can( $user_id, 'publish_posts' ) ||
      user_can( $user_id, 'publish_fcn_stories' ) ||
      user_can( $user_id, 'publish_fcn_chapters' ) ||
      user_can( $user_id, 'publish_fcn_collections' );

    // Filter
    $check = apply_filters( 'fictioneer_filter_is_author', $check, $user_id );

    // Return result
    return $check;
  }
}

if ( ! function_exists( 'fictioneer_is_moderator' ) ) {
  /**
   * Checks if a user is a moderator
   *
   * @since 5.0.0
   *
   * @param int $user_id  The user ID to check.
   *
   * @return boolean To be or not to be.
   */

  function fictioneer_is_moderator( $user_id ) {
    // Abort conditions
    if ( ! $user_id ) {
      return false;
    }

    // Check capabilities
    $check = user_can( $user_id, 'moderate_comments' );

    // Filter
    $check = apply_filters( 'fictioneer_filter_is_moderator', $check, $user_id );

    // Return result
    return $check;
  }
}

if ( ! function_exists( 'fictioneer_is_editor' ) ) {
  /**
   * Checks if a user is an editor
   *
   * @since 5.0.0
   *
   * @param int $user_id  The user ID to check.
   *
   * @return boolean To be or not to be.
   */

  function fictioneer_is_editor( $user_id ) {
    // Abort conditions
    if ( ! $user_id ) {
      return false;
    }

    // Check capabilities
    $check = user_can( $user_id, 'editor' ) || user_can( $user_id, 'administrator' );

    // Filter
    $check = apply_filters( 'fictioneer_filter_is_editor', $check, $user_id );

    // Return result
    return $check;
  }
}

// =============================================================================
// GET META FIELDS
// =============================================================================

if ( ! function_exists( 'fictioneer_get_story_chapter_ids' ) ) {
  /**
   * Wrapper for get_post_meta() to get story chapter IDs.
   *
   * @since 5.8.2
   *
   * @param int $post_id  Optional. The ID of the post the field belongs to.
   *                      Defaults to current post ID.
   *
   * @return array Array of chapter post IDs or an empty array.
   */

  function fictioneer_get_story_chapter_ids( $post_id = null ) {
    // Setup
    $chapter_ids = get_post_meta( $post_id ?? get_the_ID(), 'fictioneer_story_chapters', true );

    // Always return an array
    return is_array( $chapter_ids ) ? $chapter_ids : [];
  }
}

if ( ! function_exists( 'fictioneer_get_word_count' ) ) {
  /**
   * Wrapper for get_post_meta() to get word count.
   *
   * @since 5.8.2
   * @since 5.9.4 - Add logic for optional multiplier.
   * @since 5.22.3 - Moved multiplier to fictioneer_multiply_word_count()
   * @since 5.23.1 - Added filter and additional sanitization.
   * @since 5.27.4 - Added $word_count parameter.
   *
   * @param int|null $post_id     Optional. The ID of the post the field belongs to.
   *                              Defaults to current post ID.
   * @param int|null $word_count  Optional. Prepared word count for custom SQL solutions.
   *
   * @return int The word count or 0.
   */

  function fictioneer_get_word_count( $post_id = null, $word_count = null ) {
    // Get word count
    $words = $word_count ?? get_post_meta( $post_id ?? get_the_ID(), '_word_count', true ) ?: 0;
    $words = max( 0, (int) $words );

    // Filter
    $words = apply_filters( 'fictioneer_filter_word_count', $words, $post_id );

    // Apply multiplier and return
    return fictioneer_multiply_word_count( $words );
  }
}

if ( ! function_exists( 'fictioneer_get_story_word_count' ) ) {
  /**
   * Return word count for the whole story.
   *
   * @since 5.22.3
   * @since 5.23.1 - Added filter and additional sanitization.
   * @since 5.27.4 - Added $word_count parameter.
   *
   * @param int|null $post_id     Optional. Post ID of the story.
   * @param int|null $word_count  Optional. Prepared word count for custom SQL solutions.
   *
   * @return int The word count or 0.
   */

  function fictioneer_get_story_word_count( $post_id = null, $word_count = null ) {
    // Get word count
    $words = $word_count ?? get_post_meta( $post_id, 'fictioneer_story_total_word_count', true ) ?: 0;
    $words = max( 0, (int) $words );

    // Filter
    $words = apply_filters( 'fictioneer_filter_story_word_count', $words, $post_id );

    // Apply multiplier and return
    return fictioneer_multiply_word_count( $words );
  }
}

if ( ! function_exists( 'fictioneer_multiply_word_count' ) ) {
  /**
   * Multiply word count with factor from options.
   *
   * @since 5.22.3
   *
   * @param int $words  Word count.
   *
   * @return int The updated word count.
   */

  function fictioneer_multiply_word_count( $words ) {
    // Setup
    $multiplier = floatval( get_option( 'fictioneer_word_count_multiplier', 1.0 ) );

    // Multiply
    if ( $multiplier !== 1.0 ) {
      $words = (int) ( $words * $multiplier );
    }

    // Always return an integer greater or equal 0
    return max( 0, $words );
  }
}

if ( ! function_exists( 'fictioneer_get_content_field' ) ) {
  /**
   * Wrapper for get_post_meta() with content filters applied.
   *
   * @since 5.0.0
   *
   * @param string $field    Name of the meta field to retrieve.
   * @param int    $post_id  Optional. The ID of the post the field belongs to.
   *                         Defaults to current post ID.
   *
   * @return string The single field value formatted as content.
   */

  function fictioneer_get_content_field( $field, $post_id = null ) {
    // Setup
    $content = get_post_meta( $post_id ?? get_the_ID(), $field, true );

    // Apply default filter functions from the_content (but nothing else)
    $content = do_blocks( $content );
    $content = wptexturize( $content );
    $content = convert_chars( $content );
    $content = wpautop( $content );
    $content = shortcode_unautop( $content );
    $content = prepend_attachment( $content );
    $content = wp_replace_insecure_home_url( $content );
    $content = wp_filter_content_tags( $content );
    $content = convert_smilies( $content );

    // Return formatted/filtered content
    return $content;
  }
}

if ( ! function_exists( 'fictioneer_get_icon_field' ) ) {
  /**
   * Wrapper for get_post_meta() to get Font Awesome icon class.
   *
   * @since 5.0.0
   * @since 5.26.0 - Add $icon parameter as override.
   *
   * @param string      $field    Name of the meta field to retrieve.
   * @param int|null    $post_id  Optional. The ID of the post the field belongs to.
   *                              Defaults to current post ID.
   * @param string|null $icon     Optional. Pre-retrieved icon string as override. Default null.
   *
   * @return string The Font Awesome class.
   */

  function fictioneer_get_icon_field( $field, $post_id = null, $icon = null ) {
    // Setup
    $icon = $icon ?? get_post_meta( $post_id ?? get_the_ID(), $field, true );
    $icon_object = json_decode( $icon ); // Check for ACF Font Awesome plugin

    // Empty?
    if ( $icon === '' ) {
      return FICTIONEER_DEFAULT_CHAPTER_ICON;
    }

    // Valid?
    if ( ! $icon_object && ( empty( $icon ) || strpos( $icon, 'fa-' ) !== 0 ) ) {
      return FICTIONEER_DEFAULT_CHAPTER_ICON;
    }

    if ( $icon_object && ( ! property_exists( $icon_object, 'style' ) || ! property_exists( $icon_object, 'id' ) ) ) {
      return FICTIONEER_DEFAULT_CHAPTER_ICON;
    }

    // Return
    if ( $icon_object && property_exists( $icon_object, 'style' ) && property_exists( $icon_object, 'id' ) ) {
      return 'fa-' . $icon_object->style . ' fa-' . $icon_object->id;
    } else {
      return esc_attr( $icon );
    }
  }
}

// =============================================================================
// GET COOKIE CONSENT
// =============================================================================

if ( ! function_exists( 'fictioneer_get_consent' ) && get_option( 'fictioneer_cookie_banner' ) ) {
  /**
   * Get cookie consent
   *
   * Checks the current user’s consent cookie for their preferences, either 'full'
   * or 'necessary' by default. Returns false if no consent cookie is set.
   *
   * @since 4.7.0
   *
   * @return boolean|string Either false or a string describing the level of consent.
   */

  function fictioneer_get_consent() {
    if ( ! isset( $_COOKIE['fcn_cookie_consent'] ) || $_COOKIE['fcn_cookie_consent'] === '' ) {
      return false;
    }

    return strval( $_COOKIE['fcn_cookie_consent'] );
  }
}

// =============================================================================
// SHOW LOGIN
// =============================================================================

/**
 * Checks whether the login should be rendered
 *
 * @since 5.18.1
 *
 * @return boolean True or false.
 */

function fictioneer_show_login() {
  $enabled = get_option( 'fictioneer_enable_oauth' ) || get_option( 'fictioneer_show_wp_login_link' );

  return ( $enabled && ! is_user_logged_in() ) || get_option( 'fictioneer_enable_public_cache_compatibility' );
}

// =============================================================================
// SHOW NON-PUBLIC CONTENT
// =============================================================================

/**
 * Wrapper for is_user_logged_in() with global public cache consideration
 *
 * If public caches are served to all users, including logged-in users, it is
 * necessary to render items that would normally be skipped for logged-out
 * users, such as the profile link. They are hidden via CSS, which works as
 * long as the 'logged-in' class is still set on the <body>.
 *
 * @since 5.0.0
 *
 * @return boolean True or false.
 */

function fictioneer_show_auth_content() {
  return is_user_logged_in() ||
    get_option( 'fictioneer_enable_public_cache_compatibility' ) ||
    get_option( 'fictioneer_enable_ajax_authentication' );
}

// =============================================================================
// FICTIONEER TRANSLATIONS
// =============================================================================

/**
 * Return selected translations.
 *
 * Adding theme options for all possible translations would be a pain,
 * so this is done with a function that can be filtered as needed. For
 * giving your site a personal touch.
 *
 * @since 5.0.0
 *
 * @param string  $key     Key for requested translation.
 * @param boolean $escape  Optional. Escape the string for safe use in
 *                         attributes. Default false.
 *
 * @return string The translation or an empty string if not found.
 */

function fcntr( $key, $escape = false ) {
  static $strings = null;

  // Define default translations
  if ( $strings === null ) {
    $strings = array(
      'account' => __( 'Account', 'fictioneer' ),
      'bookshelf' => __( 'Bookshelf', 'fictioneer' ),
      'admin' => _x( 'Admin', 'Caption for administrator badge label.', 'fictioneer' ),
      'anonymous_guest' => __( 'Anonymous Guest', 'fictioneer' ),
      'author' => _x( 'Author', 'Caption for author badge label.', 'fictioneer' ),
      'bbcodes_modal' => _x( 'BBCodes', 'Heading for BBCodes tutorial modal.', 'fictioneer' ),
      'blog' => _x( 'Blog', 'Blog page name, mainly used in breadcrumbs.', 'fictioneer' ),
      'bookmark' => __( 'Bookmark', 'fictioneer' ),
      'bookmarks' => __( 'Bookmarks', 'fictioneer' ),
      'warning_notes' => __( 'Warning Notes', 'fictioneer' ),
      'deleted_user' => __( 'Deleted User', 'fictioneer' ),
      'follow' => _x( 'Follow', 'Follow a story.', 'fictioneer' ),
      'follows' => _x( 'Follows', 'List of followed stories.', 'fictioneer' ),
      'forget' => _x( 'Forget', 'Forget story set to be read later.', 'fictioneer' ),
      'formatting' => _x( 'Formatting', 'Toggle for chapter formatting modal.', 'fictioneer' ),
      'formatting_modal' => _x( 'Formatting', 'Chapter formatting modal heading.', 'fictioneer' ),
      'frontpage' => _x( 'Home', 'Frontpage page name, mainly used in breadcrumbs.', 'fictioneer' ),
      'is_followed' => _x( 'Followed', 'Story is followed.', 'fictioneer' ),
      'is_read' => _x( 'Read', 'Story or chapter is marked as read.', 'fictioneer' ),
      'is_read_later' => _x( 'Read later', 'Story is marked to be read later.', 'fictioneer' ),
      'jump_to_comments' => __( 'Jump: Comments', 'fictioneer' ),
      'jump_to_bookmark' => __( 'Jump: Bookmark', 'fictioneer' ),
      'login' => __( 'Login', 'fictioneer' ),
      'login_modal' => _x( 'Login', 'Login modal heading.', 'fictioneer' ),
      'login_with' => _x( 'Log in with', 'OAuth 2.0 login option plus appended icon.', 'fictioneer' ),
      'logout' => __( 'Logout', 'fictioneer' ),
      'mark_read' => _x( 'Mark Read', 'Mark story as read.', 'fictioneer' ),
      'mark_unread' => _x( 'Mark Unread', 'Mark story as unread.', 'fictioneer' ),
      'moderator' => _x( 'Mod', 'Caption for moderator badge label', 'fictioneer' ),
      'next' => __( '<span class="on">Next</span><span class="off"><i class="fa-solid fa-caret-right"></i></span>', 'fictioneer' ),
      'no_bookmarks' => __( 'No bookmarks.', 'fictioneer' ),
      'password' => __( 'Password', 'fictioneer' ),
      'previous' => __( '<span class="off"><i class="fa-solid fa-caret-left"></i></span><span class="on">Previous</span>', 'fictioneer' ),
      'read_later' => _x( 'Read Later', 'Remember a story to be read later.', 'fictioneer' ),
      'read_more' => _x( 'Read More', 'Read more of a post.', 'fictioneer' ),
      'reminders' => _x( 'Reminders', 'List of stories to read later.', 'fictioneer' ),
      'site_settings' => __( 'Site Settings', 'fictioneer' ),
      'story_blog' => _x( 'Blog', 'Blog tab of the story.', 'fictioneer' ),
      'subscribe' => _x( 'Subscribe', 'Subscribe to a story.', 'fictioneer' ),
      'unassigned_group' => _x( 'Unassigned', 'Chapters not assigned to group.', 'fictioneer' ),
      'unfollow' => _x( 'Unfollow', 'Stop following a story.', 'fictioneer' ),
      'E' => _x( 'E', 'Age rating E for Everyone.', 'fictioneer' ),
      'T' => _x( 'T', 'Age rating T for Teen.', 'fictioneer' ),
      'M' => _x( 'M', 'Age rating M for Mature.', 'fictioneer' ),
      'A' => _x( 'A', 'Age rating A for Adult.', 'fictioneer' ),
      'Everyone' => _x( 'Everyone', 'Age rating Everyone.', 'fictioneer' ),
      'Teen' => _x( 'Teen', 'Age rating Teen.', 'fictioneer' ),
      'Mature' => _x( 'Mature', 'Age rating Mature.', 'fictioneer' ),
      'Adult' => _x( 'Adult', 'Age rating Adult.', 'fictioneer' ),
      'Completed' => _x( 'Completed', 'Completed story status.', 'fictioneer' ),
      'Ongoing' => _x( 'Ongoing', 'Ongoing story status', 'fictioneer' ),
      'Hiatus' => _x( 'Hiatus', 'Hiatus story status', 'fictioneer' ),
      'Oneshot' => _x( 'Oneshot', 'Oneshot story status', 'fictioneer' ),
      'Canceled' => _x( 'Canceled', 'Canceled story status', 'fictioneer' ),
      'comment_anchor' => _x( '<i class="fa-solid fa-link"></i>', 'Text or icon for paragraph anchor in comments.', 'fictioneer' ),
      'bbcode_b' => _x( '<code>[b]</code><strong>Bold</strong><code>[/b]</code> of you to assume I have a plan.', 'BBCode example.', 'fictioneer' ),
      'bbcode_i' => _x( 'Deathbringer, emphasis on <code>[i]</code><em>death</em><code>[/i]</code>.', 'BBCode example.', 'fictioneer' ),
      'bbcode_s' => _x( 'I’m totally <code>[s]</code><strike>crossed out</strike><code>[/s]</code> by this.', 'BBCode example.', 'fictioneer' ),
      'bbcode_li' => _x( '<ul><li class="comment-list-item">Listless I’m counting my <code>[li]</code>bullets<code>[/li]</code>.</li></ul>', 'BBCode example.', 'fictioneer' ),
      'bbcode_img' => _x( '<code>[img]</code>https://www.agine.this<code>[/img]</code> %s', 'BBCode example.', 'fictioneer' ),
      'bbcode_link' => _x( '<code>[link]</code><a href="http://topwebfiction.com/" target="_blank" class="link">http://topwebfiction.com</a><code>[/link]</code>.', 'BBCode example.', 'fictioneer' ),
      'bbcode_link_name' => _x( '<code>[link=https://www.n.ot]</code><a href="http://topwebfiction.com/" class="link">clickbait</a><code>[/link]</code>.', 'BBCode example.', 'fictioneer' ),
      'bbcode_quote' => _x( '<blockquote><code>[quote]</code>… me like my landlord!<code>[/quote]</code></blockquote>', 'BBCode example.', 'fictioneer' ),
      'bbcode_spoiler' => _x( '<code>[spoiler]</code><span class="spoiler">Spanish Inquisition!</span><code>[/spoiler]</code>', 'BBCode example.', 'fictioneer' ),
      'bbcode_ins' => _x( '<code>[ins]</code><ins>Insert</ins><code>[/ins]</code> more bad puns!', 'BBCode example.', 'fictioneer' ),
      'bbcode_del' => _x( '<code>[del]</code><del>Delete</del><code>[/del]</code> your browser history!', 'BBCode example.', 'fictioneer' ),
      'log_in_with' => _x( 'Enter your details or log in with:', 'Comment form login note.', 'fictioneer' ),
      'logged_in_as' => _x( '<span>Logged in as <strong><a href="%1$s">%2$s</a></strong>. <a class="logout-link" href="%3$s" data-action="click->fictioneer#logout">Log out?</a></span>', 'Comment form logged-in note.', 'fictioneer' ),
      'accept_privacy_policy' => _x( 'I accept the <b><a class="link" href="%s" target="_blank">privacy policy</a></b>.', 'Comment form privacy checkbox.', 'fictioneer' ),
      'save_in_cookie' => _x( 'Save in cookie for next time.', 'Comment form cookie checkbox.', 'fictioneer' ),
      'future_prefix' => _x( 'Scheduled:', 'Chapter list status prefix.', 'fictioneer' ),
      'trashed_prefix' => _x( 'Trashed:', 'Chapter list status prefix.', 'fictioneer' ),
      'private_prefix' => _x( 'Private:', 'Chapter list status prefix.', 'fictioneer' ),
      'free_patreon_tier' => _x( 'Follower (Free)', 'Free Patreon tier (follower).', 'fictioneer' ),
      'private_comment' => _x( 'Private Comment', 'Comment type translation.', 'fictioneer' ),
      'comment_comment' => _x( 'Public Comment', 'Comment type translation.', 'fictioneer' ),
      'approved_comment_status' => _x( 'Approved', 'Comment status translation.', 'fictioneer' ),
      'hold_comment_status' => _x( 'Hold', 'Comment status translation.', 'fictioneer' ),
      'unapproved_comment_status' => _x( 'Unapproved', 'Comment status translation.', 'fictioneer' ),
      'spam_comment_status' => _x( 'Spam', 'Comment status translation.', 'fictioneer' ),
      'trash_comment_status' => _x( 'Trash', 'Comment status translation.', 'fictioneer' ),
    );

    // Filter static translations
    $strings = apply_filters( 'fictioneer_filter_translations_static', $strings );

    if ( has_filter( 'fictioneer_filter_translations' ) ) {
      $strings = apply_filters( 'fictioneer_filter_translations', $strings ); // Legacy
    }
  }

  // Return requested translation if defined...
  if ( isset( $strings[ $key ] ) ) {
    return $escape ? esc_attr( $strings[ $key ] ) : $strings[ $key ];
  }

  // ... otherwise return empty string
  return '';
}

// =============================================================================
// CHECK WHETHER COMMENTING IS DISABLED
// =============================================================================

if ( ! function_exists( 'fictioneer_is_commenting_disabled' ) ) {
  /**
   * Check whether commenting is disabled
   *
   * Differs from comments_open() in the regard that it does not hide the whole
   * comment section but does not allow new comments to be posted.
   *
   * @since 5.0.0
   *
   * @param int|null $post_id  Post ID the comments are for. Defaults to current post ID.
   *
   * @return boolean True or false.
   */

  function fictioneer_is_commenting_disabled( $post_id = null ) {
    // Setup
    $post_id = $post_id ?? get_the_ID();

    // Return immediately if...
    if (
      get_option( 'fictioneer_disable_commenting' ) ||
      get_post_meta( $post_id, 'fictioneer_disable_commenting', true )
    ) {
      return true;
    }

    // Check parent story if chapter...
    if ( get_post_type( $post_id ) === 'fcn_chapter' ) {
      $story_id = fictioneer_get_chapter_story_id( $post_id );

      if ( $story_id ) {
        return get_post_meta( $story_id, 'fictioneer_disable_commenting', true ) == true;
      }
    }

    return false;
  }
}

// =============================================================================
// BBCODES
// =============================================================================

if ( ! function_exists( 'fictioneer_bbcodes' ) ) {
  /**
   * Interprets BBCodes into HTML
   *
   * Note: Spoilers do not work properly if wrapping multiple lines or other codes.
   *
   * @since 4.0.0
   * @link https://stackoverflow.com/a/17508056/17140970
   *
   * @param string $content  The content.
   *
   * @return string The content with interpreted BBCodes.
   */

  function fictioneer_bbcodes( $content ) {
    // Setup
    $img_search = '\s*https:[^\"\'|;<>\[\]]+?\.(?:png|jpg|jpeg|gif|webp|svg|avif|tiff).*?\s*';
    $url_search = '\s*(http|ftp|https):\/\/[\w-]+(\.[\w-]+)+([\w.,@?^=%&amp;:\/~+#-]*[\w@?^=%&amp;\/~+#-])?\s*';

    // Deal with some multi-line spoiler issues
    if ( preg_match_all( '/\[spoiler](.+?)\[\/spoiler]/is', $content, $spoilers, PREG_PATTERN_ORDER ) ) {
      foreach ( $spoilers[0] as $spoiler ) {
        $replace = str_replace( '<p></p>', ' ', $spoiler );
        $replace = preg_replace( '/\[quote](.+?)\[\/quote]/is', '<blockquote class="spoiler">$1</blockquote>', $replace );

        $content = str_replace( $spoiler, $replace, $content );
      }
    }

    // Possible patterns
    $patterns = array(
      '/\[spoiler]\[quote](.+?)\[\/quote]\[\/spoiler]/is',
      '/\[spoiler](.+?)\[\/spoiler]/i',
      '/\[spoiler](.+?)\[\/spoiler]/is',
      '/\[b](.+?)\[\/b]/i',
      '/\[i](.+?)\[\/i]/i',
      '/\[s](.+?)\[\/s]/i',
      '/\[quote](.+?)\[\/quote]/is',
      '/\[ins](.+?)\[\/ins]/is',
      '/\[del](.+?)\[\/del]/is',
      '/\[li](.+?)\[\/li]/i',
      "/\[link.*]\[img]\s*($img_search)\s*\[\/img]\[\/link]/i",
      "/\[img]\s*($img_search)\s*\[\/img]/i",
      "/\[link\]\s*($url_search)\s*\[\/link\]/i",
      "/\[link=\s*[\"']?\s*($url_search)\s*[\"']?\s*\](.+?)\[\/link\]/i",
      '/\[anchor]\s*([^\"\'|;<>\[\]]+?)\s*\[\/anchor]/i'
    );

    // HTML replacements
    $replacements = array(
      '<blockquote class="spoiler">$1</blockquote>',
      '<span class="spoiler">$1</span>',
      '<div class="spoiler">$1</div>',
      '<strong>$1</strong>',
      '<em>$1</em>',
      '<strike>$1</strike>',
      '<blockquote>$1</blockquote>',
      '<ins>$1</ins>',
      '<del>$1</del>',
      '<div class="comment-list-item">$1</div>',
      '<span class="comment-image-consent-wrapper"><button type="button" class="button _secondary consent-button" title="$1">' . _x( '<i class="fa-solid fa-image"></i> Show Image', 'Comment image consent wrapper button.', 'fictioneer' ) . '</button><a href="$1" class="comment-image-link" rel="noreferrer noopener nofollow" target="_blank"><img class="comment-image" data-src="$1"></a></span>',
      '<span class="comment-image-consent-wrapper"><button type="button" class="button _secondary consent-button" title="$1">' . _x( '<i class="fa-solid fa-image"></i> Show Image', 'Comment image consent wrapper button.', 'fictioneer' ) . '</button><img class="comment-image" data-src="$1"></span>',
      "<a href=\"$1\" rel=\"noreferrer noopener nofollow\">$1</a>",
      "<a href=\"$1\" rel=\"noreferrer noopener nofollow\">$5</a>",
      '<a href="#$1" rel="noreferrer noopener nofollow" data-block="start" class="comment-anchor">:anchor:</a>'
    );

    // Pattern replace
    $content = preg_replace( $patterns, $replacements, $content );

    // Icons
    $content = str_replace( ':anchor:', fcntr( 'comment_anchor' ), $content );

    return $content;
  }
}

// =============================================================================
// GET TAXONOMY NAMES
// =============================================================================

if ( ! function_exists( 'fictioneer_get_taxonomy_names' ) ) {
  /**
   * Get all taxonomies of a post
   *
   * @since 5.0.20
   *
   * @param int     $post_id  ID of the post to get the taxonomies of.
   * @param boolean $flatten  Whether to flatten the result. Default false.
   *
   * @return array Array with all taxonomies.
   */

  function fictioneer_get_taxonomy_names( $post_id, $flatten = false ) {
    // Setup
    $t = [];
    $t['tags'] = get_the_tags( $post_id );
    $t['fandoms'] = get_the_terms( $post_id, 'fcn_fandom' );
    $t['characters'] = get_the_terms( $post_id, 'fcn_character' );
    $t['warnings'] = get_the_terms( $post_id, 'fcn_content_warning' );
    $t['genres'] = get_the_terms( $post_id, 'fcn_genre' );

    // Validate
    foreach ( $t as $key => $tax ) {
      $t[ $key ] = is_array( $t[ $key ] ) ? $t[ $key ] : [];
    }

    // Extract
    foreach ( $t as $key => $tax ) {
      $t[ $key ] = array_map( function( $a ) { return $a->name; }, $t[ $key ] );
    }

    // Return flattened
    if ( $flatten ) {
      return array_merge( $t['tags'], $t['fandoms'], $t['characters'], $t['warnings'], $t['genres'] );
    }

    // Return without empty arrays
    return array_filter( $t, 'count' );
  }
}

// =============================================================================
// GET FONT COLORS
// =============================================================================

if ( ! function_exists( 'fictioneer_get_font_colors' ) ) {
  /**
   * Returns array of font color items
   *
   * @since 5.1.1
   *
   * @return array Font items (css and name).
   */

  function fictioneer_get_font_colors() {
    // Setup default font colors
    $colors = array(
      array( 'css' => 'var(--fg-tinted)', 'name' => _x( 'Tinted', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-500)', 'name' => _x( 'Baseline', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-600)', 'name' => _x( 'Low', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-700)', 'name' => _x( 'Lower', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-800)', 'name' => _x( 'Lowest', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-400)', 'name' => _x( 'High', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-300)', 'name' => _x( 'Higher', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => 'var(--fg-200)', 'name' => _x( 'Highest', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => '#fff', 'name' => _x( 'White', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => '#999', 'name' => _x( 'Gray', 'Chapter font color name.', 'fictioneer' ) ),
      array( 'css' => '#000', 'name' => _x( 'Black', 'Chapter font color name.', 'fictioneer' ) )
    );

    // Apply filters and return
    return apply_filters( 'fictioneer_filter_font_colors', $colors );
  }
}

// =============================================================================
// BUILD FRONTEND NOTICE
// =============================================================================

if ( ! function_exists( 'fictioneer_notice' ) ) {
  /**
   * Render or return a frontend notice element
   *
   * @since 5.2.5
   *
   * @param string $message   The notice to show.
   * @param string $type      Optional. The notice type. Default 'warning'.
   * @param bool   $display   Optional. Whether to render or return. Default true.
   *
   * @return void|string The build HTML or nothing if rendered.
   */

  function fictioneer_notice( $message, $type = 'warning', $display = true ) {
    $output = '<div class="notice _' . esc_attr( $type ) . '">';

    if ( $type === 'warning' ) {
      $output .= '<i class="fa-solid fa-triangle-exclamation"></i>';
    }

    $output .= "<div>{$message}</div></div>";

    if ( $display ) {
      echo $output;
    } else {
      return $output;
    }
  }
}

// =============================================================================
// MINIFY HTML
// =============================================================================

if ( ! function_exists( 'fictioneer_minify_html' ) ) {
  /**
   * Minifies a HTML string
   *
   * This is not safe for `<pre>` or `<code>` tags!
   *
   * @since 5.4.0
   *
   * @param string $html  The HTML string to be minified.
   *
   * @return string The minified HTML string.
   */

  function fictioneer_minify_html( $html ) {
    return preg_replace( '/\s+/', ' ', trim( $html ) );
  }
}

// =============================================================================
// GET AUTHOR IDS OF POST
// =============================================================================

if ( ! function_exists( 'fictioneer_get_post_author_ids' ) ) {
  /**
   * Returns array of author IDs for a post ID
   *
   * @since 5.4.8
   *
   * @param int $post_id  The post ID.
   *
   * @return array The author IDs.
   */

  function fictioneer_get_post_author_ids( $post_id ) {
    $author_ids = get_post_meta( $post_id, 'fictioneer_story_co_authors', true ) ?: [];
    $author_ids = is_array( $author_ids ) ? $author_ids : [];
    array_unshift( $author_ids, get_post_field( 'post_author', $post_id ) );

    return array_unique( $author_ids );
  }
}

// =============================================================================
// DELETE TRANSIENTS THAT INCLUDE A STRING
// =============================================================================

if ( ! function_exists( 'fictioneer_delete_transients_like' ) ) {
  /**
   * Delete Transients with a like key and return the count deleted
   *
   * Note: The fast variant of this function is not compatible with
   * external object caches such as Memcached, because without the
   * help of delete_transient(), it won't know about the change.
   *
   * @since 5.4.9
   *
   * @param string  $partial_key  String that is part of the key.
   * @param boolean $fast         Optional. Whether to delete with a single SQL query or
   *                              loop each Transient with delete_transient(), which can
   *                              trigger hooked actions (if any). Default false.
   *
   * @return int Count of deleted Transients.
   */

  function fictioneer_delete_transients_like( $partial_key, $fast = false ) {
    // Globals
    global $wpdb;

    // Setup
    $count = 0;

    // Fast?
    if ( $fast ) {
      // Prepare SQL
      $sql = $wpdb->prepare(
        "DELETE FROM $wpdb->options WHERE `option_name` LIKE %s OR `option_name` LIKE %s",
        "%_transient%{$partial_key}%",
        "%_transient_timeout%{$partial_key}%"
      );

      // Query
      $count = $wpdb->query( $sql );
    } else {
      // Prepare SQL
      $sql = $wpdb->prepare(
        "SELECT `option_name` AS `name` FROM $wpdb->options WHERE `option_name` LIKE %s",
        "_transient%{$partial_key}%"
      );

      // Query
      $transients = $wpdb->get_col( $sql );

      // Build full keys and delete
      foreach ( $transients as $transient ) {
        $key = str_replace( '_transient_', '', $transient );
        $count += delete_transient( $key ) ? 1 : 0;
      }
    }

    // Return count
    return $count;
  }
}

// =============================================================================
// GET OPTION PAGE LINK
// =============================================================================

if ( ! function_exists( 'fictioneer_get_assigned_page_link' ) ) {
  /**
   * Returns permalink for an assigned page or null
   *
   * @since 5.4.9
   *
   * @param string $option  The option name of the page assignment.
   *
   * @return string|null The permalink or null.
   */

  function fictioneer_get_assigned_page_link( $option ) {
    // Setup
    $page_id = get_option( $option );

    // Null if no page has been selected (null or -1)
    if ( empty( $page_id ) || $page_id < 0 ) {
      return null;
    }

    // Get permalink from options or post
    $link = get_option( "{$option}_link" );

    if ( empty( $link ) ) {
      $link = get_permalink( $page_id );
      update_option( "{$option}_link", $link, true ); // Save for next time
    }

    // Return
    return $link;
  }
}

// =============================================================================
// SAVE GUARD
// =============================================================================

if ( ! function_exists( 'fictioneer_multi_save_guard' ) ) {
  /**
   * Alias for fictioneer_save_guard()
   *
   * @since 5.30.0
   *
   * @param int $post_id  The ID of the updated post.
   *
   * @return boolean True if NOT allowed, false otherwise.
   */

  function fictioneer_multi_save_guard( $post_id ) {
    fictioneer_save_guard( $post_id );
  }
}

if ( ! function_exists( 'fictioneer_save_guard' ) ) {
  /**
   * Prevent unnecessary triggers of save hooks.
   *
   * @since 5.5.2
   * @since 5.30.0 - Refactored and renamed.
   *
   * @param int $post_id  The ID of the updated post.
   *
   * @return boolean True if NOT allowed, false otherwise.
   */

  function fictioneer_save_guard( $post_id ) {
    // Always allow trash action to pass
    if ( get_post_status( $post_id ) === 'trash' ) {
      return false;
    }

    // Block unnecessary triggers
    if (
      wp_is_post_autosave( $post_id ) ||
      wp_is_post_revision( $post_id ) ||
      get_post_status( $post_id ) === 'auto-draft'
    ) {
      return true;
    }

    // Optional: Block REST requests
    if (
      ( defined( 'REST_REQUEST' ) && REST_REQUEST &&
      get_option( 'fictioneer_disable_rest_save_actions' ) )
    ) {
      return true;
    }

    // Pass
    return false;
  }
}

// =============================================================================
// REDIRECT TO 404
// =============================================================================

if ( ! function_exists( 'fictioneer_redirect_to_404' ) ) {
  /**
   * Redirects the current request to the WordPress 404 page
   *
   * @since 5.6.0
   *
   * @global WP_Query $wp_query  The main WP_Query instance.
   */

  function fictioneer_redirect_to_404() {
    global $wp_query;

    // Remove scripts to avoid errors
    add_action( 'wp_print_scripts', function() {
      wp_dequeue_script( 'fictioneer-chapter-scripts' );
      wp_dequeue_script( 'fictioneer-suggestion-scripts' );
      wp_dequeue_script( 'fictioneer-tts-scripts' );
      wp_dequeue_script( 'fictioneer-story-scripts' );
    }, 99 );

    // Set query to 404
    $wp_query->set_404();
    status_header( 404 );
    nocache_headers();
    fictioneer_get_template_part( 404 );

    // Terminate
    exit();
  }
}

// =============================================================================
// PREVIEW ACCESS VERIFICATION
// =============================================================================

if ( ! function_exists( 'fictioneer_verify_unpublish_access' ) ) {
  /**
   * Verifies access to unpublished posts (not drafts)
   *
   * @since 5.6.0
   *
   * @return boolean True if access granted, false otherwise.
   */

  function fictioneer_verify_unpublish_access( $post_id ) {
    // Setup
    $post = get_post( $post_id );
    $authorized = false;

    // Check for allowed future chapters
    if (
      get_option( 'fictioneer_show_scheduled_chapters' ) &&
      $post->post_type === 'fcn_chapter' &&
      $post->post_status === 'future'
    ) {
      $authorized = true;
    }

    // Always let owner to pass
    if ( get_current_user_id() === absint( $post->post_author ) ) {
      $authorized = true;
    }

    // Always let administrators pass
    if ( current_user_can( 'manage_options' ) ) {
      $authorized = true;
    }

    // Check capability for post type
    if ( $post->post_status === 'private' ) {
      switch ( $post->post_type ) {
        case 'post':
          $authorized = current_user_can( 'edit_private_posts' );
          break;
        case 'page':
          $authorized = current_user_can( 'edit_private_pages' );
          break;
        case 'fcn_chapter':
          $authorized = current_user_can( 'read_private_fcn_chapters' );
          break;
        case 'fcn_story':
          $authorized = current_user_can( 'read_private_fcn_stories' );
          break;
        case 'fcn_recommendation':
          $authorized = current_user_can( 'read_private_fcn_recommendations' );
          break;
        case 'fcn_collection':
          $authorized = current_user_can( 'read_private_fcn_collections' );
          break;
        default:
          $authorized = current_user_can( 'edit_others_posts' );
      }
    }

    // Drafts are handled by WordPress
    return apply_filters( 'fictioneer_filter_verify_unpublish_access', $authorized, $post_id, $post );
  }
}

// =============================================================================
// TOGGLE ACTION TO SAVE/TRASH/UNTRASH/DELETE HOOKS WITH POST ID
// =============================================================================

/**
 * Toggle callback to save, trash, untrash, and delete hooks (1 argument).
 *
 * This helper saves some time/space adding or removing a callback action to
 * all four default post operations. But only with the first argument: post_id.
 *
 * @since 5.6.3
 *
 * @param callable $function  The callback function to be added.
 * @param bool     $add       Optional. Whether to add or remove. Default add.
 * @param int      $priority  Optional. Used to specify the order in which the
 *                            functions associated with a particular action are
 *                            executed. Default 10. Lower numbers correspond with
 *                            earlier execution, and functions with the same
 *                            priority are executed in the order in which they
 *                            were added to the action.
 */

function fictioneer_toggle_stud_actions( $function, $add = true, $priority = 10 ) {
  $hooks = ['save_post', 'trashed_post', 'delete_post', 'untrash_post'];

  foreach ( $hooks as $hook ) {
    $add ? add_action( $hook, $function, $priority ) : remove_action( $hook, $function, $priority );
  }
}

// =============================================================================
// CONVERT URL LIST TO ARRAY
// =============================================================================

/**
 * Turn line-break separated list into array of links
 *
 * @since 5.7.4
 *
 * @param string $list  The list of links
 *
 * @return array The array of links.
 */

function fictioneer_url_list_to_array( $list ) {
  // Already array?
  if ( is_array( $list ) ) {
    return $list;
  }

  // Catch falsy values
  if ( empty( $list ) ) {
    return [];
  }

  // Prepare URLs
  $urls = [];
  $lines = preg_split( '/\r\n|\r|\n/', $list );

  // Extract
  foreach ( $lines as $line ) {
    $tuple = explode( '|', $line );
    $tuple = array_map( 'trim', $tuple );

    $urls[] = array(
      'name' => wp_strip_all_tags( $tuple[0] ),
      'url' => Sanitizer::sanitize_url_https( $tuple[1] )
    );
  }

  // Return
  return $urls;
}

// =============================================================================
// RETURN NO FORMAT STRING
// =============================================================================

/**
 * Returns an unformatted replacement string
 *
 * @since 5.7.5
 *
 * @return string Just a simple '%s'.
 */

function fictioneer__return_no_format() {
  return '%s';
}

// =============================================================================
// RETURN PUBLISH STRING
// =============================================================================

/**
 * Helper to just return 'publish'.
 *
 * @since 4.28.0
 *
 * @return string Just a simple 'publish'.
 */

function fictioneer__return_publish_status( $param = null ) {
  return 'publish';
}

// =============================================================================
// TRUNCATE STRING
// =============================================================================

/**
 * Returns a truncated string without tags
 *
 * @since 5.9.0
 *
 * @param string      $string    The string to truncate.
 * @param int         $length    Maximum length in characters.
 * @param string|null $ellipsis  Optional. Truncation indicator suffix.
 *
 * @return string The truncated string without tags.
 */

function fictioneer_truncate( $string, $length, $ellipsis = null ) {
  // Setup
  $string = wp_strip_all_tags( $string ); // Prevent tags from being cut off
  $ellipsis = $ellipsis ?? FICTIONEER_TRUNCATION_ELLIPSIS;

  // Return truncated string
  if ( function_exists( 'mb_strimwidth' ) ) {
    return mb_strimwidth( $string, 0, $length, $ellipsis );
  } else {
    return strlen( $string ) > $length ? substr( $string, 0, $length ) . $ellipsis : $string;
  }
}

// =============================================================================
// COMPARE WORDPRESS VERSION
// =============================================================================

/**
 * Compare installed WordPress version against version string
 *
 * @since 5.12.2
 * @global wpdb $wp_version  Current WordPress version string.
 *
 * @param string $version   The version string to test against.
 * @param string $operator  Optional. How to compare. Default '>='.
 *
 * @return boolean True or false.
 */

function fictioneer_compare_wp_version( $version, $operator = '>=' ) {
  global $wp_version;

  return version_compare( $wp_version, $version, $operator );
}

// =============================================================================
// CSS LOADING PATTERN
// =============================================================================

if ( ! function_exists( 'fictioneer_get_async_css_loading_pattern' ) ) {
  /**
   * Returns the media attribute and loading strategy for stylesheets
   *
   * @since 5.12.2
   *
   * @return string Media attribute script for stylesheet links.
   */

  function fictioneer_get_async_css_loading_pattern() {
    if ( FICTIONEER_ENABLE_ASYNC_ONLOAD_PATTERN ) {
      return 'media="print" onload="this.media=\'all\'; this.onload=null;"';
    }

    return 'media="all"';
  }
}

// =============================================================================
// GENERATE PLACEHOLDER
// =============================================================================

if ( ! function_exists( 'fictioneer_generate_placeholder' ) ) {
  /**
   * Dummy implementation (currently uses a CSS background).
   *
   * @since 5.14.0
   *
   * @param array|null $args  Optional arguments to generate a placeholder.
   *
   * @return string The placeholder URL or data URI.
   */

  function fictioneer_generate_placeholder( $args = [] ) {
    return '';
  }
}

// =============================================================================
// FIND USER(S)
// =============================================================================

/**
 * Find (first) user by display name.
 *
 * @since 5.14.1
 *
 * @param string $display_name  The display name to search for.
 *
 * @return WP_User|null The first matching user or null if not found.
 */

function fictioneer_find_user_by_display_name( $display_name ) {
  // No choice but to query all because the display name is not unique
  $users = get_users(
    array(
      'search' => sanitize_user( $display_name ),
      'search_columns' => ['display_name'],
      'number' => 1
    )
  );

  // If found, return first
  if ( ! empty( $users ) ) {
    return $users[0];
  }

  // Not found
  return null;
}

// =============================================================================
// JOIN ARRAYS IN SPECIAL WAYS
// =============================================================================

if ( ! function_exists( 'fictioneer_get_human_readable_list' ) ) {
  /**
   * Join string in an array as human readable list.
   *
   * @since 5.15.0
   * @link https://gist.github.com/SleeplessByte/4514697
   *
   * @param array $array  Array of strings.
   *
   * @return string The human readable list.
   */

  function fictioneer_get_human_readable_list( $array ) {
    // Setup
    $comma = _x( ', ', 'Human readable list joining three or more items except the last two.', 'fictioneer' );
    $double = _x( ' or ', 'Human readable list joining two items.', 'fictioneer' );
    $final = _x( ', or ', 'Human readable list joining the last two of three or more items.', 'fictioneer' );

    // One or two items
    if ( count( $array ) < 3 ) {
      return implode( $double, $array );
    }

    // Three or more items
    array_splice( $array, -2, 2, implode( $final, array_slice( $array, -2, 2 ) ) );

    // Finish
    return implode( $comma , $array );
  }
}

// =============================================================================
// PATREON UTILITIES
// =============================================================================

/**
 * Get Patreon data for a post
 *
 * Note: Considers both the post meta and global settings.
 *
 * @since 5.15.0
 *
 * @param WP_Post|null $post  The post to check. Default to global $post.
 *
 * @return array|null Array with 'gated' (bool), 'gate_tiers' (array), 'gate_cents' (int),
 *                    and 'gate_lifetime_cents' (int). Null if the post could not be found.
 */

function fictioneer_get_post_patreon_data( $post = null ) {
  // Static cache
  static $cache = [];

  // Post?
  $post = $post ?? get_post();

  if ( ! $post ) {
    return null;
  }

  $post_id = $post->ID;

  // Check cache
  if ( isset( $cache[ $post_id ] ) ) {
    return $cache[ $post_id ];
  }

  // Setup
  $global_tiers = get_option( 'fictioneer_patreon_global_lock_tiers', [] ) ?: [];
  $global_amount_cents = get_option( 'fictioneer_patreon_global_lock_amount', 0 ) ?: 0;
  $global_lifetime_amount_cents = get_option( 'fictioneer_patreon_global_lock_lifetime_amount', 0 ) ?: 0;

  $post_tiers = get_post_meta( $post_id, 'fictioneer_patreon_lock_tiers', true );
  $post_tiers = is_array( $post_tiers ) ? $post_tiers : [];
  $post_amount_cents = absint( get_post_meta( $post_id, 'fictioneer_patreon_lock_amount', true ) );

  $parent_tiers = [];
  $parent_amount_cents = 0;

  if ( $post->post_type === 'fcn_chapter' && ! get_post_meta( $post_id, 'fictioneer_patreon_override', true ) ) {
    $parent_id = fictioneer_get_chapter_story_id( $post_id );

    if ( $parent_id && get_post_meta( $parent_id, 'fictioneer_patreon_inheritance', true ) ) {
      $parent_tiers = get_post_meta( $parent_id, 'fictioneer_patreon_lock_tiers', true );
      $parent_tiers = is_array( $parent_tiers ) ? $parent_tiers : [];
      $parent_amount_cents = absint( get_post_meta( $parent_id, 'fictioneer_patreon_lock_amount', true ) );
    }
  }

  $check_tiers = array_merge( $global_tiers, $parent_tiers, $post_tiers );
  $check_tiers = array_unique( $check_tiers );

  $check_amount_cents = $post_amount_cents > 0 ? $post_amount_cents : $parent_amount_cents;
  $check_amount_cents = $check_amount_cents > 0 ? $check_amount_cents : $global_amount_cents;
  $check_amount_cents = $post_amount_cents > 0 && $global_amount_cents > 0
    ? min( $post_amount_cents, $global_amount_cents ) : $check_amount_cents;
  $check_amount_cents = max( $check_amount_cents, 0 );

  // Compile
  $data = array(
    'gated' => $check_tiers || $check_amount_cents > 0,
    'gate_tiers' => $check_tiers,
    'gate_cents' => $check_amount_cents,
    'gate_lifetime_cents' => $global_lifetime_amount_cents
  );

  // Cache
  $cache[ $post_id ] = $data;

  // Return
  return $data;
}

// =============================================================================
// STRING LENGTH
// =============================================================================

if ( ! function_exists( 'mb_strlen' ) ) {
  /**
   * Fallback function for mb_strlen
   *
   * @param string $string    The string to being measured.
   * @param string $encoding  The character encoding. Default UTF-8.
   *
   * @return int The number of characters in the string.
   */

  function mb_strlen( $string, $encoding = 'UTF-8' ) {
    if ( $encoding !== 'UTF-8' ) {
      return strlen( $string );
    }

    $converted_string = iconv( $encoding, 'UTF-16', $string );

    if ( $converted_string === false ) {
      return strlen( $string );
    } else {
      return strlen( $converted_string ) / 2; // Each character is 2 bytes in UTF-16
    }
  }
}

// =============================================================================
// GET ALL PUBLISHING AUTHORS
// =============================================================================

/**
 * Returns all authors with published posts
 *
 * Note: Qualified post types are fcn_story, fcn_chapter, fcn_recommendation,
 * and post. The result is cached for 12 hours as Transient.
 *
 * @since 5.24.0
 * @link https://developer.wordpress.org/reference/functions/get_users/
 *
 * @param array $args  Optional. Array of additional query arguments.
 *
 * @return array Array of WP_User object, stdClass objects, or IDs.
 */

function fictioneer_get_publishing_authors( $args = [] ) {
  static $authors = null;

  $key = 'fictioneer_publishing_authors_' . md5( serialize( $args ) );

  if ( ! $authors && $transient = get_transient( $key ) ) {
    $authors = $transient;
  }

  if ( $authors ) {
    return $authors;
  }

  $authors = get_users(
    array_merge(
      array( 'has_published_posts' => ['fcn_story', 'fcn_chapter', 'fcn_recommendation', 'post'] ),
      $args
    )
  );

  set_transient( $key, $authors, 12 * HOUR_IN_SECONDS );

  return $authors;
}

// =============================================================================
// GET STORY STATUS LABEL
// =============================================================================

/**
 * Return the translated label of the story or override string.
 *
 * @since 5.30.1
 *
 * @param int         $story_id  Post ID.
 * @param string|null $status    Optional. The internal story status string.
 *
 * @return string Translated label of the story status or override string.
 */

function fictioneer_get_story_status_label( $story_id, $status = null ) {
  $override = get_post_meta( $story_id, 'fictioneer_story_status_override', true );

  if ( $override ) {
    return $override;
  }

  $status = $status ?: get_post_meta( $story_id, 'fictioneer_story_status', true );

  return fcntr( $status );
}

// =============================================================================
// MAGIC QUOTES & UNSLASH
// =============================================================================

/**
 * Check for magic quotes indicator field.
 *
 * Looks in `$_POST['fictioneer_magic_quotes_test']` (or a different key) for
 * an indicator string that may have magic quotes applied. This requires to have
 * a hidden input with a telling string in the submission, such as `O'Reilly`,
 * which would become `O\'Reilly`.
 *
 * @since 5.28.0
 *
 * @param string $key  Key for the super global. Default 'fictioneer_magic_quotes_test'.
 *
 * @return null|bool Null if the indicator field is missing, otherwise true if
 *                   magic quotes were found and false if not.
 */

function fictioneer_has_magic_quotes( $key = 'fictioneer_magic_quotes_test' ) {
  static $result = null;

  if ( $result !== null ) {
    return $result;
  }

  if ( ! isset( $_POST[ $key ] ) ) {
    return null; // Unknown
  }

  if ( preg_match( '/\\\\[\'"\\\\]/', $_POST[ $key ] ?? '' ) === 1 ) {
    $result = true;

    return true;
  }

  $result = false;

  return false;
}

/**
 * Unslash data if there are magic quotes
 *
 * Note: Relies on the presence of an indicator string in `$_POST`,
 * otherwise wp_unslash() will not be applied.
 *
 * @since 5.28.0
 *
 * @param string|array $data  The data to maybe unslash.
 *
 * @return string|array Unslashed or unchanged data.
 */

function fictioneer_maybe_unslash( $data ) {
  if ( fictioneer_has_magic_quotes() ) {
    return wp_unslash( $data );
  }

  return $data;
}

// =============================================================================
// CHECK WHETHER USER CAN EDIT ANY POST TYPES
// =============================================================================

/**
 * Return whether the user can edit any post types.
 *
 * @since 5.33.0
 *
 * @param int $user_id  ID of the user to check.
 *
 * @return bool True if the user can edit any post types, false otherwise.
 */

function fictioneer_user_can_post_any( $user_id ) {
  return user_can( $user_id, 'edit_posts' ) || user_can( $user_id, 'edit_pages' ) || user_can( $user_id, 'edit_fcn_stories' ) || user_can( $user_id, 'edit_fcn_chapters' ) || user_can( $user_id, 'edit_fcn_collections' ) || user_can( $user_id, 'edit_fcn_recommendations' );
}
