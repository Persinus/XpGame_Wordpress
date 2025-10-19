<?php
/**
 * NewsBlogger functions and definitions
 *
 * @package NewsBlogger
 */

// Global variables define
define('NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI', get_template_directory_uri());
define('NEWSBLOGGER_TEMPLATE_DIR_URI', get_stylesheet_directory_uri());
define('NEWSBLOGGER_CHILD_TEMPLATE_DIR', trailingslashit(get_stylesheet_directory()));

// wp_body_open function definition
if ( ! function_exists( 'wp_body_open' ) ) {

    function wp_body_open() {
        /**
         * Triggered after the opening <body> tag.
         */
        do_action( 'wp_body_open' );
    }
}

/**
 * Load all core theme function files
*/
require NEWSBLOGGER_CHILD_TEMPLATE_DIR . '/inc/theme-color/custom-color.php';
function newsblogger_load_customizer_image_radio_control() {
    // Check if the class doesn't already exist
    if ( ! class_exists( 'NewsBlogger_Img_Radio_Control' ) ) {
        require_once get_template_directory() . '/inc/customizer/custom-controls/customizer-image-radio/customizer-image-radio.php';
    }
}
// Hook this function to `after_setup_theme` to ensure it loads at the correct time
add_action( 'after_setup_theme', 'newsblogger_load_customizer_image_radio_control' );

/* Enqueue Style & Scipts */
add_action('wp_enqueue_scripts', 'newsblogger_enqueue_styles');
function newsblogger_enqueue_styles() {
    // enqueue styles
    wp_enqueue_style('newsblogger-dark-style', NEWSBLOGGER_TEMPLATE_DIR_URI . '/assets/css/dark.css');
    if ( ! function_exists( 'spncp_activate' )) :
        if (get_theme_mod('custom_color_enable') == true) {
                add_action('wp_footer','newsblogger_custom_color_css');
        }
        else {
            wp_enqueue_style('newsblogger-default-style', NEWSBLOGGER_TEMPLATE_DIR_URI . '/assets/css/default.css');
        }
    endif;
    wp_enqueue_style('newsblogger-parent-style', NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/style.css' );
    wp_style_add_data('newsblogger-parent-style', 'rtl', 'replace' );
    wp_style_add_data('newsblogger-style', 'rtl', 'replace' );

    // enqueue scripts
    if ( ! function_exists( 'spncp_activate' ) ):
        if(get_theme_mod('hide_show_missed_section',true) == true){
            wp_enqueue_script('newsblogger-custom', NEWSBLOGGER_TEMPLATE_DIR_URI . '/assets/js/missed-custom.js', array('jquery'), '',true);
        }
    endif;
}

/* After theme setup */
add_action('after_setup_theme', 'newsblogger_agency_setup');
function newsblogger_agency_setup() {

    // Add default posts and comments RSS feed links to head.
    add_theme_support( 'automatic-feed-links' );
    // Add theme supports.
    add_theme_support( 'title-tag' );
    add_theme_support( "align-wide" );
    add_editor_style();
    add_theme_support( 'responsive-embeds' );
    add_theme_support( 'wp-block-styles' );
    add_theme_support( 'register_block_style' );
    add_theme_support( 'register_block_pattern' );
    /*
    * Enable support for Post Thumbnails on posts and pages.
    */add_theme_support( 'post-thumbnails' );
    // Add theme support for HTML5.
    add_theme_support( 'html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
    ) );

    // Theme translation
    load_theme_textdomain( 'newsblogger', NEWSBLOGGER_CHILD_TEMPLATE_DIR . '/languages' );

    if( !class_exists('Newscrunch_Plus') ) {
        //About Theme         
        $newsblogger_theme = wp_get_theme();   
        if ('NewsBlogger' == $newsblogger_theme->name ) {
            if(is_admin()) {
                require NEWSBLOGGER_CHILD_TEMPLATE_DIR . '/admin/admin-init.php';
            }
        }
    }
}

