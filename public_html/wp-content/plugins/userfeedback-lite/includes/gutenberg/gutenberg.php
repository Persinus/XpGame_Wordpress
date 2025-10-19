<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('UserFeedbackGutenberg')) {
	class UserFeedbackGutenberg
	{
		public function __construct()
		{
            $this->require_files();

			add_action('enqueue_block_editor_assets', [$this, 'editor_assets']);
		}

		public function editor_assets()
		{
			global $wp_scripts;

			// stop loading gutenberg related assets/blocks/sidebars if WP version is less than 5.4
			if (!userfeedback_load_gutenberg_app()) {
				return;
			}

			if (function_exists('get_current_screen')) {
				$current_screen = get_current_screen();

				if (is_object($current_screen) && 'widgets' === $current_screen->id) {
					return;
				}
			}

			wp_enqueue_script( 'lodash', includes_url('js') . '/underscore.min.js' );
			$plugins_style_path = '/assets/gutenberg/css/editor.css';
			$version_path       = userfeedback_is_pro_version() ? 'pro' : 'lite';
			if ("pro" === $version_path) {
				$plugins_js_path    = '/assets/gutenberg/js/editor-pro.min.js';
			} else if ("lite" === $version_path) {
				$plugins_js_path    = '/assets/gutenberg/js/editor-lite.min.js';
			}

			$js_dependencies = array(
				'wp-plugins',
				'wp-element',
				'wp-i18n',
				'wp-api-request',
				'wp-data',
				'wp-hooks',
				'wp-plugins',
				'wp-components',
				'wp-blocks',
				'wp-block-editor',
				'wp-compose',
			);

			if (
				!$wp_scripts->query('wp-edit-widgets', 'enqueued') &&
				!$wp_scripts->query('wp-customize-widgets', 'enqueued')
			) {
				$js_dependencies[] = 'wp-editor';
				$js_dependencies[] = 'wp-edit-post';
			}

			// Enqueue our plugin JavaScript.
			wp_enqueue_script(
				'userfeedback-gutenberg-editor-js',
				plugins_url($plugins_js_path, USERFEEDBACK_PLUGIN_FILE),
				$js_dependencies,
				userfeedback_get_asset_version(),
				true
			);

			// Enqueue our plugin JavaScript.
			wp_enqueue_style(
				'userfeedback-gutenberg-editor-css',
				plugins_url($plugins_style_path, USERFEEDBACK_PLUGIN_FILE),
				array(),
				userfeedback_get_asset_version()
			);

			$posttype = userfeedback_get_current_post_type();

			$query = UserFeedback_Survey::where(
				array(
					array('status', '=', 'publish'), // Get only published and drafts by default
				)
			)->with_count(array('responses'));
			$surveys_result = $query->get();
			$survey_options = [
				[
					'value' => 0,
					'label' => __('None', 'userfeedback-lite'),
				]
			];
			$surveys = array_map(function ($survey) {
				return array(
					'value' => $survey->id,
					'label' => $survey->title,
				);
			}, $surveys_result);

			$survey_options = array_merge($survey_options, $surveys);

			// Localize script for sidebar plugins.
			wp_localize_script(
				'userfeedback-gutenberg-editor-js',
				'userfeedback_gutenberg_tool_vars',
				apply_filters('userfeedback_gutenberg_tool_vars', array(
					'ajaxurl'                      => admin_url('admin-ajax.php'),
					'nonce'                        => wp_create_nonce('userfeedback_gutenberg_headline_nonce'),
					'allowed_post_types'           => apply_filters('userfeedback_metabox_post_types', array('post')),
					'current_post_type'            => $posttype,
					'translations'                 => wp_get_jed_locale_data(userfeedback_is_pro_version() ? 'userfeedback-premium' : 'userfeedback-lite'),
					'vue_assets_path'              => plugins_url($version_path . '/assets/vue/', USERFEEDBACK_PLUGIN_FILE),
					'license_type'                 => (UserFeedback()->license->get_license_type()) ? 'pro' : 'lite',
					'supports_custom_fields'       => post_type_supports($posttype, 'custom-fields'),
					'public_post_type'             => $posttype ? is_post_type_viewable($posttype) : 0,
					'upgrade_url'                  => userfeedback_get_upgrade_link('lite-metabox', 'disable-all-surveys', 'https://www.userfeedback.com/lite/'),
					'addons_url'                   => admin_url('admin.php?page=userfeedback_addons'),
					'create_survey_link'           => admin_url('admin.php?page=userfeedback_surveys#/new/setup'),
					'all_surveys_link'             => admin_url('admin.php?page=userfeedback_surveys'),
					'isnetwork'                    => is_network_admin(),
					'addons'                       => ! userfeedback_is_pro_version() ? array() : userfeedback_get_parsed_addons(),
					'surveys'                      => $survey_options
				))
			);
		}

		public function require_files()
		{
			require_once plugin_dir_path(__FILE__) . 'metabox.php';
		}
	}
	new UserFeedbackGutenberg();
}
