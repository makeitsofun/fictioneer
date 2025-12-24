<?php

namespace Fictioneer;

defined( 'ABSPATH' ) OR exit;

class User {
  /**
   * Get custom avatar URL
   *
   * @since 4.0.0
   * @since 5.34.0 - Moved into User class.
   *
   * @param WP_User $user The user to get the avatar for.
   *
   * @return string|boolean The custom avatar URL or false.
   */

  public static function get_custom_avatar_url( $user ) {
    if ( $user && is_object( $user ) && ! $user->fictioneer_enforce_gravatar ) {
      $avatar_url = empty( $user->fictioneer_external_avatar_url ) ? null : $user->fictioneer_external_avatar_url;

      if ( ! empty( $avatar_url ) ) {
        return $avatar_url;
      }
    }

    return false;
  }
}
