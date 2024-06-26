<?php

namespace Barn2\Plugin\Better_Recent_Comments;

/**
 * Factory to create/return the shared plugin instance.
 *
 * @package   Barn2\better-recent-comments
 * @author    Kestrel <support@kestrelwp.com>
 * @license   GPL-3.0
 * @copyright Kestrel
 */
class Plugin_Factory {

	private static $plugin = null;

	public static function create( $file, $version ) {
		if ( null === self::$plugin ) {
			self::$plugin = new Plugin( $file, $version );
		}
		return self::$plugin;
	}

}
