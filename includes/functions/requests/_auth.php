<?php

// No direct access!
defined( 'ABSPATH' ) OR exit;

// =============================================================================
// REPLACEMENT FUNCTIONS
// =============================================================================

/**
 * Returns the user avatar URL.
 *
 * @since 5.27.0
 *
 * @param stdClass $user  User object.
 * @param int      $size  Optional. Size of the avatar. Default 96.
 *
 * @return string The URL or an empty string.
 */

function ffcnr_get_avatar_url( $user, $size = 96 ) {
  if ( ! $user ) {
    return '';
  }

  $options = ffcnr_load_options( ['avatar_default'] );
  $default = $options['avatar_default'] ?? 'mystery';
  $meta = ffcnr_load_user_meta( $user->ID, 'fictioneer' );
  $email = $user->user_email ?? 'nonexistentemail@example.com';
  $disabled = ( $meta['fictioneer_disable_avatar'] ?? 0 ) || ( $meta['fictioneer_admin_disable_avatar'] ?? 0 );

  $filtered = apply_filters( 'ffcnr_get_avatar_url', '', $user, $size, $meta, $options );

  if ( ! empty( $filtered ) ) {
    return (string) $filtered;
  }

  // Custom avatar
  if (
    ! $disabled &&
    ! ( $meta['fictioneer_enforce_gravatar'] ?? 0 ) &&
    ! empty( $meta['fictioneer_external_avatar_url'] )
  ) {
    $url = (string) $meta['fictioneer_external_avatar_url'];

    if ( function_exists( 'wp_http_validate_url' ) && wp_http_validate_url( $url ) ) {
      return $url;
    }
  }

  // Gravatar
  if ( $email ) {
    $gravatar_styles = array(
      'mystery' => 'mm',
      'blank' => 'blank',
      'gravatar_default' => '',
      'identicon' => 'identicon',
      'wavatar' => 'wavatar',
      'monsterid' => 'monsterid',
      'retro' => 'retro'
    );

    $default = $gravatar_styles[ $default ] ?? 'mm';
    $email_hash = $disabled ? 'foobar' : md5( mb_strtolower( trim( $user->user_email ) ) );

    return "https://www.gravatar.com/avatar/{$email_hash}?s={$size}&d={$default}";
  }

  return '';
}

/**
 * Checks if a user is an administrator.
 *
 * @since 5.27.0
 *
 * @param stdClass $user  User object.
 *
 * @return boolean To be or not to be.
 */

function ffcnr_is_admin( $user ) {
  return (bool) ( $user->caps['manage_options'] ?? false );
}

/**
 * Checks if a user is an author.
 *
 * @since 5.27.0
 *
 * @param stdClass $user  User object.
 *
 * @return boolean To be or not to be.
 */

function ffcnr_is_author( $user ) {
  return (bool) (
    ( $user->caps['publish_posts'] ?? false ) ||
    ( $user->caps['publish_fcn_stories'] ?? false ) ||
    ( $user->caps['publish_fcn_chapters'] ?? false ) ||
    ( $user->caps['publish_fcn_collections'] ?? false )
  );
}

/**
 * Checks if a user is a moderator.
 *
 * @since 5.27.0
 *
 * @param stdClass $user  User object.
 *
 * @return boolean To be or not to be.
 */

function ffcnr_is_moderator( $user ) {
  return (bool) ( $user->caps['moderate_comments'] ?? false );
}

/**
 * Checks if a user is an editor.
 *
 * @since 5.27.0
 *
 * @param stdClass $user  User object.
 *
 * @return boolean To be or not to be.
 */

function ffcnr_is_editor( $user ) {
  return (bool) ( $user->caps['edit_others_posts'] ?? false );
}

/**
 * Return a user's Follows.
 *
 * @since 5.27.0
 * @see includes/functions/users/_follows.php
 *
 * @param stdClass $user  User to get the Follows for.
 *
 * @return array Follows.
 */

