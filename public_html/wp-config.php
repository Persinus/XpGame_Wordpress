<?php

/** WP 2FA plugin data encryption key. For more information please visit melapress.com */
define( 'WP2FA_ENCRYPT_KEY', 'zQlEURYWKDg1pohd8/cfwA==' );

define( 'ITSEC_ENCRYPTION_KEY', 'PkdXPXUgXkdUUS4jQT1COz9TIFlqSCFJL2dbNFFYVGVNeFhyWV9MS0N9SGVlKTkzLTlpKnsmdUo5L05NYzNeQA==' );

define( 'WP_CACHE', true );
 // WP-Optimize Cache
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */
// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'ismvgkqjhosting_xpgame' );
/** Database username */
define( 'DB_USER', 'ismvgkqjhosting_admin' );
/** Database password */
define( 'DB_PASSWORD', 'FaEE2405@' );
/** Database hostname */
define( 'DB_HOST', 'localhost' );
/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );
/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );
/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',         'IXPf ,qTc3_%J2yVZ2y;=hya-nx Sn Y@N)KW,3uoh2kT~1w*(]q^TcP8}PGx3 /' );
define( 'SECURE_AUTH_KEY',  '##Rq,qAI3Q9or8z*NVSagu~f~/sw<_5p!,q`TCMt`.G!x2XMMgg_+K5U&b#&(6fT' );
define( 'LOGGED_IN_KEY',    '`j8o(;GB5[8/(1Qy/qsY.[A2Mr&=r[={X0&iW3%V<<l}=EW2Z+^)VWMS02s<-noe' );
define( 'NONCE_KEY',        'N:x-V(%l>gGt@~Q?(*|X*)J7/7^h#MPc[ %l,8A~Ks{yk+glEwL15G@3+=:}~eV<' );
define( 'AUTH_SALT',        'sri1)Z:)y8#(:++,+NX[deGP#]anBFJh(71B}h5R/Dr4`e*(gs`7B(hv-CF}mNaS' );
define( 'SECURE_AUTH_SALT', '!VH.br;qp;x/:GH_x5BqcASqTerlp)L7S+Y=Qb-5=h:JGE?tG).v)}i]=~}OD{pM' );
define( 'LOGGED_IN_SALT',   'U67c[ZiI2HWnhW1[qX-<E!xH %/{%7#W{*t`og)xCO.Kk&>;Y]TH^9C:tp57ZdFR' );
define( 'NONCE_SALT',       '~7W`uDv0qJel3TU0A}f+_$(l1+^mZb4D3eJ_`VvIkx)p}g5: lqF`]{Z^Z0Ho8Ru' );
/**#@-*/
/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
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
 * visit the documentation.
 *
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
define( 'WP_DEBUG', false );
/* Add any custom values between this line and the "stop editing" line. */
/* That's all, stop editing! Happy publishing. */
/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}
/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';