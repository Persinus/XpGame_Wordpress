<?php
/**
 * Lazy loading meta box.
 *
 * @since 3.2.0
 * @package WP_Smush
 *
 * @var array $conflicts  Conflicting plugins.
 * @var array $cpts       Custom post types.
 * @var array $settings   Lazy loading settings.
 */

use Smush\App\Abstract_Page;
use Smush\Core\CDN\CDN_Helper;
use Smush\Core\Lazy_Load\Lazy_Load_Helper;

if ( ! defined( 'WPINC' ) ) {
	die;
}

// We need this for uploader to work properly.
wp_enqueue_media();
wp_enqueue_style( 'wp-color-picker' );

?>

<p>
	<?php
	esc_html_e( 'This feature delays loading offscreen images until they\'re in view, helping your page load faster, use less bandwidth, and meet Google PageSpeed recommendations such as deferring offscreen images, properly sizing them, and setting explicit width and height.', 'wp-smushit' );
	?>
</p>
<?php if ( ! $conflicts || ! is_array( $conflicts ) || empty( $conflicts ) ) : ?>
	<div class="sui-notice sui-notice-success">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-check-tick sui-md" aria-hidden="true"></i>
				<p><?php esc_html_e( 'Lazy loading is active.', 'wp-smushit' ); ?></p>
			</div>
		</div>
	</div>
<?php else : ?>
	<div class="sui-notice sui-notice-warning">
		<div class="sui-notice-content">
			<div class="sui-notice-message">
				<i class="sui-notice-icon sui-icon-warning-alert sui-md" aria-hidden="true"></i>
				<p>
					<?php
					printf( /* translators: %s - list of plugins */
						esc_html__( "We've detected another active plugin that offers Lazy Load: %s. Smush may not work as expected if Lazy Load is enabled in multiple plugins. For best results, activate Lazy Load in only one plugin at a time.", 'wp-smushit' ),
						'<strong>' . esc_html( join( ', ', $conflicts ) ) . '</strong>'
					);
					?>
				</p>
			</div>
		</div>
	</div>
<?php endif; ?>

