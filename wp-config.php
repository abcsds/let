<?php
/**
 * The base configurations of the WordPress.
 *
 * This file has the following configurations: MySQL settings, Table Prefix,
 * Secret Keys, and ABSPATH. You can find more information by visiting
 * {@link https://codex.wordpress.org/Editing_wp-config.php Editing wp-config.php}
 * Codex page. You can get the MySQL settings from your web host.
 *
 * This file is used by the wp-config.php creation script during the
 * installation. You don't have to use the web site, you can just copy this file
 * to "wp-config.php" and fill in the values.
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'letemsgn_wp');

/** MySQL database username */
define('DB_USER', 'letemsgn_wp');

/** MySQL database password */
define('DB_PASSWORD', 'OS.53PmM6(');

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
define('AUTH_KEY',         '5hcsvwxustwhimxp7hxqnin6vuff109u9tniolca6utbvdzqgfbunszgcbcfa8jy');
define('SECURE_AUTH_KEY',  'n8312e2yiilvhd1yci4oo5tizpngce5g5pq3dhxl32a19kvbgecfb7yluugpflwv');
define('LOGGED_IN_KEY',    'rkyqk16mijjv4kieo8av9ur72duxlbqam20v06l5zdqi5vwrvva7ddxbck83y6lv');
define('NONCE_KEY',        'j0wmljnlglwtnnmtdy0qbggzpe8vwxvh3wcce89n8ojbaniwnc4aqxudtthjrm9y');
define('AUTH_SALT',        'zf4vpmqiuyolsulp71owmnj7oaut9dsvznokimewvoenuzm8qujz1rrezucoy3wz');
define('SECURE_AUTH_SALT', 'axhqcesbyrff2mayb1rbatckvlnp3qao4y5sifhdhbt1dh3rf8ztucj0pc1nvxvi');
define('LOGGED_IN_SALT',   '4yfq01xyamewvxwimjagvhfvxcogszahymkyu97ljsump4x9o6xsdjxyjkobxh1q');
define('NONCE_SALT',       'mizoydlkz49fvalsaabxjjx5m9cpharnk5nub4nyklfwtabdnomeycjojck39npc');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each a unique
 * prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 */
define('WP_DEBUG', false);

/* Multisite */
define( 'WP_ALLOW_MULTISITE', true );
define('MULTISITE', true);
define('SUBDOMAIN_INSTALL', false);
define('DOMAIN_CURRENT_SITE', 'let-emprendimientopublico.mx');
define('PATH_CURRENT_SITE', '/');
define('SITE_ID_CURRENT_SITE', 1);
define('BLOG_ID_CURRENT_SITE', 1);

/* That's all, stop editing! Happy blogging. */

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
