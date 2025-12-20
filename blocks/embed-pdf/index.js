(function (wp) {
    var el = wp.element.createElement;
    var __ = wp.i18n && wp.i18n.__ ? wp.i18n.__ : function (s) { return s; };
    var registerBlockType = wp.blocks.registerBlockType;
    var InspectorControls = wp.blockEditor.InspectorControls;
    var useBlockProps = wp.blockEditor.useBlockProps;
    var MediaUpload = wp.blockEditor && wp.blockEditor.MediaUpload ? wp.blockEditor.MediaUpload : (wp.editor && wp.editor.MediaUpload ? wp.editor.MediaUpload : null);
    var MediaUploadCheck = wp.blockEditor && wp.blockEditor.MediaUploadCheck ? wp.blockEditor.MediaUploadCheck : (wp.editor && wp.editor.MediaUploadCheck ? wp.editor.MediaUploadCheck : function (props) { return el(wp.element.Fragment, null, props.children); });
    var PanelBody = wp.components.PanelBody;
    var TextControl = wp.components.TextControl;
    var SelectControl = wp.components.SelectControl;
    var ToggleControl = wp.components.ToggleControl;
    var Button = wp.components.Button;
    var useEffect = wp.element.useEffect;
    var useState = wp.element.useState;

    // Get defaults from PHP (injected via wp_add_inline_script).
    var defaults = window.advancedPdfEmbedderBlockDefaults || {
        width: '100%',
        height: '600px',
        theme: 'light',
        language: 'en',
        toolbar: true,
        sidebar: true,
        download: true,
        print: true,
        annotations: true,
        redact: true,
        zoom: true
    };

    registerBlockType('advanced-pdf-embedder/viewer', {
        edit: function (props) {
            var attributes = props.attributes;
            var setAttributes = props.setAttributes;
            var blockProps = useBlockProps ? useBlockProps() : {};
            var _useState = useState(false);
            var initialized = _useState[0];
            var setInitialized = _useState[1];

            // Apply defaults on first render if block is newly inserted (no URL set yet)
            useEffect(function () {
                if (!initialized && !attributes.url) {
                    // Apply PHP settings as initial values for new blocks
                    setAttributes({
                        width: defaults.width || '100%',
                        height: defaults.height || '600px',
                        theme: defaults.theme || 'light',
                        language: defaults.language || 'en',
                        showToolbar: defaults.toolbar !== undefined ? defaults.toolbar : true,
                        showSidebar: defaults.sidebar !== undefined ? defaults.sidebar : true,
                        allowDownload: defaults.download !== undefined ? defaults.download : true,
                        allowPrint: defaults.print !== undefined ? defaults.print : true,
                        allowAnnotations: defaults.annotations !== undefined ? defaults.annotations : true,
                        allowRedaction: defaults.redact !== undefined ? defaults.redact : true,
                        allowZoom: defaults.zoom !== undefined ? defaults.zoom : true
                    });
                    setInitialized(true);
                } else if (!initialized) {
                    setInitialized(true);
                }
            }, []);

            return el(
                wp.element.Fragment,
                null,
                el(
                    InspectorControls,
                    null,
                    el(
                        PanelBody,
                        { title: __('PDF Settings', 'advanced-pdf-embedder'), initialOpen: true },
                        el(TextControl, {
                            label: __('PDF URL', 'advanced-pdf-embedder'),
                            value: attributes.url || '',
                            onChange: function (val) { setAttributes({ url: val }); },
                            help: __('Enter the full URL to the PDF file', 'advanced-pdf-embedder')
                        }),
                        MediaUpload && el(MediaUploadCheck, null,
                            el(MediaUpload, {
                                onSelect: function (media) {
                                    if (media && media.url) {
                                        setAttributes({ url: media.url });
                                    }
                                },
                                allowedTypes: ['application/pdf'],
                                render: function (obj) {
                                    return el(Button, {
                                        onClick: obj.open,
                                        isSecondary: true,
                                        style: { marginTop: '8px' }
                                    }, attributes.url ? __('Replace from Media Library', 'advanced-pdf-embedder') : __('Select from Media Library', 'advanced-pdf-embedder'));
                                }
                            })
                        ),
                        el(TextControl, {
                            label: __('Width', 'advanced-pdf-embedder'),
                            value: attributes.width || '100%',
                            onChange: function (val) { setAttributes({ width: val }); }
                        }),
                        el(TextControl, {
                            label: __('Height', 'advanced-pdf-embedder'),
                            value: attributes.height || '600px',
                            onChange: function (val) { setAttributes({ height: val }); }
                        }),
                        el(SelectControl, {
                            label: __('Theme', 'advanced-pdf-embedder'),
                            value: attributes.theme || 'light',
                            options: [
                                { label: __('Light', 'advanced-pdf-embedder'), value: 'light' },
                                { label: __('Dark', 'advanced-pdf-embedder'), value: 'dark' },
                            ],
                            onChange: function (val) { setAttributes({ theme: val }); }
                        }),
                        el(SelectControl, {
                            label: __('Language', 'advanced-pdf-embedder'),
                            value: attributes.language || 'en',
                            options: [
                                { label: __('English', 'advanced-pdf-embedder'), value: 'en' },
                                { label: __('French', 'advanced-pdf-embedder'), value: 'fr' },
                                { label: __('German', 'advanced-pdf-embedder'), value: 'de' },
                                { label: __('Spanish', 'advanced-pdf-embedder'), value: 'es' },
                                { label: __('Dutch', 'advanced-pdf-embedder'), value: 'nl' },
                            ],
                            onChange: function (val) { setAttributes({ language: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Toolbar', 'advanced-pdf-embedder'),
                            checked: attributes.showToolbar !== undefined ? attributes.showToolbar : true,
                            onChange: function (val) { setAttributes({ showToolbar: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Show Sidebar', 'advanced-pdf-embedder'),
                            checked: attributes.showSidebar !== undefined ? attributes.showSidebar : true,
                            onChange: function (val) { setAttributes({ showSidebar: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Allow Download', 'advanced-pdf-embedder'),
                            checked: attributes.allowDownload !== undefined ? attributes.allowDownload : true,
                            onChange: function (val) { setAttributes({ allowDownload: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Allow Print', 'advanced-pdf-embedder'),
                            checked: attributes.allowPrint !== undefined ? attributes.allowPrint : true,
                            onChange: function (val) { setAttributes({ allowPrint: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Allow Annotations', 'advanced-pdf-embedder'),
                            checked: attributes.allowAnnotations !== undefined ? attributes.allowAnnotations : true,
                            onChange: function (val) { setAttributes({ allowAnnotations: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Allow Redaction', 'advanced-pdf-embedder'),
                            checked: attributes.allowRedaction !== undefined ? attributes.allowRedaction : true,
                            onChange: function (val) { setAttributes({ allowRedaction: val }); }
                        }),
                        el(ToggleControl, {
                            label: __('Allow Zoom', 'advanced-pdf-embedder'),
                            checked: attributes.allowZoom !== undefined ? attributes.allowZoom : true,
                            onChange: function (val) { setAttributes({ allowZoom: val }); }
                        })
                    )
                ),
                el(
                    'div',
                    Object.assign({}, blockProps, {
                        style: {
                            border: '2px dashed #667eea',
                            borderRadius: '12px',
                            padding: '24px',
                            textAlign: 'center',
                            backgroundColor: '#f8f7ff',
                            display: 'flex',
                            flexDirection: 'column',
                            alignItems: 'center',
                            gap: '12px'
                        }
                    }),
                    el('div', {
                        style: {
                            width: '48px',
                            height: '48px',
                            background: 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)',
                            borderRadius: '12px',
                            display: 'flex',
                            alignItems: 'center',
                            justifyContent: 'center'
                        }
                    },
                        el('svg', {
                            width: '24',
                            height: '24',
                            viewBox: '0 0 24 24',
                            fill: 'none',
                            stroke: '#fff',
                            strokeWidth: '2'
                        },
                            el('path', { d: 'M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z' }),
                            el('polyline', { points: '14 2 14 8 20 8' }),
                            el('line', { x1: '16', y1: '13', x2: '8', y2: '13' }),
                            el('line', { x1: '16', y1: '17', x2: '8', y2: '17' })
                        )
                    ),
                    el('strong', { style: { fontSize: '16px', color: '#374151' } }, __('Advanced PDF Embedder', 'advanced-pdf-embedder')),
                    attributes.url
                        ? el('span', { style: { fontSize: '13px', color: '#667eea', wordBreak: 'break-all', maxWidth: '100%' } }, attributes.url)
                        : el('em', { style: { fontSize: '13px', color: '#9ca3af' } }, __('Select a PDF from the block settings →', 'advanced-pdf-embedder'))
                )
            );
        },
        save: function () {
            return null;
        }
    });
})(window.wp);
