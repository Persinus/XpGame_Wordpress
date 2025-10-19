<?php

/**
 * Metabox class.
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('UserFeedbackGutenbergMetabox')) {
	class UserFeedbackGutenbergMetabox
	{
		public function __construct()
		{
			add_action('init', [$this, 'register_meta']);
			add_action('save_post', [$this, 'save_custom_fields']);
		}

		public function register_meta()
		{
			register_post_meta(
				'',
				'_uf_show_specific_survey',
				[
					'auth_callback' => '__return_true',
					'default'       => 0,
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'number',
				]
			);
			register_post_meta(
				'',
				'_uf_disable_surveys',
				[
					'auth_callback' => '__return_true',
					'default'       => false,
					'show_in_rest'  => true,
					'single'        => true,
					'type'          => 'boolean',
				]
			);
		}

		public function save_custom_fields($current_post_id)
		{
		}
	}
	new UserFeedbackGutenbergMetabox();
}
