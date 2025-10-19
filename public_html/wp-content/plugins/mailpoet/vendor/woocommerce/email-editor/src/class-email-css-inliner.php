<?php
declare( strict_types = 1 );
namespace Automattic\WooCommerce\EmailEditor;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Engine\Renderer\Css_Inliner;
use Pelago\Emogrifier\CssInliner;
class Email_Css_Inliner implements Css_Inliner {
 private CssInliner $inliner;
 public function from_html( string $unprocessed_html ): self {
 $that = new self();
 $that->inliner = CssInliner::fromHtml( $unprocessed_html );
 return $that;
 }
 public function inline_css( string $css = '' ): self {
 if ( ! isset( $this->inliner ) ) {
 throw new \LogicException( 'You must call from_html before calling inline_css' );
 }
 $this->inliner->inlineCss( $css );
 return $this;
 }
 public function render(): string {
 if ( ! isset( $this->inliner ) ) {
 throw new \LogicException( 'You must call from_html before calling inline_css' );
 }
 return $this->inliner->render();
 }
}
