<?php
/**
 * Advanced PDF Embedder Main Plugin Class
 *
 * @package AdvancedPDFEmbedder
 * @since   1.0.0
 */

namespace AdvancedPDFEmbedder;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Main plugin class for Advanced PDF Embedder.
 *
 * Handles shortcode rendering, Gutenberg block registration,
 * settings page, and TinyMCE integration.
 *
 * @since 1.0.0
 */
class Plugin
{

	/**
	 * Initialize and run the plugin.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function run()
	{
		add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_shortcode('embedpdf', array($this, 'render_shortcode'));
		add_action('admin_menu', array($this, 'add_settings_page'));
		add_action('init', array($this, 'register_block'));
		add_action('admin_init', array($this, 'register_tinymce_embed_button'));
		add_action('admin_init', array($this, 'register_settings'));
		add_action('admin_head', array($this, 'print_tinymce_defaults'));
		add_action('enqueue_block_editor_assets', array($this, 'enqueue_block_editor_assets'));
	}

	/**
	 * Enqueue frontend scripts.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_scripts()
	{
		wp_register_script(
			'advanced-pdf-embedder-viewer',
			'https://cdn.jsdelivr.net/npm/@embedpdf/snippet@2/dist/embedpdf.js',
			array(),
			'2.0.0',
			true
		);

		// Add type="module" attribute to the script tag
		add_filter('script_loader_tag', array($this, 'add_module_type_attribute'), 10, 3);
	}

	/**
	 * Add type="module" to the EmbedPDF script tag.
	 *
	 * @since 1.0.0
	 * @param string $tag    The script tag.
	 * @param string $handle The script handle.
	 * @param string $src    The script source.
	 * @return string Modified script tag.
	 */
	public function add_module_type_attribute($tag, $handle, $src)
	{
		if ('advanced-pdf-embedder-viewer' === $handle) {
			$tag = '<script type="module" src="' . esc_url($src) . '"></script>' . "\n";
			$tag .= '<script type="module">import EmbedPDF from "' . esc_url($src) . '"; window.EmbedPDF = EmbedPDF;</script>' . "\n";
		}
		return $tag;
	}

