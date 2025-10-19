<?php 
// Prevent direct access to the file
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/*
* Plugin Name:          SEO Optimized Images
* Plugin URI:           https://webriti.com
* Description:          The **SEO Optimized Images** plugin lets you dynamically insert SEO Friendly alt attributes and title attributes to your Images. Simply activate the plugin, provide the pattern, and you are ready to go. 
* Version:              2.1.7
* Requires at least:    3.3+
* Requires PHP:         5.2
* Tested up to:         6.6
* Author:               priyanshu.mittal
* Author URI:           https://webriti.com
* License:              GPLv2 or later
* License URI:          https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain:          seo-optimized-images
* Domain Path:          /lang
*/
if ( ! function_exists( 'sobw_fs' ) ) {
    // Create a helper function for easy SDK access.
    function sobw_fs() {
        global $sobw_fs;

        if ( ! isset( $sobw_fs ) ) {
            // Include Freemius SDK.
            require_once dirname(__FILE__) . '/freemius/start.php';

            $sobw_fs = fs_dynamic_init( array(
                'id'                  => '11446',
                'slug'                => 'seo-optimized-images',
                'type'                => 'plugin',
                'public_key'          => 'pk_6e46acfd96081d4a426676bda6038',
                'is_premium'          => false,
                'has_premium_version' => true,
                'has_addons'          => false,
                'has_paid_plans'      => true,
                'menu'                => array(
                    'slug'           => 'soi_setting',
                    'account' => true,
                    'contact' => true,
					'support' => true,
                ),
               'navigation'                     => 'menu',
            ) );
        }

        return $sobw_fs;
    }

    // Init Freemius.
    sobw_fs();
    // Signal that SDK was initiated.
    do_action( 'sobw_fs_loaded' );
}
// Plugin Root File
if ( ! defined( 'SEO_IMAGES_LITE_PLUGIN_FILE' ) ) {
	define( 'SEO_IMAGES_LITE_PLUGIN_FILE', __FILE__ );
}

add_action('admin_menu', 'soi_add_menu_page');
function soi_add_menu_page()
{
	$seoimageslite_lang_dir  = dirname( plugin_basename( SEO_IMAGES_LITE_PLUGIN_FILE ) ) . '/lang/';
	load_plugin_textdomain( 'seo-optimized-images', false, $seoimageslite_lang_dir );
	
	add_menu_page( 'soi_settings_page', __('SEO Optimized Images','seo-optimized-images'), 'administrator', 'soi_setting','soi_create_setting_page',''); 
}

function soi_plugin_activate() {
    $soi_options_array['soi_alt_value'] = '%name %title';
	$soi_options_array['soi_title_value'] ='';
	$soi_options_array['soi_override_alt_value'] = '1';
	$soi_options_array['soi_override_title_value'] = '1';
	update_option ('soi_options_values', $soi_options_array );
}
// Register activation hook
register_activation_hook(__FILE__, 'soi_plugin_activate');

function soi_create_setting_page()
{
	require_once('seo-optimized-images-settings.php');
}

add_action( 'admin_enqueue_scripts', 'soi_load_custom_wp_admin_style' ); 
function soi_load_custom_wp_admin_style($hook) {
    if ($hook != 'toplevel_page_soi_setting'){return;} // we dont want to load our css on other pages
    if ( ! function_exists( 'get_plugin_data' ) ) {
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    }

    // Get the plugin data
    $plugin_data = get_plugin_data( __FILE__ );
    $soi_version = $plugin_data['Version'];
    // Register the style with the dynamic version number
    wp_register_style('soi_custom_wp_admin_css', plugins_url('css/plugin-admin-panel.css', __FILE__), array(), $soi_version);
    // Enqueue the style
    wp_enqueue_style('soi_custom_wp_admin_css');
    wp_enqueue_style( 'wp-color-picker' ); // here we add the color picker style for use in our plugin   
}

function soi_load_custom_wp_admin_scripts($hook) {  
    if ($hook != 'toplevel_page_soi_setting') { return; } // we dont want to load our js on other pages
    wp_register_script( 'soi_custom_wp_admin_js', plugin_dir_url( __FILE__ ) . 'js/plugin-admin-panel.js',array('jquery','jquery-ui-core','jquery-ui-tabs','wp-color-picker'),'1.0.0', false );
    wp_enqueue_script ('soi_custom_wp_admin_js');       
}
add_action( 'admin_enqueue_scripts', 'soi_load_custom_wp_admin_scripts' );