function fictioneer_load_follows( $user ) {
  // Setup
  $follows = ffcnr_get_user_meta( $user->ID, 'fictioneer_user_follows', 'fictioneer' );
  $timestamp = time() * 1000; // Compatible with Date.now() in JavaScript

  // Validate/Initialize
  if ( empty( $follows ) || ! is_array( $follows ) || ! array_key_exists( 'data', $follows ) ) {
    $follows = array( 'data' => [], 'seen' => $timestamp, 'updated' => $timestamp );
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_follows', $follows );
  }

  if ( ! array_key_exists( 'updated', $follows ) ) {
    $follows['updated'] = $timestamp;
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_follows', $follows );
  }

  if ( ! array_key_exists( 'seen', $follows ) ) {
    $follows['seen'] = $timestamp;
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_follows', $follows );
  }

  // Return
  return $follows;
}

/**
 * Return a user's Reminders.
 *
 * @since 5.27.0
 * @see includes/functions/users/_reminders.php
 *
 * @param stdClass $user  User to get the Reminders for.
 *
 * @return array Reminders.
 */

function fictioneer_load_reminders( $user ) {
  // Setup
  $reminders = ffcnr_get_user_meta( $user->ID, 'fictioneer_user_reminders', 'fictioneer' );
  $timestamp = time() * 1000; // Compatible with Date.now() in JavaScript

  // Validate/Initialize
  if ( empty( $reminders ) || ! is_array( $reminders ) || ! array_key_exists( 'data', $reminders ) ) {
    $reminders = array( 'data' => [], 'updated' => $timestamp );
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_reminders', $reminders );
  }

  if ( ! array_key_exists( 'updated', $reminders ) ) {
    $reminders['updated'] = $timestamp;
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_reminders', $reminders );
  }

  // Return
  return $reminders;
}

/**
 * Return a user's Checkmarks.
 *
 * @since 5.27.0
 * @see includes/functions/users/_checkmarks.php
 *
 * @param stdClass $user  User to get the checkmarks for.
 *
 * @return array Checkmarks.
 */

function fictioneer_load_checkmarks( $user ) {
  // Setup
  $checkmarks = ffcnr_get_user_meta( $user->ID, 'fictioneer_user_checkmarks', 'fictioneer' );
  $timestamp = time() * 1000; // Compatible with Date.now() in JavaScript

  // Validate/Initialize
  if ( empty( $checkmarks ) || ! is_array( $checkmarks ) || ! array_key_exists( 'data', $checkmarks ) ) {
    $checkmarks = array( 'data' => [], 'updated' => $timestamp );
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_checkmarks', $checkmarks );
  }

  if ( ! array_key_exists( 'updated', $checkmarks ) ) {
    $checkmarks['updated'] = $timestamp;
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_checkmarks', $checkmarks );
  }

  // Return
  return $checkmarks;
}

/**
 * Return a unique MD5 hash for the user.
 *
 * @since 5.27.0
 * @see includes/functions/_helpers-users.php
 *
 * @param stdClass $user  User to get the fingerprint for.
 *
 * @return string The unique fingerprint hash or empty string if not found.
 */

function fictioneer_get_user_fingerprint( $user ) {
  if ( ! $user ) {
    return '';
  }

  $fingerprint = ffcnr_get_user_meta( $user->ID, 'fictioneer_user_fingerprint', 'fictioneer' );

  if ( empty( $fingerprint ) ) {
    $fingerprint = md5( $user->user_login . $user->ID );
    ffcnr_update_user_meta( $user->ID, 'fictioneer_user_fingerprint', $fingerprint );
  }

  return $fingerprint;
}

/**
 * Get alerts.
 *
 * @since 5.31.0
 *
 * @global wpdb $wpdb  WordPress database object.
 *
 * @param array|null $args  Optional. Additional query arguments.
 *
 * @return array Queried alerts.
 */