	/**
	 * Enqueue block editor assets with global defaults.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function enqueue_block_editor_assets()
	{
		$defaults = $this->get_default_options();
		wp_add_inline_script(
			'wp-block-editor',
			'window.advancedPdfEmbedderBlockDefaults = ' . wp_json_encode($defaults) . ';',
			'before'
		);
	}

	/**
	 * Render the [embedpdf] shortcode.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output for the PDF viewer.
	 */
	public function render_shortcode($atts)
	{
		$defaults = $this->get_default_options();

		$atts = shortcode_atts(array(
			'url' => '',
			'width' => $defaults['width'],
			'height' => $defaults['height'],
			'theme' => $defaults['theme'],
			'language' => $defaults['language'],
			'toolbar' => $defaults['toolbar'] ? 'true' : 'false',
			'sidebar' => $defaults['sidebar'] ? 'true' : 'false',
			'download' => $defaults['download'] ? 'true' : 'false',
			'print' => $defaults['print'] ? 'true' : 'false',
			'annotations' => $defaults['annotations'] ? 'true' : 'false',
			'redact' => $defaults['redact'] ? 'true' : 'false',
			'zoom' => $defaults['zoom'] ? 'true' : 'false',
			'background_app' => $defaults['backgroundApp'],
			'background_surface' => $defaults['backgroundSurface'],
		), $atts, 'embedpdf');

		// Validate URL - must be a valid HTTP/HTTPS URL.
		$url = esc_url_raw($atts['url']);
		if (empty($url) || !preg_match('/^https?:\/\//', $url)) {
			return '';
		}

		// Sanitize dimensions.
		$width = $this->sanitize_dimension($atts['width'], '100%');
		$height = $this->sanitize_dimension($atts['height'], '600px');

		// Sanitize background colors
		$background_app = sanitize_hex_color($atts['background_app']) ?: '#111827';
		$background_surface = sanitize_hex_color($atts['background_surface']) ?: '#1f2937';

		$id = 'embedpdf-' . wp_unique_id();

		// Build theme configuration with background colors
		$theme_config = array(
			'preference' => $atts['theme'],
		);

		// Add background color overrides for both light and dark modes
		$background_overrides = array(
			'background' => array(
				'app' => $background_app,
				'surface' => $background_surface,
			),
		);
		$theme_config['light'] = $background_overrides;
		$theme_config['dark'] = $background_overrides;

		$config = array(
			'type' => 'container',
			'src' => $url,
			'theme' => $theme_config,
			'i18n' => array(
				'defaultLocale' => $atts['language'],
				'fallbackLocale' => 'en',
			),
			'ui' => $this->build_ui_config($atts),
		);

		// Disabled categories aligned with EmbedPDF docs.
		$disabled_categories = $this->get_disabled_categories($atts);
		if (!empty($disabled_categories)) {
			$config['disabledCategories'] = $disabled_categories;
		}

		wp_enqueue_script('advanced-pdf-embedder-viewer');

		ob_start();
		?>
		<div id="<?php echo esc_attr($id); ?>" class="wp-embedpdf-container"
			style="width: <?php echo esc_attr($width); ?>; height: <?php echo esc_attr($height); ?>;" role="document"
			aria-label="<?php esc_attr_e('PDF Viewer', 'advanced-pdf-embedder'); ?>"></div>
		<script>
			(function () {
				var container = document.getElementById(<?php echo wp_json_encode($id); ?>);
				var config = <?php echo wp_json_encode($config); ?>;
				var maxAttempts = 50;
				var attempts = 0;

				function initEmbedPDF() {
					if (window.EmbedPDF) {
						try {
							window.EmbedPDF.init(Object.assign({}, config, { target: container }));
						} catch (error) {
							console.error('EmbedPDF initialization failed:', error);
							container.innerHTML = '<p style="color:red;"><?php echo esc_js(__('Failed to load PDF viewer.', 'advanced-pdf-embedder')); ?></p>';
						}
					} else if (attempts < maxAttempts) {
						attempts++;
						setTimeout(initEmbedPDF, 200);
					} else {
						container.innerHTML = '<p style="color:red;"><?php echo esc_js(__('PDF viewer library not loaded.', 'advanced-pdf-embedder')); ?></p>';
					}
				}

				if (document.readyState === 'loading') {
					document.addEventListener('DOMContentLoaded', initEmbedPDF);
				} else {
					initEmbedPDF();
				}
			})();
		</script>
		<noscript>
			<p><?php esc_html_e('Please enable JavaScript to view this PDF.', 'advanced-pdf-embedder'); ?></p>
		</noscript>
		<?php
		return ob_get_clean();
	}

	/**
	 * Build the UI configuration array for EmbedPDF.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return array UI configuration.
	 */
	private function build_ui_config($atts)
	{
		return array(
			'toolbars' => array(
				'main-toolbar' => array(
					'visible' => $this->is_enabled($atts['toolbar']),
				),
			),
			'sidebars' => array(
				'main-sidebar' => array(
					'visible' => $this->is_enabled($atts['sidebar']),
				),
			),
			'commands' => array(
				'download' => array(
					'visible' => $this->is_enabled($atts['download']),
				),
				'print' => array(
					'visible' => $this->is_enabled($atts['print']),
				),
				'export.print' => array(
					'visible' => $this->is_enabled($atts['print']),
				),
				'export.download' => array(
					'visible' => $this->is_enabled($atts['download']),
				),
				'annotation' => array(
					'visible' => $this->is_enabled($atts['annotations']),
				),
				'redaction' => array(
					'visible' => $this->is_enabled($atts['redact']),
				),
				'zoom' => array(
					'visible' => $this->is_enabled($atts['zoom']),
				),
			),
		);
	}

