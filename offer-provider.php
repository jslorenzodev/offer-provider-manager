<?php
/**
 * Plugin Name: Offer Provider Manager
 * Description: Manages companies, users, offer providers with categories and coupons.
 * Version:     2.0.0
 * Author:      Custom Plugin
 * Text Domain: offer-provider
 *
 */

declare( strict_types=1 );

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'OPM_VERSION',  '2.0.0' );
define( 'OPM_PATH',     plugin_dir_path( __FILE__ ) );
define( 'OPM_URL',      plugin_dir_url( __FILE__ ) );
define( 'OPM_BASENAME', plugin_basename( __FILE__ ) );

// Autoloader — maps namespace OPM\Src\* to /src/*/
spl_autoload_register( static function ( string $class ): void {
    $prefix = 'OPM\\Src\\';
    if ( strncmp( $class, $prefix, strlen( $prefix ) ) !== 0 ) {
        return;
    }
    $relative = str_replace( '\\', DIRECTORY_SEPARATOR, substr( $class, strlen( $prefix ) ) );
    $file     = OPM_PATH . 'src' . DIRECTORY_SEPARATOR . $relative . '.php';
    if ( file_exists( $file ) ) {
        require_once $file;
    }
} );

// Activation / Deactivation hooks
register_activation_hook( __FILE__, static function (): void {
    // Call registration methods directly — activation fires before `init`,
    // so we cannot rely on add_action('init') hooks being executed.
    ( new OPM\Src\PostTypes\OfferProviderPostType() )->registerPostType();
    ( new OPM\Src\Taxonomy\ProviderCategoryTaxonomy() )->registerTaxonomy();
    ( new OPM\Src\Registration\RegistrationRouter() )->addRewriteRules();
    OPM\Src\Database\Schema::install();
    flush_rewrite_rules();
} );

register_deactivation_hook( __FILE__, static function (): void {
    flush_rewrite_rules();
} );

// Boot the plugin
add_action( 'plugins_loaded', static function (): void {
    OPM\Src\Plugin::getInstance()->boot();
} );