function fictioneer_get_alerts( $args = [] ) {
  global $wpdb;

  $defaults = [
    'types' => [],
    'post_ids' => [],
    'story_ids' => [],
    'author' => null,
    'roles' => [],
    'for_roles' => [],
    'user_ids' => [],
    'for_user_id' => null,
    'tags' => [],
    'only_ids' => false
  ];

  $args = wp_parse_args( $args, $defaults );

  $table = $wpdb->prefix . 'fcn_alerts';
  $fields = $args['only_ids'] ? 'ID' : 'ID, type, content, url'; // 'date, date_gmt' will be appended
  $global_types = ['info', 'alert', 'warning'];
  $has_filters = false;
  $params = [];
  $filtered_where = [];
  $filtered_params = [];

  if ( ! empty( $args['types'] ) ) {
    $types = array_filter( array_map( 'sanitize_key', (array) $args['types'] ) );

    if ( $types ) {
      $has_filters = true;
      $placeholders = implode( ', ', array_fill( 0, count( $types ), '%s' ) );
      $filtered_where[] = "type IN ({$placeholders})";
      $filtered_params = array_merge( $filtered_params, $types );
    }
  }

  if ( ! empty( $args['post_ids'] ) ) {
    $post_ids = array_filter( array_map( 'intval', (array) $args['post_ids'] ) );
    $post_ids = array_filter( $post_ids, function( $value ) { return $value > 0; } );

    if ( $post_ids ) {
      $has_filters = true;
      $placeholders = implode( ', ', array_fill( 0, count( $post_ids ), '%d' ) );
      $filtered_where[] = "post_id IN ({$placeholders})";
      $filtered_params = array_merge( $filtered_params, $post_ids );
    }
  }

  if ( ! empty( $args['story_ids'] ) ) {
    $story_ids = array_filter( array_map( 'intval', (array) $args['story_ids'] ) );
    $story_ids = array_filter( $story_ids, function( $value ) { return $value > 0; } );

    if ( $story_ids ) {
      $has_filters = true;
      $placeholders = implode( ', ', array_fill( 0, count( $story_ids ), '%d' ) );
      $filtered_where[] = "story_id IN ({$placeholders})";
      $filtered_params = array_merge( $filtered_params, $story_ids );
    }
  }

  if ( isset( $args['author'] ) && is_numeric( $args['author'] ) && $args['author'] > 0 ) {
    $has_filters = true;
    $filtered_where[] = $wpdb->prepare( 'author = %d', (int) $args['author'] );
  }

  if ( ! empty( $args['roles'] ) ) {
    $role_where = ['roles IS NULL'];

    foreach ( (array) $args['roles'] as $role ) {
      $role = sanitize_key( $role );

      if ( $role ) {
        $has_filters = true;
        $role_where[] = $wpdb->prepare( 'roles LIKE %s', '%"' . $role . '";%' );
      }
    }

    $filtered_where[] = '( ' . implode( ' OR ', $role_where ) . ' )';
  } else {
    $filtered_where[] = 'roles IS NULL';
  }

  if ( ! empty( $args['user_ids'] ) ) {
    $user_where = ['users IS NULL'];

    foreach ( (array) $args['user_ids'] as $user_id ) {
      $user_id = max( (int) $user_id, 0 );

      if ( $user_id > 0 ) {
        $has_filters = true;
        $user_where[] = $wpdb->prepare( 'users LIKE %s', '%"' . $user_id . '";%' );
      }
    }

    $filtered_where[] = '( ' . implode( ' OR ', $user_where ) . ' )';
  } else {
    $filtered_where[] = 'users IS NULL';
  }

  if ( ! empty( $args['tags'] ) ) {
    $tag_where = ['tags IS NULL'];

    foreach ( (array) $args['tags'] as $tag ) {
      $tag = sanitize_key( $tag );

      if ( $tag ) {
        $has_filters = true;
        $tag_where[] = $wpdb->prepare( 'tags LIKE %s', '%"' . $tag . '";%' );
      }
    }

    $filtered_where[] = '( ' . implode( ' OR ', $tag_where ) . ' )';
  } else {
    $filtered_where[] = 'tags IS NULL';
  }

  if ( ! empty( $args['since'] ) ) {
    $has_filters = true;

    $since = is_numeric( $args['since'] )
      ? gmdate( 'Y-m-d H:i:s', (int) $args['since'] )
      : sanitize_text_field( $args['since'] );

    $filtered_where[] = $wpdb->prepare( 'date_gmt >= %s', $since );
  }

  $filtered_where[] = $wpdb->prepare( 'date_gmt <= %s', gmdate( 'Y-m-d H:i:s' ) );

  $global_placeholders = implode( ', ', array_fill( 0, count( $global_types ), '%s' ) );
  $sql_global = "SELECT {$fields}, date, date_gmt FROM $table WHERE type IN ($global_placeholders) AND date_gmt <= %s";

  if ( $has_filters ) {
    $sql_filtered = "SELECT {$fields}, date, date_gmt FROM $table";

    if ( $filtered_where ) {
      $sql_filtered .= ' WHERE ' . implode( ' AND ', $filtered_where );
    }

    $sql = "($sql_filtered) UNION ALL ($sql_global)";
    $params = array_merge( $filtered_params, $global_types, [ gmdate( 'Y-m-d H:i:s' ) ] );
  } else {
    $sql = $sql_global;
    $params = array_merge( $global_types, [ gmdate( 'Y-m-d H:i:s' ) ] );
  }

  if ( ffcnr_get_option( 'fictioneer_enable_extended_alert_queries' ) && ! empty( $args['for_user_id'] ) ) {
    $user_id = sanitize_key( $args['for_user_id'] );

    if ( (int) $user_id > 0 ) {
      $sql .= " UNION ALL (SELECT {$fields}, date, date_gmt FROM $table WHERE users LIKE %s AND date_gmt <= %s)";
      $params = array_merge( $params, [ '%"' . $wpdb->esc_like( $user_id ) . '";%', gmdate( 'Y-m-d H:i:s' ) ] );
    }
  }

  if ( ffcnr_get_option( 'fictioneer_enable_extended_alert_queries' ) && ! empty( $args['for_roles'] ) ) {
    $roles = array_filter( array_map( 'sanitize_key', (array) $args['for_roles'] ) );

    if ( ! empty( $roles ) ) {
      $role_clauses = [];
      $role_params = [];

      foreach ( $roles as $role ) {
        $role_clauses[] = 'roles LIKE %s';
        $role_params[]  = '%"' . $wpdb->esc_like( $role ) . '";%';
      }

      $sql .= " UNION ALL (SELECT {$fields}, date, date_gmt FROM $table WHERE (" . implode( ' OR ', $role_clauses ) . ") AND date_gmt <= %s)";

      $role_params[] = gmdate( 'Y-m-d H:i:s' );
      $params = array_merge( $params, $role_params );
    }
  }

  $sql .= ' ORDER BY date_gmt DESC LIMIT 69';
  $results = $wpdb->get_results( $wpdb->prepare( $sql, ...$params ), ARRAY_A );

  if ( empty( $results ) ) {
    return [];
  }

  $exclude_ids = array_map( 'intval', (array) ( $args['exclude_ids'] ?? [] ) );
  $date_format = ffcnr_get_option( 'fictioneer_alert_date_format', 'Y-m-d H:i' ) ?: 'Y-m-d H:i';
  $filtered_results = [];

  foreach ( $results as &$row ) {
    if ( in_array( (int) $row['ID'], $exclude_ids, true ) ) {
      continue;
    }

    if ( $args['only_ids'] ) {
      $filtered_results[] = (int) $row['ID'];
    } else {
      $row['id'] = (int) $row['ID'];
      unset( $row['ID'] );

      $timestamp = strtotime( $row['date_gmt'] );

      $row['date'] = wp_date( $date_format, $timestamp );

      $filtered_results[] = $row;
    }
  }

  return $filtered_results;
}

