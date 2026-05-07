# Advanced PDF Embedder

Embed PDF documents in WordPress with a configurable EmbedPDF 2.x viewer for Gutenberg, Classic Editor, and shortcode-based workflows.

[![WordPress Plugin Version](https://img.shields.io/badge/WordPress-5.8%2B-blue.svg)](https://wordpress.org/)
[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](https://www.gnu.org/licenses/agpl-3.0.html)
[![PHP Version](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)](https://php.net/)

![Plugin Screenshot](https://github.com/guilamu/advanced-pdf-embedder/blob/main/screenshot.jpg)

## Add PDFs Anywhere

- Add PDFs with the **Advanced PDF Embedder** block in Gutenberg.
- Insert PDFs from the Classic Editor **PDF** button without writing shortcode syntax.
- Use the `[embedpdf]` shortcode when you need reusable templates, custom attributes, or programmatic output.

## Choose Viewer Controls

- Choose `light`, `dark`, or `system` themes and set app and surface background overrides.
- Enable or disable toolbar, sidebar, download, print, annotations, redaction, zoom, and View/Insert/Form menus.
- Set widths, heights, `auto` first-page sizing, default zoom, and viewer language options.

## Extend Site-Wide Defaults

- Set global defaults in **Settings → Advanced PDF Embedder** for new Gutenberg and TinyMCE inserts.
- Pass current EmbedPDF config sections such as `permissions`, `scroll`, `export`, `theme_light`, `theme_dark`, `i18n_config`, `ui_config`, and `zoom_config`.
- Override the viewer script URL, allowed HTTPS hosts, shortcode defaults, or final `EmbedPDF.init()` config with WordPress filters.

## Key Features

- **Flexible Embeds:** Add PDFs with Gutenberg, Classic Editor, or shortcode-based embeds.
- **Advanced Config Builder:** Merge current EmbedPDF config sections without forking the plugin.
- **Multilingual:** Expose viewer language choices for English, French, German, Spanish, and Dutch.
- **Translation-Ready:** Includes a POT template, English and French translation files, and block editor translation JSON files.
- **Secure:** Validates remote viewer script URLs, restricts allowed hosts, sanitizes shortcode attributes, and escapes rendered output.
- **GitHub Updates:** Supports update checks against GitHub releases through the bundled updater.

## Requirements

- A publicly reachable PDF URL for each embed, plus JavaScript enabled in the visitor's browser.
- Network access to `cdn.jsdelivr.net` unless you override the viewer script source with the provided filters.
- WordPress 5.8 or higher.
- PHP 7.4 or higher.

## Installation

1. Upload the `advanced-pdf-embedder` folder to `/wp-content/plugins/`, or install the ZIP from GitHub Releases through **Plugins → Add New → Upload Plugin**.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Settings → Advanced PDF Embedder** and configure the default width, height, theme, language, and viewer features.
4. Insert PDFs with the **Advanced PDF Embedder** block, the Classic Editor **PDF** button, or the `[embedpdf]` shortcode.
5. If you use a custom EmbedPDF script host, allow it with the provided filters before loading embeds.

## FAQ

### Which editor workflows are supported?

The plugin supports Gutenberg, the Classic Editor (TinyMCE), and direct shortcode embeds. All three routes feed into the same PHP render layer, so the frontend viewer behavior stays consistent.

### Can I change the shortcode defaults site-wide?

Yes. Use the `advanced_pdf_embedder_shortcode_defaults` filter to change the defaults before `shortcode_atts()` runs.

```php
add_filter( 'advanced_pdf_embedder_shortcode_defaults', function( $defaults, $plugin_defaults ) {
    $defaults['theme'] = 'system';
    $defaults['default_zoom'] = 'fit-page';

    return $defaults;
}, 10, 2 );
```

### Can I customize the final EmbedPDF configuration?

Yes. Use the `advanced_pdf_embedder_config` filter to adjust the final config array after the plugin builds it.

```php
add_filter( 'advanced_pdf_embedder_config', function( $config, $atts ) {
    $config['permissions'] = array(
        'enforceDocumentPermissions' => false,
        'overrides' => array(
            'print' => false,
        ),
    );

    return $config;
}, 10, 2 );
```

### Can I load EmbedPDF from a different URL?

Yes. Override the default jsDelivr source with `advanced_pdf_embedder_viewer_script_url`, and allow additional HTTPS hosts with `advanced_pdf_embedder_allowed_viewer_hosts` when needed.

```php
add_filter( 'advanced_pdf_embedder_viewer_script_url', function( $url ) {
    return plugins_url( 'assets/vendor/embedpdf.js', __FILE__ );
} );

add_filter( 'advanced_pdf_embedder_allowed_viewer_hosts', function( $hosts ) {
    $hosts[] = 'example-cdn.com';

    return $hosts;
} );
```

### Can I hide the View, Insert, or Form menus without writing raw JSON?

Yes. Use `view_menu`, `insert_menu`, and `form_menu` in the shortcode, or the matching controls in Gutenberg, TinyMCE, and the global defaults screen.

### Why does the viewer stay blank?

Check that the PDF URL is publicly accessible, the page is loading JavaScript successfully, and any custom viewer script URL is served over HTTPS from an allowed host. If the viewer script URL fails validation, the plugin does not load the EmbedPDF module.

## Shortcode Reference

### Basic Example

```text
[embedpdf url="https://example.com/document.pdf" width="100%" height="600px"]
```

### Attribute Reference

| Attribute | Type | Default | Description |
|-----------|------|---------|-------------|
| `url` | string | required | Full URL to the PDF file. |
| `width` | string | `100%` | Viewer width in `px`, `%`, `em`, `rem`, `vh`, or `vw`. |
| `height` | string | `600px` | Viewer height. Use `auto` to fit the first page. |
| `theme` | string | `light` | Theme: `light`, `dark`, or `system`. |
| `language` | string | `en` | Viewer language: `en`, `fr`, `de`, `es`, or `nl`. |
| `toolbar` | boolean | `true` | Show or hide the toolbar. |
| `sidebar` | boolean | `true` | Show or hide the sidebar. |
| `download` | boolean | `true` | Allow or block PDF download. |
| `print` | boolean | `true` | Allow or block PDF printing. |
| `annotations` | boolean | `true` | Enable or disable annotation tools. |
| `redact` | boolean | `true` | Enable or disable redaction tools. |
| `zoom` | boolean | `true` | Enable or disable zoom controls. |
| `view_menu` | boolean | `true` | Show or hide the View mode menu button. |
| `insert_menu` | boolean | `true` | Show or hide the Insert mode menu and disable Insert tools when false. |
| `form_menu` | boolean | `true` | Show or hide the Form mode menu and disable Form tools when false. |
| `default_zoom` | string | `fit-width` | Initial zoom: `fit-width`, `fit-page`, or a percentage from `25` to `1600`. |
| `background_app` | string | empty | App background color override in hex. |
| `background_surface` | string | empty | Surface background color override in hex. |
| `disabled_categories` | string | empty | Comma-separated EmbedPDF category slugs to disable in addition to built-in toggles. |
| `permissions` | JSON object | empty | Advanced `permissions` config passed to `EmbedPDF.init()`. |
| `scroll` | JSON object | empty | Advanced `scroll` config such as `defaultStrategy` or `defaultPageGap`. |
| `export` | JSON object | empty | Advanced `export` config such as `defaultFileName`. |
| `theme_light` | JSON object | empty | Advanced overrides merged into `theme.light`. |
| `theme_dark` | JSON object | empty | Advanced overrides merged into `theme.dark`. |
| `i18n_config` | JSON object | empty | Advanced i18n overrides merged into the generated `i18n` config. |
| `ui_config` | JSON object | empty | Advanced UI overrides merged into the generated `ui` config. |
| `zoom_config` | JSON object | empty | Advanced zoom overrides merged into the generated `zoom` config. |

### Advanced Example

```text
[embedpdf
    url="https://example.com/document.pdf"
    width="100%"
    height="800px"
    theme="system"
    language="fr"
    toolbar="true"
    sidebar="false"
    download="true"
    print="false"
    annotations="true"
    redact="false"
    zoom="true"
    view_menu="false"
    insert_menu="false"
    form_menu="false"
    default_zoom="fit-width"
    background_app="#2e2e2e"
    background_surface="#2e2e2e"
    disabled_categories="panel-search,selection-copy,history"
    permissions='{"enforceDocumentPermissions":false,"overrides":{"print":false,"copyContents":true}}'
    scroll='{"defaultStrategy":"horizontal","defaultPageGap":20}'
    export='{"defaultFileName":"proposal-reviewed.pdf"}'
    theme_light='{"accent":{"primary":"#0f766e"}}'
    theme_dark='{"accent":{"primary":"#14b8a6"}}'
]
```

Use single quotes around JSON shortcode values so the inner JSON can keep standard double quotes.

## Known Issues

- Advanced JSON config sections are supported, but only the most common controls are currently exposed in Gutenberg and the Classic Editor dialog.
- The repository currently ships WordPress translation files for English and French; other locales may fall back to English for plugin UI strings until additional translations are added.

## Limitations

- Advanced JSON config fields are supported through shortcode attributes and filters, but not every section has a dedicated Gutenberg or TinyMCE control yet.
- Remote viewer scripts must be local plugin files or HTTPS URLs on hosts allowed by `advanced_pdf_embedder_allowed_viewer_hosts`.
- The viewer can only load PDFs that are reachable by the browser; protected or blocked URLs will not render in the frontend viewer.

## Troubleshooting

### The viewer area is blank.

Confirm that the PDF URL opens directly in a browser tab, the page is not blocking JavaScript, and the EmbedPDF script URL is valid. If you override the script source, make sure the URL uses HTTPS and its host is allowed.

### The Classic Editor button does not appear.

The button is only useful in TinyMCE contexts, and it is intended for users who can edit posts or pages. Switch to the Classic Editor/TinyMCE screen and confirm the current user has the expected editing capabilities.

### My advanced config changes are not visible in the block sidebar.

The block and TinyMCE dialogs expose the common controls, not every low-level EmbedPDF option. For advanced `permissions`, `scroll`, `export`, or per-section JSON overrides, use shortcode attributes or the documented filters.

## Project Structure

```text
advanced-pdf-embedder/
├── advanced-pdf-embedder.php                 # Plugin bootstrap, hooks, defaults, updater, and bug reporter integration
├── uninstall.php                             # Removes plugin options on uninstall
├── README.md                                 # User and developer documentation
├── LICENSE                                   # AGPL-3.0 license text
├── screenshot.jpg                            # Repository screenshot used in documentation
├── assets/
│   └── editor-button.js                      # Classic Editor modal, defaults, and shortcode insertion
├── blocks/
│   └── embed-pdf/
│       ├── block.json                        # Block metadata and attribute schema
│       ├── index.asset.php                   # Generated script dependency and version data
│       ├── index.js                          # Gutenberg inspector controls and editor behavior
│       └── style.css                         # Frontend block styles
├── includes/
│   ├── class-advanced-pdf-embedder.php       # Shortcode rendering, settings, block rendering, TinyMCE, and viewer config building
│   ├── class-github-updater.php              # GitHub release parsing and update integration
│   └── Parsedown.php                         # Markdown parsing for release metadata output
└── languages/
    ├── advanced-pdf-embedder.pot            # Translation template
    ├── advanced-pdf-embedder-en_US.po       # English translation source
    ├── advanced-pdf-embedder-fr_FR.po       # French translation source
    ├── advanced-pdf-embedder-fr_FR.mo       # French translation binary
    └── *.json                               # Block editor translation files
```

## Changelog

### 1.4.5 - 2026-05-07
- **New:** Added the hook-friendly EmbedPDF config builder support for current options such as `permissions`, `scroll`, `export`, `disabled_categories`, and per-section overrides.
- **New:** Added View, Insert, and Form menu controls across shortcode rendering, Gutenberg, TinyMCE, and the global defaults screen.
- **Fixed:** Versioned the TinyMCE modal script URL so updated settings do not disappear behind a cached older `editor-button.js` file.
- **Improved:** Added `system` theme support and refreshed the GitHub updater details modal behavior.
- **Improved:** Updated translation catalogs and release metadata for the 1.4.5 release.

### 1.4.1 - 2026-03-31
- **Improved:** Rewrote the GitHub updater to match the current README parsing and release rendering template.
- **Improved:** Added a "View details" thickbox link in the plugin row meta.

### 1.4.0 - 2026-03-11
- **New:** Added `height="auto"` to size the viewer to the first page without scrolling.
- **Improved:** Recalculate auto-height on window resize.
- **Improved:** Added height help text in Gutenberg and the TinyMCE modal.

### 1.3.3 - 2026-03-11
- **New:** Added default zoom controls to Gutenberg, TinyMCE, and the global settings page.
- **Improved:** Applied default zoom through EmbedPDF's zoom configuration.

### 1.3.2 - 2026-03-11
- **Fixed:** Corrected light theme rendering by removing hardcoded dark background defaults.

### 1.3.1 - 2026-03-11
- **Fixed:** Restored Gutenberg block availability by shipping the correct block asset references.

### 1.3.0 - 2026-03-06
- **Security:** Hardened remote script and update URL validation.
- **Fixed:** Restored the TinyMCE button for users who can edit posts or pages.
- **Improved:** Sanitized shortcode theme and language handling more strictly.
- **Changed:** Updated viewer loading documentation for the current EmbedPDF 2.x CDN source.

### 1.2.1 - 2026-01-26
- **New:** Added customizable app and surface background colors.
- **New:** Added background controls in the admin settings screen and Gutenberg.
- **New:** Added `background_app` and `background_surface` shortcode attributes.

### 1.2.0 - 2026-01-19
- **New:** Integrated Guilamu Bug Reporter support.
- **New:** Added a "Report a Bug" link in the plugins list.

### 1.1.1 - 2025-12-29
- **Improved:** Added GitHub auto-updates from repository releases.
- **Changed:** Updated the plugin author and plugin URI to the official GitHub repository.

### 1.0.0 - 2025-12-29
- Initial release.
- Added shortcode embeds, Gutenberg support, Classic Editor support, global defaults, translations, and EmbedPDF 2.x loading.

## License

This project is licensed under the GNU Affero General Public License v3.0 (AGPL-3.0) - see the [LICENSE](LICENSE) file for details.

---

<p align="center">
  Made with love for the WordPress community
</p>
