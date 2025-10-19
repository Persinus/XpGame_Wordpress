<?php

/**
 * Install plugins which are not addons.
 */
function userfeedback_install_plugin()
{
	check_ajax_referer('userfeedback-install', 'nonce');
	$post_data = sanitize_post($_POST, 'raw');
	if (!userfeedback_can_install_plugins()) {
		wp_send_json(
			array(
				'error' => esc_html__('You are not allowed to install plugins', 'userfeedback-lite'),
			)
		);
	}

	$slug = isset($post_data['slug']) ? sanitize_text_field(wp_unslash($post_data['slug'])) : false;

	if (!$slug) {
		wp_send_json(
			array(
				'message' => esc_html__('Missing plugin name.', 'userfeedback-lite'),
			)
		);
	}

	include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
	$api = plugins_api(
		'plugin_information',
		array(
			'slug'   => $slug,
			'fields' => array(
				'short_description' => false,
				'sections'          => false,
				'requires'          => false,
				'rating'            => false,
				'ratings'           => false,
				'downloaded'        => false,
				'last_updated'      => false,
				'added'             => false,
				'tags'              => false,
				'compatibility'     => false,
				'homepage'          => false,
				'donate_link'       => false,
			),
		)
	);

	if (is_wp_error($api)) {
		return $api->get_error_message();
	}

	$download_url = $api->download_link;

	$method = '';
	$url    = add_query_arg(
		array(
			'page' => 'userfeedback_settings',
		),
		admin_url('admin.php')
	);
	$url    = esc_url($url);

	ob_start();
	if (false === ($creds = request_filesystem_credentials($url, $method, false, false, null))) {
		$form = ob_get_clean();

		wp_send_json(array('form' => $form));
	}

	// If we are not authenticated, make it happen now.
	if (!WP_Filesystem($creds)) {
		ob_start();
		request_filesystem_credentials($url, $method, true, false, null);
		$form = ob_get_clean();

		wp_send_json(array('form' => $form));
	}

	// We do not need any extra credentials if we have gotten this far, so let's install the plugin.
	userfeedback_require_upgrader();

	// Prevent language upgrade in ajax calls.
	remove_action('upgrader_process_complete', array('Language_Pack_Upgrader', 'async_upgrade'), 20);
	// Create the plugin upgrader with our custom skin.
	$installer = new UserFeedback_Plugin_Upgrader( new UserFeedback_Skin() );
	$installer->install( $download_url );

	// set uncanny affiliate program
	if('uncanny-automator' == $slug) {
		update_option('uncannyautomator_source', 'UF');
	}

	// Flush the cache and return the newly installed plugin basename.
	wp_cache_flush();
	wp_send_json_success();
	wp_die();
}
add_action( 'wp_ajax_userfeedback_install_plugin', 'userfeedback_install_plugin' );

function userfeedback_activate_plugin(){
	check_ajax_referer( 'userfeedback-install', 'nonce' );
	$post_data = sanitize_post( $_POST, 'raw' );
	if ( ! userfeedback_can_install_plugins() ) {
		wp_send_json(
			array(
				'error' => esc_html__( 'You are not allowed to install plugins', 'userfeedback-lite' ),
			)
		);
	}

	$basename = isset( $post_data['basename'] ) ? sanitize_text_field( wp_unslash( $post_data['basename'] ) ) : false;

	if ( ! $basename ) {
		wp_send_json(
			array(
				'message' => esc_html__( 'Missing plugin name.', 'userfeedback-lite' ),
			)
		);
	}
	activate_plugin( $basename, '', false, true );

	wp_send_json_success();
	wp_die();
}

add_action( 'wp_ajax_userfeedback_activate_plugin', 'userfeedback_activate_plugin' );

/**
 * Get recommended plugins
 */
