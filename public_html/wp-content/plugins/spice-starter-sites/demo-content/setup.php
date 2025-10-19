<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * @package Spice Starter Sites
 * @since 1.0
 */


/**
 * Set import files
 */
if ( !function_exists( 'spice_starter_sites_import_files' ) ) {

    function spice_starter_sites_import_files() {

        // Demos url
        $demo_url = 'https://olivewp.org/owp/startersites/';

        return array(
            array(
                'import_file_name'              =>  esc_html__('Construction', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'construction/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'construction/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'construction/customize-export.dat',
                'preview_url'                   =>  'https://owp-construction.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/construction/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Architect', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'architect/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'architect/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'architect/customize-export.dat',
                'preview_url'                   =>  'https://owp-architect.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/architect/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Charity', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'charity/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'charity/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'charity/customize-export.dat',
                'preview_url'                   =>  'https://owp-charity.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/charity/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Professional Services', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'professional-services/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'professional-services/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'professional-services/customize-export.dat',
                'preview_url'                   =>  'https://owp-professional-services.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/professional-services/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Dental Care', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'dental-care/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'dental-care/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'dental-care/customize-export.dat',
                'preview_url'                   =>  'https://owp-dental-care.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/dental-care/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Digital Marketing', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'digital-marketing/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'digital-marketing/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'digital-marketing/customize-export.dat',
                'preview_url'                   =>  'https://owp-digital-marketing.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/digital-marketing/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Modern Agency', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'modern-agency/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'modern-agency/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'modern-agency/customize-export.dat',
                'preview_url'                   =>  'https://owp-modern-agency.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/modern-agency/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Office', 'spice-starter-sites'),
                'categories'                    =>  [ 'Free Demos' ],
                'import_file_url'               =>  $demo_url . 'office/sample-data.xml',
                'import_widget_file_url'        =>  $demo_url . 'office/widgets.wie',
                'import_customizer_file_url'    =>  $demo_url . 'office/customize-export.dat',
                'preview_url'                   =>  'https://owp-office.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/office/thumb.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Business', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-business.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/business/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Travel', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-travel.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/travel/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Spacare', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-spacare.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/spacare/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Interior', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-interior.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/interior/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Real Estate', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-realestate.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/realestate/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Skin Spa', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-skinspa.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/skinspa/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Healthcare', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-healthcare.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/healthcare/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Gymer', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-gymer.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/gymer/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Yoga', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-yoga.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/yoga/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Massage', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-massage.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/massagecenter/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Finance', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-finance.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/finance/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Industry', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-industry.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/industry/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Coffee Shop', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-coffee-shop.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/coffee-shop/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Insurance', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-insurance.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/insurance/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Creative Agency', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-creative-agency.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/creative-agency/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Adventure', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-adventure.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/adventure/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Carpenter', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-carpenter.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/carpenter/thumb-pro.png',
            ),
            array(
                'import_file_name'              =>  esc_html__('Startup Agency', 'spice-starter-sites'),
                'categories'                    =>  [ 'Premium Demos' ],
                'preview_url'                   =>  'https://owp-startup-agency.olivewp.org/',
                'import_preview_image_url'      =>  $demo_url . 'thumbnail/startup-agency/thumb-pro.png',
            )
        );
    }

}
add_filter( 'pt-ocdi/import_files', 'spice_starter_sites_import_files' );

/**
 * Define actions that happen after import
 */
