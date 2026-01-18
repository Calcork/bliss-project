/**
 * RichTextEditor - Abstract base for rich text editor implementations.
 *
 * This module provides a facade for rich text editing functionality,
 * allowing the underlying editor (Tiptap, CKEditor, etc.) to be swapped
 * without changing application code.
 *
 * All editors output ProseMirror JSON format for consistency.
 *
 * Usage:
 *   import { initRichTextEditors } from './RichTextEditor/RichTextEditor.js';
 *   initRichTextEditors();
 *
 * Or for specific elements:
 *   import { createEditor } from './RichTextEditor/RichTextEditor.js';
 *   const editor = await createEditor(containerElement, hiddenInput, options);
 */

// Current implementation uses Tiptap via CDN
const EDITOR_IMPLEMENTATION = 'tiptap';

// Toolbar presets - define which buttons appear for each preset
const TOOLBAR_PRESETS = {
    full: [
        { command: 'bold', icon: 'B', title: 'Bold' },
        { command: 'italic', icon: 'I', title: 'Italic' },
        { command: 'strike', icon: 'S', title: 'Strikethrough' },
        { command: 'separator' },
        { command: 'heading', level: 1, icon: 'H1', title: 'Heading 1' },
        { command: 'heading', level: 2, icon: 'H2', title: 'Heading 2' },
        { command: 'heading', level: 3, icon: 'H3', title: 'Heading 3' },
        { command: 'separator' },
        { command: 'bulletList', icon: '\u2022', title: 'Bullet List' },
        { command: 'orderedList', icon: '1.', title: 'Numbered List' },
        { command: 'separator' },
        { command: 'blockquote', icon: '\u201C', title: 'Quote' },
        { command: 'codeBlock', icon: '</>', title: 'Code Block' },
        { command: 'separator' },
        { command: 'link', icon: '\uD83D\uDD17', title: 'Link' },
        { command: 'image', icon: '\ud83d\uddbc\ufe0f', title: 'Image' },
        { command: 'separator' },
        { command: 'rtl', icon: '\u2190', title: 'Right-to-Left' },
        { command: 'separator' },
        { command: 'undo', icon: '\u21B6', title: 'Undo' },
        { command: 'redo', icon: '\u21B7', title: 'Redo' },
    ],
    minimal: [
        { command: 'bold', icon: 'B', title: 'Bold' },
        { command: 'italic', icon: 'I', title: 'Italic' },
        { command: 'separator' },
        { command: 'rtl', icon: '\u2190', title: 'Right-to-Left' },
        { command: 'separator' },
        { command: 'undo', icon: '\u21B6', title: 'Undo' },
        { command: 'redo', icon: '\u21B7', title: 'Redo' },
    ],
};

/**
 * Create a rich text editor instance.
 *
 * @param {HTMLElement} container - The container element for the editor
 * @param {HTMLInputElement} hiddenInput - Hidden input to store JSON content
 * @param {Object} options - Editor options
 * @returns {Promise<Object>} The editor instance
 */
export async function createEditor(container, hiddenInput, options = {}) {
    if (EDITOR_IMPLEMENTATION === 'tiptap') {
        return await createTiptapEditor(container, hiddenInput, options);
    }

    throw new Error(`Unknown editor implementation: ${EDITOR_IMPLEMENTATION}`);
}

/**
 * Initialize all rich text editors on the page.
 * Finds elements with data-richtext-editor attribute and initializes them.
 *
 * @returns {Promise<Object[]>} Array of editor instances
 */
export async function initRichTextEditors() {
    const editors = [];
    const editorContainers = document.querySelectorAll('[data-richtext-editor]');

    for (const container of editorContainers) {
        const fieldName = container.getAttribute('data-richtext-editor');
        const hiddenInput = document.querySelector(`[data-richtext-input="${fieldName}"]`);

        if (!hiddenInput) {
            console.error(`RichTextEditor: Hidden input not found for field "${fieldName}"`);
            continue;
        }

        const placeholder = container.getAttribute('data-richtext-placeholder') || '';
        const preset = container.getAttribute('data-richtext-preset') || 'full';

        try {
            const editor = await createEditor(container, hiddenInput, { placeholder, preset });
            editors.push(editor);
        } catch (error) {
            console.error(`RichTextEditor: Failed to initialize editor for "${fieldName}"`, error);
        }
    }

    return editors;
}

