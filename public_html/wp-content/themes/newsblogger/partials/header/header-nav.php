<!-- Header section-->
<header class="header-sidebar header-12" itemscope itemtype="http://schema.org/WPHeader">
	<?php get_template_part( 'partials/header/top-header' );?>
	<nav class="spnc spnc-custom trsprnt-menu" role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
		<div class="spnc-header-logo">
			<div class="spnc-container">
				<div>
					<?php do_action('newscrunch_header_logo');?>
				</div>
			</div>
		</div>
		<div class="spnc-navbar <?php if(get_theme_mod('hide_show_sticky_header',false) != false):?>header-sticky<?php endif; ?>">
			<div class="spnc-container">
				<div class="spnc-row">
					<button class="spnc-menu-open spnc-toggle" type="button" aria-controls="menu" aria-expanded="false" onclick="openNav()" aria-label="<?php esc_attr_e('Menu','newsblogger'); ?>"><i class="fas fa-bars"></i>
					</button>
					<?php if( get_theme_mod('hide_show_toggle_icon',true ) == true ): ?>
						<div class=spnc-head-wrap>
							<div class="spnc-header-right">
								<div class="spnc-widget-toggle">
									<a class="spnc-toggle-icon" onclick="spncOpenPanel()" href="#" title="<?php esc_attr_e('Toggle Icon','newsblogger'); ?>"><i class="fas fa-bars"></i></a>
								</div>
							</div>
						</div>
					<?php endif; 
					if( get_theme_mod('hide_show_toggle_icon',true ) == true ):?>	
						<div id="spnc_panelSidebar" class="spnc_sidebar_panel">
							<a href="javascript:void(0)" class="spnc_closebtn" onclick="spncClosePanel()" title="<?php esc_attr_e('Close Icon','newsblogger'); ?>">×</a>
							<div class="spnc-right-sidebar">
								<div class="spnc-sidebar" id="spnc-sidebar-panel-fixed">
							    	<div class="right-sidebar">      
										<?php newscrunch_side_panel_widget_area( 'menu-widget-area' );?>        
									</div>
								</div>
							</div>
						</div>
					<?php endif;?> 
					<!-- /.spnc-collapse -->
					<div class="collapse spnc-collapse" id="spnc-menu-open">
						<a class="spnc-menu-close" onclick="closeNav()" href="#" title="<?php esc_attr_e('Close Off-Canvas','newsblogger'); ?>"><i class="fa-solid fa-xmark"></i></a>
						<?php do_action('newscrunch_header_logo');?>
						<div class="ml-0">
							<?php
							$newsblogger_nav = '<ul class="nav spnc-nav">%3$s';
							$newsblogger_nav .= '<li class="menu-item dropdown search_exists">'; 
				       		$newsblogger_nav .= '</li>';
							$newsblogger_nav .= '</ul>'; 
							$newsblogger_menu_class='';
							wp_nav_menu( array (
								'theme_location'	=>	'primary', 
								'menu_class'    	=>	'nav spnc-nav '.$newsblogger_menu_class.'',
								'items_wrap'    	=>	$newsblogger_nav,
								'fallback_cb'   	=>	'newscrunch_fallback_page_menu',
								'walker'        	=>	new Newscrunch_Nav_Walker()
							));
							?>
						</div>
					</div>
					<!-- /.spnc-collapse -->

					<?php if ( class_exists( 'WooCommerce' ) ) :?>
		                <div class="header-cart nav spnc-nav spnc-right">
		                    <li class="menu-item">
		                        <a class="cart-icon" href="<?php echo esc_url( wc_get_cart_url() ); ?>">
		                        	<i class="fas fa-shopping-cart"></i>
		                            <span class="cart-count"><?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?></span>
		    	                </a>
			                    <ul class="dropdown-menu">
			                 		<div class="cart-dropdown"><?php woocommerce_mini_cart(); ?></div>
		                        </ul>
		                    </li>
		                </div>
					<?php endif;?>
					
					<?php if( get_theme_mod('hide_show_search_icon',true ) == true ):
						if(get_theme_mod('select_search_layout','toggle')=='toggle'):?>
							<ul class="nav spnc-nav">
								<li class="menu-item dropdown">
									<a href="#" title="<?php esc_attr_e('Search','newsblogger'); ?>" class="search-icon dropdown" aria-haspopup="true"
										aria-expanded="false"><i class="fas fa-search"></i></a>
									<ul class="dropdown-menu pull-right search-panel" role="menu">
										<li>
											<div class="form-spnc-container">
												<form method="get" id="searchform" autocomplete="off" class="search-form" action="<?php echo esc_url( home_url( '/' )); ?>">
													<div class="search-results-container"></div>

													<input autofocus type="search" class="search-field" placeholder="<?php echo esc_attr__('Search','newsblogger'); ?>" value="" name="s" id="s" autofocus>
													<input type="submit" class="search-submit" value="<?php echo esc_attr__('Search','newsblogger');?>">
												</form>
											</div>
										</li>
									</ul>
								</li>
							</ul>
						<?php endif;
						if(get_theme_mod('select_search_layout','toggle')=='lightbox'):?>
							<ul class="nav spnc-nav">			         
				         		<li class="menu-item dropdown">
									<a href="#searchbar_fullscreen" class="search-icon" aria-haspopup="true" aria-expanded="false" title="<?php esc_attr_e('Search','newsblogger'); ?>"><i class="fas fa-search"></i></a>
								</li>
							</ul>
							<div id="searchbar_fullscreen">
								<button type="button" class="close" aria-label="<?php esc_attr_e('Close Search','newsblogger'); ?>">×</button>
								<form method="get" id="searchform" autocomplete="off" class="search-form" action="<?php echo esc_url( home_url( '/' )); ?>">
									<label>
										<input autofocus type="search" class="search-field" placeholder="<?php echo esc_attr__('Search','newsblogger'); ?>" value="" name="s" id="s" autofocus>
									</label>
									<input type="submit" class="search-submit btn" value="<?php echo esc_attr__('Search','newsblogger');?>">
								</form>
							</div>
						<?php endif;
					endif;
					if( get_theme_mod('hide_show_dark_light_icon',true ) == true ):?>
						<div class="spnc-dark-layout">
							<a class="spnc-dark-icon" id="spnc-layout-icon" href="#" title="<?php esc_attr_e('Light/Dark Mode','newsblogger'); ?>"><i class="fas fa-solid fa-moon"></i></a>
						</div>
					<?php endif; ?>
				</div> <!-- /.spnc-container-fluid -->
				<div class="spnc-nav-menu-overlay"></div>
			</div>
		</div><!-- /.spnc-navbar -->
	</nav>
	<!--/Logo & Menu Section-->
</header>
<!-- End Header Sidebar-->     
<div class="clrfix"></div>