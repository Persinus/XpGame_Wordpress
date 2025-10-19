<?php
declare( strict_types = 1 );
namespace Automattic\WooCommerce\EmailEditor\Integrations\Core\Renderer\Blocks;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Engine\Renderer\ContentRenderer\Block_Renderer;
use Automattic\WooCommerce\EmailEditor\Engine\Renderer\ContentRenderer\Rendering_Context;
use Automattic\WooCommerce\EmailEditor\Integrations\Utils\Styles_Helper;
use Automattic\WooCommerce\EmailEditor\Integrations\Utils\Table_Wrapper_Helper;
use WP_Style_Engine;
abstract class Abstract_Block_Renderer implements Block_Renderer {
 protected function get_styles_from_block( array $block_styles, $skip_convert_vars = false ) {
 return Styles_Helper::get_styles_from_block( $block_styles, $skip_convert_vars );
 }
 protected function compile_css( ...$styles ): string {
 return WP_Style_Engine::compile_css( array_merge( ...$styles ), '' );
 }
 protected function add_spacer( $content, $email_attrs ): string {
 $gap_style = WP_Style_Engine::compile_css( array_intersect_key( $email_attrs, array_flip( array( 'margin-top' ) ) ), '' ) ?? '';
 $padding_style = WP_Style_Engine::compile_css( array_intersect_key( $email_attrs, array_flip( array( 'padding-left', 'padding-right' ) ) ), '' ) ?? '';
 $table_attrs = array(
 'align' => 'left',
 'width' => '100%',
 'style' => $gap_style,
 );
 $cell_attrs = array(
 'style' => $padding_style,
 );
 $div_content = sprintf(
 '<div class="email-block-layout" style="%1$s %2$s">%3$s</div>',
 esc_attr( $gap_style ),
 esc_attr( $padding_style ),
 $content
 );
 return Table_Wrapper_Helper::render_outlook_table_wrapper( $div_content, $table_attrs, $cell_attrs );
 }
 public function render( string $block_content, array $parsed_block, Rendering_Context $rendering_context ): string {
 return $this->add_spacer(
 $this->render_content( $block_content, $parsed_block, $rendering_context ),
 $parsed_block['email_attrs'] ?? array()
 );
 }
 abstract protected function render_content( string $block_content, array $parsed_block, Rendering_Context $rendering_context ): string;
}