/*
 * Add Body Class
 */
add_filter( 'body_class', 'newsblogger_body_class' );
function newsblogger_body_class( $classes ) {
        $classes[] = 'newsblogger';
        $classes[] = 'nchild';
    return $classes;
}

$newsblogger_theme = wp_get_theme();  

// Notice to add required plugin
if(!class_exists('Newscrunch_Plus')){
    if('NewsBlogger' == $newsblogger_theme->name) :
        function newsblogger_admin_plugin_notice_warn() {
            $theme_name=wp_get_theme();
            if ( get_option( 'dismissed-newsblogger_comanion_plugin', false ) ) {
               return;
            }

            $dismissed = get_user_meta(get_current_user_id(), 'nb_welcome_admin_notice_dismissed', true);
    
            if ($dismissed) {
                return;
            } ?>

            <div class="updated notice is-dismissible newscrunch-theme-notice">
                <div class="dashboard-hero-panel">
                    <div class="hero-panel-content">
                        <div class="hero-panel-subtitle">
                            <?php esc_html_e('Hello', 'newsblogger'); 
                            echo ', '; 
                            $current_user = wp_get_current_user();
                            echo esc_html($current_user->display_name);
                            ?>
                        </div>
                        <div class="hero-panel-title">
                            <?php 
                            /* translators: %s: theme name */
                            printf(esc_html__('Welcome to', 'newsblogger') . ' %s', $theme_name ); ?>
                        </div>
                        <div class="hero-panel-description">
                            <?php 
                            /* translators: %s: theme name */
                            printf(esc_html__("%s is now installed and ready to use. We've provide some links to get you started.", 'newsblogger'), $theme_name ); ?>
                        </div>
                        <div class="theme-admin-button-wrap theme-admin-button-group">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=newsblogger-welcome')); ?>" class="button theme-admin-button admin-button-secondary" target="_self" title="<?php esc_attr_e('Theme Dashboard', 'newsblogger'); ?>">
                                    <span class="dashicons dashicons-dashboard"></span>
                                    <span><?php esc_html_e('Theme Dashboard', 'newsblogger'); ?></span>
                            </a>
                            <a href="<?php echo esc_url('https://spicethemes.com/newsblogger-wordpress-theme/#newsblogger_demo_lite'); ?>" class="button theme-admin-button admin-button-secondary" target="_blank" title="<?php esc_attr_e('Live Demo', 'newsblogger'); ?>">
                                <span class="dashicons dashicons-welcome-view-site"></span>
                                <span><?php esc_html_e('View Live Demos', 'newsblogger'); ?></span>
                            </a>
                            <a href="<?php echo esc_url('https://helpdoc.spicethemes.com/category/newscrunch/'); ?>" class="button theme-admin-button admin-button-secondary" target="_blank" title="<?php esc_attr_e('Help Docs', 'newsblogger'); ?>">
                                <span class="dashicons dashicons-media-document"></span>
                                <span><?php esc_html_e('Theme Documentation', 'newsblogger'); ?></span>
                            </a>            
                            <?php if(!class_exists('Spice_Starter_Sites')){?>
                                <button id="install-plugin-button" data-plugin-url="<?php echo esc_url( 'https://spicethemes.com/extensions/spice-starter-sites.zip' ); ?>">
                                    <?php echo esc_html__( 'Install Plugin', 'newsblogger' ); ?>
                                </button>
                           <?php }?>
                        </div>
                    </div>
                    <div class="hero-panel-image">
                        <img src="<?php echo esc_url(get_theme_file_uri().'/admin/assets/img/welcome-banner.png');?>" alt="<?php esc_attr_e('Welcome Banner','newsblogger'); ?>">
                    </div>
                </div>
                <p><a href="#" class="dismiss-welcome-notice"><?php _e('Dismiss this notice', 'newsblogger'); ?></a></p>
            </div>
            
            <script type="text/javascript">
                jQuery(function($) {
                $( document ).on( 'click', '.newscrunch-theme-notice .notice-dismiss', function () {
                    var type = $( this ).closest( '.newscrunch-theme-notice' ).data( 'notice' );
                    $.ajax( ajaxurl,
                      {
                        type: 'POST',
                        data: {
                          action: 'dismissed_notice_handler',
                          type: type,
                        }
                      } );
                  } );
              });
            </script>

            <script>
                jQuery(document).ready(function($) {
                    $('.dismiss-welcome-notice').on('click', function(e) {
                        e.preventDefault();
                        $('.newscrunch-theme-notice').fadeOut();
                        $.post(ajaxurl, {
                            action: 'dismiss_nb_welcome_admin_notice',
                            security: '<?php echo wp_create_nonce("dismiss_nb_welcome_admin_notice_nonce"); ?>'
                        });
                    });
                });
            </script>
        <?php  }

        function newsblogger_dismiss_welcome_admin_notice() {
            check_ajax_referer('dismiss_nb_welcome_admin_notice_nonce', 'security');
            update_user_meta(get_current_user_id(), 'nb_welcome_admin_notice_dismissed', true);
            wp_die();
        }
        add_action('wp_ajax_dismiss_nb_welcome_admin_notice', 'newsblogger_dismiss_welcome_admin_notice');

        function newsblogger_add_update_admin_notice() {
            $theme = wp_get_theme(); 
            $dismissed = get_user_meta(get_current_user_id(), 'nb_update_admin_notice_dismissed', true);
    
            if ($dismissed) {
                return;
            } ?>
            <div class="newscrunch-update-notice notice notice-info is-dismissible">
                <div class="notice-content-wrap">
                    <div class="notice-content">
                        <h2><?php printf( esc_html__('%1$s Current %2$s', 'newsblogger'), esc_html($theme->name), '<span>Version' . ' ' . esc_html($theme->get('Version')) . '</span>'); ?></h2>
                        
                        <p class="notice-des">
                            <?php printf( '%1$s %2$s %3$s', esc_html__("We've consistently aimed to meet our users' needs and demands. In order to address specific requirements and rectify issues from our previous version, we've rolled out version","newsblogger"), esc_html($theme->get('Version')), esc_html__('complete with exciting new features. Take a look now!','newsblogger')); ?>
                        </p>

                        <ol class="admin-notice-up-list">
                            <li><?php echo "Added custom video block support for Featured Video Section in PRO."; ?></li>
                            <li><?php echo "Fixed some typography issues."; ?></li>
                        </ol>

                        <div class="admin-notice-up-btn-wrap">
                            <a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" class="button theme-admin-button admin-button-secondary" target="_blank" title="<?php esc_attr_e('Try Now', 'newsblogger'); ?>">
                                <span class="dashicons dashicons-admin-customizer"></span>
                                <span><?php esc_html_e('Try It Now', 'newsblogger'); ?></span>
                            </a>

                            <a href="<?php echo esc_url('https://spicethemes.com/newsblogger-changelog/'); ?>" class="button theme-admin-button admin-button-secondary" target="_blank" title="<?php esc_attr_e('Changelog', 'newsblogger'); ?>">
                                <span class="dashicons dashicons-visibility"></span>
                                <span><?php esc_html_e('See Changelog', 'newsblogger'); ?></span>
                            </a>

                            <a href="<?php echo esc_url('https://youtube.com/playlist?list=PLTfjrb24Pq_DeJOZdKEaP3rZPbHuOCLtZ&si=rsDRjg6uD5J_LFkv'); ?>" class="button theme-admin-button admin-button-secondary" target="_blank" title="<?php esc_attr_e('Watch Videos', 'newsblogger'); ?>">
                                <span class="dashicons dashicons-youtube"></span>
                                <span><?php esc_html_e('Watch Videos', 'newsblogger'); ?></span>
                            </a>

                            <a href="<?php echo esc_url('https://spicethemes.com/newscrunch/'); ?>" class="button theme-admin-button admin-button-secondary" target="_blank" title="<?php esc_attr_e('Upgrade To Pro', 'newsblogger'); ?>">
                                <span class="dashicons dashicons-update"></span>
                                <span><?php esc_html_e('Upgrade To Pro', 'newsblogger'); ?></span> 
                            </a>

                        </div>
                    </div>
                </div>
                <p><a href="#" class="dismiss-update-notice"><?php _e('Dismiss this notice', 'newsblogger'); ?></a></p>
            </div>

            <script>
                jQuery(document).ready(function($) {
                    $('.dismiss-update-notice').on('click', function(e) {
                        e.preventDefault();
                        $('.newscrunch-update-notice').fadeOut();
                        $.post(ajaxurl, {
                            action: 'dismiss_nb_update_admin_notice',
                            security: '<?php echo wp_create_nonce("dismiss_nb_update_admin_notice_nonce"); ?>'
                        });
                    });
                });
            </script>
        <?php
        }

        function newsblogger_dismiss_update_admin_notice() {
            check_ajax_referer('dismiss_nb_update_admin_notice_nonce', 'security');
            update_user_meta(get_current_user_id(), 'nb_update_admin_notice_dismissed', true);
            wp_die();
        }
        add_action('wp_ajax_dismiss_nb_update_admin_notice', 'newsblogger_dismiss_update_admin_notice');

        global $pagenow;
        if ( "themes.php" == $pagenow && is_admin() ) {
            add_action('admin_notices', 'newsblogger_admin_plugin_notice_warn' );
            add_action('admin_notices', 'newsblogger_add_update_admin_notice');
            add_action('wp_ajax_dismissed_notice_handler', 'newsblogger_ajax_notice_handler');
        }
    endif;
}

