<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('UserFeedback_Metabox')) {
    class UserFeedback_Metabox
    {
        public function __construct()
        {
            add_action('init', [$this, 'register_meta']);

            if (!is_admin()) {
                return;
            }

            add_action('load-post.php', [$this, 'meta_box_init']);
            add_action('load-post-new.php', [$this, 'meta_box_init']);
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

        public function meta_box_init()
        {
            $post_type = $this->get_current_post_type();
            if (!is_post_type_viewable($post_type)) {
                return;
            }

            add_action('admin_enqueue_scripts', array($this, 'load_metabox_styles'));
            if ($this->is_gutenberg_editor() && $this->posttype_supports_gutenberg()) {
                return;
            }
            add_action('add_meta_boxes', [$this, 'create_meta_box']);
        }

        public function load_metabox_styles()
        {
            wp_register_style('userfeedback-admin-metabox-style', plugins_url('assets/css/uf-metabox.css', USERFEEDBACK_PLUGIN_FILE), array(), userfeedback_get_asset_version());
            wp_enqueue_style('userfeedback-admin-metabox-style');
        }


        private function posttype_supports_gutenberg()
        {
            return post_type_supports(userfeedback_get_current_post_type(), 'custom-fields');
        }

        private function get_current_post_type()
        {
            global $post;

            if ($post && $post->post_type) {
                return $post->post_type;
            }

            global $typenow;

            if ($typenow) {
                return $typenow;
            }

            global $current_screen;

            if ($current_screen && $current_screen->post_type) {
                return $current_screen->post_type;
            }

            if (isset($_REQUEST['post_type'])) {
                return sanitize_key($_REQUEST['post_type']);
            }

            return null;
        }

        private function is_gutenberg_editor()
        {
            if (function_exists('is_gutenberg_page') && is_gutenberg_page()) {
                return true;
            }

            $current_screen = get_current_screen();
            if (method_exists($current_screen, 'is_block_editor') && $current_screen->is_block_editor()) {
                return true;
            }

            return false;
        }

        public function create_meta_box()
        {
            add_meta_box(
                'userfeedback-metabox',
                'UserFeedback',
                [$this, 'print_metabox_html'],
                null,
                'side',
                'high'
            );
        }

        public function save_custom_fields($current_post_id)
		{
            if (!isset($_POST['userfeedback_metabox_nonce']) || !wp_verify_nonce($_POST['userfeedback_metabox_nonce'], 'userfeedback_metabox')) {
                return;
            }
            if(isset($_POST['_uf_disable_surveys'])){
                update_post_meta($current_post_id, '_uf_disable_surveys', sanitize_text_field($_POST['_uf_disable_surveys']));
            } else {
                update_post_meta($current_post_id, '_uf_disable_surveys', false);
            }
            if(isset($_POST['_uf_show_specific_survey'])){
                update_post_meta($current_post_id, '_uf_show_specific_survey', sanitize_text_field($_POST['_uf_show_specific_survey']));
            }
            
            
		}

        public function print_metabox_html($post)
        {
            $disable_surveys = (bool) get_post_meta($post->ID, '_uf_disable_surveys', true);
            $specific_survey = get_post_meta($post->ID, '_uf_show_specific_survey', true);
            wp_nonce_field('userfeedback_metabox', 'userfeedback_metabox_nonce');

            $addons = userfeedback_is_pro_version() ? userfeedback_get_parsed_addons() : [];

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

?>
            <!-- disable all surveys -->
            <div class="userfeedback-metabox" id="userfeedback-metabox-disable-surveys">
                <div class="userfeedback-metabox-input-checkbox">
                    <label class="">
                        <input type="checkbox" name="_uf_disable_surveys" value="1" <?php checked($disable_surveys); ?> <?php disabled(!userfeedback_is_pro_version()); ?>>
                        <span class="userfeedback-metabox-input-checkbox-label"><?php _e('Disable All UserFeedback Surveys', 'userfeedback-lite'); ?></span>
                    </label>
                </div>
                <div class="userfeedback-metabox-helper">
                    <?php _e('Toggle to disable all surveys on this page.', 'userfeedback-lite'); ?>
                </div>
            </div>

            <?php if (!userfeedback_is_pro_version()) { ?>
                <div class="userfeedback-metabox-pro-badge">
                    <span>
                        <svg width="15" height="14" viewBox="0 0 15 14" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6.57617 1.08203L4.92578 4.45898L1.19336 4.99219C0.533203 5.09375 0.279297 5.90625 0.761719 6.38867L3.42773 9.00391L2.79297 12.6855C2.69141 13.3457 3.40234 13.8535 3.98633 13.5488L7.3125 11.7969L10.6133 13.5488C11.1973 13.8535 11.9082 13.3457 11.8066 12.6855L11.1719 9.00391L13.8379 6.38867C14.3203 5.90625 14.0664 5.09375 13.4062 4.99219L9.69922 4.45898L8.02344 1.08203C7.74414 0.498047 6.88086 0.472656 6.57617 1.08203Z" fill="#31862D" />
                        </svg>
                        <?php _e('This is a PRO feature.', 'userfeedback-lite'); ?>
                    </span>
                    <div class="userfeedback-metabox-pro-badge-upgrade">
                        <a href="<?php echo userfeedback_get_upgrade_link('disable-all-surveys', 'lite-metabox', "https://www.userfeedback.com/lite/"); 
                                    ?>" target="_blank" rel="noopener">
                            <?php _e('Upgrade', 'userfeedback-lite'); ?>
                        </a>
                    </div>
                </div>
            <?php } ?>

            <!-- show specific survey -->
            <div class="userfeedback-metabox" id="userfeedback-metabox-specific-survey">
                <div class="userfeedback-metabox-input-select">
                    <label class="">
                        <div class="userfeedback-metabox-label"><?php _e('Show Specific Survey', 'userfeedback-lite') ?></div>
                        <select name="_uf_show_specific_survey" <?php disabled(!userfeedback_is_pro_version() || !$addons['targeting']->active); ?>>
                            <?php
                            foreach ($survey_options as $survey) {
                                $selected = $specific_survey == $survey['value'] ? 'selected' : '';
                                echo '<option value="' . esc_attr($survey['value']) . '" ' . $selected . '>' . esc_html($survey['label']) . '</option>';
                            }
                            ?>
                        </select>
                    </label>
                </div>
                <div class="userfeedback-metabox-helper">
                    <?php _e('Toggle to disable all surveys on this page.', 'userfeedback-lite'); ?>
                </div>
            </div>

            <?php if ( ! userfeedback_is_pro_version() ) { ?>
				<div class="userfeedback-metabox-pro-badge">
                        <span>
                            <svg width="15" height="14" viewBox="0 0 15 14" fill="none"
								 xmlns="http://www.w3.org/2000/svg">
                            <path
								d="M6.57617 1.08203L4.92578 4.45898L1.19336 4.99219C0.533203 5.09375 0.279297 5.90625 0.761719 6.38867L3.42773 9.00391L2.79297 12.6855C2.69141 13.3457 3.40234 13.8535 3.98633 13.5488L7.3125 11.7969L10.6133 13.5488C11.1973 13.8535 11.9082 13.3457 11.8066 12.6855L11.1719 9.00391L13.8379 6.38867C14.3203 5.90625 14.0664 5.09375 13.4062 4.99219L9.69922 4.45898L8.02344 1.08203C7.74414 0.498047 6.88086 0.472656 6.57617 1.08203Z"
								fill="#31862D"/>
                            </svg>
                            <?php _e( 'Page Targeting is a Pro feature.', 'userfeedback-lite' ); ?>
                        </span>
					<div class="userfeedback-metabox-pro-badge-upgrade">
						<a href="<?php echo userfeedback_get_upgrade_link( 'show-specific-survey', 'lite-metabox', "https://www.userfeedback.com/lite/" ); ?>"
						   target="_blank" rel="noopener">
							<?php _e( 'Upgrade', 'userfeedback-lite' ); ?>
						</a>
					</div>
				</div>
			<?php } ?>
            
            <?php if ( userfeedback_is_pro_version() && isset($addons['targeting']) && !$addons['targeting']->active ) { 
                ?>
				<div class="userfeedback-metabox-pro-badge">
                        <span>
                            <svg width="15" height="14" viewBox="0 0 15 14" fill="none"
								 xmlns="http://www.w3.org/2000/svg">
                            <path
								d="M6.57617 1.08203L4.92578 4.45898L1.19336 4.99219C0.533203 5.09375 0.279297 5.90625 0.761719 6.38867L3.42773 9.00391L2.79297 12.6855C2.69141 13.3457 3.40234 13.8535 3.98633 13.5488L7.3125 11.7969L10.6133 13.5488C11.1973 13.8535 11.9082 13.3457 11.8066 12.6855L11.1719 9.00391L13.8379 6.38867C14.3203 5.90625 14.0664 5.09375 13.4062 4.99219L9.69922 4.45898L8.02344 1.08203C7.74414 0.498047 6.88086 0.472656 6.57617 1.08203Z"
								fill="#31862D"/>
                            </svg>
                            <?php _e( 'Activate Targeting Addon.', 'userfeedback-lite' ); ?>
                        </span>
					<div class="userfeedback-metabox-pro-badge-upgrade">
						<a href="<?php echo esc_url(admin_url('admin.php??page=userfeedback_addons')); ?>"
						   target="_blank" rel="noopener">
							<?php _e( 'Activate', 'userfeedback-lite' ); ?>
						</a>
					</div>
				</div>
			<?php } ?>

<?php
        }
    }
    new UserFeedback_Metabox();
}