/**
 * Create a Tiptap editor instance.
 *
 * @param {HTMLElement} container - The container element
 * @param {HTMLInputElement} hiddenInput - Hidden input for JSON storage
 * @param {Object} options - Editor options
 * @returns {Promise<Object>} Tiptap Editor instance
 */
async function createTiptapEditor(container, hiddenInput, options = {}) {
    // Import Tiptap from CDN
    const { Editor } = await import('https://esm.sh/@tiptap/core@2');
    const StarterKit = (await import('https://esm.sh/@tiptap/starter-kit@2')).default;
    const Placeholder = (await import('https://esm.sh/@tiptap/extension-placeholder@2')).default;
    const Link = (await import('https://esm.sh/@tiptap/extension-link@2')).default;
    const Image = (await import('https://esm.sh/@tiptap/extension-image@2')).default;

    // Parse initial content from hidden input
    let initialContent = null;
    if (hiddenInput.value) {
        try {
            initialContent = JSON.parse(hiddenInput.value);
        } catch (e) {
            // If not valid JSON, treat as empty
            console.warn('RichTextEditor: Initial content is not valid JSON, starting fresh');
        }
    }

    // Get preset (default to 'full')
    const preset = options.preset || 'full';

    // Create editor with toolbar
    const wrapper = document.createElement('div');
    wrapper.className = 'richtext-wrapper';

    const toolbar = createToolbar(preset);
    const editorElement = document.createElement('div');
    editorElement.className = 'richtext-content';

    wrapper.appendChild(toolbar);
    wrapper.appendChild(editorElement);
    container.appendChild(wrapper);

    // Get grant URL from container's data attribute, or use default API endpoint
    const grantUrl = container.getAttribute('data-richtext-grant-url') || '/api/rich-text/grant';

    // Initialize Tiptap
    const editor = new Editor({
        element: editorElement,
        extensions: [
            StarterKit,
            Placeholder.configure({
                placeholder: options.placeholder || 'Start writing...',
            }),
            Link.configure({
                openOnClick: false,
                HTMLAttributes: {
                    target: '_blank',
                    rel: 'noopener noreferrer nofollow',
                },
            }),
            Image.configure({
                HTMLAttributes: {
                    class: 'richtext-image',
                },
            }),
        ],
        content: initialContent,
        onUpdate: ({ editor }) => {
            // Sync content to hidden input on every change
            const json = editor.getJSON();
            hiddenInput.value = JSON.stringify(json);
        },
        editorProps: {
            handlePaste: (view, event) => {
                const items = event.clipboardData?.items;
                if (!items) return false;

                for (const item of items) {
                    if (item.type.startsWith('image/')) {
                        event.preventDefault();
                        const file = item.getAsFile();
                        if (file) {
                            uploadImageWithGrant(editor, file, grantUrl);
                        }
                        return true;
                    }
                }
                return false;
            },
            handleDrop: (view, event) => {
                const files = event.dataTransfer?.files;
                if (!files || files.length === 0) return false;

                for (const file of files) {
                    if (file.type.startsWith('image/')) {
                        event.preventDefault();
                        uploadImageWithGrant(editor, file, grantUrl);
                        return true;
                    }
                }
                return false;
            },
        },
    });

    // Wire up toolbar buttons (pass grantUrl for image command)
    wireToolbar(toolbar, editor, grantUrl);

    // Set RTL if page is RTL
    const pageDir = document.documentElement.getAttribute('dir') || document.body.getAttribute('dir');
    if (pageDir === 'rtl') {
        editor.view.dom.setAttribute('dir', 'rtl');
    }

    // Update toolbar state to reflect initial RTL
    updateToolbarState(toolbar, editor);

    // Return wrapped editor with consistent API
    return {
        instance: editor,
        getJSON: () => editor.getJSON(),
        getHTML: () => editor.getHTML(),
        getText: () => editor.getText(),
        setContent: (content) => editor.commands.setContent(content),
        destroy: () => editor.destroy(),
    };
}

/**
 * Create the editor toolbar with formatting buttons.
 *
 * @param {string} preset - Toolbar preset name ('full', 'minimal', etc.)
 * @returns {HTMLElement} Toolbar element
 */
