<?php
declare(strict_types = 1);
namespace Automattic\WooCommerce\EmailEditor\Engine\Renderer\ContentRenderer;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Engine\Logger\Email_Editor_Logger;
use Automattic\WooCommerce\EmailEditor\Engine\Renderer\Css_Inliner;
use Automattic\WooCommerce\EmailEditor\Engine\Theme_Controller;
use Automattic\WooCommerce\EmailEditor\Integrations\Core\Renderer\Blocks\Fallback;
use WP_Block_Template;
use WP_Block_Type_Registry;
use WP_Post;
class Content_Renderer {
 private Process_Manager $process_manager;
 private Theme_Controller $theme_controller;
 const CONTENT_STYLES_FILE = 'content.css';
 private WP_Block_Type_Registry $block_type_registry;
 private Css_Inliner $css_inliner;
 private $backup_template_content;
 private $backup_template_id;
 private $backup_post;
 private $backup_query;
 private Fallback $fallback_renderer;
 private Email_Editor_Logger $logger;
 public function __construct(
 Process_Manager $preprocess_manager,
 Css_Inliner $css_inliner,
 Theme_Controller $theme_controller,
 Email_Editor_Logger $logger
 ) {
 $this->process_manager = $preprocess_manager;
 $this->theme_controller = $theme_controller;
 $this->css_inliner = $css_inliner;
 $this->logger = $logger;
 $this->block_type_registry = WP_Block_Type_Registry::get_instance();
 $this->fallback_renderer = new Fallback();
 }
 private function initialize() {
 add_filter( 'render_block', array( $this, 'render_block' ), 10, 2 );
 add_filter( 'block_parser_class', array( $this, 'block_parser' ) );
 add_filter( 'woocommerce_email_blocks_renderer_parsed_blocks', array( $this, 'preprocess_parsed_blocks' ) );
 }
 public function render( WP_Post $post, WP_Block_Template $template ): string {
 $this->set_template_globals( $post, $template );
 $this->initialize();
 $rendered_html = get_the_block_template_html();
 $this->reset();
 return $this->process_manager->postprocess( $this->inline_styles( $rendered_html, $post, $template ) );
 }
 public function block_parser() {
 return 'Automattic\WooCommerce\EmailEditor\Engine\Renderer\ContentRenderer\Blocks_Parser';
 }
 public function preprocess_parsed_blocks( array $parsed_blocks ): array {
 return $this->process_manager->preprocess( $parsed_blocks, $this->theme_controller->get_layout_settings(), $this->theme_controller->get_styles() );
 }
 public function render_block( string $block_content, array $parsed_block ): string {
 $context = new Rendering_Context( $this->theme_controller->get_theme() );
 $block_type = $this->block_type_registry->get_registered( $parsed_block['blockName'] );
 try {
 if ( $block_type && isset( $block_type->render_email_callback ) && is_callable( $block_type->render_email_callback ) ) {
 return call_user_func( $block_type->render_email_callback, $block_content, $parsed_block, $context );
 }
 } catch ( \Exception $error ) {
 $this->logger->error(
 'Error thrown while rendering block.',
 array(
 'exception' => $error,
 'block_name' => $parsed_block['blockName'],
 'parsed_block' => $parsed_block,
 'message' => $error->getMessage(),
 )
 );
 // Returning the original content.
 return $block_content;
 }
 return $this->fallback_renderer->render( $block_content, $parsed_block, $context );
 }
 private function set_template_globals( WP_Post $email_post, WP_Block_Template $template ) {
 global $_wp_current_template_content, $_wp_current_template_id, $wp_query, $post;
 // Backup current values of globals.
 // Because overriding the globals can affect rendering of the page itself, we need to backup the current values.
 $this->backup_template_content = $_wp_current_template_content;
 $this->backup_template_id = $_wp_current_template_id;
 $this->backup_query = $wp_query;
 $this->backup_post = $email_post;
 $_wp_current_template_id = $template->id;
 $_wp_current_template_content = $template->content;
 $wp_query = new \WP_Query( array( 'p' => $email_post->ID ) ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- We need to set the query for correct rendering the blocks.
 $post = $email_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- We need to set the post for correct rendering the blocks.
 }
 private function reset(): void {
 remove_filter( 'render_block', array( $this, 'render_block' ) );
 remove_filter( 'block_parser_class', array( $this, 'block_parser' ) );
 remove_filter( 'woocommerce_email_blocks_renderer_parsed_blocks', array( $this, 'preprocess_parsed_blocks' ) );
 // Restore globals to their original values.
 global $_wp_current_template_content, $_wp_current_template_id, $wp_query, $post;
 $_wp_current_template_content = $this->backup_template_content;
 $_wp_current_template_id = $this->backup_template_id;
 $wp_query = $this->backup_query; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring of the query.
 $post = $this->backup_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restoring of the post.
 }
 private function inline_styles( $html, WP_Post $post, $template = null ) {
 $styles = (string) file_get_contents( __DIR__ . '/' . self::CONTENT_STYLES_FILE );
 $styles .= (string) file_get_contents( __DIR__ . '/../../content-shared.css' );
 // Apply default contentWidth to constrained blocks.
 $layout = $this->theme_controller->get_layout_settings();
 $styles .= sprintf(
 '
 .is-layout-constrained > *:not(.alignleft):not(.alignright):not(.alignfull) {
 max-width: %1$s;
 margin-left: auto !important;
 margin-right: auto !important;
 }
 .is-layout-constrained > .alignwide {
 max-width: %2$s;
 margin-left: auto !important;
 margin-right: auto !important;
 }
 ',
 $layout['contentSize'],
 $layout['wideSize']
 );
 // Get styles from theme.
 $styles .= $this->theme_controller->get_stylesheet_for_rendering( $post, $template );
 $block_support_styles = $this->theme_controller->get_stylesheet_from_context( 'block-supports', array() );
 // Get styles from block-supports stylesheet. This includes rules such as layout (contentWidth) that some blocks use.
 // @see https://github.com/WordPress/WordPress/blob/3c5da9c74344aaf5bf8097f2e2c6a1a781600e03/wp-includes/script-loader.php#L3134
 // @internal :where is not supported by emogrifier, so we need to replace it with *.
 $block_support_styles = str_replace(
 ':where(:not(.alignleft):not(.alignright):not(.alignfull))',
 '*:not(.alignleft):not(.alignright):not(.alignfull)',
 $block_support_styles
 );
 $block_support_styles = preg_replace(
 '/group-is-layout-(\d+) >/',
 'group-is-layout-$1 > tbody tr td >',
 $block_support_styles
 );
 $styles .= $block_support_styles;
 $styles = '<style>' . wp_strip_all_tags( (string) apply_filters( 'woocommerce_email_content_renderer_styles', $styles, $post ) ) . '</style>';
 return $this->css_inliner->from_html( $styles . $html )->inline_css()->render();
 }
}