if ( !function_exists( 'spice_starter_sites_after_import_mods' ) ) {

    function spice_starter_sites_after_import_mods() {

        //Assign the menu
        $main_menu = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
        set_theme_mod( 'nav_menu_locations', array(
                'primary' => $main_menu->term_id,
            )
        );

        //Asign the static front page and the blog page
        $front_page_query = new WP_Query(array(
            'title' => 'Home',
            'post_type' => 'page',
            'posts_per_page' => 1,
        ));
        $blog_page_query = new WP_Query(array(
            'title' => 'Blog',
            'post_type' => 'page',
            'posts_per_page' => 1,
        ));

        // Check if the queries returned any pages
        $front_page = $front_page_query->have_posts() ? $front_page_query->posts[0] : null;
        $blog_page  = $blog_page_query->have_posts() ? $blog_page_query->posts[0] : null;

        // Ensure that both pages were found before updating options
        if ($front_page) {
            update_option('show_on_front', 'page');
            update_option('page_on_front', $front_page->ID);
        }

        if ($blog_page) {
            update_option('page_for_posts', $blog_page->ID);
        }

    }

}
add_action( 'pt-ocdi/after_import', 'spice_starter_sites_after_import_mods' );

// Custom CSS for OCDI plugin
function spice_starter_sites_ocdi_css() { ?>
    <style >
        .ocdi__gl-item:nth-child(n+9) .ocdi__gl-item-buttons .button-primary, .ocdi .ocdi__theme-about, .ocdi__intro-text {
          display: none;
        }
    </style>
<?php }
add_action('admin_enqueue_scripts', 'spice_starter_sites_ocdi_css');

// Change the "One Click Demo Import" name from "Starter Sites" in Appearance menu
function spice_starter_sites_ocdi_plugin_page_setup( $default_settings ) {

    $default_settings['parent_slug'] = 'themes.php';
    $default_settings['page_title']  = esc_html__( 'One Click Demo Import' , 'spice-starter-sites' );
    $default_settings['menu_title']  = esc_html__( 'Starter Sites' , 'spice-starter-sites' );
    $default_settings['capability']  = 'import';
    $default_settings['menu_slug']   = 'one-click-demo-import';

    return $default_settings;

}
add_filter( 'ocdi/plugin_page_setup', 'spice_starter_sites_ocdi_plugin_page_setup' );

// Register required plugins for the demo's
function spice_starter_sites_register_plugins( $plugins ) {

    // List of plugins used by all theme demos.
    $theme_plugins = [
        [   
            'name'     =>   'Elementor', 
            'slug'     =>   'elementor',
            'required' =>   true,
        ],
        [ 
            'name'     =>   'Spice Post Slider',
            'slug'     =>   'spice-post-slider',
            'required' =>   true,
        ],
        [ 
            'name'     =>   'Contact Form 7',
            'slug'     =>   'contact-form-7',
            'required' =>   true,
        ],
        [ 
            'name'     =>   'Yoast SEO',
            'slug'     =>   'wordpress-seo',
            'required' =>   true,
        ],
        [ 
            'name'     =>   'Unique Headers',
            'slug'     =>   'unique-headers',
            'required' =>   true,
        ]
    ];

    // Check if user is on the theme recommended plugins step and a demo was selected.
    if ( isset( $_GET['step'] ) && $_GET['step'] === 'import' && isset( $_GET['import'] ) ) {
        // Unsash _wpnonce and sanitize inputs
        if ( isset( $_GET['_wpnonce'] ) ) {

            // Unslash and sanitize the _wpnonce
            $nonce = sanitize_key( wp_unslash( $_GET['_wpnonce'] ) );
            
            // Verify nonce
            if ( wp_verify_nonce( $nonce, 'import_plugins_action' ) ) {
                
                // Unsash and sanitize the 'import' value before using it
                $import = sanitize_text_field( wp_unslash( $_GET['import'] ) );
                
                if ( $import === '3' || $import === '5' || $import === '6' || $import === '7' ) {
                    $theme_plugins[] = [
                        'name'     => 'Spice Social Share',
                        'slug'     => 'spice-social-share',
                        'required' => true,
                    ];
                }
            }
        }
    }

    return array_merge( $plugins, $theme_plugins );

}
add_filter( 'ocdi/register_plugins', 'spice_starter_sites_register_plugins' );

/**
* Remove branding
*/
add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );