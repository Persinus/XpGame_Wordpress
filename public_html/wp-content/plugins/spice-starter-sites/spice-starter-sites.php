<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*
Plugin Name:        Spice Starter Sites
Plugin URI:         https://olivewp.org/
Description:        The plugin allows you to create professional designed pixel perfect websites in minutes. Import the stater sites to create the beautiful websites.
Version:            1.3.3.3
Requires at least:  5.3
Requires PHP:       5.2
Tested up to:       6.8.2
Author:             spicethemes
Author URI:         https://spicethemes.com
License:            GPLv2 or later
License URI:        http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain:        spice-starter-sites
Domain Path:        /languages
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
        die;
}
define('SPICE_STARTER_SITES_PLUGIN_PATH',trailingslashit(plugin_dir_path(__FILE__)));
define('SPICE_STARTER_SITES_PLUGIN_URL',trailingslashit(plugins_url('/',__FILE__)));
define('SPICE_STARTER_SITES_PLUGIN_UPLOAD',trailingslashit( wp_upload_dir()['basedir'] ) );

// Assuming SPICE_STARTER_SITES_VERSION is defined somewhere in your plugin
if ( ! defined( 'SSSP_VERSION' ) ) {
    define( 'SSSP_VERSION', '1.3.3.3' );
}

/**
 * Set up and initialize
 */

class Spice_Starter_Sites {
        private static $instance;

        /**
         * Actions setup
         */
        public function __construct() {
            add_action( 'plugins_loaded', array( $this, 'constants' ), 2 );
            add_action( 'plugins_loaded', array( $this, 'includes' ), 4 );
            add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
             
        }