function userfeedback_get_plugins()
{

	if (!function_exists('get_plugins')) {
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
	}

	$installed_plugins = get_plugins();

	$plugins = array();

	// MonsterInsights
	$plugins['monsterinsights'] = array(
		'active'    => function_exists('MonsterInsights'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-mi.png',
		'title'     => 'MonsterInsights',
		'excerpt'   => __('The leading WordPress analytics plugin that shows you how people find and use your website, so you can make data driven decisions to grow your business. Properly set up Google Analytics without writing code.', 'userfeedback-lite'),
		'installed' => array_key_exists('google-analytics-for-wordpress/googleanalytics.php', $installed_plugins) || array_key_exists('google-analytics-premium/googleanalytics-premium.php', $installed_plugins),
		'basename'  => 'google-analytics-for-wordpress/googleanalytics.php',
		'slug'      => 'google-analytics-for-wordpress',
		'settings'  => admin_url('admin.php?page=monsterinsights-settings'),
	);

	// WPForms.
	$plugins['wpforms-lite'] = array(
		'active'    => function_exists('wpforms'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-wpforms.png',
		'title'     => 'WPForms',
		'excerpt'   => __('The best drag & drop WordPress form builder. Easily create beautiful contact forms, surveys, payment forms, and more with our 1000+ form templates. Trusted by over 6 million websites as the best forms plugin.', 'userfeedback-lite'),
		'installed' => array_key_exists('wpforms-lite/wpforms.php', $installed_plugins),
		'basename'  => 'wpforms-lite/wpforms.php',
		'slug'      => 'wpforms-lite',
		'settings'  => admin_url('admin.php?page=wpforms-overview'),
	);

	// AIOSEO.
	$plugins['aioseo'] = array(
		'active'    => function_exists('aioseo'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-all-in-one-seo.png',
		'title'     => 'AIOSEO',
		'excerpt'   => __('The original WordPress SEO plugin and toolkit that improves your website’s search rankings. Comes with all the SEO features like Local SEO, WooCommerce SEO, sitemaps, SEO optimizer, schema, and more.', 'userfeedback-lite'),
		'installed' => array_key_exists('all-in-one-seo-pack/all_in_one_seo_pack.php', $installed_plugins),
		'basename'  => (userfeedback_is_installed_aioseo_pro()) ? 'all-in-one-seo-pack-pro/all_in_one_seo_pack.php' : 'all-in-one-seo-pack/all_in_one_seo_pack.php',
		'slug'      => 'all-in-one-seo-pack',
		'settings'  => admin_url('admin.php?page=aioseo'),
	);

	// OptinMonster.
	$plugins['optinmonster'] = array(
		'active'    => class_exists('OMAPI'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-om.png',
		'title'     => 'OptinMonster',
		'excerpt'   => __('Instantly get more subscribers, leads, and sales with the #1 conversion optimization toolkit. Create high converting popups, announcement bars, spin a wheel, and more with smart targeting and personalization.', 'userfeedback-lite'),
		'installed' => array_key_exists('optinmonster/optin-monster-wp-api.php', $installed_plugins),
		'basename'  => 'optinmonster/optin-monster-wp-api.php',
		'slug'      => 'optinmonster',
		'settings'  => admin_url('admin.php?page=optin-monster-dashboard'),
	);

	// RafflePress
	$plugins['rafflepress'] = array(
		'active'    => defined('RAFFLEPRESS_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-rafflepress.png',
		'title'     => 'RafflePress',
		'excerpt'   => __('Turn your website visitors into brand ambassadors! Easily grow your email list, website traffic, and social media followers with the most powerful giveaways & contests plugin for WordPress.', 'userfeedback-lite'),
		'installed' => array_key_exists('rafflepress/rafflepress.php', $installed_plugins),
		'basename'  => 'rafflepress/rafflepress.php',
		'slug'      => 'rafflepress',
		'settings'  => admin_url('admin.php?page=rafflepress_lite'),
	);

	// SeedProd.
	$plugins['coming-soon'] = array(
		'active'    => defined('SEEDPROD_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-seedprod.png',
		'title'     => 'SeedProd',
		'excerpt'   => __('The fastest drag & drop landing page builder for WordPress. Create custom landing pages without writing code, connect them with your CRM, collect subscribers, and grow your audience. Trusted by 1 million sites.', 'userfeedback-lite'),
		'installed' => array_key_exists('coming-soon/coming-soon.php', $installed_plugins),
		'basename'  => 'coming-soon/coming-soon.php',
		'slug'      => 'coming-soon',
		'settings'  => admin_url('admin.php?page=seedprod_lite'),
	);

	// WP Mail Smtp.
	$plugins['wp-mail-smtp'] = array(
		'active'    => function_exists('wp_mail_smtp'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-smtp.png',
		'title'     => 'WP Mail SMTP',
		'excerpt'   => __('Improve your WordPress email deliverability and make sure that your website emails reach user’s inbox with the #1 SMTP plugin for WordPress. Over 3 million websites use it to fix WordPress email issues.', 'userfeedback-lite'),
		'installed' => array_key_exists('wp-mail-smtp/wp_mail_smtp.php', $installed_plugins),
		'basename'  => 'wp-mail-smtp/wp_mail_smtp.php',
		'slug'      => 'wp-mail-smtp',
	);

	// EDD
	$plugins['easy-digital-downloads'] = array(
		'active'    => class_exists('Easy_Digital_Downloads'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-edd.png',
		'title'     => 'Easy Digital Downloads',
		'excerpt'   => __('The best WordPress eCommerce plugin for selling digital downloads. Start selling eBooks, software, music, digital art, and more within minutes. Accept payments, manage subscriptions, advanced access control, and more.', 'userfeedback-lite'),
		'installed' => array_key_exists('easy-digital-downloads/easy-digital-downloads.php', $installed_plugins),
		'basename'  => 'easy-digital-downloads/easy-digital-downloads.php',
		'slug'      => 'easy-digital-downloads',
	);

	// Smash Balloon (Instagram)
	$plugins['smash-balloon-instagram'] = array(
		'active'    => defined('SBIVER'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-sb-instagram.png',
		'title'     => 'Smash Balloon Instagram Feeds',
		'excerpt'   => __('Easily display Instagram content on your WordPress site without writing any code. Comes with multiple templates, ability to show content from multiple accounts, hashtags, and more. Trusted by 1 million websites.', 'userfeedback-lite'),
		'installed' => array_key_exists('instagram-feed/instagram-feed.php', $installed_plugins),
		'basename'  => 'instagram-feed/instagram-feed.php',
		'slug'      => 'instagram-feed',
		'settings'  => admin_url('admin.php?page=sbi-settings'),
	);

	// Smash Balloon (Facebook)
	$plugins['smash-balloon-facebook'] = array(
		'active'    => defined('CFFVER'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-sb-facebook.png',
		'title'     => 'Smash Balloon Facebook Feeds',
		'excerpt'   => __('Easily display Facebook content on your WordPress site without writing any code. Comes with multiple templates, ability to embed albums, group content, reviews, live videos, comments, and reactions.', 'userfeedback-lite'),
		'installed' => array_key_exists('custom-facebook-feed/custom-facebook-feed.php', $installed_plugins),
		'basename'  => 'custom-facebook-feed/custom-facebook-feed.php',
		'slug'      => 'custom-facebook-feed',
		'settings'  => admin_url('admin.php?page=cff-setup'),
	);

	// Smash Balloon (YouTube)
	$plugins['smash-balloon-youtube'] = array(
		'active'    => defined('SBYVER'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-sb-youtube.png',
		'title'     => 'Smash Balloon YouTube Feeds',
		'excerpt'   => __('Easily display YouTube videos on your WordPress site without writing any code. Comes with multiple layouts, ability to embed live streams, video filtering, ability to combine multiple channel videos, and more.', 'userfeedback-lite'),
		'installed' => array_key_exists('feeds-for-youtube/youtube-feed.php', $installed_plugins),
		'basename'  => 'feeds-for-youtube/youtube-feed.php',
		'slug'      => 'feeds-for-youtube',
		'settings'  => admin_url('admin.php?page=sby-feed-builder'),
	);

	// Smash Balloon (Twitter)
	$plugins['smash-balloon-twitter'] = array(
		'active'    => defined('CTF_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-sb-twitter.png',
		'title'     => 'Smash Balloon Twitter Feeds',
		'excerpt'   => __('Easily display Twitter content in WordPress without writing any code. Comes with multiple layouts, ability to combine multiple Twitter feeds, Twitter card support, tweet moderation, and more.', 'userfeedback-lite'),
		'installed' => array_key_exists('custom-twitter-feeds/custom-twitter-feed.php', $installed_plugins),
		'basename'  => 'custom-twitter-feeds/custom-twitter-feed.php',
		'slug'      => 'custom-twitter-feeds',
		'settings'  => admin_url('admin.php?page=ctf-feed-builder'),
	);

	// TrustPulse
	$plugins['trustpulse'] = array(
		'active'    => defined('TRUSTPULSE_PLUGIN_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-trustpulse.png',
		'title'     => 'TrustPulse',
		'excerpt'   => __('Boost your sales and conversions by up to 15% with real-time social proof notifications. TrustPulse helps you show live user activity and purchases to help convince other users to purchase.', 'userfeedback-lite'),
		'installed' => array_key_exists('trustpulse-api/trustpulse.php', $installed_plugins),
		'basename'  => 'trustpulse-api/trustpulse.php',
		'slug'      => 'trustpulse-api',
		'settings'  => admin_url('admin.php?page=trustpulse'),
	);

	// SearchWP
	$plugins['searchwp'] = array(
		'active'    => defined('SEARCHWP_LIVE_SEARCH_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-searchwp.png',
		'title'     => 'SearchWP',
		'excerpt'   => __('The most advanced WordPress search plugin. Customize your WordPress search algorithm, reorder search results, track search metrics, and everything you need to leverage search to grow your business.', 'userfeedback-lite'),
		'installed' => array_key_exists('searchwp-live-ajax-search/searchwp-live-ajax-search.php', $installed_plugins),
		'basename'  => 'searchwp-live-ajax-search/searchwp-live-ajax-search.php',
		'slug'      => 'searchwp-live-ajax-search',
		'settings'  => admin_url('admin.php?page=searchwp-live-search'),
	);

	// AffiliateWP
	$plugins['affiliatewp'] = array(
		'active'    => class_exists('AffiliateWP_Requirements_Check'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-affiliate-wp.png',
		'title'     => 'AffiliateWP',
		'excerpt'   => __('The #1 affiliate management plugin for WordPress. Easily create an affiliate program for your eCommerce store or membership site within minutes and start growing your sales with the power of referral marketing.', 'userfeedback-lite'),
		'installed' => array_key_exists('affiliate-wp/affiliate-wp.php', $installed_plugins),
		'basename'  => 'affiliate-wp/affiliate-wp.php',
		'slug'      => 'affiliate-wp',
		'settings'  => admin_url('admin.php?page=searchwp-live-search'),
		'redirect'  => 'https://affiliatewp.com',
	);

	// WP Simple Pay
	$plugins['wpsimplepay'] = array(
		'active'    => defined('SIMPLE_PAY_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-wp-simple-pay.png',
		'title'     => 'WP Simple Pay',
		'excerpt'   => __('The #1 Stripe payments plugin for WordPress. Start accepting one-time and recurring payments on your WordPress site without setting up a shopping cart. No code required.', 'userfeedback-lite'),
		'installed' => array_key_exists('stripe/stripe-checkout.php', $installed_plugins),
		'basename'  => 'stripe/stripe-checkout.php',
		'slug'      => 'stripe',
		'settings'  => admin_url('edit.php?post_type=simple-pay&page=simpay_settings'),
	);

	// Sugar Calendar
	$plugins['sugarcalendar'] = array(
		'active'    => class_exists('Sugar_Calendar\\Requirements_Check'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-sugar-calendar.png',
		'title'     => 'Sugar Calendar',
		'excerpt'   => __('A simple & powerful event calendar plugin for WordPress that comes with all the event management features including payments, scheduling, timezones, ticketing, recurring events, and more.', 'userfeedback-lite'),
		'installed' => array_key_exists('sugar-calendar-lite/sugar-calendar-lite.php', $installed_plugins),
		'basename'  => 'sugar-calendar-lite/sugar-calendar-lite.php',
		'slug'      => 'sugar-calendar-lite',
		'settings'  => admin_url('admin.php?page=sugar-calendar'),
	);

	// Charitable
	$plugins['charitable'] = array(
		'active'    => class_exists( 'Charitable' ),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-charitable.png',
		'title'     => 'Charitable',
		'excerpt'   => __('Top-rated WordPress donation and fundraising plugin. Over 10,000+ non-profit organizations and website owners use Charitable to create fundraising campaigns and raise more money online.', 'userfeedback-lite'),
		'installed' => array_key_exists('charitable/charitable.php', $installed_plugins),
		'basename'  => 'charitable/charitable.php',
		'slug'      => 'charitable',
		'settings'  => admin_url('admin.php?page=charitable'),
	);

	// WPCode
	$plugins['wpcode'] = array(
		'active'    => function_exists( 'WPCode' ),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-wpcode.png',
		'title'     => 'WPCode',
		'excerpt'   => __('Future proof your WordPress customizations with the most popular code snippet management plugin for WordPress. Trusted by over 1,500,000+ websites for easily adding code to WordPress right from the admin area.', 'userfeedback-lite'),
		'installed' => array_key_exists('insert-headers-and-footers/ihaf.php', $installed_plugins),
		'basename'  => 'insert-headers-and-footers/ihaf.php',
		'slug'      => 'insert-headers-and-footers',
		'settings'  => admin_url('admin.php?page=wpcode-settings'),
	);

	// Duplicator
	$plugins['duplicator'] = array(
		'active'    => defined( 'DUPLICATOR_VERSION' ),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-duplicator.png',
		'title'     => 'Duplicator',
		'excerpt'   => __('Leading WordPress backup & site migration plugin. Over 1,500,000+ smart website owners use Duplicator to make reliable and secure WordPress backups to protect their websites. It also makes website migration really easy.', 'userfeedback-lite'),
		'installed' => array_key_exists('duplicator/duplicator.php', $installed_plugins),
		'basename'  => 'duplicator/duplicator.php',
		'slug'      => 'duplicator',
		'settings'  => admin_url('admin.php?page=duplicator-settings'),
	);

	// PushEngage
	$plugins['pushengage'] = array(
		'active'    => defined('PUSHENGAGE_VERSION'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-pushengage.png',
		'title'     => 'PushEngage',
		'excerpt'   => __('Connect with your visitors after they leave your website with the leading web push notification software. Over 10,000+ businesses worldwide use PushEngage to send 15 billion notifications each month.', 'userfeedback-lite'),
		'installed' => array_key_exists('pushengage/main.php', $installed_plugins),
		'basename'  => 'pushengage/main.php',
		'slug'      => 'pushengage',
	);

	// Uncanny Automator
	$plugins['uncanny-automator'] = array(
		'active'    => function_exists('automator_get_recipe_id'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-uncanny-automator.png',
		'title'     => 'Uncanny Automator',
		'excerpt'   => __('Automate everything with the #1 no-code Automation tool for WordPress.', 'userfeedback-lite'),
		'installed' => array_key_exists('uncanny-automator/uncanny-automator.php', $installed_plugins),
		'basename'  => 'uncanny-automator/uncanny-automator.php',
		'slug'      => 'uncanny-automator',
		'setup_complete'      => (bool) get_option('automator_reporting', false),
	);

	// Microsoft Clarity
	$plugins['microsoft-clarity'] = array(
		'active'    => function_exists('clarity_on_activation'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/microsoft-clarity.png',
		'title'     => 'Microsoft Clarity',
		'excerpt'   => __('See session recordings and advanced segmentation to improve your website’s performance. Works automatically with UserFeedback.', 'userfeedback-lite'),
		'installed' => array_key_exists('microsoft-clarity/clarity.php', $installed_plugins),
		'basename'  => 'microsoft-clarity/clarity.php',
		'slug'      => 'microsoft-clarity',
	);

	// Envira Gallery
	$plugins['envira-gallery-lite'] = array(
		'active'    => function_exists('envira_gallery'),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/plugin-envira-gallery-leaf.png',
		'title'     => 'Envira Gallery',
		'excerpt'   => __('Create stunning, fast galleries to display your photos, videos, and more on your website. Works great with UserFeedback.', 'userfeedback-lite'),
		'installed' => array_key_exists('envira-gallery-lite/envira-gallery-lite.php', $installed_plugins),
		'basename'  => 'envira-gallery-lite/envira-gallery-lite.php',
		'slug'      => 'envira-gallery-lite',
	);

	// Pretty Links
	$plugins['pretty-link'] = array(
		'active'    => class_exists( 'PrliBaseController' ),
		'icon'      => plugin_dir_url(USERFEEDBACK_PLUGIN_FILE) . 'assets/img/plugins/icon-prettylinks.svg',
		'title'     => 'Pretty Links',
		'excerpt'   => __('Automatically monetize your website content with affiliate links added automatically to your content.', 'userfeedback-lite'),
		'installed' => array_key_exists('pretty-link/pretty-link.php', $installed_plugins),
		'basename'  => 'pretty-link/pretty-link.php',
		'slug'      => 'pretty-link',
	);

	wp_send_json($plugins);
}
add_action('wp_ajax_userfeedback_get_plugins', 'userfeedback_get_plugins');
