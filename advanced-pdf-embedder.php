<?php
/**
 * Plugin Name: Advanced PDF Embedder
 * Plugin URI: https://github.com/embedpdf/embed-pdf-viewer
 * Description: Embed PDF viewer in WordPress using EmbedPDF 2.0.0+.
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL2
 * Text Domain: advanced-pdf-embedder
 * Domain Path: /languages
 *
 * @package AdvancedPDFEmbedder
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin constants.
define( 'ADVANCED_PDF_EMBEDDER_VERSION', '1.0.0' );
define( 'ADVANCED_PDF_EMBEDDER_PATH', plugin_dir_path( __FILE__ ) );
define( 'ADVANCED_PDF_EMBEDDER_URL', plugin_dir_url( __FILE__ ) );
define( 'ADVANCED_PDF_EMBEDDER_TEXT_DOMAIN', 'advanced-pdf-embedder' );
define( 'ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS', 'advanced_pdf_embedder_defaults' );

// Include files.
require_once ADVANCED_PDF_EMBEDDER_PATH . 'includes/class-advanced-pdf-embedder.php';

use AdvancedPDFEmbedder\Plugin;

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_init() {
	$plugin = new Plugin();
	$plugin->run();
}
add_action( 'plugins_loaded', 'advanced_pdf_embedder_init' );

/**
 * Load translations.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_load_textdomain() {
	load_plugin_textdomain( ADVANCED_PDF_EMBEDDER_TEXT_DOMAIN, false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'init', 'advanced_pdf_embedder_load_textdomain' );

/**
 * Plugin activation callback.
 *
 * Sets default options on first activation.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_activate() {
	// Set default options if they don't exist.
	if ( false === get_option( ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS ) ) {
		add_option( ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS, array(
			'width'       => '100%',
			'height'      => '600px',
			'theme'       => 'light',
			'language'    => 'en',
			'toolbar'     => true,
			'sidebar'     => true,
			'download'    => true,
			'print'       => true,
			'annotations' => true,
			'redact'      => true,
			'zoom'        => true,
		) );
	}
}
register_activation_hook( __FILE__, 'advanced_pdf_embedder_activate' );

/**
 * Plugin deactivation callback.
 *
 * Clears any transients or temporary data.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_deactivate() {
	// Placeholder for any cleanup needed on deactivation.
	// Currently no transients or crons to clear.
}
register_deactivation_hook( __FILE__, 'advanced_pdf_embedder_deactivate' );
