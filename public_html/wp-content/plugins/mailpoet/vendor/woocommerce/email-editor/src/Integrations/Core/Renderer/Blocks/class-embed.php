<?php
declare( strict_types = 1 );
namespace Automattic\WooCommerce\EmailEditor\Integrations\Core\Renderer\Blocks;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Engine\Renderer\ContentRenderer\Rendering_Context;
use Automattic\WooCommerce\EmailEditor\Integrations\Core\Renderer\Blocks\Audio;
use Automattic\WooCommerce\EmailEditor\Integrations\Utils\Html_Processing_Helper;
class Embed extends Abstract_Block_Renderer {
 public function render( string $block_content, array $parsed_block, Rendering_Context $rendering_context ): string {
 // Validate input parameters and required dependencies.
 if ( ! isset( $parsed_block['attrs'] ) || ! is_array( $parsed_block['attrs'] ) ||
 ! class_exists( '\Automattic\WooCommerce\EmailEditor\Integrations\Utils\Table_Wrapper_Helper' ) ) {
 return '';
 }
 $attr = $parsed_block['attrs'];
 // Check if this is a supported audio provider embed and has a valid URL.
 $provider = $this->get_supported_provider( $attr, $block_content );
 if ( empty( $provider ) ) {
 // For non-audio embeds, try to render as a simple link fallback.
 return $this->render_link_fallback( $attr, $block_content, $parsed_block, $rendering_context );
 }
 $url = $this->extract_provider_url( $attr, $block_content, $provider );
 if ( empty( $url ) ) {
 // Provider was detected but URL extraction failed - provide graceful fallback.
 return $this->render_link_fallback( $attr, $block_content, $parsed_block, $rendering_context );
 }
 // If we have a valid audio provider embed, proceed with normal rendering.
 // Note: Audio::render already wraps its output with add_spacer, so we return directly.
 return $this->render_content( $block_content, $parsed_block, $rendering_context );
 }
 protected function render_content( string $block_content, array $parsed_block, Rendering_Context $rendering_context ): string { // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
 $attr = $parsed_block['attrs'] ?? array();
 // Get provider and URL (validation already done in render method).
 $provider = $this->get_supported_provider( $attr, $block_content );
 $url = $this->extract_provider_url( $attr, $block_content, $provider );
 // Get appropriate label for the provider.
 $label = $this->get_provider_label( $provider, $attr );
 // Create a mock audio block structure to reuse the Audio renderer.
 $mock_audio_block = array(
 'blockName' => 'core/audio',
 'attrs' => array(
 'src' => $url,
 'label' => $label,
 ),
 'innerHTML' => '<figure class="wp-block-audio"><audio controls src="' . esc_attr( $url ) . '"></audio></figure>',
 );
 // Copy email attributes to the mock block.
 if ( isset( $parsed_block['email_attrs'] ) ) {
 $mock_audio_block['email_attrs'] = $parsed_block['email_attrs'];
 }
 // Use the Audio renderer to render the audio provider embed.
 $audio_renderer = new Audio();
 $audio_result = $audio_renderer->render( $mock_audio_block['innerHTML'], $mock_audio_block, $rendering_context );
 // If audio rendering fails, fall back to a simple link.
 if ( empty( $audio_result ) ) {
 // Use the existing render_link_fallback method for consistent spacing and formatting.
 // Create a mock attr array with the URL and label for the fallback method.
 $fallback_attr = array(
 'url' => $url,
 'label' => $label,
 );
 return $this->render_link_fallback( $fallback_attr, $block_content, $parsed_block, $rendering_context );
 }
 return $audio_result;
 }
 private function get_supported_provider( array $attr, string $block_content ): string {
 $supported_providers = array( 'pocket-casts', 'spotify', 'soundcloud', 'mixcloud', 'reverbnation' );
 // Check provider name slug.
 if ( isset( $attr['providerNameSlug'] ) && in_array( $attr['providerNameSlug'], $supported_providers, true ) ) {
 return $attr['providerNameSlug'];
 }
 // Check for supported domains in URL or content.
 $url = $attr['url'] ?? '';
 $content_to_check = ! empty( $url ) ? $url : $block_content;
 if ( strpos( $content_to_check, 'open.spotify.com' ) !== false ) {
 return 'spotify';
 }
 if ( strpos( $content_to_check, 'soundcloud.com' ) !== false ) {
 return 'soundcloud';
 }
 if ( strpos( $content_to_check, 'pca.st' ) !== false ) {
 return 'pocket-casts';
 }
 if ( strpos( $content_to_check, 'mixcloud.com' ) !== false ) {
 return 'mixcloud';
 }
 if ( strpos( $content_to_check, 'reverbnation.com' ) !== false ) {
 return 'reverbnation';
 }
 return '';
 }
 private function extract_provider_url( array $attr, string $block_content, string $provider ): string {
 // First, try to get URL from attributes.
 if ( ! empty( $attr['url'] ) ) {
 $url = $attr['url'];
 // Validate the URL from attributes.
 if ( filter_var( $url, FILTER_VALIDATE_URL ) && wp_http_validate_url( $url ) ) {
 return $url;
 }
 return '';
 }
 // If not in attributes, extract from block content using simple pattern.
 // The innerHTML always contains the URL in a predictable structure.
 if ( preg_match( '/<div class="wp-block-embed__wrapper">([^<]+)<\/div>/', $block_content, $matches ) ) {
 $url = trim( $matches[1] );
 // Validate the extracted URL.
 if ( filter_var( $url, FILTER_VALIDATE_URL ) && wp_http_validate_url( $url ) ) {
 return $url;
 }
 }
 return '';
 }
 private function get_provider_label( string $provider, array $attr ): string {
 // Use custom label if provided.
 if ( ! empty( $attr['label'] ) ) {
 return $attr['label'];
 }
 // Use default label based on provider.
 switch ( $provider ) {
 case 'spotify':
 return __( 'Listen on Spotify', 'woocommerce' );
 case 'soundcloud':
 return __( 'Listen on SoundCloud', 'woocommerce' );
 case 'pocket-casts':
 return __( 'Listen on Pocket Casts', 'woocommerce' );
 case 'mixcloud':
 return __( 'Listen on Mixcloud', 'woocommerce' );
 case 'reverbnation':
 return __( 'Listen on ReverbNation', 'woocommerce' );
 default:
 return __( 'Listen to the audio', 'woocommerce' );
 }
 }
 private function render_link_fallback( array $attr, string $block_content, array $parsed_block, Rendering_Context $rendering_context ): string {
 // Try to get URL from attributes first.
 $url = $attr['url'] ?? '';
 // If no URL in attributes, try to extract from block content.
 if ( empty( $url ) ) {
 // Look for any HTTP/HTTPS URL in the content with proper boundaries.
 if ( preg_match( '/(?<![a-zA-Z0-9.-])https?:\/\/[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}[a-zA-Z0-9\/?=&%-]*(?![a-zA-Z0-9.-])/', $block_content, $matches ) ) {
 $url = $matches[0];
 }
 }
 // If still no URL, try to use provider-specific base URL if we have a provider.
 if ( empty( $url ) && isset( $attr['providerNameSlug'] ) ) {
 $url = $this->get_provider_base_url( $attr['providerNameSlug'] );
 }
 // Validate URL with both filter_var and wp_http_validate_url.
 if ( empty( $url ) || ! filter_var( $url, FILTER_VALIDATE_URL ) || ! wp_http_validate_url( $url ) ) {
 return '';
 }
 // Get link text - use custom label if provided, otherwise use provider label for base URLs or URL.
 if ( ! empty( $attr['label'] ) ) {
 $link_text = $attr['label'];
 } else {
 // Check if this is a provider base URL (like https://open.spotify.com/).
 $provider = $attr['providerNameSlug'] ?? '';
 $base_url = $this->get_provider_base_url( $provider );
 if ( ! empty( $base_url ) && $url === $base_url ) {
 // Use provider-specific label for base URLs.
 $link_text = $this->get_provider_label( $provider, $attr );
 } else {
 // Use the URL itself for specific URLs.
 $link_text = $url;
 }
 }
 // Get color from email attributes or theme styles.
 $email_styles = $rendering_context->get_theme_styles();
 $link_color = $parsed_block['email_attrs']['color'] ?? $email_styles['color']['text'] ?? '#0073aa';
 // Sanitize color value to ensure it's a valid hex color or CSS variable.
 $link_color = Html_Processing_Helper::sanitize_color( $link_color );
 // Create a simple link.
 $link_html = sprintf(
 '<a href="%s" target="_blank" rel="noopener nofollow" style="color: %s; text-decoration: underline;">%s</a>',
 esc_url( $url ),
 esc_attr( $link_color ),
 esc_html( $link_text )
 );
 // Wrap with spacer if we have email attributes.
 return $this->add_spacer(
 $link_html,
 $parsed_block['email_attrs'] ?? array()
 );
 }
 private function get_provider_base_url( string $provider ): string {
 switch ( $provider ) {
 case 'spotify':
 return 'https://open.spotify.com/';
 case 'soundcloud':
 return 'https://soundcloud.com/';
 case 'pocket-casts':
 return 'https://pca.st/';
 case 'mixcloud':
 return 'https://www.mixcloud.com/';
 case 'reverbnation':
 return 'https://www.reverbnation.com/';
 default:
 return '';
 }
 }
}