        /**
         * Constants
        */
        function constants() {
            define( 'Spice_Starter_Sites_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
        }

        /**
         * Includes
         */
        function includes() {
            $theme=wp_get_theme();
            if($theme->name =='OliveWP' || 'OliveWP Child' == $theme->name || 'OliveWP child' == $theme->name) {
                if(! function_exists( 'spice_starter_sites_plus_plugin' ) ) {
                    require_once( Spice_Starter_Sites_DIR . 'demo-content/setup.php' );
                }
            }
        }

        static function install() {
            if ( version_compare(PHP_VERSION, '5.4', '<=') ) {
                wp_die( esc_html__( 'Spice Starter Sites requires PHP 5.4. Please contact your host to upgrade your PHP. The plugin was <strong>not</strong> activated.', 'spice-starter-sites' ) );
            };

        }

        /**
         * Returns the instance.
        */
        public static function get_instance() {

            if ( !self::$instance )
                self::$instance = new self;

            return self::$instance;
        }


        /**
         * Load the localisation file.
        */
        public function load_plugin_textdomain() {
            load_plugin_textdomain( 'spice-starter-sites' , false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
        }
}

function spice_starter_sites_plugin() {
    return Spice_Starter_Sites::get_instance();
}
add_action('plugins_loaded', 'spice_starter_sites_plugin', 1);

//Does not activate the plugin on PHP less than 5.4
register_activation_hook( __FILE__, array( 'Spice_Starter_Sites', 'install' ) );

//Add Style in About Page
add_action('admin_enqueue_scripts','spice_starter_sites_importer_style_script');
if(!function_exists('spice_starter_sites_importer_style_script')){
    function spice_starter_sites_importer_style_script(){
        $id = $GLOBALS['hook_suffix'];
        if('customize.php' !=$id){
            if('admin_page_spice-settings-importer'== $id || !empty($id) || 'admin_page_spice-settings-importer'== $id  ){
                wp_enqueue_style( 'spice-starter-sites-importer-about-css', SPICE_STARTER_SITES_PLUGIN_URL . 'assets/css/about.css', array(), SSSP_VERSION );
                wp_enqueue_script('sss-popup', SPICE_STARTER_SITES_PLUGIN_URL . 'assets/js/sss-popup.js', array('jquery'), SSSP_VERSION, true);
                if ( class_exists('Newscrunch_Plus') )
                {
                    wp_enqueue_script('newscrunch-plus-install', SPNCP_URL . 'inc/admin/assets/js/plugin-install.js', array('jquery'), SSSP_VERSION, true);   
                }
            }
        }
    }
}

//Define and declair global variable
global $spice_starter_sites_importer_filepath, $spice_starter_sites_importer_pro_filepath, $spice_starter_sites_importer_new_filepath;

function spice_starter_sites_array(){
    global $spice_starter_sites_importer_filepath, $spice_starter_sites_importer_pro_filepath, $spice_starter_sites_importer_new_filepath;
    $theme=wp_get_theme();
    if($theme->name =='Newscrunch' || 'Newscrunch Child' == $theme->name || 'Newscrunch child' == $theme->name){

        $demo_link='https://spicethemes.com/spice-newscrunch-importer/';

        $spice_starter_sites_importer_filepath= array(
           'newscrunch'=>array(
            'title'=>esc_html__('Newscrunch','spice-starter-sites'),
            'slug'=>'newscrunch',
            'categories'=>'Customizer',
            'content'=>$demo_link.'newscrunch/content.xml',
            'customizer'=>$demo_link.'newscrunch/customizer.dat',
            'widget'=>$demo_link.'newscrunch/widget.wie',
            'image'=>$demo_link.'newscrunch/newscrunch.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-one/',
            'plugin'=>'wpcf7-wpseo',
            'status'=>'',

           ),
           'politics'=>array(
            'title'=>esc_html__('Politics','spice-starter-sites'),
            'slug'=>'politics',
            'categories'=>'Customizer',
            'content'=>$demo_link.'politics/content.xml',
            'customizer'=>$demo_link.'politics/customizer.dat',
            'widget'=>$demo_link.'politics/widget.wie',
            'image'=>$demo_link.'politics/politics.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-two/',
            'plugin'=>'wpcf7-wpseo-sps',
            'status'=>'',
           ),
           'architec'=>array(
            'title'=>esc_html__('Architec','spice-starter-sites'),
            'slug'=>'architec',
            'categories'=>'Customizer',
            'content'=>$demo_link.'architec/content.xml',
            'customizer'=>$demo_link.'architec/customizer.dat',
            'widget'=>$demo_link.'architec/widget.wie',
            'image'=>$demo_link.'architec/architec.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-three/',
            'plugin'=>'wpcf7-wpseo',
            'status'=>'',
           ),
           'biznessify'=>array(
            'title'=>esc_html__('Biznessify','spice-starter-sites'),
            'slug'=>'biznessify',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'biznessify/content.xml',
            'customizer'=>$demo_link.'biznessify/customizer.dat',
            'widget'=>$demo_link.'biznessify/widget.wie',
            'image'=>$demo_link.'biznessify/biznessify.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-block-1/',
            'plugin'=>'wpcf7-sb',
            'status'=>'new',
           ),
        );
        $spice_starter_sites_importer_pro_filepath= array(
           'newscrunch-pro'=>array(
            'title'=>esc_html__('Newscrunch Pro','spice-starter-sites'),
            'slug'=>'newscrunch-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'newscrunch-pro/content.xml',
            'customizer'=>$demo_link.'newscrunch-pro/customizer.dat',
            'widget'=>$demo_link.'newscrunch-pro/widget.wie',
            'image'=>$demo_link.'newscrunch-pro/newscrunch-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-one/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
           'restaurant-pro'=>array(
            'title'=>esc_html__('Restaurant','spice-starter-sites'),
            'slug'=>'restaurant-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'restaurant-pro/content.xml',
            'customizer'=>$demo_link.'restaurant-pro/customizer.dat',
            'widget'=>$demo_link.'restaurant-pro/widget.wie',
            'image'=>$demo_link.'restaurant-pro/restaurant-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-two/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
           'technology-pro'=>array(
            'title'=>esc_html__('Technology','spice-starter-sites'),
            'slug'=>'technology-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'technology-pro/content.xml',
            'customizer'=>$demo_link.'technology-pro/customizer.dat',
            'widget'=>$demo_link.'technology-pro/widget.wie',
            'image'=>$demo_link.'technology-pro/technology-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-three/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
           'true-gamers-pro'=>array(
            'title'=>esc_html__('True Gamers','spice-starter-sites'),
            'slug'=>'true-gamers-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'true-gamers-pro/content.xml',
            'customizer'=>$demo_link.'true-gamers-pro/customizer.dat',
            'widget'=>$demo_link.'true-gamers-pro/widget.wie',
            'image'=>$demo_link.'true-gamers-pro/true-gamers-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-four/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
           'busi-crunch-pro'=>array(
            'title'=>esc_html__('Busi Crunch','spice-starter-sites'),
            'slug'=>'busi-crunch-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'busi-crunch-pro/content.xml',
            'customizer'=>$demo_link.'busi-crunch-pro/customizer.dat',
            'widget'=>$demo_link.'busi-crunch-pro/widget.wie',
            'image'=>$demo_link.'busi-crunch-pro/busi-crunch-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-five/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
            'fashion-world-pro'=>array(
            'title'=>esc_html__('Fashion World','spice-starter-sites'),
            'slug'=>'fashion-world-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'fashion-world-pro/content.xml',
            'customizer'=>$demo_link.'fashion-world-pro/customizer.dat',
            'widget'=>$demo_link.'fashion-world-pro/widget.wie',
            'image'=>$demo_link.'fashion-world-pro/fashion-world-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-six/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
           'life-style-pro'=>array(
            'title'=>esc_html__('Life Style','spice-starter-sites'),
            'slug'=>'life-style-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'life-style-pro/content.xml',
            'customizer'=>$demo_link.'life-style-pro/customizer.dat',
            'widget'=>$demo_link.'life-style-pro/widget.wie',
            'image'=>$demo_link.'life-style-pro/life-style-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-seven/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),  
           'digital-pro'=>array(
            'title'=>esc_html__('Digital','spice-starter-sites'),
            'slug'=>'digital-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'digital-pro/content.xml',
            'customizer'=>$demo_link.'digital-pro/customizer.dat',
            'widget'=>$demo_link.'digital-pro/widget.wie',
            'image'=>$demo_link.'digital-pro/digital-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-eight/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
           'pet-care-pro'=>array(
            'title'=>esc_html__('Pet Care','spice-starter-sites'),
            'slug'=>'pet-care-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'pet-care-pro/content.xml',
            'customizer'=>$demo_link.'pet-care-pro/customizer.dat',
            'widget'=>$demo_link.'pet-care-pro/widget.wie',
            'image'=>$demo_link.'pet-care-pro/pet-care-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-nine/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
           'sports-mag-pro'=>array(
            'title'=>esc_html__('Sports Mag','spice-starter-sites'),
            'slug'=>'sports-mag-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'sports-mag-pro/content.xml',
            'customizer'=>$demo_link.'sports-mag-pro/customizer.dat',
            'widget'=>$demo_link.'sports-mag-pro/widget.wie',
            'image'=>$demo_link.'sports-mag-pro/sports-mag-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-ten/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
            'fitness-club-pro'=>array(
            'title'=>esc_html__('Fitness Club','spice-starter-sites'),
            'slug'=>'fitness-club-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'fitness-club-pro/content.xml',
            'customizer'=>$demo_link.'fitness-club-pro/customizer.dat',
            'widget'=>$demo_link.'fitness-club-pro/widget.wie',
            'image'=>$demo_link.'fitness-club-pro/fitness-club-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-eleven/',
            'plugin'=>'wpcf7-wpseo-wpmap-ssp',
            'status'=>'',
           ),
            'photography-pro'=>array(
            'title'=>esc_html__('Photography','spice-starter-sites'),
            'slug'=>'photography-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'photography-pro/content.xml',
            'customizer'=>$demo_link.'photography-pro/customizer.dat',
            'widget'=>$demo_link.'photography-pro/widget.wie',
            'image'=>$demo_link.'photography-pro/photography-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-twelve/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
            'news-cloud-pro'=>array(
            'title'=>esc_html__('News Cloud','spice-starter-sites'),
            'slug'=>'news-cloud-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'newscloud-pro/content.xml',
            'customizer'=>$demo_link.'newscloud-pro/customizer.dat',
            'widget'=>$demo_link.'newscloud-pro/widget.wie',
            'image'=>$demo_link.'newscloud-pro/newscloud-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-thirteen/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
            'consulting-pro'=>array(
            'title'=>esc_html__('Consulting','spice-starter-sites'),
            'slug'=>'consulting-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'consulting-pro/content.xml',
            'customizer'=>$demo_link.'consulting-pro/customizer.dat',
            'widget'=>$demo_link.'consulting-pro/widget.wie',
            'image'=>$demo_link.'consulting-pro/consulting-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-fourteen/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
            'trip-travel-pro'=>array(
            'title'=>esc_html__('Trip Travel','spice-starter-sites'),
            'slug'=>'trip-travel-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'trip-travel-pro/content.xml',
            'customizer'=>$demo_link.'trip-travel-pro/customizer.dat',
            'widget'=>$demo_link.'trip-travel-pro/widget.wie',
            'image'=>$demo_link.'trip-travel-pro/trip-travel-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-fifteen/',
            'plugin'=>'wpcf7-wpseo-wpmap',
            'status'=>'',
           ),
            'architecture-pro'=>array(
            'title'=>esc_html__('Architecture','spice-starter-sites'),
            'slug'=>'architecture-pro',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'architecture-pro/content.xml',
            'customizer'=>$demo_link.'architecture-pro/customizer.dat',
            'widget'=>$demo_link.'architecture-pro/widget.wie',
            'image'=>$demo_link.'architecture-pro/architecture-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-block-1',
            'plugin'=>'wpcf7-wpmap-sbp',
            'status'=>'new',
           ),
            'bizsphere-pro'=>array(
            'title'=>esc_html__('Bizsphere','spice-starter-sites'),
            'slug'=>'bizsphere-pro',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'bizsphere-pro/content.xml',
            'customizer'=>$demo_link.'bizsphere-pro/customizer.dat',
            'widget'=>$demo_link.'bizsphere-pro/widget.wie',
            'image'=>$demo_link.'bizsphere-pro/bizsphere-pro.jpg',
            'demo_link'=>'https://demo-newscrunch.spicethemes.com/demo-pro-block-2/',
            'plugin'=>'wpcf7-wpmap-sbp',
            'status'=>'new',
           ),
        );
    }

    if('NewsBlogger' == $theme->name){

        $demo_link='https://spicethemes.com/spice-newscrunch-importer/';

        $spice_starter_sites_importer_filepath= array(
           'newsblogger'=>array(
            'title'=>esc_html__('Default','spice-starter-sites'),
            'slug'=>'newsblogger',
            'categories'=>'Customizer',
            'content'=>$demo_link.'newsblogger/content.xml',
            'customizer'=>$demo_link.'newsblogger/customizer.dat',
            'widget'=>$demo_link.'newsblogger/widget.wie',
            'image'=>$demo_link.'newsblogger/newsblogger.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/startersite-1/',
            'plugin'=>'wpcf7',
            'status'=>'',

           ),
           'spice-shop'=>array(
            'title'=>esc_html__('Spice Shop','spice-starter-sites'),
            'slug'=>'spice-shop',
            'categories'=>'Gutenberg',
            'subcategories'=>'Shop',
            'content'=>$demo_link.'spice-shop/content.xml',
            'customizer'=>$demo_link.'spice-shop/customizer.dat',
            'widget'=>$demo_link.'spice-shop/widget.wie',
            'image'=>$demo_link.'spice-shop/spice-shop.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/block-startersite-2/',
            'plugin'=>'wpcf7-sb-woo-wpmap',
            'status'=>'new',
           ),
           'finance'=>array(
            'title'=>esc_html__('Finance','spice-starter-sites'),
            'slug'=>'finance',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'finance/content.xml',
            'customizer'=>$demo_link.'finance/customizer.dat',
            'widget'=>$demo_link.'finance/widget.wie',
            'image'=>$demo_link.'finance/finance.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/block-startersite-1/',
            'plugin'=>'wpcf7-sb',
            'status'=>'new',
           ),
           'travel'=>array(
            'title'=>esc_html__('Travel','spice-starter-sites'),
            'slug'=>'travel',
            'categories'=>'Customizer',
            'content'=>$demo_link.'travel/content.xml',
            'customizer'=>$demo_link.'travel/customizer.dat',
            'widget'=>$demo_link.'travel/widget.wie',
            'image'=>$demo_link.'travel/travel.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/startersite-2/',
            'plugin'=>'wpcf7',
            'status'=>'',
           ),
           'business-news'=>array(
            'title'=>esc_html__('Business News','spice-starter-sites'),
            'slug'=>'business-news',
            'categories'=>'Customizer',
            'content'=>$demo_link.'business-news/content.xml',
            'customizer'=>$demo_link.'business-news/customizer.dat',
            'widget'=>$demo_link.'business-news/widget.wie',
            'image'=>$demo_link.'business-news/business-news.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/startersite-3/',
            'plugin'=>'wpcf7',
            'status'=>'',
           ),
        );
        $spice_starter_sites_importer_pro_filepath= array(
           'newsblogger-pro'=>array(
            'title'=>esc_html__('Default Pro','spice-starter-sites'),
            'slug'=>'newsblogger-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'newsblogger-pro/content.xml',
            'customizer'=>$demo_link.'newsblogger-pro/customizer.dat',
            'widget'=>$demo_link.'newsblogger-pro/widget.wie',
            'image'=>$demo_link.'newsblogger-pro/newsblogger-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-1/',
            'plugin'=>'wpcf7-wpmap',
            'status'=>'',
           ),
           'spice-shop-pro'=>array(
            'title'=>esc_html__('Spice Shop Pro','spice-starter-sites'),
            'slug'=>'spice-shop-pro',
            'categories'=>'Gutenberg',
            'subcategories'=>'Shop',
            'content'=>$demo_link.'spice-shop-pro/content.xml',
            'customizer'=>$demo_link.'spice-shop-pro/customizer.dat',
            'widget'=>$demo_link.'spice-shop-pro/widget.wie',
            'image'=>$demo_link.'spice-shop-pro/spice-shop-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-block-startersite-2/',
            'plugin'=>'wpcf7-sbp-woo-wpmap',
            'status'=>'',
           ),
           'flock-games-pro'=>array(
            'title'=>esc_html__('Flock Games Pro','spice-starter-sites'),
            'slug'=>'flock-games-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'flock-games-pro/content.xml',
            'customizer'=>$demo_link.'flock-games-pro/customizer.dat',
            'widget'=>$demo_link.'flock-games-pro/widget.wie',
            'image'=>$demo_link.'flock-games-pro/flock-games-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-7/',
            'plugin'=>'wpcf7-wpmap',
            'status'=>'',
           ),
           'corporation-pro'=>array(
            'title'=>esc_html__('Corporation Pro','spice-starter-sites'),
            'slug'=>'corporation-pro',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'corporation-pro/content.xml',
            'customizer'=>$demo_link.'corporation-pro/customizer.dat',
            'widget'=>$demo_link.'corporation-pro/widget.wie',
            'image'=>$demo_link.'corporation-pro/corporation-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-block-startersite-1/',
            'plugin'=>'wpcf7-sbp',
            'status'=>'',
           ),
           'creative-pro'=>array(
            'title'=>esc_html__('Creative','spice-starter-sites'),
            'slug'=>'creative-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'creative-pro/content.xml',
            'customizer'=>$demo_link.'creative-pro/customizer.dat',
            'widget'=>$demo_link.'creative-pro/widget.wie',
            'image'=>$demo_link.'creative-pro/creative-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-2/',
            'plugin'=>'wpcf7-wpmap',
            'status'=>'',
           ),
           'healthcare-pro'=>array(
            'title'=>esc_html__('Healthcare','spice-starter-sites'),
            'slug'=>'healthcare-pro',
            'categories'=>'Customizer',
            'content'=>$demo_link.'healthcare-pro/content.xml',
            'customizer'=>$demo_link.'healthcare-pro/customizer.dat',
            'widget'=>$demo_link.'healthcare-pro/widget.wie',
            'image'=>$demo_link.'healthcare-pro/healthcare-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-3/',
            'plugin'=>'wpcf7-wpmap',
            'status'=>'',
           ),   
           'newsblogger-arabic-pro-rtl'=>array(
            'title'=>esc_html__('NewsBlogger Arabic','spice-starter-sites'),
            'slug'=>'newsblogger-arabic-pro-rtl',
            'categories'=>'Customizer',
            'content'=>$demo_link.'newsblogger-arabic-pro-rtl/content.xml',
            'customizer'=>$demo_link.'newsblogger-arabic-pro-rtl/customizer.dat',
            'widget'=>$demo_link.'newsblogger-arabic-pro-rtl/widget.wie',
            'image'=>$demo_link.'newsblogger-arabic-pro-rtl/newsblogger-arabic-pro-rtl.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-5/',
            'plugin'=>'wpcf7-wpmap',
            'status'=>'',
           ),     
           'commercial-pro-rtl'=>array(
            'title'=>esc_html__('Commercial RTL','spice-starter-sites'),
            'slug'=>'commercial-pro-rtl',
            'categories'=>'Customizer',
            'content'=>$demo_link.'commercial-pro-rtl/content.xml',
            'customizer'=>$demo_link.'commercial-pro-rtl/customizer.dat',
            'widget'=>$demo_link.'commercial-pro-rtl/widget.wie',
            'image'=>$demo_link.'commercial-pro-rtl/commercial-pro-rtl.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-startersite-6/',
            'plugin'=>'wpcf7-wpmap',
            'status'=>'',
           ),
           'biz-buzz-pro'=>array(
            'title'=>esc_html__('Biz Buzz','spice-starter-sites'),
            'slug'=>'biz-buzz-pro',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'biz-buzz-pro/content.xml',
            'customizer'=>$demo_link.'biz-buzz-pro/customizer.dat',
            'widget'=>$demo_link.'biz-buzz-pro/widget.wie',
            'image'=>$demo_link.'biz-buzz-pro/biz-buzz-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-block-startersite-5/',
            'plugin'=>'wpcf7-wpmap-sbp',
            'status'=>'new',
           ),
           'blog-theme-pro'=>array(
            'title'=>esc_html__('Blog Theme','spice-starter-sites'),
            'slug'=>'blog-theme-pro',
            'categories'=>'Gutenberg',
            'content'=>$demo_link.'blog-theme-pro/content.xml',
            'customizer'=>$demo_link.'blog-theme-pro/customizer.dat',
            'widget'=>$demo_link.'blog-theme-pro/widget.wie',
            'image'=>$demo_link.'blog-theme-pro/blog-theme-pro.jpg',
            'demo_link'=>'https://demo-news.spicethemes.com/pro-block-startersite-4/',
            'plugin'=>'wpcf7-wpmap-sbp',
            'status'=>'new',
           ), 
        );
    }

    if($theme->name =='Appointment' || 'Appointment child' == $theme->name  || 'Appointment Child' == $theme->name  || 'Appointment Blue' == $theme->name || 'Appointment Blue child' == $theme->name  || 'Appointment Blue Child' == $theme->name  || 'Appointment Dark' == $theme->name || 'Appointment Dark child' == $theme->name  || 'Appointment Dark Child' == $theme->name  || 'Appointment Green' == $theme->name || 'Appointment Green child' == $theme->name  || 'Appointment Green Child' == $theme->name  || 'Appointment Red' == $theme->name || 'Appointment Red child' == $theme->name  || 'Appointment Red Child' == $theme->name  || 'Appointee' == $theme->name || 'Appointee Child' == $theme->name  || 'Appointee Child' == $theme->name  || 'Shk Corporate' == $theme->name || 'Shk Corporate child' == $theme->name  || 'Shk Corporate Child' == $theme->name  || 'vice' == $theme->name || 'vice child' == $theme->name  || 'vice Child' == $theme->name || $theme->name =='Appointment Pro' || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name){

            $demo_link='https://webriti.com/startersites/appointment/';
            $local_import=$local_import_widget=$local_import_customizer=$preview_url=$preview_image_url='';
                if ('Appointment' == $theme->name || 'Appointment Pro' == $theme->name || 'Appointment child' == $theme->name  || 'Appointment Child' == $theme->name || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name) {
                    $local_import               = $demo_link . 'lite/default/content.xml';
                    $local_import_widget        = $demo_link . 'lite/default/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/default/customizer.dat';
                    $preview_url                = 'https://appointment.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/default.jpg';
                }
                else if ('Appointment Green' == $theme->name) {
                    $local_import               = $demo_link . 'lite/appointment-green/content.xml';
                    $local_import_widget        = $demo_link . 'lite/appointment-green/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/appointment-green/customizer.dat';
                    $preview_url                = 'https://appointment-green.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/appointment-green.jpg';
                }
                else if ('Appointment Blue' == $theme->name) {
                    $local_import               = $demo_link . 'lite/appointment-blue/content.xml';
                    $local_import_widget        = $demo_link . 'lite/appointment-blue/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/appointment-blue/customizer.dat';
                    $preview_url                = 'https://appointment-blue.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/appointment-blue.jpg';
                }
                else if ('Appointment Red' == $theme->name) {
                    $local_import               = $demo_link . 'lite/appointment-red/content.xml';
                    $local_import_widget        = $demo_link . 'lite/appointment-red/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/appointment-red/customizer.dat';
                    $preview_url                = 'https://appointment-red.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/appointment-red.jpg';
                }
                else if ('Appointee' == $theme->name) {
                    $local_import               = $demo_link . 'lite/appointee/content.xml';
                    $local_import_widget        = $demo_link . 'lite/appointee/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/appointee/customizer.dat';
                    $preview_url                = 'https://appointee.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/appointee.jpg';
                }
                else if ('Appointment Dark' == $theme->name) {
                    $local_import               = $demo_link . 'lite/appointment-dark/content.xml';
                    $local_import_widget        = $demo_link . 'lite/appointment-dark/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/appointment-dark/customizer.dat';
                    $preview_url                = 'https://appointment-dark.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/appointment-dark.jpg';
                }
                else if ('vice' == $theme->name) {
                    $local_import               = $demo_link . 'lite/vice/content.xml';
                    $local_import_widget        = $demo_link . 'lite/vice/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/vice/customizer.dat';
                    $preview_url                = 'https://vice.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/vice.jpg';
                }
                else if ('Shk Corporate' == $theme->name) {
                    $local_import               = $demo_link . 'lite/shk-corporate/content.xml';
                    $local_import_widget        = $demo_link . 'lite/shk-corporate/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/shk-corporate/customizer.dat';
                    $preview_url                = 'https://shk-corporate.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/shk-corporate.jpg';
                }
                else{
                    $local_import               = $demo_link . 'lite/default/content.xml';
                    $local_import_widget        = $demo_link . 'lite/default/widget.wie';
                    $local_import_customizer    = $demo_link . 'lite/default/customizer.dat';
                    $preview_url                = 'https://appointment.webriti.com/';
                    $preview_image_url          = $demo_link . 'thumbnail/default.jpg';
                }
            if ('Appointment' == $theme->name || 'Appointment child' == $theme->name  || 'Appointment Child' == $theme->name ) {
                $spice_starter_sites_importer_filepath= array(
                    'appointment'=>array(
                        'title'=>esc_html__('Default','spice-starter-sites'),
                        'categories'=>'Customizer',
                        'slug'=>'appointment',
                        'content'=>$local_import,
                        'customizer'=>$local_import_customizer,
                        'widget'=>$local_import_widget,
                        'image'=>$preview_image_url,
                        'demo_link'=>$preview_url,
                        'plugin'=>'wpcf7-wpseo-wc',
                        'status'=>'',
                       ),
                    'appointment-ele'=>array(
                        'title'=>esc_html__('Default Elementor','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'appointment-ele',
                        'content'=>$demo_link.'lite/default-elementor/content.xml',
                        'customizer'=>$demo_link.'lite/default-elementor/customizer.dat',
                        'widget'=>$demo_link.'lite/default-elementor/widget.wie',
                        'image'=>$demo_link.'thumbnail/default-elementor.jpg',
                        'demo_link'=>'https://ap-default.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele-wc',
                        'status'=>'new',
                       ),
                   'business'=>array(
                        'title'=>esc_html__('Business','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'business',
                        'content'=>$demo_link.'lite/business/content.xml',
                        'customizer'=>$demo_link.'lite/business/customizer.dat',
                        'widget'=>$demo_link.'lite/business/widget.wie',
                        'image'=>$demo_link.'thumbnail/business.jpg',
                        'demo_link'=>'https://ap-business.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele',
                        'status'=>'',
                       ),
                   'restaurants'=>array(
                        'title'=>esc_html__('Restaurants','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'restaurants',
                        'content'=>$demo_link.'lite/restaurants/content.xml',
                        'customizer'=>$demo_link.'lite/restaurants/customizer.dat',
                        'widget'=>$demo_link.'lite/restaurants/widget.wie',
                        'image'=>$demo_link.'thumbnail/restaurants.jpg',
                        'demo_link'=>'https://ap-restaurants.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele',
                        'status'=>'',
                       ),
                   'appointment-gutenberg'=>array(
                        'title'=>esc_html__('Default Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'appointment-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/appointment/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/appointment/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/appointment/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/default-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-one/',
                        'plugin'=>'wpcf7-wpseo-sb-wc',
                        'status'=>'',
                       ),
                   'growkit-gutenberg'=>array(
                        'title'=>esc_html__('Growkit Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'growkit-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/growkit/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/growkit/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/growkit/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/growkit-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-two/',
                        'plugin'=>'wpcf7-wpseo-sb',
                        'status'=>'',
                       ),
                   'building-gutenberg'=>array(
                        'title'=>esc_html__('Building Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'building-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/building/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/building/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/building/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/building-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-three',
                        'plugin'=>'wpcf7-wpseo-sb',
                        'status'=>'',
                       ),
                );
            }else if('Appointment Pro' == $theme->name || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name) {
                $spice_starter_sites_importer_filepath= array(
                    'appointment-ele'=>array(
                        'title'=>esc_html__('Default Elementor','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'appointment-ele',
                        'content'=>$demo_link.'lite/default-elementor/content.xml',
                        'customizer'=>$demo_link.'lite/default-elementor/customizer.dat',
                        'widget'=>$demo_link.'lite/default-elementor/widget.wie',
                        'image'=>$demo_link.'thumbnail/default-elementor.jpg',
                        'demo_link'=>'https://ap-default.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele-wc',
                        'status'=>'new',
                       ),
                   'business'=>array(
                        'title'=>esc_html__('Business','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'business',
                        'content'=>$demo_link.'lite/business/content.xml',
                        'customizer'=>$demo_link.'lite/business/customizer.dat',
                        'widget'=>$demo_link.'lite/business/widget.wie',
                        'image'=>$demo_link.'thumbnail/business.jpg',
                        'demo_link'=>'https://ap-business.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele',
                        'status'=>'',
                       ),
                   'restaurants'=>array(
                        'title'=>esc_html__('Restaurants','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'restaurants',
                        'content'=>$demo_link.'lite/restaurants/content.xml',
                        'customizer'=>$demo_link.'lite/restaurants/customizer.dat',
                        'widget'=>$demo_link.'lite/restaurants/widget.wie',
                        'image'=>$demo_link.'thumbnail/restaurants.jpg',
                        'demo_link'=>'https://ap-restaurants.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele',
                        'status'=>'',
                       ),
                    'growkit-gutenberg'=>array(
                        'title'=>esc_html__('Growkit Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'growkit-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/growkit/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/growkit/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/growkit/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/growkit-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-two/',
                        'plugin'=>'wpcf7-wpseo-sb',
                        'status'=>'',
                       ),
                   'building-gutenberg'=>array(
                        'title'=>esc_html__('Building Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'building-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/building/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/building/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/building/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/building-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-three',
                        'plugin'=>'wpcf7-wpseo-sb',
                        'status'=>'',
                       ),
                );
            }else{
                $spice_starter_sites_importer_filepath= array(
                    'appointment'=>array(
                        'title'=>esc_html__('Default','spice-starter-sites'),
                        'categories'=>'Customizer',
                        'slug'=>'appointment',
                        'content'=>$local_import,
                        'customizer'=>$local_import_customizer,
                        'widget'=>$local_import_widget,
                        'image'=>$preview_image_url,
                        'demo_link'=>$preview_url,
                        'plugin'=>'wpcf7-wpseo-wc',
                        'status'=>'',
                       ),
                    'business'=>array(
                        'title'=>esc_html__('Business','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'business',
                        'content'=>$demo_link.'lite/business/content.xml',
                        'customizer'=>$demo_link.'lite/business/customizer.dat',
                        'widget'=>$demo_link.'lite/business/widget.wie',
                        'image'=>$demo_link.'thumbnail/business.jpg',
                        'demo_link'=>'https://ap-business.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele',
                        'status'=>'',
                       ),
                   'restaurants'=>array(
                        'title'=>esc_html__('Restaurants','spice-starter-sites'),
                        'categories'=>'Elementor',
                        'slug'=>'restaurants',
                        'content'=>$demo_link.'lite/restaurants/content.xml',
                        'customizer'=>$demo_link.'lite/restaurants/customizer.dat',
                        'widget'=>$demo_link.'lite/restaurants/widget.wie',
                        'image'=>$demo_link.'thumbnail/restaurants.jpg',
                        'demo_link'=>'https://ap-restaurants.webriti.com/',
                        'plugin'=>'wpcf7-wpseo-ele',
                        'status'=>'',
                       ),
                   'growkit-gutenberg'=>array(
                        'title'=>esc_html__('Growkit Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'growkit-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/growkit/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/growkit/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/growkit/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/growkit-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-two/',
                        'plugin'=>'wpcf7-wpseo-sb',
                        'status'=>'',
                       ),
                   'building-gutenberg'=>array(
                        'title'=>esc_html__('Building Gutenberg','spice-starter-sites'),
                        'categories'=>'Gutenberg',
                        'slug'=>'building-gutenberg',
                        'content'=>$demo_link.'lite/gutenberg/building/content.xml',
                        'customizer'=>$demo_link.'lite/gutenberg/building/customizer.dat',
                        'widget'=>$demo_link.'lite/gutenberg/building/widget.wie',
                        'image'=>$demo_link.'thumbnail/gutenberg/building-gutenberg.jpg',
                        'demo_link'=>'https://demo-appointment.webriti.com/demo-three',
                        'plugin'=>'wpcf7-wpseo-sb',
                        'status'=>'',
                       ),
                );
            }

            $spice_starter_sites_importer_pro_filepath= array(
                'appointment-pro'=>array(
                    'title'=>esc_html__('Default Pro','spice-starter-sites'),
                    'categories'=>'Customizer',
                    'slug'=>'appointment-pro',
                    'content'=>$demo_link.'pro/default/content.xml',
                    'customizer'=>$demo_link.'pro/default/customizer.dat',
                    'widget'=>$demo_link.'pro/default/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/default-pro.jpg',
                    'demo_link'=>'https://appointment-pro.webriti.com/',
                    'plugin'=>'wpcf7-woo',
                    'status'=>'',
                   ),
                'corporate'=>array(
                    'title'=>esc_html__('Corporate','spice-starter-sites'),
                    'categories'=>'Elementor',
                    'slug'=>'corporate',
                    'content'=>$demo_link.'pro/corporate/content.xml',
                    'customizer'=>$demo_link.'pro/corporate/customizer.dat',
                    'widget'=>$demo_link.'pro/corporate/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/corporate.jpg',
                    'demo_link'=>'https://ap-corporate.webriti.com/',
                    'plugin'=>'wpcf7-wpseo-ele',
                    'status'=>'',
                ),
                'maintenance'=>array(
                    'title'=>esc_html__('Maintenance','spice-starter-sites'),
                    'categories'=>'Elementor',
                    'slug'=>'maintenance',
                    'content'=>$demo_link.'pro/maintenance/content.xml',
                    'customizer'=>$demo_link.'pro/maintenance/customizer.dat',
                    'widget'=>$demo_link.'pro/maintenance/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/maintenance.jpg',
                    'demo_link'=>'https://ap-maintenance.webriti.com/',
                    'plugin'=>'wpcf7-wpseo-ele',
                    'status'=>'',
                ),
                'education'=>array(
                    'title'=>esc_html__('Education','spice-starter-sites'),
                    'categories'=>'Elementor',
                    'slug'=>'education',
                    'content'=>$demo_link.'pro/education/content.xml',
                    'customizer'=>$demo_link.'pro/education/customizer.dat',
                    'widget'=>$demo_link.'pro/education/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/education.jpg',
                    'demo_link'=>'https://ap-education.webriti.com/',
                    'plugin'=>'wpcf7-wpseo-ele',
                    'status'=>'',
                ),
                'architect'=>array(
                    'title'=>esc_html__('Architect','spice-starter-sites'),
                    'categories'=>'Elementor',
                    'slug'=>'architect',
                    'content'=>$demo_link.'pro/architect/content.xml',
                    'customizer'=>$demo_link.'pro/architect/customizer.dat',
                    'widget'=>$demo_link.'pro/architect/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/architect.jpg',
                    'demo_link'=>'https://ap-architect.webriti.com/',
                    'plugin'=>'wpcf7-wpseo-ele',
                    'status'=>'',
                ),
                'finance'=>array(
                    'title'=>esc_html__('Finance','spice-starter-sites'),
                    'categories'=>'Elementor',
                    'slug'=>'finance',
                    'content'=>$demo_link.'pro/finance/content.xml',
                    'customizer'=>$demo_link.'pro/finance/customizer.dat',
                    'widget'=>$demo_link.'pro/finance/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/finance.jpg',
                    'demo_link'=>'https://ap-finance.webriti.com/',
                    'plugin'=>'wpcf7-wpseo-ele',
                    'status'=>'',
                ),
               'appointment-pro-gutenberg'=>array(
                    'title'=>esc_html__('Default Pro Gutenberg','spice-starter-sites'),
                    'categories'=>'Gutenberg',
                    'slug'=>'appointment-pro-gutenberg',
                    'content'=>$demo_link.'pro/gutenberg/appointment-pro/content.xml',
                    'customizer'=>$demo_link.'pro/gutenberg/appointment-pro/customizer.dat',
                    'widget'=>$demo_link.'pro/gutenberg/appointment-pro/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/gutenberg/default-pro-gutenberg.jpg',
                    'demo_link'=>'https://demo-appointment.webriti.com/demo-pro-one',
                    'plugin'=>'wpcf7-wpseo-sbp',
                    'status'=>'',
                ),
               'business-gutenberg'=>array(
                    'title'=>esc_html__('Business Gutenberg','spice-starter-sites'),
                    'categories'=>'Gutenberg',
                    'slug'=>'business-gutenberg',
                    'content'=>$demo_link.'pro/gutenberg/business/content.xml',
                    'customizer'=>$demo_link.'pro/gutenberg/business/customizer.dat',
                    'widget'=>$demo_link.'pro/gutenberg/business/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/gutenberg/business-gutenberg.jpg',
                    'demo_link'=>'https://demo-appointment.webriti.com/demo-pro-two',
                    'plugin'=>'wpcf7-wpseo-sbp',
                    'status'=>'',
                ),
               'corporate-gutenberg'=>array(
                    'title'=>esc_html__('Corporate Gutenberg','spice-starter-sites'),
                    'categories'=>'Gutenberg',
                    'slug'=>'corporate-gutenberg',
                    'content'=>$demo_link.'pro/gutenberg/corporate/content.xml',
                    'customizer'=>$demo_link.'pro/gutenberg/corporate/customizer.dat',
                    'widget'=>$demo_link.'pro/gutenberg/corporate/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/gutenberg/corporate-gutenberg.jpg',
                    'demo_link'=>'https://demo-appointment.webriti.com/demo-pro-three',
                    'plugin'=>'wpcf7-wpseo-sbp',
                    'status'=>'',
                ),
               'digital-agency-gutenberg'=>array(
                    'title'=>esc_html__('Digital Agency Gutenberg','spice-starter-sites'),
                    'categories'=>'Gutenberg',
                    'slug'=>'digital-agency-gutenberg',
                    'content'=>$demo_link.'pro/gutenberg/digital-agency/content.xml',
                    'customizer'=>$demo_link.'pro/gutenberg/digital-agency/customizer.dat',
                    'widget'=>$demo_link.'pro/gutenberg/digital-agency/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/gutenberg/digital-agency-gutenberg.jpg',
                    'demo_link'=>'https://demo-appointment.webriti.com/demo-pro-four',
                    'plugin'=>'wpcf7-wpseo-sbp',
                    'status'=>'',
                ), 
                'architecture-gutenberg'=>array(
                    'title'=>esc_html__('Architecture Gutenberg','spice-starter-sites'),
                    'categories'=>'Gutenberg',
                    'slug'=>'architecture-gutenberg',
                    'content'=>$demo_link.'pro/gutenberg/architecture/content.xml',
                    'customizer'=>$demo_link.'pro/gutenberg/architecture/customizer.dat',
                    'widget'=>$demo_link.'pro/gutenberg/architecture/widget.wie',
                    'image'=>$demo_link.'pro/thumbnail/gutenberg/architecture-gutenberg.jpg',
                    'demo_link'=>'https://demo-appointment.webriti.com/demo-pro-five',
                    'plugin'=>'wpcf7-wpseo-sbp',
                    'status'=>'new',
                ),      
            );
    }
}
add_action('after_setup_theme','spice_starter_sites_array');

//Create options page
add_action( 'admin_menu', 'spice_starter_sites_importer_options_page',999 );
if(!function_exists('spice_starter_sites_importer_options_page')){
    function spice_starter_sites_importer_options_page() {
        $theme=wp_get_theme();
        if($theme->name =='Newscrunch' || 'Newscrunch Child' == $theme->name || 'Newscrunch child' == $theme->name) {

            if ( class_exists('Newscrunch_Plus') )
            {
                add_submenu_page(
                    'newscrunch-plus-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            else
            {
                add_submenu_page(
                    'newscrunch-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            
            add_submenu_page(
                'spice-starter-sites',
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                'manage_options',
                'spice-settings-importer',
                function() { $dfg=new Spice_Starter_Sites_Demo_Import(); $dfg->display();},
                1
            );
        }  
        if('NewsBlogger' == $theme->name) {
            if ( class_exists('Newscrunch_Plus') )
            {
                add_submenu_page(
                    'newscrunch-plus-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            else
            {
                add_submenu_page(
                    'newsblogger-welcome',
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    esc_html__( 'Demo Import', 'spice-starter-sites' ),
                    'manage_options',
                    'spice-starter-sites',
                    function() { require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; },
                    20
                );
            }
            
            add_submenu_page(
                'spice-starter-sites',
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                'manage_options',
                'spice-settings-importer',
                function() { $dfg=new Spice_Starter_Sites_Demo_Import(); $dfg->display();},
                1
            );
        }  
        if($theme->name =='Appointment' || 'Appointment child' == $theme->name  || 'Appointment Child' == $theme->name  || 'Appointment Blue' == $theme->name || 'Appointment Blue child' == $theme->name  || 'Appointment Blue Child' == $theme->name  || 'Appointment Dark' == $theme->name || 'Appointment Dark child' == $theme->name  || 'Appointment Dark Child' == $theme->name  || 'Appointment Green' == $theme->name || 'Appointment Green child' == $theme->name  || 'Appointment Green Child' == $theme->name  || 'Appointment Red' == $theme->name || 'Appointment Red child' == $theme->name  || 'Appointment Red Child' == $theme->name  || 'Appointee' == $theme->name || 'Appointee Child' == $theme->name  || 'Appointee Child' == $theme->name  || 'Shk Corporate' == $theme->name || 'Shk Corporate child' == $theme->name  || 'Shk Corporate Child' == $theme->name  || 'vice' == $theme->name || 'vice child' == $theme->name  || 'vice Child' == $theme->name || $theme->name =='Appointment Pro' || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name) {
            
            add_action('admin_menu', function() {
                // Check if the current page is the one we want to display
                if (isset($_GET['page']) && $_GET['page'] == 'appointment-welcome') {
                    // Add the custom page content
                    add_action('admin_init', function() { 
                        require_once SPICE_STARTER_SITES_PLUGIN_PATH.'/admin/view.php'; }
                    );
                }
            });
            add_submenu_page(
                'spice-starter-sites',
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                esc_html__( 'Demo Import', 'spice-starter-sites' ),
                'manage_options',
                'spice-settings-importer',
                function() { 
                    $dfg=new Spice_Starter_Sites_Demo_Import(); 
                    $dfg->display();
                },
                1
            );
        }
    }
}

if ( ! class_exists( '\WP_Customize_Setting' ) ) {

    require_once ABSPATH . 'wp-includes/class-wp-customize-setting.php';

    final class CEI_Option extends WP_Customize_Setting {
        
        /**
         * Import an option value for this setting.
         *
         * @since 0.3
         * @param mixed $value The option value.
         * @return void
         */
        public function import( $value ) 
        {
            $this->update( $value );    
        }
    }  
}

function spice_starter_sites_importer_customizer_settings($path)
{
    // Check to see if the settings have already been imported.
    $template = get_template();
    $imported = get_option( $template . '_customizer_import', false );
    
    // Bail if already imported.
    if ( $imported ) {
        return;
    }    
    
    // Return if the file doesn't exist.
    if ( ! file_exists( $path ) ) {
        return;
    }
    
    // Get the settings data.
    $data = @unserialize( file_get_contents( $path ) );
    
    // Return if something is wrong with the data.
    if ( 'array' != gettype( $data ) || ! isset( $data['mods'] ) ) {
        return;
    }
    
    // Import options.
    if ( isset( $data['options'] ) ) {
         foreach ( $data['options'] as $option_key => $option_value ) {

            $option = new CEI_Option( $wp_customize, $option_key, array(
                'default'       => '',
                'type'          => 'option',
                'capability'    => 'edit_theme_options'
            ) );

            $option->import( $option_value );
        }
    }

     // If wp_css is set then import it.
        if( function_exists( 'wp_update_custom_css_post' ) && isset( $data['wp_css'] ) && '' !== $data['wp_css'] ) {
            wp_update_custom_css_post( $data['wp_css'] );
        }

    // Import mods.
    foreach ( $data['mods'] as $key => $val ) {
        set_theme_mod( $key, $val );
    }
    
    // Set the option so we know these have already been imported.
    update_option( $template . '_customizer_import', true );
}

function spice_starter_sites_importer_process_import_file($file){
    global $spice_starter_sites_importer_import_results;

    // File exists?
    if (! file_exists($file)) {
        wp_die(
            esc_html__('Import file could not be found. Please try again.', 'spice-starter-sites'),
            '',
            array(
                'back_link' => true,
            )
        );
    }

    // Get file contents and decode.
    $data = file_get_contents($file);
    $data = json_decode($data);
    // Delete import file.
    // unlink($file);

    // Import the widget data
    // Make results available for display on import/export page.
    $spice_starter_sites_importer_import_results = spice_starter_sites_importer_import_data($data);
}

function spice_starter_sites_importer_import_data($data){

    global $wp_registered_sidebars;

    if (empty($data) || ! is_object($data)) {
        wp_die(
            esc_html__('Import data is invalid.', 'spice-starter-sites'),
            '',
            array(
                'back_link' => true,
            )
        );
    }

    // Hook before import.
    do_action('spice_starter_sites_importer_before_import');
    $data = apply_filters('spice_starter_sites_importer_import_data', $data);

    // Get all available widgets site supports.
    $available_widgets = spice_starter_sites_importer_available_widgets();

    // Get all existing widget instances.
    $widget_instances = array();
    foreach ($available_widgets as $widget_data) {
        $widget_instances[$widget_data['id_base']] = get_option('widget_' . $widget_data['id_base']);
    }

    // Begin results.
    $results = array();

    // Loop import data's sidebars.
    foreach ($data as $sidebar_id => $widgets) {
        // Skip inactive widgets (should not be in export file).
        if ('wp_inactive_widgets' === $sidebar_id) {
            continue;
        }

        // Check if sidebar is available on this site.
        // Otherwise add widgets to inactive, and say so.
        if (isset($wp_registered_sidebars[$sidebar_id])) {
            $sidebar_available    = true;
            $use_sidebar_id       = $sidebar_id;
            $sidebar_message_type = 'success';
            $sidebar_message      = '';
        } else {
            $sidebar_available    = false;
            $use_sidebar_id       = 'wp_inactive_widgets'; // Add to inactive if sidebar does not exist in theme.
            $sidebar_message_type = esc_html__('error', 'spice-starter-sites');
            $sidebar_message      = esc_html__('Widget area does not exist in theme (using Inactive)', 'spice-starter-sites');
        }

        // Result for sidebar
        // Sidebar name if theme supports it; otherwise ID.
        $results[$sidebar_id]['name']         = ! empty($wp_registered_sidebars[$sidebar_id]['name']) ? $wp_registered_sidebars[$sidebar_id]['name'] : $sidebar_id;
        $results[$sidebar_id]['message_type'] = $sidebar_message_type;
        $results[$sidebar_id]['message']      = $sidebar_message;
        $results[$sidebar_id]['widgets']      = array();

        // Loop widgets.
        foreach ($widgets as $widget_instance_id => $widget) {
            $fail = false;

            // Get id_base (remove -# from end) and instance ID number.
            $id_base            = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
            $instance_id_number = str_replace($id_base . '-', '', $widget_instance_id);

            // Does site support this widget?
            if (! $fail && ! isset($available_widgets[$id_base])) {
                $fail                = true;
                $widget_message_type = esc_html__('error','spice-starter-sites');
                $widget_message      = esc_html__('Site does not support widget', 'spice-starter-sites'); // Explain why widget not imported.
            }

            // Filter to modify settings object before conversion to array and import
            // Leave this filter here for backwards compatibility with manipulating objects (before conversion to array below)
            // Ideally the newer spice_starter_sites_importer_widget_settings_array below will be used instead of this.
            $widget = apply_filters('spice_starter_sites_importer_widget_settings', $widget);

            // Convert multidimensional objects to multidimensional arrays
            // Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays
            // Without this, they are imported as objects and cause fatal error on Widgets page
            // If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays
            // It is probably much more likely that arrays are used than objects, however.
            $widget = json_decode(wp_json_encode($widget), true);

            // Filter to modify settings array
            // This is preferred over the older spice_starter_sites_importer_widget_settings filter above
            // Do before identical check because changes may make it identical to end result (such as URL replacements).
            $widget = apply_filters('spice_starter_sites_importer_widget_settings_array', $widget);

            // Does widget with identical settings already exist in same sidebar?
            if (! $fail && isset($widget_instances[$id_base])) {
                // Get existing widgets in this sidebar.
                $sidebars_widgets = get_option('sidebars_widgets');
                $sidebar_widgets  = isset($sidebars_widgets[$use_sidebar_id]) ? $sidebars_widgets[$use_sidebar_id] : array(); // Check Inactive if that's where will go.

                // Loop widgets with ID base.
                $single_widget_instances = ! empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : array();
                foreach ($single_widget_instances as $check_id => $check_widget) {
                    // Is widget in same sidebar and has identical settings?
                    if (in_array("$id_base-$check_id", $sidebar_widgets, true) && (array) $widget === $check_widget) {
                        $fail                = true;
                        $widget_message_type = esc_html__('warning','spice-starter-sites');

                        // Explain why widget not imported.
                        $widget_message = esc_html__('Widget already exists', 'spice-starter-sites');

                        break;
                    }
                }
            }

            // No failure.
            if (! $fail) {
                // Add widget instance
                $single_widget_instances   = get_option('widget_' . $id_base); // All instances for that widget ID base, get fresh every time.
                $single_widget_instances   = ! empty($single_widget_instances) ? $single_widget_instances : array(
                    '_multiwidget' => 1,   // Start fresh if have to.
                );
                $single_widget_instances[] = $widget; // Add it.

                // Get the key it was given.
                end($single_widget_instances);
                $new_instance_id_number = key($single_widget_instances);

                // If key is 0, make it 1
                // When 0, an issue can occur where adding a widget causes data from other widget to load,
                // and the widget doesn't stick (reload wipes it).
                if ('0' === strval($new_instance_id_number)) {
                    $new_instance_id_number = 1;
                    $single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
                    unset($single_widget_instances[0]);
                }

                // Move _multiwidget to end of array for uniformity.
                if (isset($single_widget_instances['_multiwidget'])) {
                    $multiwidget = $single_widget_instances['_multiwidget'];
                    unset($single_widget_instances['_multiwidget']);
                    $single_widget_instances['_multiwidget'] = $multiwidget;
                }

                // Update option with new widget.
                update_option('widget_' . $id_base, $single_widget_instances);

                // Assign widget instance to sidebar.
                // Which sidebars have which widgets, get fresh every time.
                $sidebars_widgets = get_option('sidebars_widgets');

                // Avoid rarely fatal error when the option is an empty string
                // https://github.com/churchthemes/widget-importer-exporter/pull/11.
                if (! $sidebars_widgets) {
                    $sidebars_widgets = array();
                }

                // Use ID number from new widget instance.
                $new_instance_id = $id_base . '-' . $new_instance_id_number;

                // Add new instance to sidebar.
                $sidebars_widgets[$use_sidebar_id][] = $new_instance_id;

                // Save the amended data.
                update_option('sidebars_widgets', $sidebars_widgets);

                // After widget import action.
                $after_widget_import = array(
                    'sidebar'           => $use_sidebar_id,
                    'sidebar_old'       => $sidebar_id,
                    'widget'            => $widget,
                    'widget_type'       => $id_base,
                    'widget_id'         => $new_instance_id,
                    'widget_id_old'     => $widget_instance_id,
                    'widget_id_num'     => $new_instance_id_number,
                    'widget_id_num_old' => $instance_id_number,
                );
                do_action('spice_starter_sites_importer_after_widget_import', $after_widget_import);

                // Success message.
                if ($sidebar_available) {
                    $widget_message_type = esc_html__('success','spice-starter-sites');
                    $widget_message      = esc_html__('Imported', 'spice-starter-sites');
                } else {
                    $widget_message_type = esc_html__('warning','spice-starter-sites');
                    $widget_message      = esc_html__('Imported to Inactive', 'spice-starter-sites');
                }
            }

            // Result for widget instance
            $results[$sidebar_id]['widgets'][$widget_instance_id]['name']         = isset($available_widgets[$id_base]['name']) ? $available_widgets[$id_base]['name'] : $id_base;      // Widget name or ID if name not available (not supported by site).
            $results[$sidebar_id]['widgets'][$widget_instance_id]['title']        = ! empty($widget['title']) ? $widget['title'] : esc_html__('No Title', 'spice-starter-sites');  // Show "No Title" if widget instance is untitled.
            $results[$sidebar_id]['widgets'][$widget_instance_id]['message_type'] = $widget_message_type;
            $results[$sidebar_id]['widgets'][$widget_instance_id]['message']      = $widget_message;
        }
    }

    // Hook after import.
    do_action('spice_starter_sites_importer_after_import');

    // Return results.
    return apply_filters('spice_starter_sites_importer_import_results', $results);
}

function spice_starter_sites_importer_available_widgets()
{
    global $wp_registered_widget_controls;

    $widget_controls = $wp_registered_widget_controls;

    $available_widgets = array();

    foreach ($widget_controls as $widget) {
        // No duplicates.
        if (! empty( $widget['id_base'] ) && ! isset( $available_widgets[ $widget['id_base'] ] )) {
            $available_widgets[ $widget['id_base'] ]['id_base'] = $widget['id_base'];
            $available_widgets[ $widget['id_base'] ]['name']    = $widget['name'];
        }
    }

    return apply_filters( 'spice_starter_sites_importer_available_widgets', $available_widgets );
}

function spice_starter_sites_importer_set_after_import_mods() {

    $main_menu = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
    $footer_menu = get_term_by( 'name', 'Footer Menu', 'nav_menu' );
    set_theme_mod( 'nav_menu_locations', array(
            'primary' => $main_menu->term_id,
            'footer_menu' => $footer_menu->term_id,
        )
    );
}

add_action( 'spice_starter_sites_importer_after', 'spice_starter_sites_importer_set_after_import_mods' );

class Spice_Starter_Sites_Demo_Import {
    public $dir;
    public $url;
    public $demo_args;
        
   function display( ){
        $show_export = false;
        wp_nonce_field( 'export_action', 'export_nonce' );
        if ( isset( $_REQUEST['export'] ) && $_REQUEST['export'] == 1 ) {
            // Check if the nonce is set and valid
            if ( isset( $_REQUEST['export_nonce'] ) ) {
                $export_nonce = sanitize_text_field( wp_unslash( $_REQUEST['export_nonce'] ) );
                if ( wp_verify_nonce( $export_nonce, 'export_action' ) ) {
                    // Nonce is valid, proceed with the export
                    $show_export = true;
                }
            } else {
                // Invalid nonce, reject the request or handle it as needed
                wp_die( 'Security check failed!' );
            }
        }?>            
        <div class="wrap spice-starter-sites-importer-dashboard" id="ertvg">
            <div class="theme_info info-tab-content">
            <h3 class="spice-starter-sites-importer-heading"><?php esc_html_e('Spice Starter Sites Importer','spice-starter-sites');?></h3>
                <div class="block-container" id="myDivs1">
                    <div class="block-row">
                        <div class="block-col-2">
                            <?php                             
                            $theme_name  = isset($_GET['theme']) ? sanitize_text_field(wp_unslash($_GET['theme'])) : '';
                            $theme_title = isset($_GET['title']) ? sanitize_text_field(wp_unslash($_GET['title'])) : '';
                            global $spice_starter_sites_importer_filepath, $spice_starter_sites_importer_pro_filepath;
                            if(!empty($spice_starter_sites_importer_filepath[$theme_name]['image'])){
                                $attachment_id = spice_starter_sites_save_img_media_library($spice_starter_sites_importer_filepath[$theme_name]['image']);
                                echo wp_get_attachment_image($attachment_id, 'medium', false, ['style' => 'width:600px;']);
                            }
                            if(!empty($spice_starter_sites_importer_pro_filepath[$theme_name]['image'])){
                                $attachment_id = spice_starter_sites_save_img_media_library($spice_starter_sites_importer_pro_filepath[$theme_name]['image']);
                                echo wp_get_attachment_image($attachment_id, 'medium', false, ['style' => 'width:600px;']);
                            }         
                            ?>
                        </div>
                        <div class="block-col-2" align="justify">
                        <div class="importer-header">
                            <?php // Translators: %s is the theme title. ?>
                            <h3><?php printf( esc_html__( 'Theme: %s', 'spice-starter-sites' ), esc_html( $theme_title ) ); ?></h3>
                        </div>
                        <div class="importer-body">
                            <?php 
                            $theme=wp_get_theme();
                            $themeslug=$theme->stylesheet;
                            $themeprefix=str_replace('-','_',$themeslug);
                            
                            if ( class_exists('Newscrunch_Plus') ):
                                $name='newscrunch_plus_about_page';
                            elseif($theme->name =='Appointment' || 'Appointment child' == $theme->name  || 'Appointment Child' == $theme->name  || 'Appointment Blue' == $theme->name || 'Appointment Blue child' == $theme->name  || 'Appointment Blue Child' == $theme->name  || 'Appointment Green' == $theme->name || 'Appointment Green child' == $theme->name  || 'Appointment Green Child' == $theme->name  || 'Appointment Red' == $theme->name || 'Appointment Red child' == $theme->name  || 'Appointment Red Child' == $theme->name  || $theme->name =='Appointment Pro' || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name) :
                                $name='appointment_about_page';
                            else: 
                                $name=$themeprefix.'_about_page';
                            endif;
                            global $$name; 
                            $news_crunch_actions = $$name->recommended_actions;
                            $news_crunch_actions_todo = get_option( 'recommended_actions', false );
                            $plugindata = isset($_GET['plugin']) ? sanitize_text_field(wp_unslash($_GET['plugin'])) : '';

                            $plugindata_arr = explode("-", $plugindata);
                            $newplugindata_arr=array();
                            $length=sizeof($plugindata_arr);
                            for($i=0;$i<$length;$i++){
                                if($plugindata_arr[$i]==='wpcf7'){
                                    array_push($newplugindata_arr,'install_contact-form-7');
                                }
                                else if($plugindata_arr[$i]==='wpseo'){
                                    array_push($newplugindata_arr,'install_wordpress-seo');
                                }
                                if($plugindata_arr[$i]==='sps'){
                                    array_push($newplugindata_arr,'install_spice-post-slider');
                                }
                                 if($plugindata_arr[$i]==='sss'){
                                    array_push($newplugindata_arr,'install_spice-social-share');
                                }
                                 if($plugindata_arr[$i]==='sseo'){
                                    array_push($newplugindata_arr,'install_seo-optimized-images');
                                }
                                if($plugindata_arr[$i]==='ssp'){
                                    array_push($newplugindata_arr,'install_spice-slider-pro');
                                }
                                if($plugindata_arr[$i]==='ele'){
                                    array_push($newplugindata_arr,'install_elementor');
                                }
                                if($plugindata_arr[$i]==='wpmap'){
                                    array_push($newplugindata_arr,'install_wp-google-maps');
                                }
                                if($plugindata_arr[$i]==='sb'){
                                    array_push($newplugindata_arr,'install_spice-blocks');
                                }
                                if($plugindata_arr[$i]==='sbp'){
                                    array_push($newplugindata_arr,'install_spice-blocks-pro');
                                }
                                if($plugindata_arr[$i]==='wc'){
                                    array_push($newplugindata_arr,'install_webriti-companion');
                                }
                                if($plugindata_arr[$i]==='woo'){
                                    array_push($newplugindata_arr,'install_woocommerce');
                                }
                            }
                             //print_r($plugindata_arr);
                            // print_r($newplugindata_arr);
                            ?>
                            <div id="recommended_actions" class="news-crunch-tab-pane">
                                <h4><?php esc_html_e( 'Recommended Plugins:','spice-starter-sites');?></h4>
                                <table>
                                    <?php 
                                    if($news_crunch_actions): 
                                        foreach ($news_crunch_actions as $key => $news_crunch_val): 
                                            for($i=0;$i<sizeof($newplugindata_arr);$i++){
                                                if($news_crunch_val['id']==$newplugindata_arr[$i]):?>
                                                    <tr>                                                        
                                                        <td class="<?php echo esc_attr($news_crunch_val['id']);?>">
                                                            <?php echo esc_html($news_crunch_val['title']); ?>
                                                        </td>
                                                        <td style=" float: right;">
                                                            <?php 
                                                            if(!$news_crunch_val['is_done']): 
                                                                echo wp_kses_post($news_crunch_val['link']); 
                                                            else:
                                                                echo '<span class="dashicons dashicons-yes"></span>';
                                                            endif;?>
                                                        </td>
                                                    </tr>                                                        
                                                    <?php 
                                                endif;
                                            }
                                        endforeach; 
                                    endif; ?>
                                </table>
                            </div> 
                        <?php ?>  
                        <?php
                    $sse_confirm=array();
                    for($i=0;$i<$length;$i++){
                        if($plugindata_arr[$i]==='wpcf7'){
                            if(class_exists('WPCF7')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        else if($plugindata_arr[$i]==='wpseo'){
                            if(function_exists('wpseo_init')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sps'){
                            if(class_exists('Spice_Post_Slider')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='ssp'){
                            if(class_exists('Spice_Slider_Pro')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sss'){
                            if(class_exists('Spice_Social_Share')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sseo'){
                            if(function_exists('sobw_fs')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='wpmap'){
                            if(function_exists('wpgmaps_init')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sb'){
                            if(class_exists('Spice_Blocks')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='sbp'){
                            if(class_exists('Spice_Blocks_Pro')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='wc'){
                            if(function_exists('webriti_companion_activate')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='ele'){
                            if(class_exists('ComposerAutoloaderInit175d29babee7e330d642b349f38630ac')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                        if($plugindata_arr[$i]==='woo'){
                            if(class_exists('ComposerAutoloaderInit175d29babee7e330d642b349f38630ac')){array_push($sse_confirm,'true');}else{array_push($sse_confirm,'false');}
                        }
                    }
                    $devi=0;
                    for($i=0;$i<sizeof($sse_confirm);$i++){
                        if($sse_confirm[$i]==='true'){
                            $devi++;
                        }

                    }?>                 
                    <div align="left" style="padding:20px auto"> 
                        <form action="" method="POST" id="myform">
                            <button type="submit" name="spice_starter_sites_importer_demo_import" data-theme="<?php echo esc_attr($theme_name);?>" class="spice-starter-sites-importer-button spice_starter_sites_importer_demo_import button-primary">
                            <?php
                            if( get_option( 'spice_starter_sites_importer_demo_imported' ) == 1 ) {
                                esc_html_e('Import Again', 'spice-starter-sites');
                            } else {
                                esc_html_e('Import Demo Data', 'spice-starter-sites');
                            }
                            ?>
                            </button>
                            <?php
                            $theme=wp_get_theme();
                            $textdomain = $theme->get('TextDomain');
                            if($theme->name =='Appointment' || 'Appointment child' == $theme->name  || 'Appointment Child' == $theme->name  || 'Appointment Blue' == $theme->name || 'Appointment Blue child' == $theme->name  || 'Appointment Blue Child' == $theme->name  || 'Appointment Green' == $theme->name || 'Appointment Green child' == $theme->name  || 'Appointment Green Child' == $theme->name  || 'Appointment Red' == $theme->name || 'Appointment Red child' == $theme->name  || 'Appointment Red Child' == $theme->name  || $theme->name =='Appointment Pro' || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name){?>
                                <a href="<?php echo esc_url('admin.php?page=appointment-welcome');?>" class="spice-starter-sites-importer-button button-primary" ><?php esc_html_e( 'Back','spice-starter-sites');?></a>
                            <?php
                            }else if('Appointment Dark' == $theme->name || 'Appointment Dark child' == $theme->name  || 'Appointment Dark Child' == $theme->name || 'Appointee' == $theme->name || 'Appointee Child' == $theme->name  || 'Appointee Child' == $theme->name  || 'Shk Corporate' == $theme->name || 'Shk Corporate child' == $theme->name  || 'Shk Corporate Child' == $theme->name  || 'vice' == $theme->name || 'vice child' == $theme->name  || 'vice Child' == $theme->name ){?>
                                <a href="<?php echo esc_url('admin.php?page='.$textdomain.'-welcome');?>" class="spice-starter-sites-importer-button button-primary" ><?php esc_html_e( 'Back','spice-starter-sites');?></a>
                            <?php
                            }else{?>
                                <a href="<?php echo esc_url('admin.php?page=spice-starter-sites');?>" class="spice-starter-sites-importer-button button-primary" ><?php esc_html_e( 'Back','spice-starter-sites');?></a>
                            <?php } ?>
                            
                        </form>
                    </div>
                    </div>
                        </div>
                    </div>
                    
                </div>

                <div align="center" id="myDiv" style="display:none;">
                    <div class="spice-starter-sites-importer-loader">
                        <?php $image_id  = spice_starter_sites_save_img_media_library(SPICE_STARTER_SITES_PLUGIN_URL.'assets/images/import-new.gif');
                        echo wp_get_attachment_image($image_id, 'full', false, array('id' => 'loading-image')); ?>
                        <p><?php esc_html_e( "Don't Refresh the Page . It may take a few minutes, Please Wait...","spice-starter-sites");?></p>
                    </div>
                </div>
                <div align="center" id="myDivs" style="display:none;">
                    <?php $image_id  = spice_starter_sites_save_img_media_library(SPICE_STARTER_SITES_PLUGIN_URL.'assets/images/completed.png');
                    echo wp_get_attachment_image($image_id, 'full', false); ?>
                    <h3><?php esc_html_e('Success!','spice-starter-sites');?></h3>
                    <p><?php esc_html_e( 'The import process has been successful. Go to visit the site and Enjoy the theme.','spice-starter-sites');?></p>
                    <a href="<?php echo esc_url(site_url());?>" class="spice-starter-sites-importer-button button-primary" target="_blank"><?php esc_html_e( 'Visit Site','spice-starter-sites');?></a>
                </div>
            </div>
            
        </div>
        
        <script type="text/javascript">
            jQuery( document).ready( function( $ ){
                jQuery( '.spice_starter_sites_importer_demo_import').on( 'click', function( e ){
                    e.preventDefault();
                    var themedata=$(this).data('theme');
                   // alert(themedata);
                    var btn = $(this);
                    if ( btn.hasClass( 'disabled' ) ) {
                        return false;
                    }
                    $('.spice-starter-sites-importer-popup').addClass('is-visible');
                });
                    
                //return;
                jQuery( '.spice_starter_sites_importer_appprove').on( 'click', function( e ){
                    var themedata=jQuery('.spice_starter_sites_importer_demo_import').data('theme');
                    jQuery('.spice-starter-sites-importer-popup').removeClass('is-visible');
                    var params = {
                        'action': 'spice_starter_sites_importer_creater',
                        'themename':themedata,
                        '_nonce': '<?php echo esc_js(wp_create_nonce( 'spice_starter_sites_importer_demo_import' )); ?>',
                        _time:  new Date().getTime()
                    };

                    $.ajax({
                        type: 'POST',
                        url: window.ajaxurl, 
                        data: params,
                        beforeSend: function(result) {
                            $('#myDiv').show();
                        },
                        complete: function(result) {                            
                            $('#myDiv').hide();
                            $('#myDivs1').hide();
                            $('#myDivs').show();
                        },
                    });
                });            
          
                //no popup
                $('.spice_starter_sites_importer_cancel').on('click', function(event){
                    event.preventDefault();
                    $('.spice-starter-sites-importer-popup').removeClass('is-visible');
                });
                
                //close popup
                $('.spice-starter-sites-importer-popup').on('click', function(event){
                    if( $(event.target).is('.spice-starter-sites-importer-popup-close') || $(event.target).is('.spice-starter-sites-importer-popup') ) {
                        event.preventDefault();
                        $(this).removeClass('is-visible');
                    }
                });

                //close popup when clicking the esc keyboard button
                $(document).keyup(function(event){
                    if(event.which=='27'){
                        $('.spice-starter-sites-importer-popup').removeClass('is-visible');
                    }
                });
        } );
        </script>
        <div class="spice-starter-sites-importer-popup" role="alert">
            <div class="spice-starter-sites-importer-popup-container">
                <?php
                function spice_starter_sites_url_exists($url) {
                    $response = wp_remote_get($url, array('timeout' => 5)); // Set timeout for better performance

                    if (is_wp_error($response)) {
                        return false; // Return false if there's an error
                    }

                    $status_code = wp_remote_retrieve_response_code($response);

                    return ($status_code == 200);
                }
                function spice_starter_sites_search_array($id, $array) {
                   foreach ($array as $key => $val) {
                       if ($val['slug'] === $id) {
                           return $key;
                       }
                   }
                   return null;
                }

                $themename = isset($_GET['theme']) ? sanitize_text_field(wp_unslash($_GET['theme'])) : '';

                global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath;
                $checkfiles=array('content', 'customizer', 'widget');
                $findfile=false;
                 if(spice_starter_sites_search_array($themename, $spice_starter_sites_importer_pro_filepath)!=''){
                    for($i=0; $i< sizeof($checkfiles) ; $i++){
                        $string='';
                        if(spice_starter_sites_url_exists($spice_starter_sites_importer_pro_filepath[$themename][$checkfiles[$i]])){
                            $findfile=true;
                         }
                         else{
                            // Escape $checkfiles[$i] using esc_html()
                            $escaped_file_name = esc_html($checkfiles[$i]);
                            $string .= '<p>' . $escaped_file_name . ' file not found</p>';
                            echo wp_kses_post($string); // Echo the string after building it
                         }
                     }
                 }
                 if(spice_starter_sites_search_array($themename, $spice_starter_sites_importer_filepath)!=null){
                    for($i=0; $i< sizeof($checkfiles) ; $i++){
                        $string='';
                        if(spice_starter_sites_url_exists($spice_starter_sites_importer_filepath[$themename][$checkfiles[$i]])){
                            $findfile=true;                             
                        }
                        else{
                            // Escape $checkfiles[$i] using esc_html()
                            $escaped_file_name = esc_html($checkfiles[$i]);
                            $string .= '<p>' . $escaped_file_name . ' file not found</p>';
                            echo wp_kses_post($string); // Echo the string after building it
                        }
                    }
                 }
                 if($findfile==true){
                    ?>
                    <p><?php esc_html_e('Are you sure want to import demo content ?','spice-starter-sites');?></p>
                    <ul class="spice-starter-sites-importer-buttons">
                        <li><button class="spice-starter-sites-importer-button spice_starter_sites_importer_appprove button-primary " href="#"><?php esc_html_e('Yes','spice-starter-sites');?></button></li>
                        <li><button class="spice-starter-sites-importer-button spice_starter_sites_importer_cancel button-second" href="#"><?php esc_html_e('No','spice-starter-sites');?></button></li>
                    </ul>                        
                    <?php  
                 }
                ?>        
                <a href="#0" class="spice-starter-sites-importer-popup-close img-replace"></a>            
            </div>
        </div> 
        <?php
    }

}
add_action( 'wp_ajax_nopriv_spice_starter_sites_importer_creater', 'spice_starter_sites_importer_creater');
add_action( 'wp_ajax_spice_starter_sites_importer_creater', 'spice_starter_sites_importer_creater');


function spice_starter_sites_importer_creater(){

    global $wp_filesystem;
    if(class_exists('WooCommerce')){
            // Get all WooCommerce page IDs
            $page_ids = array(
                get_option('woocommerce_shop_page_id'),
                get_option('woocommerce_cart_page_id'),
                get_option('woocommerce_checkout_page_id'),
                get_option('woocommerce_myaccount_page_id'),
            );

            foreach ($page_ids as $page_id) {
                if (!empty($page_id)) {
                    wp_delete_post($page_id, true); // Permanently delete the page
                }
            }

            // Remove the options to prevent WooCommerce from reusing them
            delete_option('woocommerce_shop_page_id');
            delete_option('woocommerce_cart_page_id');
            delete_option('woocommerce_checkout_page_id');
            delete_option('woocommerce_myaccount_page_id');
    }
    
    // Initialize WP_Filesystem
    if ( !function_exists('WP_Filesystem') ) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $creds = request_filesystem_credentials(site_url());
    if ( !WP_Filesystem($creds) ) {
        // If initialization fails, exit the function
        wp_die( esc_html__('Unable to initialize WP_Filesystem.', 'spice-starter-sites'));
    }

    $theme_name = isset($_POST['themename']) ? sanitize_text_field(wp_unslash($_POST['themename'])) : '';     
    require_once SPICE_STARTER_SITES_PLUGIN_PATH . 'inc/importer/autoimporter.php';
    $theme=wp_get_theme();
    global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath;
    $uploads_dir = SPICE_STARTER_SITES_PLUGIN_PATH . 'inc/data/'.$theme_name;
    wp_mkdir_p( $uploads_dir );

    // Loop through files and use WP_Filesystem to write data
    foreach ($spice_starter_sites_importer_filepath as $spice_starter_sites_importer_target) {
        if ($theme_name === $spice_starter_sites_importer_target['slug']) {
            $uploadfiles = array('content', 'customizer', 'widget');
            foreach ($uploadfiles as $file) {
                $url = $spice_starter_sites_importer_target[$file];
                $path = $uploads_dir . '/' . basename($url);

                // Use wp_remote_get() to get the file data
                $response = wp_remote_get($url, array('timeout' => 30));

                // Check for errors
                if (is_wp_error($response)) {
                    wp_die( esc_html__('Failed to fetch the file.', 'spice-starter-sites') );
                }

                // Retrieve the body of the response
                $data = wp_remote_retrieve_body($response);

                // Ensure data is not empty
                if (empty($data)) {
                    wp_die( esc_html__('Downloaded file content is empty.', 'spice-starter-sites') );
                }

                // Write data to the file using WP_Filesystem
                if (! $wp_filesystem->put_contents($path, $data, FS_CHMOD_FILE)) {
                    wp_die( esc_html__('Failed to write file.', 'spice-starter-sites'));
                }
            }
        }
    }


    foreach ($spice_starter_sites_importer_pro_filepath as $spice_starter_sites_importer_pro_target) {
        if ($theme_name === $spice_starter_sites_importer_pro_target['slug']) {
            $uploadfiles = array('content', 'customizer', 'widget');
            foreach ($uploadfiles as $file) {
                $url = $spice_starter_sites_importer_pro_target[$file];
                $path = $uploads_dir . '/' . basename($url);

                // Fetch file contents using wp_remote_get()
                $response = wp_remote_get($url);

                // Check for errors
                if (is_wp_error($response)) {
                    $error_message = $response->get_error_message();

                    // Translators: %s is the error message returned when file fetching fails.
                    wp_die( sprintf( esc_html__('Failed to fetch file: %s', 'spice-starter-sites'), esc_html($error_message) ) );
                }

                // Retrieve the file contents
                $data = wp_remote_retrieve_body($response);

                // Translators: This message is shown when the downloaded file has no content.
                if (empty($data)) {
                    wp_die(esc_html__('Downloaded file is empty.', 'spice-starter-sites'));
                }

                // Translators: This message is shown when WordPress fails to write the downloaded file.
                if (! $wp_filesystem->put_contents($path, $data, FS_CHMOD_FILE)) {
                    wp_die(esc_html__('Failed to write file.', 'spice-starter-sites'));
                }
            }
        }
    }


    if ( ! class_exists( 'Spice_Starter_Sites_Importer_Auto' ) )
        die( 'Spice_Starter_Sites_Importer_Auto not found' );

    // call the function
    // Increase memory limit for the process
    wp_raise_memory_limit('admin'); 
    $autoimport = new Spice_Starter_Sites_Importer_Auto( );
    $args = array(
        'file'        => $uploads_dir. '/content.xml',
        'map_user_id' => 1
    );
    $autoimport->spice_starter_sites_importer_auto_callback($args);
    $autoimport->do_import(); 

    spice_starter_sites_importer_customizer_settings( $uploads_dir. '/customizer.dat');   

    spice_starter_sites_importer_process_import_file($uploads_dir. '/widget.wie' );
    
    do_action('spice_starter_sites_importer_after');
}

//Repace unicode
function spice_starter_sites_decode_unicode_entities($content) {
    // Define replacements in an associative array
    $replacements = [
        'u003c' => '<',
        'u003e' => '>',
        'u0026' => '&',
        'u0022' => '"',
        'u0027' => "'"
    ];

    // Replace all defined sequences using str_replace
    $content = str_replace(array_keys($replacements), array_values($replacements), $content);

    // Decode any other Unicode sequences
    $content = preg_replace_callback('/u([0-9a-fA-F]{4})/', function($matches) {
        return chr(hexdec($matches[1]));
    }, $content);

    return $content;
}

//After import
function spice_starter_sites_after_set_post(){
    update_option( 'show_on_front', 'posts' );
    update_option( 'page_on_front', 0 );
    $theme=wp_get_theme();
    if($theme->name =='Appointment' || 'Appointment Child' == $theme->name || 'Appointment child' == $theme->name  || $theme->name =='Appointment Pro' || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name  || $theme->name =='Appointee' || 'Appointee Child' == $theme->name || 'Appointee child' == $theme->name  || 'Appointment Dark' == $theme->name || 'Appointment Dark child' == $theme->name  || 'Appointment Dark Child' == $theme->name  || 'Shk Corporate' == $theme->name || 'Shk Corporate child' == $theme->name  || 'Shk Corporate Child' == $theme->name  || 'vice' == $theme->name || 'vice child' == $theme->name  || 'vice Child' == $theme->name  || 'Appointment Blue' == $theme->name || 'Appointment Blue child' == $theme->name  || 'Appointment Blue Child' == $theme->name  || 'Appointment Dark' == $theme->name || 'Appointment Dark child' == $theme->name  || 'Appointment Dark Child' == $theme->name  || 'Appointment Green' == $theme->name || 'Appointment Green child' == $theme->name  || 'Appointment Green Child' == $theme->name  || 'Appointment Red' == $theme->name || 'Appointment Red child' == $theme->name  || 'Appointment Red Child' == $theme->name){
        $theme_name= isset($_POST['themename']) ? sanitize_text_field(wp_unslash($_POST['themename'])) : '';
        global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath;
        
        if( $spice_starter_sites_importer_filepath[$theme_name]['categories'] == 'Gutenberg' || $spice_starter_sites_importer_pro_filepath[$theme_name]['categories'] == 'Gutenberg') {
                $main_menu = get_term_by( 'name', 'Primary Menu', 'nav_menu' );
        } 
        else {
            $main_menu = get_term_by( 'name', 'Menu 1', 'nav_menu' );
        }  
       
        set_theme_mod( 'nav_menu_locations', array(
                'primary' => $main_menu->term_id,
            )
        );

        //Fetch pages by title using `get_posts()` instead of `get_page_by_title()`
        $front_page = spice_starter_sites_get_page_by_title('Home');
        $blog_page = spice_starter_sites_get_page_by_title('Blog');

        update_option( 'show_on_front', 'page' );
        update_option('page_on_front', $front_page ? $front_page->ID : 0);
        update_option('page_for_posts', $blog_page ? $blog_page->ID : 0);

        // Process all posts
        $args = array( 'post_type' => 'post' );
        $appoint_posts = get_posts($args);
        foreach ($appoint_posts as $appoint_post){
            $appoint_post->post_title = $appoint_post->post_title.'';
            wp_update_post( $appoint_post );
        }

        // Decode Unicode entities in all posts
        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'any',
            'post_status' => 'any',
        ));

        // Loop through each post and decode Unicode entities
        foreach ($posts as $post) {
            $content = $post->post_content;
            $decoded_content = spice_starter_sites_decode_unicode_entities($content);

            // If content was changed, update the post
            if ($content !== $decoded_content) {
                wp_update_post(array(
                    'ID' => $post->ID,
                    'post_content' => $decoded_content
                ));
            }
        }
    }
    if($theme->name==='Newscrunch' || 'Newscrunch Child' == $theme->name || 'Newscrunch child' == $theme->name  || 'NewsBlogger' == $theme->name){
        $theme_name= isset($_POST['themename']) ? sanitize_text_field(wp_unslash($_POST['themename'])) : '';
        global $spice_starter_sites_importer_filepath,$spice_starter_sites_importer_pro_filepath;
        
        if( $spice_starter_sites_importer_filepath[$theme_name]['categories'] == 'Gutenberg' || $spice_starter_sites_importer_pro_filepath[$theme_name]['categories'] == 'Gutenberg') {
                   
            // Assign the static front page and the blog page using WP_Query
            $front_page_query = new WP_Query(array(
                'post_type'      => 'page',
                'title'          => 'Home',
                'posts_per_page' => 1,
            ));

            if ($front_page_query->have_posts()) {
                $front_page_query->the_post();
                update_option('show_on_front', 'page');
                update_option('page_on_front', get_the_ID());
                wp_reset_postdata();
            }

            // update post title
            $args = array(
                'post_type' => 'post',
            );
            $appoint_posts = get_posts($args);
            foreach ($appoint_posts as $appoint_post){
                $appoint_post->post_title = $appoint_post->post_title.'';
                wp_update_post( $appoint_post );
            }
            $posts = get_posts(array(
                'numberposts' => -1,
                'post_type' => 'post',
            ));

            // Loop through each post and decode Unicode entities
            foreach ($posts as $post) {
                $content = $post->post_content;
                $decoded_content = spice_starter_sites_decode_unicode_entities($content);

                if ($content !== $decoded_content) {
                    $result = wp_update_post([
                        'ID' => $post->ID,
                        'post_content' => $decoded_content
                    ], true);

                    if (is_wp_error($result)) {
                        error_log('Update failed for post ID ' . $post->ID . ': ' . $result->get_error_message());
                    }
                }
            }


            if( $spice_starter_sites_importer_filepath[$theme_name]['slug'] =='spice-shop') {
                update_option( 'woocommerce_shop_page_id', 556 ); // Replace 123 with your Shop page ID
                update_option( 'woocommerce_cart_page_id', 557 ); // Replace 124 with your Cart page ID
                update_option( 'woocommerce_checkout_page_id', 558 ); // Replace 125 with your Checkout page ID
                update_option( 'woocommerce_myaccount_page_id', 559 ); // Replace 126 with your Account page ID
            }
            elseif( $spice_starter_sites_importer_pro_filepath[$theme_name]['slug'] == 'spice-shop-pro') {
                update_option( 'woocommerce_shop_page_id', 221 ); // Replace 123 with your Shop page ID
                update_option( 'woocommerce_cart_page_id', 222 ); // Replace 124 with your Cart page ID
                update_option( 'woocommerce_checkout_page_id', 223 ); // Replace 125 with your Checkout page ID
                update_option( 'woocommerce_myaccount_page_id', 224 ); // Replace 126 with your Account page ID
            }else{
                return;
            }
        }
    }
    
}

function spice_starter_sites_get_page_by_title($title) {
    $pages = get_posts(array(
        'post_type'   => 'page',
        'title'       => $title,
        'post_status' => 'publish',
        'posts_per_page' => 1,
    ));
    return !empty($pages) ? $pages[0] : null;
}

add_action('spice_starter_sites_importer_after','spice_starter_sites_after_set_post',11);

// Custom CSS for OCDI plugin
function spice_starter_sites_ele_css() { 
    $theme=wp_get_theme();    
    if($theme->name =='Appointment' || 'Appointment Child' == $theme->name || 'Appointment child' == $theme->name || $theme->name =='Appointment Pro' || 'Appointment Pro Child' == $theme->name || 'Appointment Pro child' == $theme->name) {?>
        <style >
            .service-section .elementor-inner-column:hover .elementor-widget-container .elementor-icon-wrapper .elementor-icon svg,
            .service-column .elementor-inner-column:hover .elementor-widget-container .elementor-icon-wrapper .elementor-icon svg {
                fill: #FFFFFF ;
            }
            #myTestimonial .col-md-6.pull-left:nth-child(2n+1) {
                clear: both;
            }
        </style>
    <?php 
    }
}
add_action('wp_head', 'spice_starter_sites_ele_css');

function spice_starter_sites_save_img_media_library($image_url, $image_name = 'image') {
    // Get the upload directory
    $upload_dir = wp_upload_dir();
    // Get the file name and path
    $filename = basename($image_url);
    $file_path = trailingslashit($upload_dir['path']) . $filename;
    // Check if the image exists in the upload directory (if it's already uploaded)
    if (file_exists($file_path)) {
        // Check if this file is already in the Media Library
        $attachment_id = attachment_url_to_postid($upload_dir['url'] . '/' . $filename);
        if ($attachment_id) { return $attachment_id; }
    }
    // Fetch the image data if it's not found
    $response = wp_remote_get($image_url);
    if (is_wp_error($response)) {
        return new WP_Error('download_error', 'Failed to fetch the image.');
    }
    $image_data = wp_remote_retrieve_body($response);
    if (empty($image_data)) {
        return new WP_Error('invalid_image_data', 'The image data is empty.');
    }
    // Save the image data to the upload directory
    global $wp_filesystem;
    if (!function_exists('WP_Filesystem')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    WP_Filesystem();
    // Save the image using WP_Filesystem
    if (!$wp_filesystem->put_contents($file_path, $image_data, FS_CHMOD_FILE)) {
        return new WP_Error('file_write_error', 'Failed to write the image file.');
    }
    // Prepare the file array for insertion
    $filetype = wp_check_filetype($filename, null);
    $attachment_data = array(
        'post_mime_type' => $filetype['type'],
        'post_title'     => sanitize_file_name($image_name),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );
    // Insert the attachment into the WordPress Media Library
    $attachment_id = wp_insert_attachment($attachment_data, $file_path);
    if (is_wp_error($attachment_id)) { return $attachment_id; }
    // Generate and save the attachment metadata
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
    wp_update_attachment_metadata($attachment_id, $attachment_metadata);
    return $attachment_id; // Return the attachment ID
}
