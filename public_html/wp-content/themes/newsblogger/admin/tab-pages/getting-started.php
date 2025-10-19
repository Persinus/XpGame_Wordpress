<?php
/**
 * Getting started template
 */
$newsblogger_name = wp_get_theme();
?>
<div id="getting_started" class="newsblogger-tab-pane active">
	<div class="spice-container">
		<div class="spice-row">
			<div class="spice-col-1">
				<h1 class="newsblogger-info-title text-center"><?php 
				/* translators: %s: theme name */
				printf( esc_html__('%s Theme Configuration','newsblogger'), esc_html($newsblogger_name) ); ?><?php if( !empty($newsblogger_name['Version']) ): ?> <sup id="newsblogger-theme-version"><?php echo esc_html( $newsblogger_name['Version'] ); ?> </sup><?php endif; ?></h1>
			</div>
		</div>
		<div class="spice-row" style="margin-top: 20px;">
			<div class="spice-col-3"> 
				<div class="newsblogger-page" style="border: none;box-shadow: none;">
					<div class="mockup install-plugin-button">
						<?php if(class_exists('Spice_Starter_Sites')): ?>
							<a href="<?php echo esc_url( admin_url( 'admin.php?page=spice-starter-sites' ) ); ?>">
								<img src="<?php echo esc_url(NEWSBLOGGER_TEMPLATE_DIR_URI.'/admin/assets/img/import-demo-img.png');?>" alt="<?php esc_attr_e('Import Image','newsblogger'); ?>"/>
							</a>
						<?php else: ?>
							<button id="install-plugin-button" data-plugin-url="<?php echo esc_url( 'https://spicethemes.com/extensions/spice-starter-sites.zip' ); ?>">
                                <?php echo esc_html__( 'Install Plugin', 'newsblogger' ); ?>
                            </button>
							<img src="<?php echo esc_url(NEWSBLOGGER_TEMPLATE_DIR_URI.'/admin/assets/img/spice-starter-sites.png');?>" alt="<?php esc_attr_e('Import Image','newsblogger'); ?>"/>
						 	<span class="tooltiptext"><?php echo esc_html__('Need to install & activate the "Spice Starter Sites" plugin to import the demo.', 'newsblogger');?></span>
						<?php endif; ?>
			    	</div>
			    </div>
			</div>
			<div class="spice-col-3"> 
				<div class="newsblogger-page" style="border: none;box-shadow: none;">
			    	<div class="mockup">
			    		<a href="<?php echo esc_url( admin_url( 'customize.php' ) ); ?>" target="_blank">
			    			<img src="<?php echo NEWSBLOGGER_TEMPLATE_DIR_URI.'/admin/assets/img/customize-img.png';?>" alt="<?php esc_attr_e('Customize Image','newsblogger'); ?>"/>
			    		</a>
			    	</div>
				</div>
			</div>
		</div>
		<div class="spice-row" style="margin-top: 20px;">
			<div class="spice-col-3">
				<div class="newsblogger-page">
					<div class="newsblogger-page-top"><?php esc_html_e( 'Theme Features', 'newsblogger' ); ?></div>
					<div class="newsblogger-page-content">
						<ul class="newsblogger-page-list-flex">
							<li>
								<div class="dashicons dashicons-visibility"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=title_tagline' ) ); ?>" target="_blank" title="<?php esc_attr_e('Site Logo','newsblogger'); ?>"><?php esc_html_e('Site Logo','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-layout"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=theme_layout_setting_section' ) ); ?>" target="_blank" title="<?php esc_attr_e('Site Layout','newsblogger'); ?>"><?php esc_html_e('Site Layout','newsblogger'); ?></a>
							</li>
							<li>
							 	<div class="dashicons dashicons-heading"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=newscrunch_theme_header' ) ); ?>" target="_blank" title="<?php esc_attr_e('Header Section','newsblogger'); ?>"><?php esc_html_e('Header Section','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-format-aside"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=sidebar_layout_setting_section' ) ); ?>" target="_blank" title="<?php esc_attr_e('Sidebar Layout','newsblogger'); ?>"><?php esc_html_e('Sidebar Layout','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-share"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=newscrunch_social_icon_section' ) ); ?>" target="_blank" title="<?php esc_attr_e('Social Icons','newsblogger'); ?>"><?php esc_html_e('Social Icons','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-columns"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'customize.php?autofocus[section]=newscrunch_theme_footer' ) ); ?>" target="_blank" title="<?php esc_attr_e('Footer Widgets','newsblogger'); ?>"><?php esc_html_e('Footer Widgets','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-menu"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'nav-menus.php' ) ); ?>" target="_blank" title="<?php esc_attr_e('Menus','newsblogger'); ?>"><?php esc_html_e('Menus','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-welcome-widgets-menus"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url( admin_url( 'widgets.php' ) ); ?>" target="_blank" title="<?php esc_attr_e('Widgets','newsblogger'); ?>"><?php esc_html_e('Widgets','newsblogger'); ?></a>
							</li>
						</ul>
					</div>
				</div>
			</div>
			<div class="spice-col-3">
				<div class="newsblogger-page">
					<div class="newsblogger-page-top"><?php esc_html_e( 'Useful Links', 'newsblogger' ); ?></div>
					<div class="newsblogger-page-content">
						<ul class="newsblogger-page-list-flex page-theme-info">
							<li>
								<div class="dashicons dashicons-info"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://spicethemes.com/newsblogger-wordpress-theme/'); ?>" target="_blank" title="<?php esc_attr_e('Theme Detail','newsblogger'); ?>"><?php esc_html_e('Theme Detail','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-download"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://demo-news.spicethemes.com/startersite-1/'); ?>" target="_blank" title="<?php esc_attr_e('Theme Demos','newsblogger'); ?>"><?php esc_html_e('Theme Demos','newsblogger'); ?></a>
							</li>
							<li> 
								<div class="dashicons dashicons-media-text"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://helpdoc.spicethemes.com/category/newscrunch/'); ?>" target="_blank" title="<?php esc_attr_e('Help Docs','newsblogger'); ?>"><?php echo esc_html__('Documentation','newsblogger'); ?></a>
							</li>
							<li>
								<div class="dashicons dashicons-businesswoman"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://wordpress.org/support/theme/newsblogger/');?>" target="_blank" title="<?php esc_attr_e('Support','newsblogger'); ?>"><?php esc_html_e('Support','newsblogger');?></a>
							</li>							
						    <li> 
						    	<div class="dashicons dashicons-thumbs-up"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://wordpress.org/support/theme/newsblogger/reviews/#new-post'); ?>" target="_blank" title="<?php esc_attr_e('Valuable Feedback','newsblogger'); ?>"><?php echo esc_html__('Your feedback is valuable to us','newsblogger'); ?></a>
							</li>
							<li> 
								<div class="dashicons dashicons-dashboard"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://spicethemes.com/newsblogger-free-vs-plus/'); ?>" target="_blank" title="<?php esc_attr_e('Free or Pro Comparison','newsblogger'); ?>"><?php echo esc_html__('Free vs Pro','newsblogger'); ?></a>
							</li>  
							<li> 
								<div class="dashicons dashicons-flag"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://spicethemes.com/newsblogger-changelog/'); ?>" target="_blank" title="<?php esc_attr_e('Changelog','newsblogger'); ?>"><?php echo esc_html__('Changelog','newsblogger'); ?></a>
							</li>
							<li> 
								<div class="dashicons dashicons-migrate"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://demo-news.spicethemes.com/pro-startersite-1/'); ?>" target="_blank" title="<?php esc_attr_e('Premium Demo','newsblogger'); ?>"><?php echo esc_html__('Premium Version','newsblogger'); ?></a>
							</li>
							<li> 
								<div class="dashicons dashicons-info"></div>
								<a class="newsblogger-page-quick-setting-link" href="<?php echo esc_url('https://spicethemes.com/contact/'); ?>" target="_blank" title="<?php esc_attr_e('Pre-sales Enquiry','newsblogger'); ?>"><?php echo esc_html__('Pre-sales enquiry','newsblogger'); ?></a>
							</li> 						 
						</ul>
					</div>
				</div>
			</div>		
		</div>
	</div>
</div>