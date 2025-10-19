<?php
declare( strict_types = 1 );
namespace Automattic\WooCommerce\EmailEditor;
if (!defined('ABSPATH')) exit;
use Automattic\WooCommerce\EmailEditor\Engine\Email_Editor;
use Automattic\WooCommerce\EmailEditor\Integrations\Core\Initializer as CoreEmailEditorIntegration;
class Bootstrap {
 private $email_editor;
 private $core_email_editor_integration;
 public function __construct(
 Email_Editor $email_editor,
 CoreEmailEditorIntegration $core_email_editor_integration
 ) {
 $this->email_editor = $email_editor;
 $this->core_email_editor_integration = $core_email_editor_integration;
 }
 public function init(): void {
 add_action(
 'init',
 array(
 $this,
 'initialize',
 )
 );
 add_filter(
 'woocommerce_email_editor_initialized',
 array(
 $this,
 'setup_email_editor_integrations',
 )
 );
 add_filter(
 'block_type_metadata_settings',
 array( $this->core_email_editor_integration, 'update_block_settings' ),
 10,
 1
 );
 }
 public function initialize(): void {
 $this->email_editor->initialize();
 }
 public function setup_email_editor_integrations(): bool {
 $this->core_email_editor_integration->initialize();
 return true; // PHPStan expect returning a value from the filter.
 }
}
