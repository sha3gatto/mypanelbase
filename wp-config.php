<?php
/**
* The base configuration for WordPress
*
* The wp-config.php creation script uses this file during the
* installation. You don't have to use the web site, you can
* copy this file to "wp-config.php" and fill in the values.
*
* This file contains the following configurations:
*
* * MySQL settings
* * Secret keys
* * Database table prefix
* * ABSPATH
*
* @link https://codex.wordpress.org/Editing_wp-config.php
*
* @package WordPress
*/

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'mypanelbase');

/** MySQL database username */
define('DB_USER', 'mypanelbase');

/** MySQL database password */
define('DB_PASSWORD', 'password');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
* Authentication Unique Keys and Salts.
*
* Change these to different unique phrases!
* You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
* You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
*
* @since 2.6.0
*/
define('AUTH_KEY', 'G7IhX0TA3CKh26PjZD2QHAazZEDMRpC+hfJ9K82Oh4mIo7zCypYoCbkxz6UJANPB');
define('SECURE_AUTH_KEY', 'mKqEuybx6l8DBPQIViGIrGpgPM7p0wVvBOaOFw+0X4D7T/zOgKaXYPYehnyzqMzK');
define('LOGGED_IN_KEY', 'gHB9cs2sK1LEjFkvAGNNLjMiCtOikQrN6XtgUwiTP0CWfQ2yGk/49KDQNwHCPsZx');
define('NONCE_KEY', '4GbQYtcgKLy3yVwIC1qczzfD85lsKB+Dyswkvv79FH56DsOCKY0xsKuiUCSDQvWL');
define('AUTH_SALT', 'qsXmnV0MEkTFm/vW3kyotOjZiXtfhhp550UDHJ27ar9bVgh7k62hieraV/Oo9LFt');
define('SECURE_AUTH_SALT', 'Es++36pZGJIxdqF9nPsFEWNv6JiObFkTaf09GBzv1Ad1VcVn7hsXRzfwBtVJHapO');
define('LOGGED_IN_SALT', 'tavKRTM99QE01RSVX49mOq2gfqCtBtpQHNc2FR9naZk3k7QqKf+RY0shXlBzegY3');
define('NONCE_SALT', 'HrYhQbN7qBm0JheegwgFRhEhpruFuJGHgxiyGXOLs+zzfaDHFER0ZvzRFfo051Kx');

/**#@-*/

/**
* WordPress Database Table prefix.
*
* You can have multiple installations in one database if you give each
* a unique prefix. Only numbers, letters, and underscores please!
*/
$table_prefix = 'wp_';

/**
* For developers: WordPress debugging mode.
*
* Change this to true to enable the display of notices during development.
* It is strongly recommended that plugin and theme developers use WP_DEBUG
* in their development environments.
*
* For information on other constants that can be used for debugging,
* visit the Codex.
*
* @link https://codex.wordpress.org/Debugging_in_WordPress
*/
define('WP_DEBUG', false);
define('PLL_CACHE_HOME_URL', false);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
define('ABSPATH', dirname(__FILE__) . '/');

/* Fixes "Add media button not working", see http://www.carnfieldwebdesign.co.uk/blog/wordpress-fix-add-media-button-not-working/ */
define('CONCATENATE_SCRIPTS', false );

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