/**
 * Admin Enqueue scripts and styles.
 */
function newsblogger_notice_style() {
    wp_enqueue_style('newsblogger-admin-style', NEWSBLOGGER_TEMPLATE_DIR_URI . '/assets/css/admin.css');
    wp_enqueue_style('newsblogger-parent-admin-style', NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/assets/css/admin.css');    
}
add_action('admin_enqueue_scripts','newsblogger_notice_style');


function newsblogger_theme_header_panel_customizer( $wp_customize ) {
    // header layouts
    $wp_customize->add_setting( 'header_layout',
        array(
            'default'           => '9',
            'sanitize_callback' => 'newscrunch_sanitize_select'
        )
    );

    $wp_customize->add_control( new NewsBlogger_Image_Radio_Button_Custom_Control( $wp_customize, 'header_layout',
        array(
            'label'         =>  esc_html__( 'Header Layout', 'newsblogger'  ),
            'priority'      =>  1,
            'section'       =>  'newscrunch_theme_header',
            'choices'       =>  array(
                'default'   => array( 
                    'image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/assets/img/default.jpg',
                ),
                'full'  => array(
                    'image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/assets/img/standard.jpg',
                ),
                'center'    => array(
                    'image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/assets/img/center.jpg',
                ),
                '2'    => array(
                    'image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI. '/inc/customizer/assets/img/header2.jpg',
                ),
                '9'    => array(
                    'image' => get_stylesheet_directory_uri() . '/inc/customizer/assets/img/classic-center.jpg',
                )
            )
        )
    ) );

    // Custom color
    $wp_customize->add_setting('link_color', 
        array(
            'capability'        => 'edit_theme_options',
            'default'           => '#369ef6',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    // menu hover color
    $wp_customize->add_setting('bfooter_menu_hover_color', 
        array(
            'default'           => '#369ef6',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    // copyright text color
    $wp_customize->add_setting('copyright_text_color', 
        array(
            'default'           => '#ffffff',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    // copyright link color
    $wp_customize->add_setting('copyright_link_color', 
        array(
            'default'           => '#369ef6',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    // copyright link hover color
    $wp_customize->add_setting('copyright_link_hover_color', 
        array(
            'default'           => '#369ef6',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    // Topbar date color for dark layout
    $wp_customize->add_setting('date_dcolor', 
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );
    // Topbar time color for dark layout
    $wp_customize->add_setting('time_dcolor', 
        array(
            'default'           => '#fff',
            'sanitize_callback' => 'sanitize_hex_color'
        )
    );

    if ( ! class_exists('Newscrunch_Plus') ):
        $wp_customize->remove_section('newscrunch_featured_video_section');
        $wp_customize->remove_control('bredcrumb_page_title');
        $wp_customize->remove_control('enable_page_title');
        $wp_customize->remove_control('bredcrumb_position');
        $wp_customize->remove_control('bredcrumb_markup');
        $wp_customize->remove_control('breadcrumb_section_padding');
        $wp_customize->remove_control('enable_breadcrumb');

        /* Breadcrumb Alignment */
        $wp_customize->add_setting( 'bredcrumb_alignment',
            array(
                'default'           => 'left',
                'capability'        => 'edit_theme_options',
                'sanitize_callback' => 'newscrunch_sanitize_select'
            )
        );
        $wp_customize->add_control( new NewsBlogger_Image_Radio_Button_Custom_Control( $wp_customize, 'bredcrumb_alignment',
            array(
                'label'     => esc_html__( 'Alignment', 'newsblogger'  ),
                'section'   => 'bredcrumb_section',
                'active_callback'   => 'newscrunch_breadcrumb_section_callback',
                'priority'  => 9,
                'choices'   => 
                array(
                    'centered' => array('image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/assets/img/style-2/breadcrumb-center.png'), 
                    'left' => array('image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/assets/img/style-2/breadcrumb-left.png'),
                    'right' => array('image' => NEWSBLOGGER_PARENT_TEMPLATE_DIR_URI . '/inc/customizer/assets/img/style-2/breadcrumb-right.png')          
                )
            )
        ));
    endif;
}
add_action( 'customize_register', 'newsblogger_theme_header_panel_customizer', 11 );

// Freemius snippet code
if('NewsBlogger' == $newsblogger_theme->name) {
    if ( ! function_exists( 'newb_fs' ) ) {
        if(class_exists('Spice_Starter_Sites') && defined( 'SPICE_STARTER_SITES_PLUGIN_PATH' ) && file_exists(SPICE_STARTER_SITES_PLUGIN_PATH . '/freemius/start.php')) {
            // Create a helper function for easy SDK access.
            function newb_fs() {
                global $newb_fs;

                if ( ! isset( $newb_fs ) ) {
                    // Include Freemius SDK.
                    require_once SPICE_STARTER_SITES_PLUGIN_PATH . '/freemius/start.php';

                    $newb_fs = fs_dynamic_init( array(
                        'id'                    =>    '15745',
                        'slug'                  =>    'newsblogger',
                        'premium_slug'          =>    'newscrunch-plus',
                        'type'                  =>    'theme',
                        'public_key'            =>    'pk_59bbbe777582efc514cb8131bdc11',
                        'is_premium'            =>    true,
                        'has_premium_version'   =>    false,
                        'has_addons'            =>    false,
                        'has_paid_plans'        =>    false,
                        'menu'                  =>    array(
                            'slug'      =>  'newsblogger-welcome',
                            'account'   =>  true,
                            'support'   =>  true,
                        )
                    ) );
                }

                return $newb_fs;
            }
            // Init Freemius.
            newb_fs();
            // Signal that SDK was initiated.
            do_action( 'newb_fs_loaded' ); 
        }
    }
}

// Hook the AJAX action for logged-in users
add_action('wp_ajax_newsblogger_check_plugin_status', 'newsblogger_check_plugin_status',11);

function newsblogger_check_plugin_status() {
    
    // Check if user is authorized (must be an admin)
    if (!current_user_can('install_plugins')) {
        wp_send_json_error(esc_html__('You do not have permission to manage plugins.', 'newsblogger'));
        return;
    }

    if (!isset($_POST['plugin_slug'])) {
       wp_send_json_error(esc_html__('No plugin slug provided.', 'newsblogger'));
        return;
    }

    $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
    $plugin_main_file = $plugin_slug . '/' . $plugin_slug . '.php'; // Adjust this based on your plugin structure

    // Check if the plugin exists
    $plugins = get_plugins();
    if (isset($plugins[$plugin_main_file])) {
        if (is_plugin_active($plugin_main_file)) {
            wp_send_json_success(array('status' => 'activated'));
        } else {
            wp_send_json_success(array('status' => 'installed'));
        }
    } else {
        wp_send_json_success(array('status' => 'not_installed'));
    }
}

// Existing AJAX installation function for installing and activating
add_action('wp_ajax_newsblogger_install_activate_plugin', 'newsblogger_install_and_activate_plugin',11);

function newsblogger_install_and_activate_plugin() {
    
    // Check if user is authorized (must be an admin)
    if (!current_user_can('install_plugins')) {
        wp_send_json_error(esc_html__('You do not have permission to install plugins.', 'newsblogger'));
        return;
    }

    if (!isset($_POST['plugin_url'])) {
         wp_send_json_error(esc_html__('No plugin URL provided.', 'newsblogger'));
        return;
    }

    // Include necessary WordPress files for plugin installation
    include_once(ABSPATH . 'wp-admin/includes/file.php');
    include_once(ABSPATH . 'wp-admin/includes/misc.php');
    include_once(ABSPATH . 'wp-admin/includes/class-wp-upgrader.php');
    include_once(ABSPATH . 'wp-admin/includes/plugin.php');

    $plugin_url = esc_url($_POST['plugin_url']);
    $plugin_slug = sanitize_text_field($_POST['plugin_slug']);
    $plugin_main_file = $plugin_slug . '/' . $plugin_slug . '.php'; // Ensure this matches your plugin structure

    WP_Filesystem();
    // Download the plugin file
    $temp_file = download_url($plugin_url);

    if (is_wp_error($temp_file)) {
        wp_send_json_error($temp_file->get_error_message());
        return;
    }

    // Unzip the plugin to the plugins folder
    $plugin_folder = WP_PLUGIN_DIR;
    $result = unzip_file($temp_file, $plugin_folder);
    
    // Clean up temporary file
    unlink($temp_file);

    if (is_wp_error($result)) {
        wp_send_json_error($result->get_error_message());
        return;
    }

    // Activate the plugin if it was installed
    $activate_result = activate_plugin($plugin_main_file);

    

    // Return success with redirect URL
    if ( class_exists('Newscrunch_Plus') ){
        wp_send_json_success(array('redirect_url' => admin_url('admin.php?page=newscrunch-plus-welcome')));
    }else{
        wp_send_json_success(array('redirect_url' => admin_url('admin.php?page=newsblogger-welcome')));
    }
}

// Enqueue JavaScript for the button functionality
add_action('admin_enqueue_scripts', 'newsblogger_enqueue_plugin_installer_script',11);

function newsblogger_enqueue_plugin_installer_script() {
    wp_dequeue_script( 'newscrunch-plugin-installer-js' );
    wp_enqueue_script('newsblogger-plugin-installer-js',  NEWSBLOGGER_TEMPLATE_DIR_URI . '/admin/assets/js/plugin-installer.js', array('jquery'), null, true);
    wp_localize_script('newsblogger-plugin-installer-js', 'pluginInstallerAjax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('plugin_installer_nonce')
    ));
}