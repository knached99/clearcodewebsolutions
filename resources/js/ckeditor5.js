import {
    ClassicEditor,
    Essentials,
    Paragraph,
    Heading,
    Bold,
    Italic,
    Underline,
    Strikethrough,
    Subscript,
    Superscript,
    Link,
    List,
    BlockQuote,
    Highlight,
    Font,
    RemoveFormat,
    Alignment,
    Indent,
    HorizontalLine,
} from 'ckeditor5';

import 'ckeditor5/ckeditor5.css';

export async function createProjectEditor(element, initialData, onChange) {
    const editor = await ClassicEditor.create(element, {
        licenseKey: 'GPL',

        plugins: [
            Essentials,
            Paragraph,
            Heading,
            Bold,
            Italic,
            Underline,
            Strikethrough,
            Subscript,
            Superscript,
            Link,
            List,
            BlockQuote,
            Highlight,
            Font,
            RemoveFormat,
            Alignment,
            Indent,
            HorizontalLine,
        ],

        toolbar: {
            items: [
                'undo',
                'redo',
                '|',

                'heading',

                '|',

                'bold',
                'italic',
                'underline',
                'strikethrough',

                '|',

                'fontSize',
                'fontFamily',
                'fontColor',
                'fontBackgroundColor',

                '|',

                'highlight',
                'removeFormat',

                '|',

                'link',
                'bulletedList',
                'numberedList',
                'blockQuote',
                'horizontalLine',

                '|',

                'alignment',
                'outdent',
                'indent',
            ],

            shouldNotGroupWhenFull: false,
        },

        heading: {
            options: [
                {
                    model: 'paragraph',
                    title: 'Paragraph',
                    class: 'ck-heading_paragraph',
                },
                {
                    model: 'heading1',
                    view: 'h2',
                    title: 'Heading 1',
                    class: 'ck-heading_heading1',
                },
                {
                    model: 'heading2',
                    view: 'h3',
                    title: 'Heading 2',
                    class: 'ck-heading_heading2',
                },
                {
                    model: 'heading3',
                    view: 'h4',
                    title: 'Heading 3',
                    class: 'ck-heading_heading3',
                },
            ],
        },

        fontSize: {
            options: [
                'tiny',
                'small',
                'default',
                'big',
                'huge',
            ],
        },

        fontFamily: {
            options: [
                'default',
                'Arial, Helvetica, sans-serif',
                'Georgia, serif',
                'Verdana, Geneva, sans-serif',
                'Tahoma, Geneva, sans-serif',
                'Trebuchet MS, sans-serif',
                'Courier New, Courier, monospace',
            ],
        },

        fontColor: {
            columns: 5,
            colors: [
                {
                    color: '#ADD322',
                    label: 'Lime',
                },
                {
                    color: '#8B5CF6',
                    label: 'Purple',
                },
                {
                    color: '#EF4444',
                    label: 'Red',
                },
                {
                    color: '#3B82F6',
                    label: 'Blue',
                },
                {
                    color: '#F59E0B',
                    label: 'Orange',
                },
                {
                    color: '#10B981',
                    label: 'Green',
                },
                {
                    color: '#FFFFFF',
                    label: 'White',
                },
                {
                    color: '#D1D5DB',
                    label: 'Gray',
                },
                {
                    color: '#111827',
                    label: 'Black',
                },
            ],
        },

        fontBackgroundColor: {
            columns: 5,
            colors: [
                {
                    color: '#FFF59D',
                    label: 'Yellow',
                },
                {
                    color: '#FBCFE8',
                    label: 'Pink',
                },
                {
                    color: '#BBF7D0',
                    label: 'Green',
                },
                {
                    color: '#BFDBFE',
                    label: 'Blue',
                },
                {
                    color: '#DDD6FE',
                    label: 'Purple',
                },
                {
                    color: '#FED7AA',
                    label: 'Orange',
                },
                {
                    color: '#FECACA',
                    label: 'Red',
                },
                {
                    color: '#E5E7EB',
                    label: 'Gray',
                },
            ],
        },

        highlight: {
            options: [
                {
                    model: 'yellowMarker',
                    class: 'marker-yellow',
                    title: 'Yellow marker',
                },
                {
                    model: 'greenMarker',
                    class: 'marker-green',
                    title: 'Green marker',
                },
                {
                    model: 'pinkMarker',
                    class: 'marker-pink',
                    title: 'Pink marker',
                },
                {
                    model: 'blueMarker',
                    class: 'marker-blue',
                    title: 'Blue marker',
                },
            ],
        },

        link: {
            addTargetToExternalLinks: true,
            defaultProtocol: 'https://',

            decorators: {
                openInNewTab: {
                    mode: 'manual',
                    label: 'Open in a new tab',
                    defaultValue: true,
                    attributes: {
                        target: '_blank',
                        rel: 'noopener noreferrer',
                    },
                },
            },
        },

        alignment: {
            options: [
                'left',
                'center',
                'right',
                'justify',
            ],
        },

        initialData: initialData || '',

        placeholder: 'Write your project description...',
    });

    editor.model.document.on('change:data', () => {
        onChange(editor.getData());
    });

    return editor;
}