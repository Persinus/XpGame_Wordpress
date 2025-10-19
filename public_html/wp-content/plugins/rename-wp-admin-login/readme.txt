=== Rename wp-admin login ===
Tags:              rename wp-admin login, change wp-login, wp-admin, login, custom login url
Contributors:      nunosarmento
Requires at least: 5.0
Tested up to:      6.8
Stable tag:        1.0.0
License:           GPL-2.0+

Rename wp-admin login* is a plugin that allows us to rename wp-admin login URL to anything you want

== Description ==

*Rename wp-admin login* is a plugin that allows us to rename wp-admin login URL to anything you want. It does not change WordPress core files, the plugin simply intercepts page requests and works on any WordPress website. After you activate this plugin the wp-admin URL and wp-login.php will become unavailable, so you should bookmark or remember the url. Disable this plugin brings your site back exactly to the state it was before.


== Support ==

**Like this plugin?** Please [Rate It](https://wordpress.org/support/plugin/rename-wp-admin-login/reviews/?filter=5) or [Buy me a coffee](https://ko-fi.com/nunosarmento)

**Have a problem?** Please write a message in the [WordPress Support Forum](https://wordpress.org/support/plugin/rename-wp-admin-login/)


== How to use the plugin ==

Go under Settings and then click on "Permalinks" and change your URL under "Rename wp-admin login".

Step 1: Add new login URL

Step 2: Add redirect URL

== Credits ==

This plugin was forked/adapted/fixed/updated from this plugin https://wordpress.org/plugins/rename-wp-login/ - @ellatrix thank you for starting the base of my plugin.


== Installation ==

1. Go to Plugins › Add New.
2. Search for *Rename wp-admin login*.
3. Download and activate it.
4. Go under Settings and then click on "Permalinks" and change your URL under "Rename wp-admin login"
5. You can change this anytime, just go back to Settings › Permalinks › Rename wp-admin login.

== Frequently Asked Questions ==

= I can't login? =
Did you forgot the login URL? Or for any other reason you can't login on the website you will need to delete the plugin via SFT/FTP or cPanel on your hosting.

Path for the plugin folder:
/wp-content/plugins/rename-wp-admin-login

= Does it work on WordPress Multisite with Subdomains? =
Yes, it does work. You should setup the login URL in each website (Settings-->Permalinks)

= Does it work with Polylang? =
Yes, it works but not tested with the URL option "The language is set from different domains".

= Does it work with Buddyboss? =
No, Buddyboss has their own wp-admin redirect functions.

= Does it work with BuddyPress? =
No, BuddyPress has their own wp-admin redirect functions.


== Changelog ==

= 1.0.0 =
* Initial version.