function createToolbar(preset = 'full') {
    const toolbar = document.createElement('div');
    toolbar.className = 'richtext-toolbar';

    // Get buttons for this preset, fallback to full
    const buttons = TOOLBAR_PRESETS[preset] || TOOLBAR_PRESETS.full;

    buttons.forEach(btn => {
        if (btn.command === 'separator') {
            const sep = document.createElement('div');
            toolbar.appendChild(sep);
        } else {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'richtext-toolbar-btn';
            button.setAttribute('data-command', btn.command);
            if (btn.level) {
                button.setAttribute('data-level', btn.level);
            }
            button.title = btn.title;
            button.textContent = btn.icon;
            toolbar.appendChild(button);
        }
    });

    return toolbar;
}

/**
 * Wire toolbar buttons to editor commands.
 *
 * @param {HTMLElement} toolbar - Toolbar element
 * @param {Object} editor - Tiptap editor instance
 * @param {string} grantUrl - The grant endpoint URL for image uploads
 */
function wireToolbar(toolbar, editor, grantUrl) {
    const buttons = toolbar.querySelectorAll('.richtext-toolbar-btn');

    buttons.forEach(button => {
        const command = button.getAttribute('data-command');
        const level = button.getAttribute('data-level');

        button.addEventListener('click', (e) => {
            e.preventDefault();

            switch (command) {
                case 'bold':
                    editor.chain().focus().toggleBold().run();
                    break;
                case 'italic':
                    editor.chain().focus().toggleItalic().run();
                    break;
                case 'strike':
                    editor.chain().focus().toggleStrike().run();
                    break;
                case 'heading':
                    editor.chain().focus().toggleHeading({ level: parseInt(level) }).run();
                    break;
                case 'bulletList':
                    editor.chain().focus().toggleBulletList().run();
                    break;
                case 'orderedList':
                    editor.chain().focus().toggleOrderedList().run();
                    break;
                case 'blockquote':
                    editor.chain().focus().toggleBlockquote().run();
                    break;
                case 'codeBlock':
                    editor.chain().focus().toggleCodeBlock().run();
                    break;
                case 'undo':
                    editor.chain().focus().undo().run();
                    break;
                case 'redo':
                    editor.chain().focus().redo().run();
                    break;
                case 'link':
                    handleLinkCommand(editor);
                    break;
                case 'image':
                    handleImageCommand(editor, grantUrl);
                    break;
                case 'rtl':
                    handleRtlCommand(editor);
                    break;
            }

            updateToolbarState(toolbar, editor);
        });
    });

    // Update active state on selection change
    editor.on('selectionUpdate', () => {
        updateToolbarState(toolbar, editor);
    });

    editor.on('update', () => {
        updateToolbarState(toolbar, editor);
    });
}

/**
 * Update toolbar button active states based on current selection.
 *
 * @param {HTMLElement} toolbar - Toolbar element
 * @param {Object} editor - Tiptap editor instance
 */
function updateToolbarState(toolbar, editor) {
    const buttons = toolbar.querySelectorAll('.richtext-toolbar-btn');

    buttons.forEach(button => {
        const command = button.getAttribute('data-command');
        const level = button.getAttribute('data-level');

        let isActive = false;

        switch (command) {
            case 'bold':
                isActive = editor.isActive('bold');
                break;
            case 'italic':
                isActive = editor.isActive('italic');
                break;
            case 'strike':
                isActive = editor.isActive('strike');
                break;
            case 'heading':
                isActive = editor.isActive('heading', { level: parseInt(level) });
                break;
            case 'bulletList':
                isActive = editor.isActive('bulletList');
                break;
            case 'orderedList':
                isActive = editor.isActive('orderedList');
                break;
            case 'blockquote':
                isActive = editor.isActive('blockquote');
                break;
            case 'codeBlock':
                isActive = editor.isActive('codeBlock');
                break;
            case 'link':
                isActive = editor.isActive('link');
                break;
            case 'rtl':
                isActive = editor.view.dom.getAttribute('dir') === 'rtl';
                break;
        }

        button.classList.toggle('is-active', isActive);
    });
}

/**
 * Handle link command - show prompt for URL.
 *
 * @param {Object} editor - Tiptap editor instance
 */