	/**
	 * Get disabled categories based on shortcode attributes.
	 *
	 * @since 1.0.0
	 * @param array $atts Shortcode attributes.
	 * @return array List of disabled categories.
	 */
	private function get_disabled_categories($atts)
	{
		$disabled_categories = array();

		if (!$this->is_enabled($atts['toolbar'])) {
			$disabled_categories[] = 'toolbar';
		}

		if (!$this->is_enabled($atts['sidebar'])) {
			$disabled_categories[] = 'panel';
		}

		if (!$this->is_enabled($atts['print'])) {
			$disabled_categories[] = 'document-print';
			$disabled_categories[] = 'export';
		}

		if (!$this->is_enabled($atts['download'])) {
			$disabled_categories[] = 'document-export';
			$disabled_categories[] = 'export';
		}

		if (!$this->is_enabled($atts['annotations'])) {
			$disabled_categories[] = 'annotation';
		}

		if (!$this->is_enabled($atts['redact'])) {
			$disabled_categories[] = 'redaction';
		}

		if (!$this->is_enabled($atts['zoom'])) {
			$disabled_categories[] = 'zoom';
		}

		return array_values(array_unique($disabled_categories));
	}

	/**
	 * Sanitize CSS dimension value (e.g., "100%", "600px", "50vh").
	 *
	 * @since 1.0.0
	 * @param string $value   The dimension value to sanitize.
	 * @param string $default Default value if sanitization fails.
	 * @return string Sanitized dimension value.
	 */
	private function sanitize_dimension($value, $default = '100%')
	{
		$value = trim($value);
		// Allow only valid CSS dimension patterns.
		if (preg_match('/^\d+(\.\d+)?(px|%|em|rem|vh|vw)?$/i', $value)) {
			return $value;
		}
		return $default;
	}

	/**
	 * Check if a boolean attribute is enabled.
	 *
	 * Handles both actual booleans and string representations.
	 *
	 * @since 1.0.0
	 * @param mixed $value The value to check.
	 * @return bool True if enabled, false otherwise.
	 */
	private function is_enabled($value)
	{
		if (is_bool($value)) {
			return $value;
		}
		return 'false' !== $value && false !== $value && '0' !== $value;
	}

