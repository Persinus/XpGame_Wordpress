<?php
 /*
 Plugin Name: Rename wp-admin login
 Plugin URI: https://wordpress.org/plugins/rename-wp-admin-login/
 Description: Rename wp-admin login URL to your own URL. Example: http://www.example.com/secure-login. You just need to go to your WordPress admin menu and under Settings click on "Permalinks" and change your URL under "Rename wp-admin login" area.
 Version: 1.0.0
 Author: Nuno Morais Sarmento
 Author URI: https://www.nuno-sarmento.com
 Text Domain: rename-wp-admin-login
 Domain Path: /languages

Copyright 2022  Nuno Morais Sarmento (email : hello@nuno-sarmento.com)
This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

// Acknowledgements to Ella van Durpe (https://wordpress.org/plugins/rename-wp-login/), some of whose code was used
// in the development of this plug-in. This plugin (https://wordpress.org/plugins/rename-wp-login/) don't have any copyright policy.

 */

 defined('ABSPATH') or die('°_°’');

if( ! defined( 'NS_Rename_WP_Admin_Login_Version' ) ) {
	define( 'NS_Rename_WP_Admin_Login_Version', '1.0.0' );
}
if( ! defined( 'NS_Rename_WP_Admin_Login_Name' ) ) {
	define( 'NS_Rename_WP_Admin_Login_Name', 'Rename wp-admin login' );
}
if ( ! defined( 'NS_Rename_WP_Admin_Login_Path' ) ) {
	define( 'NS_Rename_WP_Admin_Login_Path', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'NS_Rename_WP_Admin_Login_Base_Uri' ) ) {
	define( 'NS_Rename_WP_Admin_Login_Base_Uri', plugin_dir_url( __FILE__ ) );
}

load_plugin_textdomain( 'rename-wp-admin-login', false, basename( dirname( __FILE__ ) ) . '/languages' );
require_once NS_Rename_WP_Admin_Login_Path . 'includes/class-rename-wp-admin-login.php';
