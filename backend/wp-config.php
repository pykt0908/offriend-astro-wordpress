<?php
/**
 * The base configuration for WordPress
 * Configured for Offriend project with local XAMPP MySQL
 */

// ** Database settings - XAMPP local default ** //
define( 'DB_NAME', 'offriend_db' );
define( 'DB_USER', 'root' );
define( 'DB_PASSWORD', '' );
define( 'DB_HOST', 'localhost' );
define( 'DB_CHARSET', 'utf8mb4' );
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 */
define( 'AUTH_KEY',         '1a2b3c4d5e6f7g8h9i0j!@#$%^&*()_+offriend1' );
define( 'SECURE_AUTH_KEY',  '2b3c4d5e6f7g8h9i0j1k!@#$%^&*()_+offriend2' );
define( 'LOGGED_IN_KEY',    '3c4d5e6f7g8h9i0j1k2l!@#$%^&*()_+offriend3' );
define( 'NONCE_KEY',        '4d5e6f7g8h9i0j1k2l3m!@#$%^&*()_+offriend4' );
define( 'AUTH_SALT',        '5e6f7g8h9i0j1k2l3m4n!@#$%^&*()_+offriend5' );
define( 'SECURE_AUTH_SALT', '6f7g8h9i0j1k2l3m4n5o!@#$%^&*()_+offriend6' );
define( 'LOGGED_IN_SALT',   '7g8h9i0j1k2l3m4n5o6p!@#$%^&*()_+offriend7' );
define( 'NONCE_SALT',       '8h9i0j1k2l3m4n5o6p7q!@#$%^&*()_+offriend8' );
/**#@-*/

$table_prefix = 'wp_';

define( 'WP_DEBUG', false );

/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