	/**
	 * Add the settings page to the WordPress admin menu.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function add_settings_page()
	{
		add_options_page(
			esc_html__('Advanced PDF Embedder Settings', 'advanced-pdf-embedder'),
			esc_html__('Advanced PDF Embedder', 'advanced-pdf-embedder'),
			'manage_options',
			'advanced-pdf-embedder',
			array($this, 'render_settings_page')
		);
	}

	/**
	 * Register plugin settings using the Settings API.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_settings()
	{
		register_setting('advanced_pdf_embedder_defaults', ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS, array($this, 'sanitize_defaults'));

		add_settings_section(
			'advanced_pdf_embedder_main_section',
			esc_html__('EmbedPDF Defaults', 'advanced-pdf-embedder'),
			function () {
				echo '<p>' . esc_html__('Defaults used by the TinyMCE button and Gutenberg block when inserting a PDF.', 'advanced-pdf-embedder') . '</p>';
			},
			'advanced-pdf-embedder'
		);

		$this->add_setting_field('width', __('Width', 'advanced-pdf-embedder'), 'text', '100%');
		$this->add_setting_field('height', __('Height', 'advanced-pdf-embedder'), 'text', '600px');
		$this->add_setting_field('theme', __('Theme', 'advanced-pdf-embedder'), 'select', 'light', array('light' => __('Light', 'advanced-pdf-embedder'), 'dark' => __('Dark', 'advanced-pdf-embedder')));
		$this->add_setting_field('language', __('Language', 'advanced-pdf-embedder'), 'select', 'en', $this->get_language_options());
		$this->add_setting_field('toolbar', __('Show Toolbar', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('sidebar', __('Show Sidebar', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('download', __('Allow Download', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('print', __('Allow Print', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('annotations', __('Allow Annotations', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('redact', __('Allow Redaction', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('zoom', __('Allow Zoom', 'advanced-pdf-embedder'), 'checkbox', true);
		$this->add_setting_field('backgroundApp', __('App Background Color', 'advanced-pdf-embedder'), 'color', '#111827');
		$this->add_setting_field('backgroundSurface', __('Surface Background Color', 'advanced-pdf-embedder'), 'color', '#1f2937');
	}

	/**
	 * Add a settings field to the settings page.
	 *
	 * @since 1.0.0
	 * @param string $key     Field key.
	 * @param string $label   Field label.
	 * @param string $type    Field type (text, select, checkbox).
	 * @param mixed  $default Default value.
	 * @param array  $options Options for select fields.
	 * @return void
	 */
	private function add_setting_field($key, $label, $type, $default, $options = array())
	{
		add_settings_field(
			'advanced_pdf_embedder_' . $key,
			$label,
			function () use ($key, $type, $default, $options, $label) {
				// Decode any entities that may come from translations to avoid double-encoding apostrophes.
				$label_text = wp_specialchars_decode($label);
				$options_saved = get_option(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS, array());
				$value = isset($options_saved[$key]) ? $options_saved[$key] : $default;

				switch ($type) {
					case 'text':
						echo '<input type="text" name="' . esc_attr(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS) . '[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" />';
						break;
					case 'select':
						echo '<select name="' . esc_attr(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS) . '[' . esc_attr($key) . ']">';
						foreach ($options as $opt_value => $opt_label) {
							$selected = selected($value, $opt_value, false);
							$opt_label_text = wp_specialchars_decode($opt_label);
							echo '<option value="' . esc_attr($opt_value) . '" ' . $selected . '>' . esc_html($opt_label_text) . '</option>';
						}
						echo '</select>';
						break;
					case 'checkbox':
						$checked = !empty($value) ? 'checked' : '';
						echo '<label><input type="checkbox" name="' . esc_attr(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS) . '[' . esc_attr($key) . ']" value="1" ' . $checked . ' /> ' . esc_html($label_text) . '</label>';
						break;
					case 'color':
						echo '<input type="text" name="' . esc_attr(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS) . '[' . esc_attr($key) . ']" value="' . esc_attr($value) . '" class="regular-text" data-default-color="' . esc_attr($default) . '" />';
						echo '<p class="description">' . esc_html__('Enter a hex color value (e.g., #111827)', 'advanced-pdf-embedder') . '</p>';
						break;
				}
			},
			'advanced-pdf-embedder',
			'advanced_pdf_embedder_main_section'
		);
	}

	/**
	 * Sanitize settings before saving.
	 *
	 * @since 1.0.0
	 * @param array $input Raw input values.
	 * @return array Sanitized values.
	 */
	public function sanitize_defaults($input)
	{
		$output = array();
		$output['width'] = isset($input['width']) ? $this->sanitize_dimension($input['width'], '100%') : '100%';
		$output['height'] = isset($input['height']) ? $this->sanitize_dimension($input['height'], '600px') : '600px';
		$output['theme'] = isset($input['theme']) && in_array($input['theme'], array('light', 'dark'), true) ? $input['theme'] : 'light';
		$allowed_langs = array_keys($this->get_language_options());
		$output['language'] = isset($input['language']) && in_array($input['language'], $allowed_langs, true) ? $input['language'] : 'en';
		$output['toolbar'] = !empty($input['toolbar']);
		$output['sidebar'] = !empty($input['sidebar']);
		$output['download'] = !empty($input['download']);
		$output['print'] = !empty($input['print']);
		$output['annotations'] = !empty($input['annotations']);
		$output['redact'] = !empty($input['redact']);
		$output['zoom'] = !empty($input['zoom']);
		$output['backgroundApp'] = isset($input['backgroundApp']) ? sanitize_hex_color($input['backgroundApp']) : '#111827';
		$output['backgroundSurface'] = isset($input['backgroundSurface']) ? sanitize_hex_color($input['backgroundSurface']) : '#1f2937';
		// Fallback to defaults if sanitization fails
		if (empty($output['backgroundApp'])) $output['backgroundApp'] = '#111827';
		if (empty($output['backgroundSurface'])) $output['backgroundSurface'] = '#1f2937';
		return $output;
	}

