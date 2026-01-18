<?php
/**
 * Plugin Name: Advanced PDF Embedder
 * Plugin URI: https://github.com/guilamu/advanced-pdf-embedder
 * Description: Embed PDF viewer in WordPress using EmbedPDF 2.0.0+.
 * Version: 1.2.0
 * Author: Guilamu
 * Author URI: https://github.com/guilamu
 * License: GPL2
 * Text Domain: advanced-pdf-embedder
 * Domain Path: /languages
 * Update URI: https://github.com/guilamu/advanced-pdf-embedder/
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * @package AdvancedPDFEmbedder
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}

// Plugin constants.
define('ADVANCED_PDF_EMBEDDER_VERSION', '1.2.0');
define('ADVANCED_PDF_EMBEDDER_PATH', plugin_dir_path(__FILE__));
define('ADVANCED_PDF_EMBEDDER_URL', plugin_dir_url(__FILE__));
define('ADVANCED_PDF_EMBEDDER_TEXT_DOMAIN', 'advanced-pdf-embedder');
define('ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS', 'advanced_pdf_embedder_defaults');
define('ADVANCED_PDF_EMBEDDER_BASENAME', plugin_basename(__FILE__));

// Include the GitHub auto-updater.
require_once ADVANCED_PDF_EMBEDDER_PATH . 'includes/class-github-updater.php';

// Include files.
require_once ADVANCED_PDF_EMBEDDER_PATH . 'includes/class-advanced-pdf-embedder.php';

use AdvancedPDFEmbedder\Plugin;

/**
 * Initialize the plugin.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_init()
{
	$plugin = new Plugin();
	$plugin->run();
}
add_action('plugins_loaded', 'advanced_pdf_embedder_init');

/**
 * Load translations.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_load_textdomain()
{
	load_plugin_textdomain(ADVANCED_PDF_EMBEDDER_TEXT_DOMAIN, false, dirname(plugin_basename(__FILE__)) . '/languages');
}
add_action('init', 'advanced_pdf_embedder_load_textdomain');

/**
 * Plugin activation callback.
 *
 * Sets default options on first activation.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_activate()
{
	// Set default options if they don't exist.
	if (false === get_option(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS)) {
		add_option(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS, array(
			'width' => '100%',
			'height' => '600px',
			'theme' => 'light',
			'language' => 'en',
			'toolbar' => true,
			'sidebar' => true,
			'download' => true,
			'print' => true,
			'annotations' => true,
			'redact' => true,
			'zoom' => true,
		));
	}
}
register_activation_hook(__FILE__, 'advanced_pdf_embedder_activate');

/**
 * Plugin deactivation callback.
 *
 * Clears any transients or temporary data.
 *
 * @since 1.0.0
 * @return void
 */
function advanced_pdf_embedder_deactivate()
{
	// Placeholder for any cleanup needed on deactivation.
	// Currently no transients or crons to clear.
}
register_deactivation_hook(__FILE__, 'advanced_pdf_embedder_deactivate');

/**
 * Register with Guilamu Bug Reporter
 */
add_action('plugins_loaded', function() {
    if (class_exists('Guilamu_Bug_Reporter')) {
        Guilamu_Bug_Reporter::register(array(
            'slug'        => 'advanced-pdf-embedder',
            'name'        => 'Advanced PDF Embedder',
            'version'     => ADVANCED_PDF_EMBEDDER_VERSION,
            'github_repo' => 'guilamu/advanced-pdf-embedder',
        ));
    }
}, 20);

/**
 * Add 'Report a Bug' link to plugin row meta.
 *
 * @param array  $links Plugin row meta links.
 * @param string $file  Plugin file path.
 * @return array Modified links.
 */
function advanced_pdf_embedder_plugin_row_meta($links, $file) {
    if (ADVANCED_PDF_EMBEDDER_BASENAME !== $file) {
        return $links;
    }

    if (class_exists('Guilamu_Bug_Reporter')) {
        $links[] = sprintf(
            '<a href="#" class="guilamu-bug-report-btn" data-plugin-slug="advanced-pdf-embedder" data-plugin-name="%s">%s</a>',
            esc_attr__('Advanced PDF Embedder', 'advanced-pdf-embedder'),
            esc_html__('🐛 Report a Bug', 'advanced-pdf-embedder')
        );
    } else {
        $links[] = '<a href="https://github.com/guilamu/guilamu-bug-reporter/releases" target="_blank">🐛 Report a Bug (install Bug Reporter)</a>';
    }

    return $links;
}
add_filter('plugin_row_meta', 'advanced_pdf_embedder_plugin_row_meta', 10, 2);