<div class="smush-basic-settins" id="lazyload-basic-settings">
	<div class="sui-box-settings-row" id="lazyload-media-types-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Media Types', 'wp-smushit' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Choose which media types you want to lazy load.', 'wp-smushit' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<?php
			$allowed_formats    = array( 'jpeg', 'png', 'webp', 'gif', 'svg' );
			$media_type_matches = array();
			// Default: treat as all selected if format not set or invalid.
			if ( ! isset( $settings['format'] ) || ! is_array( $settings['format'] ) ) {
				$all_selected = true;
			} else {
				$media_type         = $settings['format'];
				$media_type_matches = array_filter(
					array_intersect_key( $media_type, array_flip( $allowed_formats ) ),
					function ( $value ) {
						return 1 == $value;
					}
				);

				$all_selected = count( $media_type_matches ) === count( $allowed_formats );
			}
			?>
			<div class="sui-side-tabs sui-tabs">
				<div data-tabs="">
					<label for="all-media-type" class="sui-tab-item <?php echo $all_selected ? 'active' : ''; ?>">
						<input type="radio" name="lazy-load-media-type" value="all" id="all-media-type" <?php checked( $all_selected ); ?>>
						<?php esc_html_e( 'All', 'wp-smushit' ); ?>
					</label>
					<label for="custom-media-type" class="sui-tab-item <?php echo $all_selected ? '' : 'active'; ?>">
						<input type="radio" name="lazy-load-media-type" value="custom" id="custom-media-type" <?php checked( $all_selected, false ); ?>>
						<?php esc_html_e( 'Custom', 'wp-smushit' ); ?>
					</label>
				</div><!-- end data-tabs -->
				<div data-panes>
					<div class="sui-description <?php echo $all_selected ? 'active' : ''; ?>">
						<?php
						printf(
						/* translators: %s: media types*/
							esc_html__( 'All media types: %s', 'wp-smushit' ),
							'<strong>JPEG, PNG, WebP, GIF, SVG</strong>'
						);
						?>
					</div>
					<div class="sui-tab-boxed <?php echo $all_selected ? '' : 'active'; ?>">
						<?php
						foreach ( $allowed_formats as $format ) {
							// If image sizes array isn't set, mark all checked ( Default Values ).
							$checked = $all_selected || ! empty( $media_type_matches[ $format ] );
							?>
							<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
								<input type='hidden' value='0' name='format[<?php echo esc_attr( $format ); ?>]' />
								<input class="lazyload-media-type-input" type="checkbox" <?php checked( $checked, true ); ?>
										id="format-<?php echo esc_attr( $format ); ?>"
										name="format[<?php echo esc_attr( $format ); ?>]">
								<span aria-hidden="true">&nbsp;</span>
								<span>
									<?php echo esc_attr( sprintf( '.%s', $format ) ); ?>
								</span>
							</label>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Embedded Content -->
	<?php $iframe_format_enabled = ! isset( $settings['format']['iframe'] ) || $settings['format']['iframe']; ?>
	<div class="sui-box-settings-row" id="lazyload-embed-content-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Embedded Content', 'wp-smushit' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Choose lazy load options for videos and iframes.', 'wp-smushit' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<div class="sui-form-field">
				<label for="format-iframe" class="sui-toggle">
					<input type='hidden' value='0' name='format[iframe]' />
					<input
						type="checkbox"
						id="format-iframe"
						name="format[iframe]"
						aria-labelledby="format-iframe-label"
						aria-describedby="format-iframe-description"
						<?php checked( $iframe_format_enabled ); ?>
					/>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="format-iframe-label" class="sui-toggle-label">
						<?php esc_html_e( 'Enable lazy loading for iframes', 'wp-smushit' ); ?>
					</span>
					<?php
						$class_names = array(
							'sui-form-field',
							'lazyload-embed-videos',
						);

						if ( ! $iframe_format_enabled ) {
							$class_names[] = 'sui-hidden';
						}
						?>
					<div id="format-iframe-description" class="<?php echo esc_attr( join( ' ', $class_names ) ); ?>" style="margin-left:0;">
						<label for="embed_video" class="sui-checkbox sui-checkbox-stacked">
							<input type='hidden' value='0' name='format[embed_video]' />
							<input type="checkbox" name="format[embed_video]" id="embed_video" <?php checked( ! empty( $settings['format']['embed_video'] ) ); ?> />
							<span aria-hidden="true"></span>
							<span><?php esc_html_e( 'Replace YouTube or Vimeo iframes with preview images', 'wp-smushit' ); ?></span>
							<span style="margin-left:10px; font-size: 9px; line-height: 12px; padding: 2px 6px 1px; border-radius: 12px;" class="sui-tag smush-sui-tag-new"><?php esc_html_e( 'New', 'wp-smushit' ); ?></span>
						</label>
						<span class="sui-description">
							<?php esc_html_e( 'This can significantly improve loading time if there are a lot of embedded videos on a page.', 'wp-smushit' ); ?>
						</span>
					</div>
				</label>
			</div>
		</div>
	</div>
	<!-- End Embedded Content -->

	<div class="sui-box-settings-row" id="lazyload-output-locations-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Output Locations', 'wp-smushit' ); ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'By default we will lazy load all images, but you can refine this to specific media outputs too.', 'wp-smushit' ); ?>
			</span>
		</div>
		<div class="sui-box-settings-col-2">
			<?php
			$output_location_types = array(
				'content'    => __( 'Content', 'wp-smushit' ),
				'widgets'    => __( 'Widgets', 'wp-smushit' ),
				'thumbnails' => __( 'Post Thumbnail', 'wp-smushit' ),
				'gravatars'  => __( 'Gravatars', 'wp-smushit' ),
			);
			$enabled_output_types  = array();

			// Default: assume all types selected if 'format' not set or invalid.
			if ( ! isset( $settings['output'] ) || ! is_array( $settings['output'] ) ) {
				$all_types_selected = true;
			} else {
				$format_settings = $settings['output'];

				$enabled_output_types = array_filter(
					array_intersect_key( $format_settings, $output_location_types ),
					function ( $value ) {
						return 1 == $value;
					}
				);

				$all_types_selected = count( $enabled_output_types ) === count( $output_location_types );
			}
			?>
			<div class="sui-side-tabs sui-tabs">
				<div data-tabs="">
					<label for="all-output-location" class="sui-tab-item <?php echo $all_types_selected ? 'active' : ''; ?>">
						<input type="radio" name="lazy-load-output-locations" value="all" id="all-output-location" <?php checked( $all_types_selected ); ?>>
						<?php esc_html_e( 'All', 'wp-smushit' ); ?>
					</label>
					<label for="custom-output-location" class="sui-tab-item <?php echo $all_types_selected ? '' : 'active'; ?>">
						<input type="radio" name="lazy-load-output-locations" value="custom" id="custom-output-location" <?php checked( $all_types_selected, false ); ?>>
						<?php esc_html_e( 'Custom', 'wp-smushit' ); ?>
					</label>
				</div><!-- end data-tabs -->
				<div data-panes>
					<div class="sui-description <?php echo $all_types_selected ? 'active' : ''; ?>">
						<?php
						printf(
						/* translators: %s: locations*/
							esc_html__( 'All locations: %s', 'wp-smushit' ),
							'<strong>Content, Widgets, Post Thumbnail, Gravatars.</strong>'
						);
						?>
					</div>
					<div class="sui-tab-boxed <?php echo $all_types_selected ? '' : 'active'; ?>">
						<?php
						foreach ( $output_location_types as $location => $location_name ) {
							// If image sizes array isn't set, mark all checked ( Default Values ).
							$checked = $all_types_selected || ! empty( $enabled_output_types[ $location ] );
							?>
							<label class="sui-checkbox sui-checkbox-stacked sui-checkbox-sm">
								<input type='hidden' value='0' name='output[<?php echo esc_attr( $location ); ?>]' />
								<input type="checkbox" <?php checked( $checked, true ); ?>
										id="output-<?php echo esc_attr( $location ); ?>"
										name="output[<?php echo esc_attr( $location ); ?>]">
								<span aria-hidden="true">&nbsp;</span>
								<span>
									<?php echo esc_attr( $location_name ); ?>
								</span>
							</label>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Image Sizing -->
	<?php
		$auto_resizing_enabled     = $this->settings->is_auto_resizing_active();
		$image_dimensions_enabled  = $this->settings->should_add_missing_dimensions();
		$cdn_dynamic_sizes_enabled = CDN_Helper::get_instance()->is_dynamic_sizes_active();
		$is_pro                    = WP_Smush::is_pro();

	?>
	<div class="sui-box-settings-row" id="lazyload-image-resizing-settings-row">
		<div class="sui-box-settings-col-1">
			<span class="sui-settings-label">
				<?php esc_html_e( 'Image Sizing', 'wp-smushit' ); ?>
				<?php if ( ! $is_pro ) : ?>
				<span class="sui-tag sui-tag-pro" style="margin-left: 3px;"><?php esc_html_e( 'PRO', 'wp-smushit' ); ?></span>
				<?php endif; ?>
				<?php if ( self::should_show_new_feature_hotspot() ) : ?>
					<?php
					// Hide the new feature hotspot if the user has already seen it.
					self::hide_new_feature_hotspot();
					?>
					<span class="smush-new-feature-dot" style="position:relative; margin-left: 20px;"></span>
				<?php endif; ?>
			</span>
			<span class="sui-description">
				<?php esc_html_e( 'Automatically resize, add dimensions, and generate dynamic sizes to ensure that the correct image size is loaded in every situation.', 'wp-smushit' ); ?>
			</span>
			<?php
			if ( ! $is_pro ) :
				$upgrade_url = $this->get_utm_link(
					array(
						'utm_campaign' => 'smush_lazyload_image-sizing',
					)
				);
			?>
			<a class="smush-upsell-link" href="<?php echo esc_url( $upgrade_url ); ?>" style="display:block; margin-top:-3px; font-size:13px" target="_blank">
				<strong>
					<?php esc_html_e( 'SALE - Limited Offer', 'wp-smushit' ); ?>
				</strong>
				<span class="sui-icon-open-new-window" aria-hidden="true"></span>
			</a>
			<?php endif; ?>
		</div>
		<div class="sui-box-settings-col-2">
			<!-- Auto Resizing -->
			<div class="sui-form-field">
				<label for="auto_resizing" class="sui-toggle">
					<input type='hidden' value='0' name='auto_resizing' />
					<input
						type="checkbox"
						id="auto_resizing"
						name="auto_resizing"
						aria-labelledby="auto_resizing-label"
						aria-describedby="auto_resizing-description"
						<?php echo $is_pro ? '' : 'disabled'; ?>
						<?php checked( $auto_resizing_enabled ); ?>
					/>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<div id="auto_resizing-label" class="sui-toggle-label">
						<?php esc_html_e( 'Enable automatic resizing of my images', 'wp-smushit' ); ?>
					</div>
					<div class="sui-description" style="pointer-events: all">
						<?php esc_html_e( 'Load your site faster with images that automatically fit the container size, thus eliminating the “Properly size images” PageSpeed warnings.', 'wp-smushit' ); ?>
					</div>
					<div style="margin-left: 44px">
					<?php if ( $is_pro && ! $cdn_dynamic_sizes_enabled && $this->settings->has_cdn_page() ) : ?>
						<div class="sui-upsell-notice" style="margin-top:10px">
							<div class="sui-notice sui-notice-blue">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<span class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></span>
										<p><?php esc_html_e( 'Optimize performance even more by generating extra image sizes on the fly with the Dynamic Image Sizing feature in the CDN.', 'wp-smushit' ); ?></p>
										<p><a class="sui-button smush-sui-button-outline smush-sui-button-outline-blue" href="<?php echo esc_url( $this->get_url( 'smush-cdn' ) ); ?>#cdn_dynamic_sizes-settings-row"><?php esc_html_e( 'Go To CDN', 'wp-smushit' ); ?></a></p>
									</div>
								</div>
							</div>
						</div>
					<?php endif; ?>
					</div>
				</label>
			</div>
			<!-- End Auto Resizing -->
			<!-- Image Dimensions -->
			<div class="sui-form-field">
				<label for="image_dimensions" class="sui-toggle">
					<input type='hidden' value='0' name='image_dimensions' />
					<input
						type="checkbox"
						id="image_dimensions"
						name="image_dimensions"
						aria-labelledby="image_dimensions-label"
						aria-describedby="image_dimensions-description"
						<?php echo $is_pro ? '' : 'disabled'; ?>
						<?php checked( $image_dimensions_enabled ); ?>
					/>
					<span class="sui-toggle-slider" aria-hidden="true"></span>
					<span id="image_dimensions-label" class="sui-toggle-label">
						<?php esc_html_e( 'Add missing dimensions', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Add width and height attributes before the image is loaded to speed up rendering and reduce layout shifts. This resolves PageSpeed Insights "Ensure images have explicit width and height" recommendation.', 'wp-smushit' ); ?>
					</span>
				</label>
			</div>
			<!-- End Image Dimensions -->
		</div>
	</div>
</div>
<div class="sui-accordion sui-accordion-block smush-advanced-settings" id="lazyload-advanced-settings">
	<div class="sui-accordion-item">
		<div class="sui-accordion-item-header">
			<div class="sui-accordion-item-title sui-trim-title"><?php esc_html_e( 'Advanced Settings', 'wp-smushit' ); ?></div>
			<div class="sui-accordion-col-auto">
				<button type="button" class="sui-button-icon sui-accordion-open-indicator" aria-label="Open Item"><span class="sui-icon-chevron-down" aria-hidden="true"></span></button>
			</div>
		</div>
		<div class="sui-accordion-item-body">

			<div class="sui-box-settings-row" id="lazyload-include-exclude-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Exclusions', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'By default lazy loading is enabled for all content. Here you can define pages, posts, image classes that you want to exclude.', 'wp-smushit' ); ?>
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<div class="sui-tabs sui-tabs-overflow">
						<div role="tablist" class="sui-tabs-menu">
							<button
									type="button"
									role="tab"
									id="post_type_exclusions"
									class="sui-tab-item active"
									aria-controls="post_type_exclusions_content"
									aria-selected="true">
								<?php esc_html_e( 'Post Types', 'wp-smushit' ); ?>
							</button>

							<button
									type="button"
									role="tab"
									id="custom_exclusions"
									class="sui-tab-item"
									aria-controls="custom_exclusions_content"
									aria-selected="false"
									tabindex="-1">
								<?php esc_html_e( 'Custom Exclusions', 'wp-smushit' ); ?>
							</button>
						</div>
						<div class="sui-tabs-content">

							<div
									role="tabpanel"
									tabindex="0"
									id="post_type_exclusions_content"
									class="sui-tab-content active"
									aria-labelledby="post_type_exclusions">
								<div class="sui-form-field">
									<div class="sui-description">
										<?php esc_html_e( 'Choose the post types you want to lazy load.', 'wp-smushit' ); ?>
									</div>
									<table class="sui-table">
										<thead>
										<tr>
											<th><?php esc_html_e( 'Name', 'wp-smushit' ); ?></th>
											<th><?php esc_html_e( 'Type', 'wp-smushit' ); ?></th>
											<th>&nbsp;</th>
										</tr>
										</thead>
										<tbody>
										<tr>
											<td><strong><?php esc_html_e( 'Frontpage', 'wp-smushit' ); ?></strong></td>
											<td>frontpage</td>
											<td>
												<label class="sui-toggle" for="include-frontpage">
													<input type='hidden' value='0' name='include[frontpage]' />
													<input type="checkbox" name="include[frontpage]" id="include-frontpage" <?php checked( $settings['include']['frontpage'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<tr>
											<td><strong><?php esc_html_e( 'Blog', 'wp-smushit' ); ?></strong></td>
											<td>home</td>
											<td>
												<label class="sui-toggle" for="include-home">
													<input type='hidden' value='0' name='include[home]' />
													<input type="checkbox" name="include[home]" id="include-home" <?php checked( $settings['include']['home'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<tr>
											<td><strong><?php esc_html_e( 'Pages', 'wp-smushit' ); ?></strong></td>
											<td>page</td>
											<td>
												<label class="sui-toggle" for="include-page">
													<input type='hidden' value='0' name='include[page]' />
													<input type="checkbox" name="include[page]" id="include-page" <?php checked( $settings['include']['page'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<tr>
											<td><strong><?php esc_html_e( 'Posts', 'wp-smushit' ); ?></strong></td>
											<td>single</td>
											<td>
												<label class="sui-toggle" for="include-single">
													<input type='hidden' value='0' name='include[single]' />
													<input type="checkbox" name="include[single]" id="include-single" <?php checked( $settings['include']['single'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<tr>
											<td><strong><?php esc_html_e( 'Archives', 'wp-smushit' ); ?></strong></td>
											<td>archive</td>
											<td>
												<label class="sui-toggle" for="include-archive">
													<input type='hidden' value='0' name='include[archive]' />
													<input type="checkbox" name="include[archive]" id="include-archive" <?php checked( $settings['include']['archive'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<tr>
											<td><strong><?php esc_html_e( 'Categories', 'wp-smushit' ); ?></strong></td>
											<td>category</td>
											<td>
												<label class="sui-toggle" for="include-category">
													<input type='hidden' value='0' name='include[category]' />
													<input type="checkbox" name="include[category]" id="include-category" <?php checked( $settings['include']['category'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<tr>
											<td><strong><?php esc_html_e( 'Tags', 'wp-smushit' ); ?></strong></td>
											<td>tag</td>
											<td>
												<label class="sui-toggle" for="include-tag">
													<input type='hidden' value='0' name='include[tag]' />
													<input type="checkbox" name="include[tag]" id="include-tag" <?php checked( $settings['include']['tag'] ); ?>>
													<span class="sui-toggle-slider"></span>
												</label>
											</td>
										</tr>
										<?php foreach ( $cpts  as $custom_post_type ) : ?>
											<tr>
												<td><strong><?php echo esc_html( $custom_post_type->label ); ?></strong></td>
												<td><?php echo esc_html( $custom_post_type->name ); ?></td>
												<td>
													<label class="sui-toggle" for="include-<?php echo esc_attr( $custom_post_type->name ); ?>">
														<input type='hidden' value='0' name='include[<?php echo esc_attr( $custom_post_type->name ); ?>]' />
														<input type="checkbox" name="include[<?php echo esc_attr( $custom_post_type->name ); ?>]" id="include-<?php echo esc_attr( $custom_post_type->name ); ?>"
															<?php checked( ! isset( $settings['include'][ $custom_post_type->name ] ) || $settings['include'][ $custom_post_type->name ] ); ?> />
														<span class="sui-toggle-slider"></span>
													</label>
												</td>
											</tr>
										<?php endforeach; ?>
										</tbody>
									</table>
								</div>
							</div>

							<div
									role="tabpanel"
									tabindex="0"
									id="custom_exclusions_content"
									class="sui-tab-content"
									aria-labelledby="custom_exclusions"
									hidden>
								<div class="sui-form-field">
									<div class="sui-description" style="margin-bottom: 20px;">
										<?php esc_html_e( 'Define specific items that you want to be excluded from lazy loading.', 'wp-smushit' ); ?>
									</div>
									<strong><?php esc_html_e( 'Post, Pages & URLs', 'wp-smushit' ); ?></strong>
									<div class="sui-description">
										<?php esc_html_e( 'Add URLs to the posts and/or pages you want to disable lazy loading on.', 'wp-smushit' ); ?>
									</div>
									<?php
									$strings = '';
									if ( is_array( $settings['exclude-pages'] ) ) {
										$strings = join( PHP_EOL, $settings['exclude-pages'] );
									}
									?>
									<textarea class="sui-form-control" name="exclude-pages" placeholder="<?php esc_attr_e( 'E.g. /page', 'wp-smushit' ); ?>"><?php echo esc_attr( $strings ); ?></textarea>
									<div class="sui-description">
										<?php
										printf(
										/* translators: %1$s - opening strong tag, %2$s - closing strong tag */
											esc_html__( 'Add page or post URLs one per line in relative format. I.e. %1$s/example-page%2$s or %1$s/example-page/sub-page/%2$s.', 'wp-smushit' ),
											'<strong>',
											'</strong>'
										);
										?>
									</div>
								</div>

								<div class="sui-form-field">
									<strong><?php esc_html_e( 'Keywords', 'wp-smushit' ); ?></strong>
									<div class="sui-description">
										<?php esc_html_e( 'Specify keywords from the image or iframe code - classes, IDs, filenames, source URLs or any string of characters - to exclude from lazy loading (case-sensitive).', 'wp-smushit' ); ?>
									</div>
									<?php
									$strings = '';
									if ( is_array( $settings['exclude-classes'] ) ) {
										$strings = join( PHP_EOL, $settings['exclude-classes'] );
									}
									?>
									<textarea class="sui-form-control" name="exclude-classes" placeholder="<?php esc_attr_e( 'Add keywords, one per line', 'wp-smushit' ); ?>"><?php echo esc_attr( $strings ); ?></textarea>
									<div class="sui-description">
										<?php
										printf(
										/* translators: %1$s - opening strong tag, %2$s - closing strong tag */
											esc_html__( 'Add one keyword per line. E.g. %1$s#image-id%2$s or %1$s.image-class%2$s or %1$slogo_image%2$s or %1$sgo_imag%2$s or %1$sx.com/%2$s', 'wp-smushit' ),
											'<strong>',
											'</strong>'
										);
										?>
									</div>
								</div>
							</div>
						</div>
					</div>

				</div>
			</div>
			<div class="sui-box-settings-row" id="lazyload-display-animation-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Display & Animation', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Choose how you want preloading images to be displayed, as well as how they animate into view.', 'wp-smushit' ); ?>
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<strong><?php esc_html_e( 'Display', 'wp-smushit' ); ?></strong>
					<div class="sui-description">
						<?php esc_html_e( 'Choose how you want the non-loaded image to look.', 'wp-smushit' ); ?>
					</div>

					<div class="sui-side-tabs sui-tabs">
						<div data-tabs>
							<label for="animation-fadein" class="sui-tab-item <?php echo 'fadein' === $settings['animation']['selected'] ? 'active' : ''; ?>">
								<input type="radio" name="animation[selected]" value="fadein" id="animation-fadein" <?php checked( $settings['animation']['selected'], 'fadein' ); ?> />
								<?php esc_html_e( 'Fade In', 'wp-smushit' ); ?>
							</label>
							<label for="animation-spinner" class="sui-tab-item <?php echo 'spinner' === $settings['animation']['selected'] ? 'active' : ''; ?>">
								<input type="radio" name="animation[selected]" value="spinner" id="animation-spinner" <?php checked( $settings['animation']['selected'], 'spinner' ); ?> />
								<?php esc_html_e( 'Spinner', 'wp-smushit' ); ?>
							</label>
							<label for="animation-placeholder" class="sui-tab-item <?php echo 'placeholder' === $settings['animation']['selected'] ? 'active' : ''; ?>">
								<input type="radio" name="animation[selected]" value="placeholder" id="animation-placeholder" <?php checked( $settings['animation']['selected'], 'placeholder' ); ?> />
								<?php esc_html_e( 'Placeholder', 'wp-smushit' ); ?>
							</label>
							<label for="animation-disabled" class="sui-tab-item <?php echo 'none' === $settings['animation']['selected'] ? 'active' : ''; ?>">
								<input type="radio" name="animation[selected]" value="none" id="animation-disabled" <?php checked( $settings['animation']['selected'], 'none' ); ?> />
								<?php esc_html_e( 'None', 'wp-smushit' ); ?>
							</label>
						</div><!-- end data-tabs -->
						<div data-panes>
							<div class="sui-tab-boxed <?php echo 'fadein' === $settings['animation']['selected'] ? 'active' : ''; ?>">
								<strong><?php esc_html_e( 'Animation', 'wp-smushit' ); ?></strong>
								<span class="sui-description">
									<?php esc_html_e( 'Once the image has loaded, choose how you want the image to display when it comes into view.', 'wp-smushit' ); ?>
								</span>
								<div class="sui-form-field-inline">
									<div class="sui-form-field">
										<label for="fadein-duration" class="sui-label"><?php esc_html_e( 'Duration', 'wp-smushit' ); ?></label>
										<input type='hidden' value='0' name='animation[duration]' />
										<input type="number" name="animation[duration]" placeholder="400" value="<?php echo absint( $settings['animation']['fadein']['duration'] ); ?>" id="fadein-duration" class="sui-form-control sui-input-sm sui-field-has-suffix">
										<span class="sui-field-suffix"><?php esc_html_e( 'ms', 'wp-smushit' ); ?></span>
									</div>
									<div class="sui-form-field">
										<label for="fadein-delay" class="sui-label"><?php esc_html_e( 'Delay', 'wp-smushit' ); ?></label>
										<input type='hidden' value='0' name='animation[delay]' />
										<input type="number" name="animation[delay]" placeholder="0" value="<?php echo absint( $settings['animation']['fadein']['delay'] ); ?>" id="fadein-delay" class="sui-form-control sui-input-sm sui-field-has-suffix">
										<span class="sui-field-suffix"><?php esc_html_e( 'ms', 'wp-smushit' ); ?></span>
									</div>
								</div>
							</div>

							<div class="sui-tab-boxed <?php echo 'spinner' === $settings['animation']['selected'] ? 'active' : ''; ?>" id="smush-lazy-load-spinners">
								<span class="sui-description">
									<?php esc_html_e( 'Display a spinner where the image will be during lazy loading. You can choose a predefined spinner, or upload your own GIF.', 'wp-smushit' ); ?>
								</span>
								<label class="sui-label"><?php esc_html_e( 'Spinner', 'wp-smushit' ); ?></label>
								<div class="sui-box-selectors">
									<ul>
										<?php for ( $i = 1; $i <= 5; $i++ ) : ?>
											<li>
												<label for="spinner-<?php echo absint( $i ); ?>" class="sui-box-selector">
													<input type="radio" name="animation[spinner-icon]" id="spinner-<?php echo absint( $i ); ?>" value="<?php echo absint( $i ); ?>" <?php checked( (int) $settings['animation']['spinner']['selected'] === $i ); ?> />
													<span>
														<img alt="<?php esc_attr_e( 'Spinner image', 'wp-smushit' ); ?>&nbsp;<?php echo absint( $i ); ?>" src="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/smush-lazyloader-' . $i . '.gif' ); ?>" />
													</span>
												</label>
											</li>
										<?php endfor; ?>

										<?php foreach ( $settings['animation']['spinner']['custom'] as $image ) : ?>
											<?php $custom_link = wp_get_attachment_image_src( $image, 'full' ); ?>
											<li><label for="spinner-<?php echo absint( $image ); ?>" class="sui-box-selector">
													<input type="radio" name="animation[spinner-icon]" id="spinner-<?php echo absint( $image ); ?>" value="<?php echo absint( $image ); ?>" <?php checked( $image === $settings['animation']['spinner']['selected'] ); ?> />
													<span>
										<button class="remove-selector sui-button-icon sui-tooltip smush-ll-remove" data-id="<?php echo absint( $image ); ?>" data-tooltip="<?php esc_attr_e( 'Remove', 'wp-smushit' ); ?>">
											<i class="sui-icon-close" aria-hidden="true" data-id="<?php echo absint( $image ); ?>" data-type="spinner"></i>
										</button>

										<img alt="<?php esc_attr_e( 'Spinner image', 'wp-smushit' ); ?>&nbsp;<?php echo absint( $image ); ?>" src="<?php echo esc_url( $custom_link[0] ); ?>" />
									</span>
												</label></li>
										<?php endforeach; ?>
									</ul>

									<div class="sui-upload">
										<input type="hidden" name="animation[custom-spinner]" id="smush-spinner-icon-file" value="">

										<div class="sui-upload-image" aria-hidden="true">
											<div class="sui-image-mask"></div>
											<div role="button" class="sui-image-preview" id="smush-spinner-icon-preview" onclick="WP_Smush.Lazyload.addLoaderIcon()"></div>
										</div>

										<a class="sui-upload-button" id="smush-upload-spinner" onclick="WP_Smush.Lazyload.addLoaderIcon()">
											<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Upload file', 'wp-smushit' ); ?>
										</a>

										<div class="sui-upload-file" id="smush-remove-spinner">
											<span></span>
											<button aria-label="<?php esc_attr_e( 'Remove file', 'wp-smushit' ); ?>">
												<i class="sui-icon-close" aria-hidden="true"></i>
											</button>
										</div>
									</div>
								</div>
							</div>

							<div class="sui-tab-boxed <?php echo 'placeholder' === $settings['animation']['selected'] ? 'active' : ''; ?>" id="smush-lazy-load-placeholder">
					<span class="sui-description">
						<?php esc_html_e( 'Display a placeholder to display instead of the actual image during lazy loading. You can choose a predefined image, or upload your own.', 'wp-smushit' ); ?>
					</span>
								<label class="sui-label"><?php esc_html_e( 'Image', 'wp-smushit' ); ?></label>
								<div class="sui-box-selectors">
									<ul>
										<?php for ( $i = 1; $i <= 2; $i++ ) : ?>
											<li><label for="placeholder-icon-<?php echo absint( $i ); ?>" class="sui-box-selector">
													<input type="radio" name="animation[placeholder-icon]" id="placeholder-icon-<?php echo absint( $i ); ?>" value="<?php echo absint( $i ); ?>" <?php checked( (int) $settings['animation']['placeholder']['selected'] === $i ); ?> />
													<span>
										<img alt="<?php esc_attr_e( 'Placeholder image', 'wp-smushit' ); ?>&nbsp;<?php echo absint( $i ); ?>" src="<?php echo esc_url( WP_SMUSH_URL . 'app/assets/images/smush-placeholder.png' ); ?>" />
									</span>
												</label></li>
										<?php endfor; ?>

										<?php foreach ( $settings['animation']['placeholder']['custom'] as $image ) : ?>
											<?php $custom_link = wp_get_attachment_image_src( $image, 'full' ); ?>
											<li><label for="placeholder-icon-<?php echo absint( $image ); ?>" class="sui-box-selector">
													<input type="radio" name="animation[placeholder-icon]" id="placeholder-icon-<?php echo absint( $image ); ?>" value="<?php echo absint( $image ); ?>" <?php checked( $image === $settings['animation']['placeholder']['selected'] ); ?> />
													<span>
										<button class="remove-selector sui-button-icon sui-tooltip smush-ll-remove" data-tooltip="<?php esc_attr_e( 'Remove', 'wp-smushit' ); ?>">
											<i class="sui-icon-close" aria-hidden="true" data-id="<?php echo absint( $image ); ?>" data-type="placeholder"></i>
										</button>
										<img alt="<?php esc_attr_e( 'Placeholder image', 'wp-smushit' ); ?>&nbsp;<?php echo absint( $image ); ?>" src="<?php echo esc_url( $custom_link[0] ); ?>" />
									</span>
												</label></li>
										<?php endforeach; ?>
									</ul>

									<div class="sui-upload">
										<input type="hidden" name="animation[custom-placeholder]" id="smush-placeholder-icon-file" value="" />

										<div class="sui-upload-image" aria-hidden="true">
											<div class="sui-image-mask"></div>
											<div role="button" class="sui-image-preview" id="smush-placeholder-icon-preview" onclick="WP_Smush.Lazyload.addLoaderIcon('placeholder')"></div>
										</div>

										<a class="sui-upload-button" id="smush-upload-placeholder" onclick="WP_Smush.Lazyload.addLoaderIcon('placeholder')">
											<i class="sui-icon-upload-cloud" aria-hidden="true"></i> <?php esc_html_e( 'Upload file', 'wp-smushit' ); ?>
										</a>

										<div class="sui-upload-file" id="smush-remove-placeholder">
											<span></span>
											<button aria-label="<?php esc_attr_e( 'Remove file', 'wp-smushit' ); ?>">
												<i class="sui-icon-close" aria-hidden="true"></i>
											</button>
										</div>
									</div>
								</div>

								<?php $color = isset( $settings['animation']['placeholder']['color'] ) ? $settings['animation']['placeholder']['color'] : '#F3F3F3'; ?>
								<label class="sui-label" for="smush-color-picker"><?php esc_html_e( 'Background color', 'wp-smushit' ); ?></label>
								<div class="sui-colorpicker-wrap">
									<div class="sui-colorpicker sui-colorpicker-hex" aria-hidden="true">
										<div class="sui-colorpicker-value">
								<span role="button">
									<span style="background-color: <?php echo esc_attr( $color ); ?>"></span>
								</span>
											<input type="text" value="<?php echo esc_attr( $color ); ?>" readonly="readonly" />
											<button><i class="sui-icon-close" aria-hidden="true"></i></button>
										</div>
										<button class="sui-button"><?php esc_html_e( 'Select', 'wp-smushit' ); ?></button>
									</div>
									<input type="text" name="animation[color]" value="<?php echo esc_attr( $color ); ?>" id="smush-color-picker" class="sui-colorpicker-input" data-attribute="<?php echo esc_attr( $color ); ?>" />
								</div>
							</div>

							<div class="sui-notice <?php echo ! $settings['animation']['selected'] ? 'active' : ''; ?>">
								<div class="sui-notice-content">
									<div class="sui-notice-message">
										<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
										<p><?php esc_html_e( 'Images will flash into view as soon as they are ready to display.', 'wp-smushit' ); ?></p>
									</div>
								</div>
							</div>
						</div><!-- end data-panes -->
					</div><!-- end .sui-tabs -->
				</div><!-- end .sui-box-settings-col-2 -->
				<script>
					jQuery(document).ready(function($){
						var $suiPickerInputs = $('#smush-color-picker');

						$suiPickerInputs.wpColorPicker({
															width: 300,
															change: function(event, ui) {
																$(this).val( ui.color.toCSS() ).trigger('change');
															}
														});

						if ( $suiPickerInputs.hasClass('wp-color-picker') ) {
							$suiPickerInputs.each( function() {
								var $suiPickerInput = $(this),
									$suiPicker      = $suiPickerInput.closest('.sui-colorpicker-wrap'),
									$suiPickerColor = $suiPicker.find('.sui-colorpicker-value span[role=button]'),
									$suiPickerValue = $suiPicker.find('.sui-colorpicker-value'),
									$wpPicker       = $suiPickerInput.closest('.wp-picker-container'),
									$wpPickerButton = $wpPicker.find('.wp-color-result');

								// Listen to color change
								$suiPickerInput.on('change', function() {
									// Change color preview
									$suiPickerColor.find('span').css({
																		'background-color': $wpPickerButton.css('background-color')
																	});

									// Change color value
									$suiPickerValue.find('input').val( $suiPickerInput.val() );
								});

								// Open iris picker
								$suiPicker.find('.sui-button, span[role=button]').on('click', function(e) {
									$wpPickerButton.trigger('click');

									e.preventDefault();
									e.stopPropagation();
								});

								// Clear color value
								$suiPickerValue.find('button').on( 'click', function(e) {
									e.preventDefault();

									$wpPicker.find('.wp-picker-clear').trigger('click');
									$suiPickerValue.find('input').val('');
									$suiPickerInput.val('').trigger('change');
									$suiPickerColor.find('span').css({
																		'background-color': ''
																	});

									e.preventDefault();
									e.stopPropagation();
								});

							});
						}
					});
				</script>
				<style>
					#smush-lazy-load-placeholder .sui-box-selector input + span,
					#smush-lazy-load-placeholder .sui-box-selector input:checked + span {
						background-color: <?php echo esc_attr( $color ); ?>;
					}
				</style>
			</div><!-- end .sui-box-settings-row -->
			<div class="sui-box-settings-row" id="lazyload-scripts-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Scripts', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'By default we will load the required scripts in your footer for max performance benefits. If you are having issues, you can switch this to the header.', 'wp-smushit' ); ?>
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<div class="sui-form-field">
						<strong><?php esc_attr_e( 'Method', 'wp-smushit' ); ?></strong>
						<div class="sui-description">
							<?php esc_html_e( 'By default we will load the required scripts in your footer for max performance benefits. If you are having issues, you can switch this to the header.', 'wp-smushit' ); ?>
						</div>

						<div class="sui-side-tabs sui-tabs">
							<div data-tabs>
								<label for="script-footer" class="sui-tab-item <?php echo $settings['footer'] ? 'active' : ''; ?>">
									<input type="radio" name="footer" value="on" id="script-footer" <?php checked( $settings['footer'] ); ?> />
									<?php esc_html_e( 'Footer', 'wp-smushit' ); ?>
								</label>

								<label for="script-header" class="sui-tab-item <?php echo $settings['footer'] ? '' : 'active'; ?>">
									<input type="radio" name="footer" value="off" id="script-header" <?php checked( $settings['footer'], false ); ?> />
									<?php esc_html_e( 'Header', 'wp-smushit' ); ?>
								</label>
							</div>

							<div data-panes>
								<div class="sui-notice active">
									<div class="sui-notice-content">
										<div class="sui-notice-message">
											<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
											<p><?php esc_html_e( 'Your theme must be using the wp_footer() function.', 'wp-smushit' ); ?></p>
										</div>
									</div>
								</div>
								<div class="sui-notice">
									<div class="sui-notice-content">
										<div class="sui-notice-message">
											<i class="sui-notice-icon sui-icon-info sui-md" aria-hidden="true"></i>
											<p><?php esc_html_e( 'Your theme must be using the wp_head() function.', 'wp-smushit' ); ?></p>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="sui-box-settings-row" id="lazyload-native-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Native lazy load', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Enable support for native browser lazy loading.', 'wp-smushit' ); ?>
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<div class="sui-form-field">
						<label for="native" class="sui-toggle">
							<input
									type="checkbox"
									id="native"
									name="native"
									aria-labelledby="native-label"
									aria-describedby="native-description"
								<?php checked( isset( $settings['native'] ) && $settings['native'] ); ?>
							/>
							<span class="sui-toggle-slider" aria-hidden="true"></span>
							<span id="native-label" class="sui-toggle-label">
					<?php esc_html_e( 'Enable native lazy loading', 'wp-smushit' ); ?>
				</span>
							<span id="native-description" class="sui-description">
					<?php
					printf(
					/* translators: %1$s - opening a tag, %2$s - closing a tag */
						esc_html__( 'In some cases can cause the "Defer offscreen images" Google PageSpeed audit to fail. See browser compatibility %1$shere%2$s.', 'wp-smushit' ),
						'<a href="https://caniuse.com/#feat=loading-lazy-attr" target="_blank">',
						'</a>'
					);
					?>
					<br/>
					<?php
					/* translators: %1$s - opening strong tag, %2$s - closing strong tag */
					printf( esc_html__( '%1$sNote:%2$s Video iframes will continue using JavaScript lazy loading even when this is enabled.', 'wp-smushit' ), '<strong>', '</strong>' );
					?>
				</span>
						</label>
					</div>
				</div>
			</div>
			<?php
			$lazyload_helper          = Lazy_Load_Helper::get_instance();
			$native_lazy_enabled      = $lazyload_helper->is_native_lazy_loading_enabled();
			$noscript_fallback_active = $lazyload_helper->is_noscript_fallback_enabled();
			?>
			<div class="sui-box-settings-row" id="lazyload-noscript-settings-row" style="display: <?php echo $native_lazy_enabled ? 'none' : 'flex'; ?>">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Noscript Tag', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php esc_html_e( 'Add a fallback mechanism for lazy-loading in browsers that do not support JavaScript.', 'wp-smushit' ); ?>
					</span>
				</div>

				<div class="sui-box-settings-col-2">
					<div class="sui-form-field">
						<label for="noscript_fallback" class="sui-toggle">
							<input
									type="checkbox"
									id="noscript_fallback"
									name="noscript_fallback"
									aria-labelledby="noscript_fallback-label"
									aria-describedby="noscript_fallback-description"
								<?php checked( $noscript_fallback_active ); ?>
							/>
							<span class="sui-toggle-slider" aria-hidden="true"></span>
							<span id="noscript-label" class="sui-toggle-label">
								<?php esc_html_e( 'Enable Noscript Fallback', 'wp-smushit' ); ?>
							</span>
							<span id="noscript-description" class="sui-description">
								<?php
									$docs_link = $this->get_utm_link(
										array(
											'utm_campaign' => 'smush_noscript_docs',
										),
										'https://wpmudev.com/docs/wpmu-dev-plugins/smush/#noscript-tag'
									);
									printf(
									/* translators: 1: Opening strong tag, 2: Closing strong tag, 3: a docs link */
										esc_html__( 'Enabling this may cause broken elements and an increased HTML size, potentially leading to performance and visual issues. %1$sUse only if needed for compatibility with unsupported browsers.%2$s %3$s', 'wp-smushit' ),
										'<strong>',
										'</strong>',
										$this->whitelabel->hide_doc_link() ? '' : '<a target="_blank" href="' . esc_url( $docs_link ) . '">' . esc_html__( 'Learn More', 'wp-smushit' ) . '</a>'
									);
									?>
							</span>
						</label>
					</div>
				</div>
			</div>
			<div class="sui-box-settings-row" id="lazyload-deactivate-settings-row">
				<div class="sui-box-settings-col-1">
					<span class="sui-settings-label">
						<?php esc_html_e( 'Deactivate', 'wp-smushit' ); ?>
					</span>
					<span class="sui-description">
						<?php
						esc_html_e(
							'No longer wish to use this feature? Turn it off instantly by hitting Deactivate.',
							'wp-smushit'
						);
						?>
					</span>
				</div>
				<div class="sui-box-settings-col-2">
					<button class="sui-button sui-button-ghost" id="smush-cancel-lazyload">
						<span class="sui-loading-text">
							<i class="sui-icon-power-on-off" aria-hidden="true"></i>
							<?php esc_html_e( 'Deactivate', 'wp-smushit' ); ?>
						</span>
						<i class="sui-icon-loader sui-loading" aria-hidden="true"></i>
					</button>
				</div>
			</div>
		</div>
	</div>
</div>