	/**
	 * Get available language options.
	 *
	 * @since 1.0.0
	 * @return array Associative array of language codes and labels.
	 */
	private function get_language_options()
	{
		return array(
			'en' => __('English', 'advanced-pdf-embedder'),
			'fr' => __('French', 'advanced-pdf-embedder'),
			'de' => __('German', 'advanced-pdf-embedder'),
			'es' => __('Spanish', 'advanced-pdf-embedder'),
			'nl' => __('Dutch', 'advanced-pdf-embedder'),
		);
	}

	/**
	 * Get default options merged with saved values.
	 *
	 * @since 1.0.0
	 * @return array Default options.
	 */
	private function get_default_options()
	{
		$saved = get_option(ADVANCED_PDF_EMBEDDER_OPTION_DEFAULTS, array());
		$defaults = array(
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
			'backgroundApp' => '#111827',
			'backgroundSurface' => '#1f2937',
		);
		return wp_parse_args($saved, $defaults);
	}

	/**
	 * Print TinyMCE defaults as inline script.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function print_tinymce_defaults()
	{
		// Only output when the rich editor is available.
		if ('true' !== get_user_option('rich_editing')) {
			return;
		}

		$defaults = $this->get_default_options();
		$languages = array();
		foreach ($this->get_language_options() as $code => $label) {
			$languages[] = array(
				'text' => $label,
				'value' => $code,
			);
		}
		$i18n = array(
			'title' => __('Embed PDF', 'advanced-pdf-embedder'),
			'browse' => __('Browse Media Library', 'advanced-pdf-embedder'),
			'placeholder' => __('Select a PDF from the Media Library', 'advanced-pdf-embedder'),
			'width' => __('Width', 'advanced-pdf-embedder'),
			'height' => __('Height', 'advanced-pdf-embedder'),
			'theme' => __('Theme', 'advanced-pdf-embedder'),
			'language' => __('Language', 'advanced-pdf-embedder'),
			'showToolbar' => __('Show Toolbar', 'advanced-pdf-embedder'),
			'showSidebar' => __('Show Sidebar', 'advanced-pdf-embedder'),
			'allowDownload' => __('Allow Download', 'advanced-pdf-embedder'),
			'allowPrint' => __('Allow Print', 'advanced-pdf-embedder'),
			'allowAnnotations' => __('Allow Annotations', 'advanced-pdf-embedder'),
			'allowRedaction' => __('Allow Redaction', 'advanced-pdf-embedder'),
			'allowZoom' => __('Allow Zoom', 'advanced-pdf-embedder'),
			'insert' => __('Insert PDF', 'advanced-pdf-embedder'),
			'selectPdfTitle' => __('Select a PDF', 'advanced-pdf-embedder'),
			'selectPdfButton' => __('Use this PDF', 'advanced-pdf-embedder'),
			'light' => __('Light', 'advanced-pdf-embedder'),
			'dark' => __('Dark', 'advanced-pdf-embedder'),
		);
		printf(
			'<script>window.advancedPdfEmbedderDefaults = %s; window.advancedPdfEmbedderI18n = %s; window.advancedPdfEmbedderLanguages = %s;</script>',
			wp_json_encode($defaults),
			wp_json_encode($i18n),
			wp_json_encode($languages)
		);
	}

	/**
	 * Render the settings page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page()
	{
		?>
		<div class="wrap">
			<h1><?php echo esc_html__('Advanced PDF Embedder Settings', 'advanced-pdf-embedder'); ?></h1>
			<p><?php echo esc_html__('Set default options for the Embed PDF TinyMCE button and Gutenberg block.', 'advanced-pdf-embedder'); ?>
			</p>
			<form method="post" action="options.php">
				<?php settings_fields('advanced_pdf_embedder_defaults'); ?>
				<?php do_settings_sections('advanced-pdf-embedder'); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Register the Gutenberg block.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_block()
	{
		$block = register_block_type(ADVANCED_PDF_EMBEDDER_PATH . 'blocks/embed-pdf', array(
			'render_callback' => array($this, 'render_block'),
		));

		// Set script translations for the block editor script.
		// The handle is generated by WordPress as: {block-name}-editor-script
		// For block name "advanced-pdf-embedder/viewer", the handle is "advanced-pdf-embedder-viewer-editor-script".
		if ($block && function_exists('wp_set_script_translations')) {
			wp_set_script_translations(
				'advanced-pdf-embedder-viewer-editor-script',
				'advanced-pdf-embedder',
				ADVANCED_PDF_EMBEDDER_PATH . 'languages'
			);
		}
	}

	/**
	 * Render the Gutenberg block on the frontend.
	 *
	 * @since 1.0.0
	 * @param array $attributes Block attributes.
	 * @return string HTML output.
	 */
	public function render_block($attributes = array())
	{
		// Ensure all attributes have default values.
		$attributes = wp_parse_args($attributes, array(
			'url' => '',
			'width' => '100%',
			'height' => '600px',
			'theme' => 'light',
			'language' => 'en',
			'showToolbar' => true,
			'showSidebar' => true,
			'allowDownload' => true,
			'allowPrint' => true,
			'allowAnnotations' => true,
			'allowRedaction' => true,
			'allowZoom' => true,
			'backgroundApp' => '#111827',
			'backgroundSurface' => '#1f2937',
		));

		// Convert block attributes to shortcode format.
		$shortcode_atts = array(
			'url' => $attributes['url'],
			'width' => $attributes['width'],
			'height' => $attributes['height'],
			'theme' => $attributes['theme'],
			'language' => $attributes['language'],
			'toolbar' => $attributes['showToolbar'] ? 'true' : 'false',
			'sidebar' => $attributes['showSidebar'] ? 'true' : 'false',
			'download' => $attributes['allowDownload'] ? 'true' : 'false',
			'print' => $attributes['allowPrint'] ? 'true' : 'false',
			'annotations' => isset($attributes['allowAnnotations']) ? ($attributes['allowAnnotations'] ? 'true' : 'false') : 'true',
			'redact' => isset($attributes['allowRedaction']) ? ($attributes['allowRedaction'] ? 'true' : 'false') : 'true',
			'zoom' => isset($attributes['allowZoom']) ? ($attributes['allowZoom'] ? 'true' : 'false') : 'true',
			'background_app' => isset($attributes['backgroundApp']) ? $attributes['backgroundApp'] : '#111827',
			'background_surface' => isset($attributes['backgroundSurface']) ? $attributes['backgroundSurface'] : '#1f2937',
		);

		return $this->render_shortcode($shortcode_atts);
	}

