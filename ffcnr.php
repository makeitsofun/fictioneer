<?php
/**
 * Fast Request Entry Point
 *
 * This file set up a minimal WordPress environment that is many times
 * faster than regular endpoints but does not load anything beyond the
 * absolute basics. No theme functions or plugins will work by default.
 * Only use this for frequent and performance-critical requests.
 *
 * @package WordPress
 * @subpackage Fictioneer
 * @since 5.27.0
 */

define( 'SHORTINIT', true );
define( 'FFCNR', true );

header( 'X-Robots-Tag: noindex, nofollow', true );
header( 'X-Content-Type-Options: nosniff' );
header( 'X-Frame-Options: DENY' );

header( 'Referrer-Policy: no-referrer' );
header( "Content-Security-Policy: default-src 'none'; script-src 'none'; style-src 'none'; img-src 'none'; object-src 'none'; frame-ancestors 'none'; base-uri 'none'; form-action 'none';" ); // Just because

header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
header( 'Pragma: no-cache' );

// Methods
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ( ! in_array( $method, [ 'GET', 'POST' ], true ) ) {
  http_response_code( 405 );
  exit;
}

// Action
$raw_action = $_GET['action'] ?? '';
$action = strtolower( (string) $raw_action );

if ( ! preg_match( '/^[a-z0-9_-]+$/', $action ) ) {
  http_response_code( 204 ); // Ping response
  exit;
}

define( 'FFCNR_ACTION', $action );

// Initialize
$load_path = realpath( dirname( __DIR__, 3 ) . '/wp-load.php' );

if ( ! $load_path || ! file_exists( $load_path ) ) {
  if ( isset( $_SERVER['DOCUMENT_ROOT'] ) ) {
    $document_root = realpath( $_SERVER['DOCUMENT_ROOT'] );

    if ( $document_root ) {
      $fallback = $document_root . '/wp-load.php';

      if ( file_exists( $fallback ) ) {
        $load_path = $fallback;
      }
    }
  }
}

if ( ! $load_path || ! file_exists( $load_path ) ) {
  http_response_code( 500 );
  echo 'Critical error: Unable to locate wp-load.php.';
  exit;
}

require_once $load_path;

if ( ! defined( 'ABSPATH' ) || ! realpath( ABSPATH ) ) {
  http_response_code( 500 );
  exit;
}

require_once __DIR__ . '/includes/functions/requests/_setup.php';

// That didn't work
http_response_code( 400 );
exit;
