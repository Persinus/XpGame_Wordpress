<?php
declare(strict_types = 1);
namespace Automattic\WooCommerce\EmailEditor\Engine\Renderer\ContentRenderer;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Integrations\Utils\Styles_Helper;
use WP_Theme_JSON;
class Rendering_Context {
 private WP_Theme_JSON $theme_json;
 public function __construct( WP_Theme_JSON $theme_json ) {
 $this->theme_json = $theme_json;
 }
 public function get_theme_json(): WP_Theme_JSON {
 return $this->theme_json;
 }
 public function get_theme_styles(): array {
 $theme = $this->get_theme_json();
 return $theme->get_data()['styles'] ?? array();
 }
 public function get_theme_settings() {
 return $this->get_theme_json()->get_settings();
 }
 public function get_layout_width_without_padding(): string {
 $styles = $this->get_theme_styles();
 $layout_settings = $this->get_theme_settings()['layout'] ?? array();
 $width = Styles_Helper::parse_value( $layout_settings['contentSize'] ?? '0px' );
 $padding = $styles['spacing']['padding'] ?? array();
 $width -= Styles_Helper::parse_value( $padding['left'] ?? '0px' );
 $width -= Styles_Helper::parse_value( $padding['right'] ?? '0px' );
 return "{$width}px";
 }
 public function translate_slug_to_color( string $color_slug ): string {
 $settings = $this->get_theme_settings();
 $color_definitions = array_merge(
 $settings['color']['palette']['theme'] ?? array(),
 $settings['color']['palette']['default'] ?? array()
 );
 foreach ( $color_definitions as $color_definition ) {
 if ( $color_definition['slug'] === $color_slug ) {
 return strtolower( $color_definition['color'] );
 }
 }
 return $color_slug;
 }
}