	/**
	 * Register TinyMCE button for the classic editor to insert the EmbedPDF shortcode.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function register_tinymce_embed_button()
	{
		if (!current_user_can('edit_posts') || !current_user_can('edit_pages')) {
			return;
		}

		if ('true' !== get_user_option('rich_editing')) {
			return;
		}

		add_filter('mce_external_plugins', array($this, 'add_tinymce_embed_plugin'));
		add_filter('mce_buttons', array($this, 'add_tinymce_embed_button_to_toolbar'));
	}

	/**
	 * Provide the TinyMCE plugin script URL.
	 *
	 * @since 1.0.0
	 * @param array $plugins Existing TinyMCE plugins.
	 * @return array Modified plugins array.
	 */
	public function add_tinymce_embed_plugin($plugins)
	{
		$plugins['advanced_pdf_embedder_button'] = ADVANCED_PDF_EMBEDDER_URL . 'assets/editor-button.js';
		return $plugins;
	}

	/**
	 * Register the TinyMCE toolbar button.
	 *
	 * @since 1.0.0
	 * @param array $buttons Existing toolbar buttons.
	 * @return array Modified buttons array.
	 */
	public function add_tinymce_embed_button_to_toolbar($buttons)
	{
		$buttons[] = 'advanced_pdf_embedder_button';
		return $buttons;
	}
}
