<?php
/**
 * Advanced PDF Embedder Uninstall
 *
 * Removes all plugin data when uninstalled.
 *
 * @package AdvancedPDFEmbedder
 * @since   1.0.0
 */

// Exit if accessed directly or not in uninstall context.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

// Delete options.
delete_option('advanced_pdf_embedder_defaults');

// For multisite, clean up all sites.
if (is_multisite()) {
	global $wpdb;

	// Get all blog IDs.
	$blog_ids = $wpdb->get_col("SELECT blog_id FROM {$wpdb->blogs}"); // phpcs:ignore WordPress.DB.DirectDatabaseQuery

	foreach ($blog_ids as $blog_id) {
		switch_to_blog($blog_id);
		delete_option('advanced_pdf_embedder_defaults');
		restore_current_blog();
	}
}
