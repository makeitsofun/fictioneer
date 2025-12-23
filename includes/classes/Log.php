<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class Log {
  /**
   * Log a message to the theme log file.
   *
   * @since 5.0.0
   * @since 5.34.0 - Moved into Log class.
   *
   * @param string       $message  What has been updated
   * @param WP_User|null $user     The user who did it. Defaults to current user.
   */

  public static function add( $message, $current_user = null ) : void {
    $current_user = $current_user ?? wp_get_current_user();
    $username = _x( 'System', 'Default name in logs.', 'fictioneer' );
    $log_hash = self::get_log_hash();
    $log_file = WP_CONTENT_DIR . "/fictioneer-{$log_hash}-log.log";
    $log_limit = 5000;
    $date = current_time( 'mysql', true );
    $rest = '';

    if ( is_object( $current_user ) && $current_user->ID > 0 ) {
      $username = $current_user->user_login . ' #' . $current_user->ID;
    }

    if ( empty( $current_user ) && wp_doing_cron() ) {
      $username = 'WP Cron';
    }

    if ( empty( $current_user ) && wp_doing_ajax() ) {
      $username = 'AJAX';
    }

    $username = empty( $username ) ? __( 'Anonymous', 'fictioneer' ) : $username;

    if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
      $rest = '[REST] ';
    }

    if ( ! file_exists( $log_file ) ) {
      file_put_contents( $log_file, '' );
    }

    $log_contents = file_get_contents( $log_file );

    $log_entries = preg_split( '/\r\n|\r|\n/', $log_contents );
    $log_entries = array_slice( $log_entries, -($log_limit + 1) );
    $log_entries[] = "[{$date} UTC] [{$username}] {$rest}{$message}";

    file_put_contents( $log_file, implode( "\n", $log_entries ) );

    chmod( $log_file, 0600 );

    $silence = WP_CONTENT_DIR . '/index.php';

    if ( ! file_exists( $silence ) ) {
      file_put_contents( $silence, "<?php\n// Silence is golden.\n" );
      chmod( $silence, 0600 );
    }
  }

  /**
   * Retrieve the log entries and returns an HTML representation.
   *
   * @since 5.0.0
   * @since 5.34.0 - Moved into Log class.
   *
   * @return string The HTML representation of the log entries.
   */

  public static function get() : string {
    $log_hash = self::get_log_hash();
    $log_file = WP_CONTENT_DIR . "/fictioneer-{$log_hash}-log.log";
    $output = '';

    if ( ! file_exists( $log_file ) ) {
      return '<ul class="fictioneer-log"><li class="fictioneer-log__item">No log entries yet.</li></ul>';
    }

    $log_contents = file_get_contents( $log_file );

    $log_entries = preg_split( '/\r\n|\r|\n/', $log_contents );
    $log_entries = array_slice( $log_entries, -250 );
    $log_entries = array_reverse( $log_entries );

    foreach ( $log_entries as $entry ) {
      $output .= '<li class="fictioneer-log__item">' . esc_html( $entry ) . '</li>';
    }

    return '<ul class="fictioneer-log">' . $output . '</ul>';
  }

  /**
   * Retrieve the debug log entries and returns an HTML representation.
   *
   * @since 5.0.0
   * @since 5.34.0 - Moved into Log class.
   *
   * @return string HTML representation of the log entries.
   */

  public static function get_debug() : string {
    $log_file = WP_CONTENT_DIR . '/debug.log';
    $output = '';

    if ( ! file_exists( $log_file ) ) {
      return '<ul class="fictioneer-log _wp-debug-log"><li class="fictioneer-log__item">No log entries yet.</li></ul>';
    }

    $log_contents = file_get_contents( $log_file );

    $log_entries = preg_split( '/\r\n|\r|\n/', $log_contents );
    $log_entries = array_slice( $log_entries, -250 );
    $log_entries = array_reverse( $log_entries );

    foreach ( $log_entries as $entry ) {
      $output .= '<li class="fictioneer-log__item _wp-debug-log">' . esc_html( $entry ) . '</li>';
    }

    return '<ul class="fictioneer-log _wp-debug-log">' . $output . '</ul>';
  }

  /**
   * Return (or creates) secret log hash used to obscure the log file name.
   *
   * @since 5.24.1
   * @since 5.34.0 - Moved into Log class.
   *
   * @return string The log hash.
   */

  protected static function get_log_hash() : string {
    $hash = strval( get_option( 'fictioneer_log_hash' ) );

    if ( ! empty( $hash ) ) {
      return $hash;
    }

    $hash = wp_generate_password( 32, false );

    update_option( 'fictioneer_log_hash', $hash, 'no' );

    return $hash;
  }
}
