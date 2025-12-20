(function () {
    // Defaults pulled from WP settings (falls back if not provided)
    var defaults = window.advancedPdfEmbedderDefaults || {
        url: '',
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

    var i18n = window.advancedPdfEmbedderI18n || {
        title: 'Embed PDF',
        browse: 'Browse Media Library',
        placeholder: 'Select a PDF from the Media Library',
        width: 'Width',
        height: 'Height',
        theme: 'Theme',
        language: 'Language',
        showToolbar: 'Show Toolbar',
        showSidebar: 'Show Sidebar',
        allowDownload: 'Allow Download',
        allowPrint: 'Allow Print',
        allowAnnotations: 'Allow Annotations',
        allowRedaction: 'Allow Redaction',
        allowZoom: 'Allow Zoom',
        insert: 'Insert PDF',
        selectPdfTitle: 'Select a PDF',
        selectPdfButton: 'Use this PDF',
        light: 'Light',
        dark: 'Dark'
    };

    var languages = window.advancedPdfEmbedderLanguages || [
        { text: 'English', value: 'en' },
        { text: 'French', value: 'fr' },
        { text: 'German', value: 'de' },
        { text: 'Spanish', value: 'es' },
        { text: 'Dutch', value: 'nl' }
    ];

    // Modal state
    var modalState = {
        url: defaults.url,
        width: defaults.width,
        height: defaults.height,
        theme: defaults.theme,
        language: defaults.language,
        toolbar: defaults.toolbar,
        sidebar: defaults.sidebar,
        download: defaults.download,
        print: defaults.print,
        annotations: defaults.annotations,
        redact: defaults.redact,
        zoom: defaults.zoom
    };

    function getModalStyles() {
        return `
            .apdf-modal-overlay {
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.6);
                backdrop-filter: blur(4px);
                z-index: 100100;
                display: flex;
                align-items: center;
                justify-content: center;
                animation: apdf-fadeIn 0.2s ease-out;
            }
            @keyframes apdf-fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }
            @keyframes apdf-slideIn {
                from { opacity: 0; transform: scale(0.95) translateY(-10px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
            .apdf-modal {
                background: #fff;
                border-radius: 16px;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
                width: 520px;
                max-width: 95vw;
                max-height: 90vh;
                overflow: hidden;
                animation: apdf-slideIn 0.3s ease-out;
            }
            .apdf-modal-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                padding: 24px 28px;
                display: flex;
                align-items: center;
                justify-content: space-between;
            }
            .apdf-modal-title {
                color: #fff;
                font-size: 20px;
                font-weight: 600;
                margin: 0;
                display: flex;
                align-items: center;
                gap: 12px;
            }
            .apdf-modal-title svg {
                width: 28px;
                height: 28px;
                opacity: 0.9;
            }
            .apdf-modal-close {
                background: rgba(255,255,255,0.2);
                border: none;
                color: #fff;
                width: 36px;
                height: 36px;
                border-radius: 50%;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s;
            }
            .apdf-modal-close:hover {
                background: rgba(255,255,255,0.3);
                transform: rotate(90deg);
            }
            .apdf-modal-body {
                padding: 24px 28px;
                max-height: 60vh;
                overflow-y: auto;
            }
            .apdf-section {
                margin-bottom: 24px;
            }
            .apdf-section:last-child {
                margin-bottom: 0;
            }
            .apdf-section-title {
                font-size: 11px;
                font-weight: 600;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                color: #6b7280;
                margin-bottom: 12px;
            }
            .apdf-file-picker {
                border: 2px dashed #e5e7eb;
                border-radius: 12px;
                padding: 20px;
                text-align: center;
                transition: all 0.2s;
                cursor: pointer;
            }
            .apdf-file-picker:hover {
                border-color: #667eea;
                background: #f8f7ff;
            }
            .apdf-file-picker.has-file {
                border-style: solid;
                border-color: #10b981;
                background: #ecfdf5;
            }
            .apdf-file-picker-icon {
                width: 48px;
                height: 48px;
                margin: 0 auto 12px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border-radius: 12px;
                display: flex;
                align-items: center;
                justify-content: center;
            }
            .apdf-file-picker-icon svg {
                width: 24px;
                height: 24px;
                color: #fff;
            }
            .apdf-file-picker-text {
                font-size: 14px;
                color: #374151;
                margin-bottom: 4px;
            }
            .apdf-file-picker-hint {
                font-size: 12px;
                color: #9ca3af;
            }
            .apdf-file-picker-url {
                font-size: 12px;
                color: #10b981;
                word-break: break-all;
                margin-top: 8px;
            }
            .apdf-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 16px;
            }
            .apdf-field {
                display: flex;
                flex-direction: column;
            }
            .apdf-field label {
                font-size: 13px;
                font-weight: 500;
                color: #374151;
                margin-bottom: 6px;
            }
            .apdf-field input,
            .apdf-field select {
                padding: 10px 14px;
                border: 1px solid #e5e7eb;
                border-radius: 8px;
                font-size: 14px;
                transition: all 0.2s;
                background: #fff;
            }
            .apdf-field input:focus,
            .apdf-field select:focus {
                outline: none;
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }
            .apdf-toggles {
                display: grid;
                grid-template-columns: repeat(2, 1fr);
                gap: 8px;
            }
            .apdf-toggle {
                display: flex;
                align-items: center;
                padding: 10px 14px;
                background: #f9fafb;
                border-radius: 8px;
                cursor: pointer;
                transition: all 0.2s;
                user-select: none;
            }
            .apdf-toggle:hover {
                background: #f3f4f6;
            }
            .apdf-toggle input {
                display: none;
            }
            .apdf-toggle-switch {
                width: 40px;
                height: 22px;
                background: #d1d5db;
                border-radius: 11px;
                position: relative;
                transition: all 0.2s;
                flex-shrink: 0;
            }
            .apdf-toggle-switch::after {
                content: '';
                position: absolute;
                width: 18px;
                height: 18px;
                background: #fff;
                border-radius: 50%;
                top: 2px;
                left: 2px;
                transition: all 0.2s;
                box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            }
            .apdf-toggle input:checked + .apdf-toggle-switch {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            }
            .apdf-toggle input:checked + .apdf-toggle-switch::after {
                left: 20px;
            }
            .apdf-toggle-label {
                margin-left: 12px;
                font-size: 13px;
                color: #374151;
            }
            .apdf-modal-footer {
                padding: 20px 28px;
                background: #f9fafb;
                border-top: 1px solid #e5e7eb;
            }
            .apdf-btn-insert {
                width: 100%;
                padding: 14px 24px;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: #fff;
                border: none;
                border-radius: 10px;
                font-size: 15px;
                font-weight: 600;
                cursor: pointer;
                transition: all 0.2s;
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 8px;
            }
            .apdf-btn-insert:hover {
                transform: translateY(-1px);
                box-shadow: 0 10px 20px -10px rgba(102, 126, 234, 0.5);
            }
            .apdf-btn-insert:active {
                transform: translateY(0);
            }
            .apdf-btn-insert:disabled {
                opacity: 0.6;
                cursor: not-allowed;
                transform: none;
                box-shadow: none;
            }
        `;
    }

    function getModalHTML() {
        var languageOptions = languages.map(function (lang) {
            var selected = lang.value === modalState.language ? 'selected' : '';
            return '<option value="' + lang.value + '" ' + selected + '>' + lang.text + '</option>';
        }).join('');

        return `
            <div class="apdf-modal">
                <div class="apdf-modal-header">
                    <h2 class="apdf-modal-title">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        ${i18n.title}
                    </h2>
                    <button type="button" class="apdf-modal-close" id="apdf-close">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    </button>
                </div>
                <div class="apdf-modal-body">
                    <div class="apdf-section">
                        <div class="apdf-section-title">PDF Document</div>
                        <div class="apdf-file-picker" id="apdf-file-picker">
                            <div class="apdf-file-picker-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="17 8 12 3 7 8"></polyline>
                                    <line x1="12" y1="3" x2="12" y2="15"></line>
                                </svg>
                            </div>
                            <div class="apdf-file-picker-text">${i18n.browse}</div>
                            <div class="apdf-file-picker-hint">${i18n.placeholder}</div>
                            <div class="apdf-file-picker-url" id="apdf-selected-url" style="display: none;"></div>
                        </div>
                    </div>
                    
                    <div class="apdf-section">
                        <div class="apdf-section-title">Dimensions & Appearance</div>
                        <div class="apdf-grid">
                            <div class="apdf-field">
                                <label for="apdf-width">${i18n.width}</label>
                                <input type="text" id="apdf-width" value="${modalState.width}">
                            </div>
                            <div class="apdf-field">
                                <label for="apdf-height">${i18n.height}</label>
                                <input type="text" id="apdf-height" value="${modalState.height}">
                            </div>
                            <div class="apdf-field">
                                <label for="apdf-theme">${i18n.theme}</label>
                                <select id="apdf-theme">
                                    <option value="light" ${modalState.theme === 'light' ? 'selected' : ''}>${i18n.light}</option>
                                    <option value="dark" ${modalState.theme === 'dark' ? 'selected' : ''}>${i18n.dark}</option>
                                </select>
                            </div>
                            <div class="apdf-field">
                                <label for="apdf-language">${i18n.language}</label>
                                <select id="apdf-language">${languageOptions}</select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="apdf-section">
                        <div class="apdf-section-title">Features</div>
                        <div class="apdf-toggles">
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-toolbar" ${modalState.toolbar ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.showToolbar}</span>
                            </label>
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-sidebar" ${modalState.sidebar ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.showSidebar}</span>
                            </label>
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-download" ${modalState.download ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.allowDownload}</span>
                            </label>
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-print" ${modalState.print ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.allowPrint}</span>
                            </label>
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-annotations" ${modalState.annotations ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.allowAnnotations}</span>
                            </label>
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-redact" ${modalState.redact ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.allowRedaction}</span>
                            </label>
                            <label class="apdf-toggle">
                                <input type="checkbox" id="apdf-zoom" ${modalState.zoom ? 'checked' : ''}>
                                <span class="apdf-toggle-switch"></span>
                                <span class="apdf-toggle-label">${i18n.allowZoom}</span>
                            </label>
                        </div>
                    </div>
                </div>
                <div class="apdf-modal-footer">
                    <button type="button" class="apdf-btn-insert" id="apdf-insert" disabled>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="12" y1="18" x2="12" y2="12"></line>
                            <line x1="9" y1="15" x2="15" y2="15"></line>
                        </svg>
                        ${i18n.insert}
                    </button>
                </div>
            </div>
        `;
    }

    function openCustomModal(editor) {
        // Reset state
        modalState.url = '';

        // Create overlay
        var overlay = document.createElement('div');
        overlay.className = 'apdf-modal-overlay';
        overlay.id = 'apdf-modal-overlay';

        // Add styles
        var styleTag = document.createElement('style');
        styleTag.id = 'apdf-modal-styles';
        styleTag.textContent = getModalStyles();
        document.head.appendChild(styleTag);

        // Add modal HTML
        overlay.innerHTML = getModalHTML();
        document.body.appendChild(overlay);

        // Get elements
        var closeBtn = document.getElementById('apdf-close');
        var filePicker = document.getElementById('apdf-file-picker');
        var insertBtn = document.getElementById('apdf-insert');
        var selectedUrlDisplay = document.getElementById('apdf-selected-url');

        // Close on overlay click
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) {
                closeModal();
            }
        });

        // Close on button click
        closeBtn.addEventListener('click', closeModal);

        // Close on Escape key
        document.addEventListener('keydown', function escHandler(e) {
            if (e.key === 'Escape') {
                closeModal();
                document.removeEventListener('keydown', escHandler);
            }
        });

        // File picker click
        filePicker.addEventListener('click', function () {
            if (window.wp && wp.media) {
                var frame = wp.media({
                    title: i18n.selectPdfTitle,
                    button: { text: i18n.selectPdfButton },
                    library: { type: 'application/pdf' },
                    multiple: false
                });

                frame.on('select', function () {
                    var attachment = frame.state().get('selection').first();
                    if (attachment) {
                        var data = attachment.toJSON();
                        if (data && data.url) {
                            modalState.url = data.url;
                            filePicker.classList.add('has-file');
                            selectedUrlDisplay.textContent = data.url;
                            selectedUrlDisplay.style.display = 'block';
                            insertBtn.disabled = false;
                        }
                    }
                });

                frame.open();
            }
        });

        // Insert button click
        insertBtn.addEventListener('click', function () {
            // Collect values
            var width = document.getElementById('apdf-width').value || defaults.width;
            var height = document.getElementById('apdf-height').value || defaults.height;
            var theme = document.getElementById('apdf-theme').value;
            var language = document.getElementById('apdf-language').value;
            var toolbar = document.getElementById('apdf-toolbar').checked;
            var sidebar = document.getElementById('apdf-sidebar').checked;
            var download = document.getElementById('apdf-download').checked;
            var print = document.getElementById('apdf-print').checked;
            var annotations = document.getElementById('apdf-annotations').checked;
            var redact = document.getElementById('apdf-redact').checked;
            var zoom = document.getElementById('apdf-zoom').checked;

            var shortcode = '[embedpdf'
                + ' url="' + modalState.url + '"'
                + ' width="' + width + '"'
                + ' height="' + height + '"'
                + ' theme="' + theme + '"'
                + ' language="' + language + '"'
                + ' toolbar="' + (toolbar ? 'true' : 'false') + '"'
                + ' sidebar="' + (sidebar ? 'true' : 'false') + '"'
                + ' download="' + (download ? 'true' : 'false') + '"'
                + ' print="' + (print ? 'true' : 'false') + '"'
                + ' annotations="' + (annotations ? 'true' : 'false') + '"'
                + ' redact="' + (redact ? 'true' : 'false') + '"'
                + ' zoom="' + (zoom ? 'true' : 'false') + '"]';

            editor.insertContent(shortcode);
            closeModal();
        });

        function closeModal() {
            var overlay = document.getElementById('apdf-modal-overlay');
            var styles = document.getElementById('apdf-modal-styles');
            if (overlay) overlay.remove();
            if (styles) styles.remove();
        }
    }

    tinymce.PluginManager.add('advanced_pdf_embedder_button', function (editor) {
        editor.addButton('advanced_pdf_embedder_button', {
            text: ' PDF',
            icon: 'dashicon dashicons-media-document',
            tooltip: i18n.title,
            onclick: function () {
                openCustomModal(editor);
            }
        });

        editor.addMenuItem('advanced_pdf_embedder_button', {
            text: i18n.title,
            icon: 'dashicon dashicons-media-document',
            context: 'insert',
            onclick: function () {
                openCustomModal(editor);
            }
        });
    });
})();