add_filter('the_content', 'soi_replace_tags', 100);	 
function soi_replace_tags ($content, $alt_text='',$title='')
{   
    global $post; 
    $soi_options_array = get_option('soi_options_values'); 
    $alt_text = isset($soi_options_array['soi_alt_value']) ? wp_kses_post($soi_options_array['soi_alt_value']) : '';
    $title_text = isset($soi_options_array['soi_title_value']) ? wp_kses_post($soi_options_array['soi_title_value']) : '';
    // get the post title for later use
    $post_title = esc_attr($post->post_title);
    // preapre the alt text
    // Check if we need to overide the default alt and existing alt text
    // We will set the flag 1 or 0 
    //check setting for overinding alt tag
    $alt_flag = isset($soi_options_array['soi_override_alt_value']) ? wp_kses_post($soi_options_array['soi_override_alt_value']) : ''; 
    //check setting for overinding title tag
    $title_flag = isset($soi_options_array['soi_override_title_value']) ? wp_kses_post($soi_options_array['soi_override_title_value']) : ''; 
   
    // Set the alt pattern
    // This piece of code first finds all the images in the page 
    // Then we proceed to finding missing or empty alt tags
    $soi_options_array = get_option('soi_options_values');

    // count number of images found in content
    $count = preg_match_all('/<img[^>]+>/i', $content, $images);

    // If we find images on the page then proceed to check the alt tags
    // We also need to calaculate the velue to be inserted in the tags based on user input
   
    if($count>0)
    {   
        // Here we will set the alt value to be inserted. 
        // $t = "$post_title"
        // we want to output like alt = "text"
        
        $t = 'alt="'. esc_attr($alt_text).'"';
        
        // we want to output like title = "text"
        $t_title = 'title="'. esc_attr($title_text) .'"';
        
        foreach($images[0] as $img)
        {   
            // check if the alt tag exists in the image        
            // Get the Name of Image Files. 
            
            $output = preg_match_all( '/<img[^>]+src=[\'"]([^\'"]+)[\'"].*>/i', $img, $matches);
            
            $get_file_name = pathinfo($matches[1][0]);
            $image_file_name = $get_file_name['filename'];
        
            // Get post categories
            $postcategories = get_the_category();
            $post_category='';
            if ($postcategories) {
                foreach($postcategories as $category) {
                    $post_category .= $category->name .' ';
                }
            }

            /// fetch the values of alt and title tags from the option panel
            $alt_text = isset($soi_options_array['soi_alt_value']) ? wp_kses_post($soi_options_array['soi_alt_value']) : '';
            $title_text = isset($soi_options_array['soi_title_value']) ? wp_kses_post($soi_options_array['soi_title_value']) : '';
        
            // Replace the Values for alt tag
            $alt_text = str_replace('%title',$post_title,$alt_text ); 
            $alt_text = str_replace('%name',$image_file_name,$alt_text );
            $alt_text = str_replace('%category',$post_category,$alt_text );

            // replace the values for title tag.
            $title_text = str_replace('%title',$post_title,$title_text ); 
            $title_text = str_replace('%name',$image_file_name,$title_text );
            $title_text = str_replace('%category',$post_category,$title_text );
        
            //configure tags with specified values from option panel.
             $t = ' alt="'.esc_attr($alt_text).'" ';
             $t_title =  ' title="'.esc_attr($title_text).'" ';
         
            //take the alt tag out from the image html markup
            $is_alt = preg_match_all('/alt="([^"]*)"/i', $img, $alt);
        
            ////////////////// check for alt tag /////////////////////////
            // In case there is not alt tag, create the tag and insert the value
            if ($alt_flag == "1")
            {
                // if alt tag is not present than insert the tag.
                if($is_alt == 0)
                {   
                    $new_img = str_replace('<img ', '<img '.$t , $img);
                    $content = str_replace($img, $new_img, $content);
                }
              
                // if alt tag is present
                elseif($is_alt==1)
                { 
                    $text = trim($alt[1][0]);
                    // Check if the alt text is empty. 
                    if(empty($text))
                    {   
                        $new_img = str_replace($alt[0][0], $t, $img);
                        $content = str_replace($img, $new_img, $content);
                    }
                 
                    // Should we override the existing alt tag
                    if ($alt_flag == "1")
                    {
                        $new_img = str_replace($alt[0][0], $t, $img);
                        $content = str_replace($img, $new_img, $content);
                    }
                 
                }
            }//////////////////// checked for alt tag ////////////////////
            
            ///////////////// check for title tag ///////////////////////////  
           
            // first check weither title tag needs to be overide    
            if($title_flag == "1"){
                if(!isset($new_img)) $new_img = $img; // when alt tag is not overridden, than , use actual image markup ie $new_img.
                $is_title = preg_match_all('/title="([^"]*)"/i', $new_img, $title);
             
                // check if title tag is not present in the img tag
                if($is_title == 0)
                {
                   // create the title tag and insert the tag
                   $final_img = str_replace('<img ', '<img '.$t_title , $new_img);
                   $content = str_replace($new_img, $final_img, $content);
                  
                } 
                else { 
                    // you are here bcs title tags exsis and needs to be override
                    $final_img = str_replace($title[0][0], $t_title, $new_img);
                    $content = str_replace($new_img, $final_img, $content);
                 }
            } 
            ///////////////////// title tag checked ////////////////       
        }
    }
    return $content;
}