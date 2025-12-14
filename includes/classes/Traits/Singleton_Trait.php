<?php

namespace Fictioneer\Traits;

defined( 'ABSPATH' ) || exit;

trait Singleton_Trait {
  protected static $instance = null;

  /**
   * Return the singleton instance.
   *
   * @since 5.34.0
   *
   * @return static Singleton instance.
   */

  final public static function instance() {
    if ( static::$instance === null ) {
      static::$instance = new static();
    }

    return static::$instance;
  }

  /**
   * No cloning the class.
   *
   * @since 5.34.0
   */

  final public function __clone() {}

  /**
   * No unserializing the class.
   *
   * @since 5.34.0
   */

  final public function __wakeup() {}
}
