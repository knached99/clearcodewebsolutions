import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Bold,
    Italic,
    Underline,
    Link,
    List,
    BlockQuote,
    Heading,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

import Dropzone from 'dropzone';
import 'dropzone/dist/dropzone.css';

document.addEventListener('DOMContentLoaded', () => {
    const editorElement = document.querySelector('#ticket-editor');
    const form = document.querySelector('#ticket-reply-form');
    const dropzoneElement = document.querySelector('#ticket-dropzone');

    if (!editorElement || !form) {
        return;
    }

    let editor = null;

    // -------------------------
    // CKEDITOR
    // -------------------------

    ClassicEditor
        .create(editorElement, {
            licenseKey: 'GPL',

            plugins: [
                Essentials,
                Paragraph,
                Bold,
                Italic,
                Underline,
                Link,
                List,
                BlockQuote,
                Heading,
            ],

            toolbar: [
                'undo',
                'redo',
                '|',
                'heading',
                '|',
                'bold',
                'italic',
                'underline',
                '|',
                'link',
                '|',
                'bulletedList',
                'numberedList',
                '|',
                'blockQuote',
            ],

            heading: {
                options: [
                    {
                        model: 'paragraph',
                        title: 'Paragraph',
                        class: 'ck-heading_paragraph',
                    },
                    {
                        model: 'heading2',
                        view: 'h2',
                        title: 'Heading',
                        class: 'ck-heading_heading2',
                    },
                    {
                        model: 'heading3',
                        view: 'h3',
                        title: 'Subheading',
                        class: 'ck-heading_heading3',
                    },
                ],
            },

            placeholder: 'Write your reply...',
        })
        .then((instance) => {
            editor = instance;

            console.log('CKEditor ready');
        })
        .catch((error) => {
            console.error('CKEditor failed:', error);
        });

    let dropzone = null;

if (dropzoneElement) {
    Dropzone.autoDiscover = false;

    dropzone = new Dropzone(dropzoneElement, {
        url: form.action,

        autoProcessQueue: false,

        clickable: true,

        acceptedFiles: 'image/jpeg,image/png,image/webp,image/gif',

        maxFiles: 10,

        maxFilesize: 10,

        addRemoveLinks: true,

        previewsContainer: dropzoneElement,

        // Don't display upload progress.
        previewTemplate: `
            <div class="dz-preview dz-file-preview">
                <div class="dz-image">
                    <img data-dz-thumbnail />
                </div>

                <div class="dz-details">
                    <div class="dz-filename">
                        <span data-dz-name></span>
                    </div>

                    <div class="dz-size">
                        <span data-dz-size></span>
                    </div>
                </div>

                <div class="dz-error-message">
                    <span data-dz-errormessage></span>
                </div>

                <button
                    type="button"
                    class="dz-remove"
                    data-dz-remove
                >
                    Remove
                </button>
            </div>
        `,
    });

    window.ticketDropzone = dropzone;
}

    // -------------------------
    // FORM SUBMIT
    // -------------------------

    form.addEventListener('submit', async (event) => {
        event.preventDefault();

        if (!editor) {
            alert('The editor is still loading.');
            return;
        }

        const message = editor.getData();

        if (!message.replace(/<[^>]*>/g, '').trim()) {
            alert('Please write a message.');
            return;
        }

        const formData = new FormData();

        const csrf = form.querySelector('input[name="_token"]');

        if (csrf) {
            formData.append('_token', csrf.value);
        }

        formData.append('message', message);

        if (dropzone) {
            dropzone.files.forEach((file) => {
                if (file.status !== Dropzone.CANCELED) {
                    formData.append(
                        'attachments[]',
                        file,
                        file.name
                    );
                }
            });
        }

        const button = document.querySelector('#send-ticket-reply');

        if (button) {
            button.disabled = true;
        }

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                body: formData,

                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                const text = await response.text();
                console.error(text);

                throw new Error('Request failed');
            }

            window.location.reload();

        } catch (error) {
            console.error(error);

            alert(
                'Unable to send your reply. Please try again.'
            );

            if (button) {
                button.disabled = false;
            }
        }
    });
});