// =============================================================================
// GET USER DATA
// =============================================================================

function ffcnr_get_user_data() {
  global $wpdb;

  // Load options
  $options = ffcnr_load_options([
    'fictioneer_enable_reminders',
    'fictioneer_enable_checkmarks',
    'fictioneer_enable_bookmarks',
    'fictioneer_enable_follows',
    'fictioneer_enable_alerts',
    'fictioneer_alert_date_format'
  ]);

  // Setup
  $user = ffcnr_get_current_user( $options );
  $logged_in = (bool) ( $user ? $user->ID : 0 );
  $nonce = $logged_in ? ffcnr_create_nonce( 'fictioneer_nonce', $user->ID ) : '';

  $data = array(
    'user_id' => $logged_in ? $user->ID : 0,
    'timestamp' => time() * 1000, // Compatible with Date.now() in JavaScript
    'loggedIn' => $logged_in,
    'follows' => false,
    'reminders' => false,
    'checkmarks' => false,
    'bookmarks' => '{}',
    'fingerprint' => fictioneer_get_user_fingerprint( $user ),
    'avatarUrl' => '',
    'isAdmin' => false,
    'isModerator' => false,
    'isAuthor' => false,
    'isEditor' => false,
    'nonce' => $nonce,
    'nonceHtml' => $nonce
      ? '<input id="fictioneer-ajax-nonce" name="fictioneer-ajax-nonce" type="hidden" value="' . esc_attr( $nonce ) . '">'
      : ''
  );

  if ( $logged_in ) {
    $data = array_merge(
      $data,
      array(
        'isAdmin' => ffcnr_is_admin( $user ),
        'isModerator' => ffcnr_is_moderator( $user ),
        'isAuthor' => ffcnr_is_author( $user ),
        'isEditor' => ffcnr_is_editor( $user ),
        'avatarUrl' => ffcnr_get_avatar_url( $user )
      )
    );
  }

  // --- ALERTS ---------------------------------------------------------------

  if ( $logged_in && ! empty( $options['fictioneer_enable_alerts'] ) ) {
    $follows = $options['fictioneer_enable_follows'] ? fictioneer_load_follows( $user ) : [];

    $read_alerts = ffcnr_get_user_meta( $user->ID, 'fictioneer_read_alerts', 'fictioneer' ) ?: [];
    $show_read_alerts = ffcnr_get_user_meta( $user->ID, 'fictioneer_show_read_alerts', 'fictioneer' ) ? true : false;

    if ( ! is_array( $read_alerts ) ) {
      $read_alerts = [];
      ffcnr_update_user_meta( $user->ID, 'fictioneer_read_alerts', $read_alerts );
    }

    $alerts = fictioneer_get_alerts(
      array(
        'story_ids' => array_keys( $follows['data'] ?? [] ),
        'exclude_ids' => $show_read_alerts ? [] : $read_alerts,
        'for_user_id' => $user->ID,
        'for_roles' => $user->roles
      )
    );

    if ( ! empty( $alerts ) ) {
      $data['alerts'] = array(
        'items' => $alerts,
        'read' => is_array( $read_alerts ) ? $read_alerts : [],
        'showRead' => $show_read_alerts
      );
    }
  }

  // --- FOLLOWS ---------------------------------------------------------------

  if ( $logged_in && ! empty( $options['fictioneer_enable_follows'] ) ) {
    $data['follows'] = fictioneer_load_follows( $user );
  }

  // --- REMINDERS -------------------------------------------------------------

  if ( $logged_in && ! empty( $options['fictioneer_enable_reminders'] ) ) {
    $data['reminders'] = fictioneer_load_reminders( $user );
  }

  // --- CHECKMARKS ------------------------------------------------------------

  if ( $logged_in && ! empty( $options['fictioneer_enable_checkmarks'] ) ) {
    $data['checkmarks'] = fictioneer_load_checkmarks( $user );
  }

  // --- BOOKMARKS -------------------------------------------------------------

  if ( $logged_in && ! empty( $options['fictioneer_enable_bookmarks'] ) ) {
    $bookmarks = ffcnr_get_user_meta( $user->ID, 'fictioneer_bookmarks', 'fictioneer' );
    $data['bookmarks'] = $bookmarks ? $bookmarks : '{}';
  }

  // --- FILTER ----------------------------------------------------------------

  $data = apply_filters( 'ffcnr_get_user_data', $data, $user );

  // ---------------------------------------------------------------------------

  $wpdb->close();

  // Response
  header( 'Content-Type: application/json; charset=utf-8' );
  http_response_code( 200 );

  echo wp_json_encode( array( 'success' => true, 'data' => $data ) );
}

ffcnr_get_user_data();