function handleLinkCommand(editor) {
    // Check if already has link - if so, remove it
    if (editor.isActive('link')) {
        editor.chain().focus().unsetLink().run();
        return;
    }

    // Get current selection text for context
    const { from, to } = editor.state.selection;
    const hasSelection = from !== to;

    // Prompt for URL
    const url = prompt('Enter URL:', 'https://');

    if (url === null) {
        // User cancelled
        editor.chain().focus().run();
        return;
    }

    if (url === '' || url === 'https://') {
        // Empty URL, remove link if exists
        editor.chain().focus().unsetLink().run();
        return;
    }

    // Validate URL has protocol
    let finalUrl = url;
    if (!/^https?:\/\//i.test(url) && !/^mailto:/i.test(url) && !/^tel:/i.test(url)) {
        finalUrl = 'https://' + url;
    }

    if (hasSelection) {
        // Apply link to selection
        editor.chain().focus().setLink({ href: finalUrl }).run();
    } else {
        // No selection - insert URL as link text
        editor.chain().focus().insertContent({
            type: 'text',
            text: finalUrl,
            marks: [{ type: 'link', attrs: { href: finalUrl } }],
        }).run();
    }
}

/**
 * Handle image command - upload image file via file picker.
 *
 * @param {Object} editor - Tiptap editor instance
 * @param {string} grantUrl - The grant endpoint URL
 */
function handleImageCommand(editor, grantUrl) {
    // Create hidden file input
    const fileInput = document.createElement('input');
    fileInput.type = 'file';
    fileInput.accept = 'image/jpeg,image/png,image/gif,image/webp';
    fileInput.style.display = 'none';

    fileInput.addEventListener('change', async () => {
        const file = fileInput.files[0];
        if (!file) {
            editor.chain().focus().run();
            return;
        }

        await uploadImageWithGrant(editor, file, grantUrl);
        fileInput.remove();
    });

    // Trigger file picker
    document.body.appendChild(fileInput);
    fileInput.click();
}

/**
 * Upload an image using the grant token flow.
 * 1. Request upload grant from API (session auth, no CSRF needed)
 * 2. Upload file to /api/upload with the grant token
 * 3. Insert resulting URL into editor
 *
 * @param {Object} editor - Tiptap editor instance
 * @param {File} file - The image file to upload
 * @param {string} grantUrl - The grant endpoint URL
 */
async function uploadImageWithGrant(editor, file, grantUrl) {
    try {
        // Step 1: Get upload grant token (session auth, no CSRF)
        const grantResponse = await fetch(grantUrl, {
            method: 'POST',
            credentials: 'same-origin', // Include session cookie
        });

        const grantResult = await grantResponse.json();

        if (!grantResult.success) {
            alert('Upload failed: ' + (grantResult.error || 'Could not get upload permission'));
            return;
        }

        // Step 2: Upload file with the token and category
        const uploadFormData = new FormData();
        uploadFormData.append('file', file);
        uploadFormData.append('token', grantResult.token);
        uploadFormData.append('category', grantResult.category);

        const uploadResponse = await fetch(grantResult.upload_url, {
            method: 'POST',
            body: uploadFormData,
        });

        const uploadResult = await uploadResponse.json();

        if (!uploadResult.success) {
            alert('Upload failed: ' + (uploadResult.error || 'Unknown error'));
            return;
        }

        // Step 3: Insert image into editor
        editor.chain().focus().setImage({
            src: uploadResult.url,
            alt: file.name.replace(/\.[^.]+$/, ''),
        }).run();
    } catch (error) {
        console.error('Image upload error:', error);
        alert('Upload failed: Network error');
    }
}

/**
 * Handle RTL command - toggle text direction.
 *
 * @param {Object} editor - Tiptap editor instance
 */
function handleRtlCommand(editor) {
    const editorElement = editor.view.dom;
    const currentDir = editorElement.getAttribute('dir');
    const newDir = currentDir === 'rtl' ? 'ltr' : 'rtl';
    editorElement.setAttribute('dir', newDir);
    editor.chain().focus().run();
}

// Auto-initialize when DOM is ready if this module is loaded directly
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        // Only auto-init if there are rich text elements on the page
        if (document.querySelector('[data-richtext-editor]')) {
            initRichTextEditors();
        }
    });
} else {
    // DOM already loaded
    if (document.querySelector('[data-richtext-editor]')) {
        initRichTextEditors();
    }
}
