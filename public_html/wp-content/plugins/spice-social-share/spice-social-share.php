<?php
/*
* Plugin Name:			Spice Social Share
* Plugin URI:  			
* Description: 			This plugin allows you to add social share buttons to your posts. The plugin is flexible and easy to use.
* Version:     			1.2.2
* Requires at least: 	5.3
* Requires PHP: 		5.2
* Tested up to: 		6.7.1
* Author:      			Spicethemes
* Author URI:  			https://spicethemes.com
* License: 				GPLv2 or later
* License URI: 			https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: 			spice-social-share
* Domain Path:  		/languages
*/

// Exit if accessed directly
if( ! defined('ABSPATH'))
{
	die('Do not open this file directly.');
}

/**
 * Main Spice_Social_Share Class
 *
 * @class Spice_Social_Share
 * @since 0.1
 * @package Spice_Social_Share
 */

final class Spice_Social_Share {

	/**
	 * The version number, plugin url and path.
	 *
	 * @var     string
	 * @access  public
	 * @since   0.1
	 */
	public $version;
	public $plugin_url;
    public $plugin_path;


	/**
	 * Constructor function.
	 *
	 * @access  public
	 * @since   0.1
	 * @return  void
	 */
	public function __construct()
	{
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->version     = '1.2.2';

		define( 'SPICE_SOCIAL_SHARE_URL', $this->plugin_url );
		define( 'SPICE_SOCIAL_SHARE_PATH', $this->plugin_path );
		define( 'SPICE_SOCIAL_SHARE_VERSION', $this->version );

		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );
		add_filter( 'the_content', array( $this, 'social_share_load_links' ) );
		add_shortcode( 'spice_social_share',array($this,'social_share_callback'));
		add_action( 'admin_enqueue_scripts', array( $this,'social_share_admin_script' ));
		add_action( 'wp_enqueue_scripts', array( $this, 'social_share_enqueue_scripts' ));
		add_action( 'customize_register', array( $this, 'social_share_controls' ) );
		add_action( 'after_setup_theme' , array( $this, 'social_share_register_options' ) );
	}
	/**
	* Adds custom controls
	*/
	public function social_share_controls( $wp_customize )
	{
		require_once ( SPICE_SOCIAL_SHARE_PATH . 'inc/customizer/controls/toggle/class-toggle-control.php' );
		require_once ( SPICE_SOCIAL_SHARE_PATH . 'inc/customizer/controls/sortable/class-sortable-control.php' );
		$wp_customize->register_control_type('Spice_Social_Share_Toggle_Control');
		$wp_customize->register_control_type( 'Spice_Social_Share_Control_Sortable' );
	}
	/**
	* Adds customizer options
	*/
	public function social_share_register_options()
	{
		require_once ( SPICE_SOCIAL_SHARE_PATH . 'inc/customizer/sanitization.php' );
		require_once ( SPICE_SOCIAL_SHARE_PATH . 'inc/customizer/customizer.php' );
		require_once ( SPICE_SOCIAL_SHARE_PATH . 'inc/customizer/fonts.php' );
	}
	/**
	* Load Admin css and js
	*/
	public function social_share_admin_script()
	{
		wp_enqueue_style('spice-social-share-admin', SPICE_SOCIAL_SHARE_URL .'assets/css/admin.css', array(), SPICE_SOCIAL_SHARE_VERSION);
	}


	/**
	* Load css and js
	*/
	public function social_share_enqueue_scripts()
	{
		wp_enqueue_style('spice-social-share-font-awesome', SPICE_SOCIAL_SHARE_URL . 'assets/css/font-awesome/css/all.min.css', array(), '6.5.1');
		wp_enqueue_style('spice-social-share-custom', SPICE_SOCIAL_SHARE_URL . 'assets/css/custom.css', array(), SPICE_SOCIAL_SHARE_VERSION);
		wp_enqueue_script('spice-social-share-custom', SPICE_SOCIAL_SHARE_URL . 'assets/js/socialshare.js', array('jquery'), SPICE_SOCIAL_SHARE_VERSION, true );
	}


	/**
	* Load Social Links
	*/
	public function social_share_load_links( $content )
	{
		if( in_array( get_post_type(), array('post') ) && is_singular( 'post' ))
		{
			if(get_theme_mod('spice_social_share_position','after')=='after')
			{
				return $content. $this->social_share_callback();
			}
			else
			{
				return $this->social_share_callback(). $content;
			}
		}
		else
		{
			return $content;
		}
	}

	public function social_share_callback()
	{
		ob_start();
		$spice_social_share_content=get_theme_mod('spice_social_share_heading', esc_html__('Share this content:', 'spice-social-share' ));?>
		<div class="spice_share_wrapper">
		<?php if($spice_social_share_content != ''):?><p class="spice_share_title"><?php echo esc_html($spice_social_share_content);?></p><?php endif;?>
		<div class="social-icon-box"><ul class="spice_social_share_list <?php if($spice_social_share_content != ''):?> margin <?php endif;?>">
		<?php
		$spice_social_share_sort=get_theme_mod( 'spice_social_share_sort', array('spice_facebook_share','spice_twitter_share','spice_linkedin_share','spice_mail_share','spice_pinterest_share','spice_whatsapp_share','spice_print_share'));
			if ( ! empty( $spice_social_share_sort ) && is_array( $spice_social_share_sort ) ) :
				foreach ( $spice_social_share_sort as $spice_social_share_sort_key => $spice_social_share_sort_val ) :
					
					if(get_theme_mod('enable_spice_facebook_share',true)==true):
						if ( 'spice_facebook_share' === $spice_social_share_sort_val ) :?>
						<li class="spice_share_item">
							<button class="spice_social_share_link spice_social_share_link_facebook"  aria-label="<?php esc_attr_e('facebook','spice-social-share'); ?>">
						        <i class="fab fa-facebook-f"></i>
					      </button>	
				  		</li>
						<?php endif;	
					endif;

					if(get_theme_mod('enable_spice_twitter_share',true)==true):
						if ( 'spice_twitter_share' === $spice_social_share_sort_val ) :?>
						<li class="spice_share_item">
							<button class="spice_social_share_link spice_social_share_link_twitter"  aria-label="<?php esc_attr_e('twitter','spice-social-share'); ?>">
						        <i class="fa-brands fa-x-twitter"></i>
					        </button>
					        <input type="hidden" id="spice_social_share_tweetuser" value="<?php echo esc_attr(get_theme_mod('spice_social_share_tw_user',''));?>"/>
				      	</li>
						<?php endif;	
					endif;

					if(get_theme_mod('enable_spice_linkedin_share',true)==true):
						if ( 'spice_linkedin_share' === $spice_social_share_sort_val ) :?>
						<li class="spice_share_item">
							<button class="spice_social_share_link spice_social_share_link_linkedin"  aria-label="<?php esc_attr_e('linkedin','spice-social-share'); ?>">
						        <i class="fab fa-linkedin-in"></i>
					       </button>
				      	</li>
						<?php endif;
					endif;

					if(get_theme_mod('enable_spice_mail_share',true)==true):
						if ( 'spice_mail_share' === $spice_social_share_sort_val ) : ?>
						<li class="spice_share_item">
							<button class="spice_social_share_link spice_social_share_link_mail"  aria-label="<?php esc_attr_e('envelope','spice-social-share'); ?>">
						        <i class="fa fa-envelope"></i>
					      	</button>
				      	</li>
						<?php endif;
					endif;

					if(get_theme_mod('enable_spice_pinterest_share',true)==true):
						if ( 'spice_pinterest_share' === $spice_social_share_sort_val ) :
							$spice_social_share_pin_link=wp_get_attachment_url( get_post_thumbnail_id( get_the_ID() ) ); ?>
						<li class="spice_share_item">
							<button class="spice_social_share_link spice_social_share_link_pinterest"  aria-label="<?php esc_attr_e('pinterest','spice-social-share'); ?>">
								<input type="hidden" id="spice_social_share_pin_link" value="<?php echo esc_attr($spice_social_share_pin_link);?>"/>
						        <i class="fab fa-pinterest"></i>
					      	</button>
					    </li>
						<?php endif;
					endif;
					if(get_theme_mod('enable_spice_whatsapp_share',true)==true):
						if ('spice_whatsapp_share' === $spice_social_share_sort_val ):
							 ?>
						<li class="spice_share_item">
							<button class="spice_social_share_link spice_social_share_link_whatsapp"  aria-label="<?php esc_attr_e('WhatsApp','spice-social-share'); ?>">
						        <i class="fa-brands fa-whatsapp"></i>
					      	</button>
					    </li>
						<?php endif;
					endif;
				if(get_theme_mod('enable_spice_print_share',true)==true):
						if ('spice_print_share' === $spice_social_share_sort_val ):?>
						<li class="spice_share_item">
						 <a class="spice_social_share_link spice_social_share_link_print print-r" href="javascript:window.print()"><i class="fa-solid fa-print"></i></a>
					    </li>
						<?php endif;
					endif;

				endforeach;	
			endif; ?>
		</ul>
	     </div>
		</div>
		<?php if(get_theme_mod('spice_social_share_typo',false) == true): ?>
		<style type="text/css">
            p.spice_share_title
            {
                font-family:'<?php echo esc_attr(get_theme_mod('spice_social_share_fontfamily','Poppins'));?>';
                font-size:<?php echo intval(get_theme_mod('spice_social_share_fontsize',18));?>px;
                line-height:<?php echo intval(get_theme_mod('spice_social_share_lheight',22));?>px;
                font-weight:<?php echo intval(get_theme_mod('spice_social_share_fontweight',400));?>;
                font-style:<?php echo esc_attr(get_theme_mod('spice_social_share_fontstyle','normal'));?>;
                text-transform:<?php echo esc_attr(get_theme_mod('spice_social_share_transform','default'));?>;
            }
        </style>
        <?php endif; 
        if(get_theme_mod('enable_spice_social_share_clr',false) == true):?>
        <style type="text/css">
            p.spice_share_title
            {
               	color: <?php echo esc_attr(get_theme_mod('spice_social_share_heading_color','#858585'));?>;
            }
            body .spice_share_wrapper .spice_social_share_list button.spice_social_share_link,body .spice_share_wrapper .spice_social_share_list a.spice_social_share_link
            {
            	background-color: <?php echo esc_attr(get_theme_mod('spice_social_share_bg_color','#efefef'));?>;
            	color: <?php echo esc_attr(get_theme_mod('spice_social_share_icon_color','#242020'));?>;
            }
            body .spice_share_wrapper .spice_social_share_list button.spice_social_share_link:hover, body .spice_share_wrapper .spice_social_share_list button.spice_social_share_link:focus,
			body .spice_share_wrapper .spice_social_share_list a.spice_social_share_link:hover, body .spice_share_wrapper .spice_social_share_list a.spice_social_share_link:focus
            {
			    background: <?php echo esc_attr(get_theme_mod('spice_social_share_bg_hover_color','#061018'));?>;
			    color: <?php echo esc_attr(get_theme_mod('spice_social_share_icon_hover_color','#ffffff'));?>;
			}
        </style>
		<?php
		endif;
		$output = ob_get_contents();
		ob_end_clean();
		return $output;
	}
	/**
	 * Load the localisation file.
	 *
	 * @access  public
	 * @since   0.1
	 * @return  void
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'spice-social-share' , false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

}
new Spice_Social_Share;