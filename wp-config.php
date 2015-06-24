<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, WordPress Language, and ABSPATH. You can find more information
 * by visiting {@link http://codex.wordpress.org/Editing_wp-config.php Editing
 * wp-config.php} Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// Include local configuration
if (file_exists(dirname(__FILE__) . '/local-config.php')) {
	include(dirname(__FILE__) . '/local-config.php');
}

// Global DB config
if (!defined('DB_NAME')) {
	define('DB_NAME', 'letemsgn_wp');
}
if (!defined('DB_USER')) {
	define('DB_USER', 'letemsgn_wp');
}
if (!defined('DB_PASSWORD')) {
	define('DB_PASSWORD', 'X8!9BP9GS]');
}
if (!defined('DB_HOST')) {
	define('DB_HOST', 'localhost');
}

/** Database Charset to use in creating database tables. */
if (!defined('DB_CHARSET')) {
	define('DB_CHARSET', 'utf8');
}

/** The Database Collate type. Don't change this if in doubt. */
if (!defined('DB_COLLATE')) {
	define('DB_COLLATE', '');
}

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'XF8I,5D*u|mFuf3Va.J=4m)+yt.LEdrSVayC73QJ7c|fg@H#pVqd%&{^|(5<-tR!');
define('SECURE_AUTH_KEY',  '|]yTmtP!Z5%A@s/8co3 Nj4a5~!w+-m:uCD4<R372ScR+a1jJAH/mb&D;L:8.G0-');
define('LOGGED_IN_KEY',    'V#|`^!0R(xJB5w+~VV&K.+gJe~N{.Re`kI+3Y;Xi-MLgyJwa@l@RhX{H0H2b]VNi');
define('NONCE_KEY',        'MI$|D:2w6E_DY{/(RDE [[V_l>R%BRlJ,+;/:d1XL^YIVoZeh~#y*8K8LdXue:+?');
define('AUTH_SALT',        '_:X?w>d?ah8_;.XQe{Yu+oP)MWTJN~<|F%KB{+4dE+W+^M.rwIr3W-qKh3(N<i&#');
define('SECURE_AUTH_SALT', '%.#k(uqDC|(<8Zg>~a0,88`$M7XP)d14hZCF-wgw#[KXkj7!SH|Dhi3I$<jP}vXD');
define('LOGGED_IN_SALT',   'h6/?`HAk(VbEzs<c`~]i$KU[-y#y%(ACr|y]w[+uXLT|btEFu|6.txh% wm5JX4!');
define('NONCE_SALT',       'cEF(%@?g6O3pYW/iINo?b]I6RD4HW,Zo1:nLxfB<Cy=oTfqm?pE-g-]P3m A<&~l');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * WordPress Localized Language, defaults to English.
 *
 * Change this to localize WordPress. A corresponding MO file for the chosen
 * language must be installed to wp-content/languages. For example, install
 * de_DE.mo to wp-content/languages and set WPLANG to 'de_DE' to enable German
 * language support.
 */
define('WPLANG', 'Spanish');



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
if (!defined('WP_DEBUG')) {
	define('WP_DEBUG', false);
}

/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
