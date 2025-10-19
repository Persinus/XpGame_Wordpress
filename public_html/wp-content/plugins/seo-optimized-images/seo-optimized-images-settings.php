<?php 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$default_options_data = array (
	'soi_alt_value' => '%name %title',
	'soi_title_value' => '',
	'soi_override_alt_value' => '1',
	'soi_override_title_value' => '1',
);      
// If there is no option setting in DB then assign default data to soi option array..      
$soi_options_array = wp_parse_args(get_option('soi_options_values'), $default_options_data); 
if(isset($_POST['submit_general_settings_tab'])) {
	if(!isset($_POST['general_settings_nonce']) || !wp_verify_nonce($_POST['general_settings_nonce'], 'general_settings_action')) {
		wp_die(esc_html__('Nonce verification failed', 'seo-optimized-images'));
    }
	$soi_options_array['soi_alt_value'] = wp_kses_post($_POST['soi_alt_value']);
	$soi_options_array['soi_title_value'] = wp_kses_post($_POST['soi_title_value']);
	$soi_options_array['soi_override_alt_value'] = wp_kses_post($_POST['soi_override_alt_value']);
	$soi_options_array['soi_override_title_value'] = wp_kses_post($_POST['soi_override_title_value']);
	update_option ('soi_options_values', $soi_options_array );
}
?>
<div class="wrap settings-wrap" id="page-settings">
    <h2><?php esc_html_e('Settings','seo-optimized-images');?></h2>
    <div id="option-tree-header-wrap">
        <ul id="option-tree-header">
            <li id="option-tree-version"><span><?php esc_html_e('SEO Optimized Images','seo-optimized-images'); ?></span>
            </li>
        </ul>
    </div>
    <div id="option-tree-settings-api">
    	<div id="option-tree-sub-header"></div>
	        <div class = "ui-tabs ui-widget ui-widget-content ui-corner-all"> 
				<!-- Tabs Begin-->
	            <ul >
	                <li id="tab_create_setting"><a href="#section_general"><?php esc_html_e('General Settings','seo-optimized-images');?></a>
	                </li>
	                <li id="tab_faq" ><a href="#section_faq"><?php esc_html_e('FAQ','seo-optimized-images');?></a>
	                </li>
	                <li id="tab_support" ><a href="#section_support"><?php esc_html_e('Support','seo-optimized-images');?></a>
	                </li>
					<li id="tab_other" ><a href="#section_other"><?php esc_html_e('Upgrade to PRO','seo-optimized-images');?></a>
	                </li>
	            </ul>
	            <!-- Tabs End-->
	    		<div id="poststuff" class="metabox-holder">
	        		<div id="post-body">
						<div id="post-body-content">
	                		<div id="section_general" class = "postbox">
	                    		<div class="inside">
	                				<div id="setting_theme_options_ui_text" class="format-settings">
	                            		<div class="format-setting-wrap">
	    									<div class = "format-setting type-textarea has-desc">
	        									<div class = "format-setting-inner">            
	    											<form method="post" action="#section_general">
													 	<?php wp_nonce_field('general_settings_action','general_settings_nonce');?>
														<div class="format-setting-label">
															<h3 class="label"><?php esc_html_e('General Settings','seo-optimized-images');?></h3>
														</div>						
	    												<table class="form-table table_custom">    
													        <tr valign="top">
													        	<th scope="row"><?php esc_html_e('Alt attribute value','seo-optimized-images');?></th>
												        		<td><input type="text" name="soi_alt_value" value="<?php echo esc_attr( $soi_options_array['soi_alt_value'] ); ?>" />
												        			<p><?php esc_html_e('The Alt attributes will be dynamically replaced by the above value.', 'seo-optimized-images');?></p>
												     				<p> 
												     					%name - <?php esc_html_e('Will insert image name.','seo-optimized-images');?><br> 
												     					%title- <?php esc_html_e('Will insert post title.','seo-optimized-images');?><br>
												             			%category - <?php esc_html_e('Will insert post categories.','seo-optimized-images');?>
											         				</p>
												        		</td>
													        </tr>
	        
															<tr valign="top">
																<th scope="row"><?php esc_html_e('Override existing alt tag','seo-optimized-images');?></th>
																<td>
																	<select id="soi_override_alt_value" name="soi_override_alt_value">
																		<?php $override_setting = array('1'=> __('YES','seo-optimized-images'), '0'=> __('NO','seo-optimized-images')); ?>
																		<?php foreach($override_setting as $key => $value) { ?>
																			<option value="<?php echo esc_attr($key); ?>" <?php if ($soi_options_array['soi_override_alt_value']==$key) { echo 'selected="selected"'; } ?>  >
																			<?php echo esc_html($value); ?> </option>
																		<?php } ?>
																	</select>
																	<p><?php esc_html_e('Do you want to override existing alt tags?','seo-optimized-images');?></p>
																</td>
															</tr>
	    
															<tr valign="top">
																<th scope="row"><?php esc_html_e('Title attribute value','seo-optimized-images');?></th>
																<td><input type="text" name="soi_title_value" value="<?php echo esc_attr( $soi_options_array['soi_title_value'] ); ?>" />
																	<p><?php esc_html_e('The Title attribute will be dynamically replaced by the above value.', 'seo-optimized-images');?></p>
																</td>
															</tr> 
	    
															<tr valign="top">
																<th scope="row"><?php esc_html_e('Override existing title tag','seo-optimized-images');?></th>
																<td>
																	<select id="soi_override_title_value" name="soi_override_title_value">
																		<?php $override_setting = array('1'=> __('YES','seo-optimized-images'), '0'=> __('NO','seo-optimized-images')); ?>
																		<?php foreach($override_setting as $key => $value) { ?>
																			<option value="<?php echo esc_attr($key); ?>" <?php if ($soi_options_array['soi_override_title_value']==$key) { echo 'selected="selected"'; } ?>  >
																			<?php echo esc_html($value); ?> </option>
																		<?php } ?>
																	</select>
																	<p><?php esc_html_e('Do you want to override existing title tags?','seo-optimized-images'); ?></p>
																</td>
															</tr>
			
															<tr valign="top">
																<th scope="row"><?php esc_html_e('Override alt and title attributes of feature/thumbnail images.','seo-optimized-images');?></th>
																<td>
																	<select disabled>
																		<?php $override_setting = array('1'=> __('YES','seo-optimized-images'), '0'=> __('NO','seo-optimized-images')); ?>
																		<?php foreach($override_setting as $key => $value) { ?>
																			<option value="<?php echo esc_attr($key); ?>" <?php if (isset($soi_options_array['soi_override_thumbnail_images'])==$key) { echo 'selected="selected"'; } ?>  >
																			<?php echo esc_html($value); ?> </option>
																		<?php } ?>
																	</select>
																	<p><?php esc_html_e('Do you want to optimize post/page thumbnail images or to make images SEO friendly?','seo-optimized-images');?><a class="prolinkbtn"><?php esc_html_e('Available In PRO','seo-optimized-images'); ?></a></p>
																</td>
															</tr>
	        
															<tr valign="top">
																<th scope="row"><?php esc_html_e('Override alt and title tags of WooCommerce products images.','seo-optimized-images');?></th>
																<td>
																	<select disabled>
																		<?php $override_setting = array('1'=> __('YES','seo-optimized-images'), '0'=> __('NO','seo-optimized-images')); ?>
																		<?php foreach($override_setting as $key => $value) { ?>
																			<option value="<?php echo esc_attr($key); ?>" <?php if (isset($soi_options_array['soi_override_woo_thumbnail_images'])==$key) { echo 'selected="selected"'; } ?>  >
																			<?php echo esc_html($value); ?> </option>
																		<?php } ?>
																	</select>
																	<p><?php esc_html_e('Do you want to optimize WooCommerce images or make them search engine friendly?','seo-optimized-images');?><a class="prolinkbtn" ><?php esc_html_e('Available In PRO','seo-optimized-images'); ?></a></p>
																</td>
															</tr>
			
															<tr valign="top">
																<th scope="row"><?php esc_html_e('Enable Yoast primary category','seo-optimized-images');?></th>
																<td>
																	<input type="checkbox" id="soi_override_yost_primary_cat" name="soi_override_yost_primary_cat" value="1" <?php if( isset($soi_options_array['soi_override_yost_primary_cat']) == true ) echo "checked"; ?> disabled>
																	<p><?php esc_html_e('Show only primary category created by Yoast SEO Plugin.', 'seo-optimized-images');?><a class="prolinkbtn" ><?php esc_html_e('Available In PRO','seo-optimized-images');?></a></p>
																</td>
															</tr>
														</table>
			
														<table class="form-table ">  
															<tr valign="top">
												        		<td><input type="submit" name="submit_general_settings_tab" value="<?php esc_attr_e('Save Changes','seo-optimized-images'); ?>" class="button button-primary"></td>
												        	</tr>
														</table>
													</form>
												</div>
											</div>
										</div>
	         						</div>
	        					</div>
	    					</div>
							<div id="section_faq" class = "postbox">
							    <div class="inside">
							        <div class="format-settings">
							            <div class="format-setting-wrap">
							                <div class="format-setting-label">
							                	<h3 class="label"><?php esc_html_e('How does it work?','seo-optimized-images');?> </h3>
							                </div>
							            </div>
							        </div>                     
							    	<p><span class="description"><?php esc_html_e('1. The plugin dynamically replaces the alt tags with the pattern specified by you. It makes no changes to the database.','seo-optimized-images'); ?>   </span></p>
							    	<p><span class="description"><?php esc_html_e('2. Since there are no changes to the database, one can have different alt tags for same images on different pages/posts.','seo-optimized-images'); ?></span></p>
							    	<p><span class="description">3. %name - <?php esc_html_e('Will insert image name.','seo-optimized-images'); ?></span></p>
							    	<p><span class="description">4. %title- <?php esc_html_e('Will insert post title.','seo-optimized-images'); ?></span></p>
							    	<p><span class="description">5. %category - <?php esc_html_e('Will insert post categories.','seo-optimized-images'); ?>  </span></p>                
								</div>		
							  	<div class="inside">
							        <div class="format-settings">
							            <div class="format-setting-wrap">
							                <div class="format-setting-label">
							                	<h3 class="label"> <?php esc_html_e('Why optimize alt tags?','seo-optimized-images');?> </h3>
							                </div>
							            </div>
							        </div>                
							    	<p><span class="description">
										<?php 
										echo wp_kses(sprintf(
											// Translators: %s is the URL to learn about alt attribute.
												__('1. According to <a target="_blank" href="%s">this post</a> on the Google Webmaster Blog, Google tends to focus on the information in the ALT text. Creating optimized alt tags can bring more traffic from Search Engines', 'seo-optimized-images'),
												esc_url('http://googlewebmastercentral.blogspot.in/2007/12/using-alt-attributes-smartly.html')
											),
											array(
												'a' => array(
													'href' => array(),
													'target' => array(),
												)
											)
										);
									?>
									</span></p>
							    	<p><span class="description"><?php esc_html_e('2. Take note that the plugin does not make changes to the database. It dynamically replaces the tags at the times of page load.','seo-optimized-images'); ?></span></p>         
								</div>			
							</div>
							<div id="section_support" class = "postbox">
							    <div class="inside">
							        <div class="format-settings">
							            <div class="format-setting-wrap">
							                <div class="format-setting-label">
							                	<h3 class="label"><?php esc_html_e('Support','seo-optimized-images'); ?> </h3>
							                </div>
							            </div>
							        </div>                  
									<p><span class="description">
									<?php 
									echo wp_kses(sprintf(
										// Translators: %s is the URL to get support for the plugin.
											__("1. For any queries contact us via the <a href='%s' target='_blank'>support forums</a>.", "seo-optimized-images"),
											esc_url('https://wordpress.org/support/plugin/seo-optimized-images')
										),
										array(
											'a' => array(
												'href' => array(),
												'target' => array(),
											)
										)
									);
									?>
									</span></p>
							    	<p><span class="description">
									<?php 
									echo wp_kses(sprintf(
											// Translators: %s is the URL to give review for the plugin.
											__('2. If you like our plugin and support, then kindly share your <a href="%s" target="_blank">feedback</a>. Your feedback is valuable.', 'seo-optimized-images'),
											esc_url('https://wordpress.org/support/view/plugin-reviews/seo-optimized-images')
										),
										array(
											'a' => array(
												'href' => array(),
												'target' => array(),
											)
										)
									);
									?>
									</span></p>               
								</div>
							</div>
							<div id="section_other" class = "postbox">
						        <div class="inside">
						            <div class="format-settings">
						                <div class="format-setting-wrap">
						                    <div class="format-setting-label">
						                    	<h3 class="label"><?php esc_html_e('Upgrade to PRO','seo-optimized-images'); ?> </h3>
						                    </div>
						                </div>
						            </div>
									<div class="row" style="margin-left:10px;background: #f7f7f7;padding-top: 10px;padding-bottom: 70px;">
										<div class="span6" style="width:85%;margin-top: auto;">
											<h3><?php esc_html_e('Pricing','seo-optimized-images'); ?></h3>
											<p> <?php esc_html_e('We have 2 packages','seo-optimized-images'); ?></p> 
											<ul>
												<li class="ui-corner-left">
													<h3><?php esc_html_e('SEO - Business package','seo-optimized-images'); ?></h3>
													<p>
													<?php echo wp_kses(sprintf(__('The package supports <b>Featured Images, Custom Post Images, and Custom Rules.</b> The price of this package is <b>$69</b>.', 'seo-optimized-images')), array('b' => array()));
													?>
												    </p>
												</li> 
												<li class="ui-corner-left">
													<h3><?php esc_html_e('SEO - Business with WooCommerce package','seo-optimized-images'); ?></h3>
													<p>
													<?php echo wp_kses(sprintf(__('The package supports <b>WooCommerce Images, Featured Images, Custom Post Images, and Custom Rules.</b> The price of this package is <b>$89</b>.', 'seo-optimized-images')), array('b' => array()));
													?>
													</p>
												</li>
											</ul>
											<p>
											<?php echo wp_kses(sprintf(__('Support and updates will be given for 1 year.<br>If you need updates and support after one year, then simply renew your subscription. If not, then you may still keep using the plugin.', 'seo-optimized-images')), array('br' => array()));
											?>
											</p>
											<h3><?php esc_html_e('How to purchase','seo-optimized-images'); ?></h3>
											<p>
												<?php echo wp_kses(sprintf(
													// Translators: %1$s is the URL to buy the plugin, %2$s is the URL to login to the account.
													__('If you are interested, buy the plugin <a href="%1$s" target="_blank">here</a>. Once the purchase is completed, <a href="%2$s" target="_blank">login</a> to your account and download the latest package.', 'seo-optimized-images'),
													esc_url('http://webriti.com/seo-optimized-images/'), esc_url('https://users.freemius.com/login')), array('a' => array('href' => array(), 'target' => array())));
												?>
											</p>

											<h3><?php esc_html_e('Looking forward to work with you','seo-optimized-images'); ?></h3>
											<p><?php esc_html_e('Thousands of users have enjoyed using our plugin.','seo-optimized-images'); ?></p>
										</div>
									</div>
									<div class="row" style="margin-left:10px;background:#fff;text-align:center"></div>
									<br><br><br>
									<div style="text-align: center;">
										<a class="btn btn-danger btn-large" href="<?php echo esc_url('http://webriti.com/seo-optimized-images/'); ?>" target="_new"> 
										<?php esc_html_e('Upgrade to PRO version', 'seo-optimized-images'); ?>
										</a>&nbsp;
									</div> 
								</div>
							</div>
	        			</div>
	    			</div>
	    		</div>
	        <div class="clear"></div>
        </div>
    </div>
</div>