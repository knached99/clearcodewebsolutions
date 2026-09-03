@php
    $status = strtolower((string) $ticket->status);

    $statusClass = match ($status) {
        'open' => 'status-open',
        'pending' => 'status-pending',
        'resolved' => 'status-resolved',
        'closed' => 'status-closed',
        default => 'status-default',
    };

    $customerName = (string) ($ticket->name ?: 'Customer');

    $customerInitial = strtoupper(
        substr($customerName, 0, 1)
    );
@endphp

@push('styles')
<style>
    [x-cloak] {
        display: none !important;
    }

    .ticket-page {
        min-height: 100vh;
        background:
            radial-gradient(circle at 15% 0%, rgba(124, 58, 237, .12), transparent 32rem),
            radial-gradient(circle at 90% 15%, rgba(192, 38, 211, .08), transparent 30rem),
            #07060b;
    }

    .ticket-shell {
        width: min(1180px, calc(100% - 32px));
        margin: 0 auto;
        padding: 125px 0 90px;
    }

    .ticket-back {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 18px;
        color: rgba(255,255,255,.35);
        font-size: 12px;
        text-decoration: none;
    }

    .ticket-back:hover {
        color: rgba(255,255,255,.75);
    }

    .guest-ticket {
        color: white;
    }

    .ticket-header,
    .ticket-panel {
        border: 1px solid rgba(255,255,255,.07);
        background: rgba(255,255,255,.035);
        box-shadow:
            0 25px 80px rgba(0,0,0,.25),
            inset 0 1px 0 rgba(255,255,255,.025);
        backdrop-filter: blur(20px);
    }

    .ticket-header {
        display: flex;
        justify-content: space-between;
        gap: 30px;
        padding: 32px;
        margin-bottom: 20px;
        border-radius: 24px;
    }

    .ticket-eyebrow {
        margin-bottom: 12px;
        color: #a78bfa;
        font-size: 10px;
        font-weight: 800;
        letter-spacing: .2em;
    }

    .ticket-header h1 {
        margin: 0;
        max-width: 800px;
        color: white;
        font-size: clamp(28px, 4vw, 42px);
        line-height: 1.1;
        letter-spacing: -.035em;
    }

    .ticket-meta {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 10px;
        margin-top: 15px;
        color: rgba(255,255,255,.3);
        font-size: 11px;
    }

    .ticket-number {
        padding: 6px 9px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 8px;
        background: rgba(255,255,255,.04);
        color: rgba(255,255,255,.6);
        font-family: monospace;
    }

    .ticket-header-side {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 12px;
        flex-shrink: 0;
    }

    .ticket-status {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 7px 11px;
        border: 1px solid;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 700;
    }

    .ticket-status > span {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: currentColor;
    }

    .status-open {
        color: #6ee7b7;
        background: rgba(16,185,129,.08);
        border-color: rgba(52,211,153,.2);
    }

    .status-pending {
        color: #fcd34d;
        background: rgba(245,158,11,.08);
        border-color: rgba(251,191,36,.2);
    }

    .status-resolved {
        color: #93c5fd;
        background: rgba(59,130,246,.08);
        border-color: rgba(96,165,250,.2);
    }

    .status-closed,
    .status-default {
        color: rgba(255,255,255,.45);
        background: rgba(255,255,255,.04);
        border-color: rgba(255,255,255,.1);
    }

    .ticket-edit-button,
    .button-primary,
    .button-secondary {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        min-height: 40px;
        padding: 0 15px;
        border-radius: 10px;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: .2s ease;
    }

    .ticket-edit-button,
    .button-secondary {
        border: 1px solid rgba(255,255,255,.08);
        background: rgba(255,255,255,.04);
        color: rgba(255,255,255,.6);
    }

    .ticket-edit-button:hover,
    .button-secondary:hover {
        background: rgba(255,255,255,.08);
        color: white;
    }

    .button-primary {
        border: 0;
        background: linear-gradient(135deg, #7c3aed, #c026d3);
        color: white;
        box-shadow: 0 10px 30px rgba(124,58,237,.2);
    }

    .button-primary:hover {
        transform: translateY(-1px);
    }

    .button-primary:disabled,
    .button-secondary:disabled,
    .ticket-edit-button:disabled {
        opacity: .5;
        cursor: not-allowed;
        transform: none;
    }

    .ticket-layout {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 300px;
        gap: 20px;
        align-items: start;
    }

    .ticket-panel {
        border-radius: 22px;
        overflow: hidden;
    }

    .panel-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 20px 22px;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }

    .panel-heading h2 {
        margin: 0;
        color: rgba(255,255,255,.85);
        font-size: 14px;
    }

    .panel-heading p {
        margin: 4px 0 0;
        color: rgba(255,255,255,.25);
        font-size: 10px;
    }

    .last-activity {
        color: rgba(255,255,255,.2);
        font-size: 10px;
    }

    .messages {
        padding: 22px;
    }

    .message {
        display: flex;
        gap: 12px;
        margin-bottom: 22px;
    }

    .message:last-child {
        margin-bottom: 0;
    }

    .message-avatar,
    .customer-avatar {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        border: 1px solid rgba(255,255,255,.08);
        background: linear-gradient(
            135deg,
            rgba(139,92,246,.2),
            rgba(217,70,239,.12)
        );
        color: #ddd6fe;
        font-weight: 800;
    }

    .message-avatar {
        width: 36px;
        height: 36px;
        border-radius: 11px;
        font-size: 11px;
    }

    .message-body {
        min-width: 0;
        flex: 1;
    }

    .message-top {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        margin-bottom: 7px;
    }

    .message-top strong {
        color: rgba(255,255,255,.72);
        font-size: 12px;
    }

    .message-top time {
        color: rgba(255,255,255,.2);
        font-size: 10px;
    }

    .message-bubble {
        padding: 14px 16px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 5px 17px 17px 17px;
        background: rgba(255,255,255,.035);
        color: rgba(255,255,255,.65);
        font-size: 13px;
        line-height: 1.75;
        overflow-wrap: anywhere;
    }

    /* Saved message attachments */

    .message-attachments {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-top: 12px;
    }

    .message-attachment {
        display: block;
        min-width: 0;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 12px;
        background: rgba(0,0,0,.18);
        text-decoration: none;
        transition: .2s ease;
    }

    .message-attachment:hover {
        border-color: rgba(168,85,247,.45);
        background: rgba(255,255,255,.04);
        transform: translateY(-1px);
    }

    .message-attachment-preview {
        display: block;
        width: 100%;
        border: 0;
        padding: 0;
        background: transparent;
        cursor: zoom-in;
        text-align: left;
    }

    .message-attachment-image {
        display: block;
        width: 100%;
        height: 180px;
        object-fit: cover;
        transition: transform .2s ease;
    }

    .message-attachment-preview:hover .message-attachment-image {
        transform: scale(1.02);
    }

    .message-attachment-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        padding: 8px 10px;
    }

    .message-attachment-name {
        display: block;
        min-width: 0;
        overflow: hidden;
        color: rgba(255,255,255,.5);
        font-size: 10px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .message-attachment-download {
        flex-shrink: 0;
        color: #a78bfa;
        font-size: 10px;
        font-weight: 700;
        text-decoration: none;
        transition: .2s ease;
    }

    .message-attachment-download:hover {
        color: #c4b5fd;
        text-decoration: underline;
    }

    .empty-conversation {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        min-height: 240px;
        gap: 8px;
        color: rgba(255,255,255,.2);
        text-align: center;
    }

    .empty-conversation strong {
        color: rgba(255,255,255,.45);
        font-size: 13px;
    }

    .empty-conversation span {
        font-size: 11px;
    }

    .reply {
        margin: 0 18px 18px;
        padding: 18px;
        border: 1px solid rgba(255,255,255,.07);
        border-radius: 18px;
        background: rgba(255,255,255,.025);
    }

    .reply-heading {
        margin-bottom: 12px;
    }

    .reply-heading strong {
        display: block;
        color: rgba(255,255,255,.75);
        font-size: 12px;
    }

    .reply-heading span {
        display: block;
        margin-top: 3px;
        color: rgba(255,255,255,.23);
        font-size: 10px;
    }

    .reply-textarea,
    .field input,
    .field select {
        width: 100%;
        border: 1px solid rgba(255,255,255,.08);
        outline: none;
        background: rgba(0,0,0,.2);
        color: rgba(255,255,255,.8);
        transition: .2s ease;
    }

    .reply-textarea {
        min-height: 150px;
        padding: 14px;
        border-radius: 13px;
        resize: vertical;
        font: inherit;
        font-size: 13px;
        line-height: 1.7;
    }

    .reply-textarea::placeholder {
        color: rgba(255,255,255,.2);
    }

    .reply-textarea:focus,
    .field input:focus,
    .field select:focus {
        border-color: rgba(139,92,246,.5);
        box-shadow: 0 0 0 3px rgba(139,92,246,.08);
    }

    /* Upload area */

    .upload-area {
        width: 100%;
        margin-top: 18px;
    }

    .upload-heading {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        margin-bottom: 12px;
    }

    .upload-heading > div {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .upload-heading strong {
        color: #fff;
        font-size: 14px;
        font-weight: 600;
    }

    .upload-heading span {
        color: rgba(255,255,255,.45);
        font-size: 12px;
    }

    .upload-count {
        flex-shrink: 0;
    }

    .upload-box {
        position: relative;
        min-height: 150px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 7px;
        padding: 25px;
        border: 1px dashed rgba(168,85,247,.45);
        border-radius: 16px;
        background: rgba(255,255,255,.025);
        color: rgba(255,255,255,.55);
        cursor: pointer;
        transition: .2s ease;
    }

    .upload-box:hover {
        border-color: rgba(217,70,239,.8);
        background: rgba(168,85,247,.06);
    }

    .upload-box strong {
        color: #fff;
        font-size: 14px;
    }

    .upload-box span {
        color: rgba(255,255,255,.45);
        font-size: 12px;
    }

    .upload-box input[type="file"] {
        position: absolute;
        width: 1px;
        height: 1px;
        padding: 0;
        margin: -1px;
        overflow: hidden;
        clip: rect(0,0,0,0);
        white-space: nowrap;
        border: 0;
    }

    .attachments-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-top: 15px;
    }

    .attachment {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        min-height: 80px;
        padding: 10px;
        box-sizing: border-box;
        border: 1px solid rgba(255,255,255,.1);
        border-radius: 14px;
        background: rgba(255,255,255,.04);
    }

    .attachment-preview {
        display: block !important;
        width: 64px !important;
        height: 64px !important;
        min-width: 64px !important;
        min-height: 64px !important;
        overflow: hidden !important;
        border-radius: 10px;
        background: #222;
    }

    .attachment-preview img {
        display: block !important;
        width: 64px !important;
        height: 64px !important;
        max-width: none !important;
        max-height: none !important;
        object-fit: cover !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .attachment-info {
        min-width: 0;
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .attachment-info strong {
        overflow: hidden;
        color: #fff;
        font-size: 13px;
        font-weight: 600;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .attachment-info span {
        color: rgba(255,255,255,.4);
        font-size: 11px;
    }

    .attachment-remove {
        position: relative !important;
        z-index: 20 !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        width: 34px;
        height: 34px;
        flex: 0 0 34px;
        padding: 0;
        border: 1px solid rgba(248,113,113,.25);
        border-radius: 9px;
        background: rgba(248,113,113,.08);
        color: #fca5a5;
        cursor: pointer;
        pointer-events: auto !important;
        transition: .2s ease;
    }

    .attachment-remove:hover {
        background: rgba(248,113,113,.16);
        border-color: rgba(248,113,113,.5);
    }

    .upload-loading {
        display: flex;
        align-items: center;
        gap: 9px;
        margin-top: 10px;
        color: rgba(255,255,255,.55);
        font-size: 12px;
    }

    .upload-spinner {
        width: 15px;
        height: 15px;
        border: 2px solid rgba(255,255,255,.15);
        border-top-color: #c084fc;
        border-radius: 50%;
        animation: ticket-upload-spin .7s linear infinite;
    }

    @keyframes ticket-upload-spin {
        to {
            transform: rotate(360deg);
        }
    }

    .field-error {
        margin-top: 7px;
        color: #fca5a5;
        font-size: 12px;
    }

    .reply-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        margin-top: 15px;
    }

    .reply-footer > span {
        color: rgba(255,255,255,.22);
        font-size: 10px;
    }

    .closed-ticket {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-wrap: wrap;
        gap: 8px;
        margin: 0 18px 18px;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.06);
        border-radius: 16px;
        background: rgba(255,255,255,.02);
        color: rgba(255,255,255,.25);
        text-align: center;
    }

    .closed-ticket strong {
        color: rgba(255,255,255,.55);
        font-size: 12px;
    }

    .closed-ticket span {
        width: 100%;
        color: rgba(255,255,255,.25);
        font-size: 10px;
    }

    /* Sidebar */

    .ticket-sidebar {
        width: 100%;
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    .ticket-sidebar-card {
        width: 100%;
        min-width: 0;
        box-sizing: border-box;
        padding: 20px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 20px;
        background: rgba(15,15,20,.72);
        box-shadow: 0 15px 45px rgba(0,0,0,.18);
    }

    .ticket-sidebar-heading {
        margin-bottom: 16px;
        color: rgba(255,255,255,.55);
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .12em;
        text-transform: uppercase;
    }

    .ticket-customer {
        display: flex;
        align-items: center;
        gap: 12px;
        min-width: 0;
    }

    .ticket-customer-avatar {
        width: 44px;
        height: 44px;
        min-width: 44px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 13px;
        background: linear-gradient(135deg, #7c3aed, #c026d3);
        color: white;
        font-size: 16px;
        font-weight: 700;
    }

    .ticket-customer-info {
        min-width: 0;
        display: flex;
        flex-direction: column;
        gap: 4px;
    }

    .ticket-customer-info strong {
        overflow: hidden;
        color: white;
        font-size: 14px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticket-customer-info span {
        overflow: hidden;
        color: rgba(255,255,255,.42);
        font-size: 12px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticket-detail-list {
        display: flex;
        flex-direction: column;
    }

    .ticket-detail-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 15px;
        padding: 12px 0;
        border-bottom: 1px solid rgba(255,255,255,.06);
    }

    .ticket-detail-row:first-child {
        padding-top: 0;
    }

    .ticket-detail-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .ticket-detail-row span {
        color: rgba(255,255,255,.42);
        font-size: 12px;
    }

    .ticket-detail-row strong {
        min-width: 0;
        overflow: hidden;
        color: rgba(255,255,255,.85);
        font-size: 12px;
        font-weight: 600;
        text-align: right;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .ticket-help-card {
        position: relative;
        overflow: hidden;
    }

    .ticket-help-icon {
        width: 38px;
        height: 38px;
        margin-bottom: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 11px;
        background: rgba(168,85,247,.12);
        color: #d8b4fe;
    }

    .ticket-help-icon svg {
        width: 20px !important;
        height: 20px !important;
        min-width: 20px !important;
    }

    .ticket-help-card > strong {
        display: block;
        color: white;
        font-size: 14px;
    }

    .ticket-help-card p {
        margin: 7px 0 0;
        color: rgba(255,255,255,.42);
        font-size: 12px;
        line-height: 1.65;
    }

    /* Edit section */

    .edit-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
        padding: 20px;
    }

    .field {
        min-width: 0;
    }

    .field-full {
        grid-column: 1 / -1;
    }

    .field label {
        display: block;
        margin-bottom: 7px;
        color: rgba(255,255,255,.5);
        font-size: 11px;
        font-weight: 600;
    }

    .field input,
    .field select {
        min-height: 42px;
        padding: 0 12px;
        border-radius: 10px;
        box-sizing: border-box;
        font-size: 12px;
    }

    .edit-actions {
        display: flex;
        justify-content: flex-end;
        gap: 8px;
        padding: 15px 20px;
        border-top: 1px solid rgba(255,255,255,.05);
    }

    /* Flash message */

    .ticket-success {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
        padding: 12px 15px;
        border: 1px solid rgba(52,211,153,.18);
        border-radius: 12px;
        background: rgba(16,185,129,.07);
        color: #a7f3d0;
        font-size: 12px;
    }

    /* Lightbox */

    .ticket-lightbox {
        position: fixed;
        inset: 0;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: rgba(0,0,0,.92);
        backdrop-filter: blur(8px);
    }

    .ticket-lightbox-image {
        display: block;
        max-width: min(95vw, 1400px);
        max-height: 90vh;
        width: auto;
        height: auto;
        object-fit: contain;
        border-radius: 12px;
        box-shadow: 0 30px 100px rgba(0,0,0,.55);
    }

    .ticket-lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 42px;
        height: 42px;
        padding: 0;
        border: 1px solid rgba(255,255,255,.12);
        border-radius: 999px;
        background: rgba(0,0,0,.55);
        color: white;
        font-size: 25px;
        line-height: 1;
        cursor: pointer;
        transition: .2s ease;
    }

    .ticket-lightbox-close:hover {
        background: rgba(255,255,255,.1);
        border-color: rgba(255,255,255,.25);
    }

    .ticket-lightbox-name {
        position: absolute;
        left: 50%;
        bottom: 20px;
        max-width: calc(100% - 40px);
        transform: translateX(-50%);
        overflow: hidden;
        padding: 8px 12px;
        border: 1px solid rgba(255,255,255,.08);
        border-radius: 8px;
        background: rgba(0,0,0,.6);
        color: rgba(255,255,255,.65);
        font-size: 11px;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Responsive */

    @media (max-width: 900px) {
        .ticket-layout {
            grid-template-columns: 1fr;
        }

        .ticket-sidebar {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }

        .ticket-help-card {
            grid-column: 1 / -1;
        }
    }

    @media (max-width: 640px) {
        .ticket-shell {
            width: calc(100% - 20px);
            padding-top: 105px;
            padding-bottom: 60px;
        }

        .ticket-header {
            flex-direction: column;
            padding: 22px;
            border-radius: 18px;
        }

        .ticket-header-side {
            align-items: flex-start;
        }

        .ticket-layout {
            gap: 12px;
        }

        .messages {
            padding: 17px;
        }

        .reply {
            margin: 0 12px 12px;
            padding: 13px;
        }

        .reply-footer {
            flex-direction: column;
            align-items: stretch;
        }

        .reply-footer .button-primary {
            width: 100%;
        }

        .ticket-sidebar {
            display: flex;
        }

        .edit-grid {
            grid-template-columns: 1fr;
        }

        .field-full {
            grid-column: auto;
        }

        .edit-actions {
            flex-direction: column-reverse;
        }

        .edit-actions button {
            width: 100%;
        }

        .upload-heading {
            flex-direction: column;
            align-items: flex-start;
        }

        .message-attachments {
            grid-template-columns: 1fr;
        }

        .message-attachment-image {
            height: 200px;
        }
    }
</style>
@endpush

<div
    class="guest-ticket"
    x-data="{
        lightbox: false,
        lightboxSrc: '',
        lightboxName: '',

        openLightbox(src, name = '') {
            this.lightboxSrc = src;
            this.lightboxName = name;
            this.lightbox = true;
            document.body.classList.add('overflow-hidden');
        },

        closeLightbox() {
            this.lightbox = false;
            this.lightboxSrc = '';
            this.lightboxName = '';
            document.body.classList.remove('overflow-hidden');
        }
    }"
    x-on:keydown.escape.window="closeLightbox()"
>
    {{-- Header --}}
    <header class="ticket-header">
        <div class="ticket-header-main">
            <div class="ticket-eyebrow">
                CUSTOMER SUPPORT
            </div>

            <h1>
                {{ $ticket->subject }}
            </h1>

            <div class="ticket-meta">
                <span class="ticket-number">
                    {{ $ticket->ticket_number }}
                </span>

                <span>
                    Opened {{ $ticket->created_at?->diffForHumans() }}
                </span>
            </div>
        </div>

        <div class="ticket-header-side">
            <div class="ticket-status {{ $statusClass }}">
                <span></span>

                {{ ucfirst($status) }}
            </div>
        </div>
    </header>

    {{-- Flash --}}
    @if (session('ticket-success'))
        <div class="ticket-success">
            <flux:icon.check-circle class="size-4" />

            {{ session('ticket-success') }}
        </div>
    @endif

    {{-- Edit ticket --}}
    @if ($showEditTicket)
        <section class="ticket-panel ticket-edit-panel">
            <div class="panel-heading">
                <div>
                    <h2>
                        Edit ticket
                    </h2>

                    <p>
                        Update the ticket information.
                    </p>
                </div>
            </div>

            <div class="edit-grid">
                <div class="field field-full">
                    <label for="ticket-subject">
                        Subject
                    </label>

                    <input
                        id="ticket-subject"
                        type="text"
                        wire:model="subject"
                    >

                    @error('subject')
                        <span class="field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="field">
                    <label for="ticket-status">
                        Status
                    </label>

                    <select
                        id="ticket-status"
                        wire:model="status"
                    >
                        <option value="open">
                            Open
                        </option>

                        <option value="pending">
                            Pending
                        </option>

                        <option value="resolved">
                            Resolved
                        </option>

                        <option value="closed">
                            Closed
                        </option>
                    </select>

                    @error('status')
                        <span class="field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div class="field">
                    <label for="ticket-priority">
                        Priority
                    </label>

                    <select
                        id="ticket-priority"
                        wire:model="priority"
                    >
                        <option value="low">
                            Low
                        </option>

                        <option value="normal">
                            Normal
                        </option>

                        <option value="high">
                            High
                        </option>

                        <option value="urgent">
                            Urgent
                        </option>
                    </select>

                    @error('priority')
                        <span class="field-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>
            </div>

            <div class="edit-actions">
                <button
                    type="button"
                    wire:click="cancelEditTicket"
                    class="button-secondary"
                >
                    Cancel
                </button>

                <button
                    type="button"
                    wire:click="editTicket"
                    wire:loading.attr="disabled"
                    class="button-primary"
                >
                    <span
                        wire:loading.remove
                        wire:target="editTicket"
                    >
                        Save changes
                    </span>

                    <span
                        wire:loading
                        wire:target="editTicket"
                    >
                        Saving...
                    </span>
                </button>
            </div>
        </section>
    @endif

    {{-- Main --}}
    <div class="ticket-layout">

        {{-- Conversation --}}
        <section class="ticket-panel">

            <div class="panel-heading">
                <div>
                    <h2>
                        Conversation
                    </h2>

                    <p>
                        {{ $ticket->messages->count() }}
                        {{ $ticket->messages->count() === 1 ? 'message' : 'messages' }}
                    </p>
                </div>

                <span class="last-activity">
                    {{ $ticket->last_activity_at?->diffForHumans() }}
                </span>
            </div>

            {{-- Messages --}}
            <div class="messages">
                @forelse ($ticket->messages as $message)

                    @php
                        $authorName = $message->author_name ?: $ticket->name;

                        $initial = strtoupper(
                            substr((string) $authorName, 0, 1)
                        );
                    @endphp

                    <article
                        class="message"
                        wire:key="message-{{ $message->id }}"
                    >
                        <div class="message-avatar">
                            {{ $initial ?: 'C' }}
                        </div>

                        <div class="message-body">

                            <div class="message-top">
                                <strong>
                                    {{ $authorName }}
                                </strong>

                                <time>
                                    {{ $message->created_at?->diffForHumans() }}
                                </time>
                            </div>

                            <div class="message-bubble">

                                {{-- Message text --}}
                                {!! nl2br(e($message->message)) !!}

                                {{-- Saved attachments --}}
                                @if ($message->attachments->isNotEmpty())

                                    <div class="message-attachments">

                                        @foreach ($message->attachments as $attachment)

                                            @php
                                                $mime = strtolower(
                                                    (string) $attachment->mime_type
                                                );

                                                $isImage = str_starts_with(
                                                    $mime,
                                                    'image/'
                                                );

                                                $isPdf = $mime === 'application/pdf';

                                                $previewUrl = route(
                                                    'guest.ticket.attachments.preview',
                                                    [
                                                        'attachment' => $attachment->id,
                                                    ]
                                                );

                                                $downloadUrl = route(
                                                    'guest.ticket.attachments.download',
                                                    [
                                                        'attachment' => $attachment->id,
                                                    ]
                                                );
                                            @endphp

                                            <div
                                                class="message-attachment"
                                                wire:key="message-attachment-{{ $attachment->id }}"
                                            >

                                               @if ($isImage)
                                        <button
                                            type="button"
                                            x-on:click.stop="openLightbox(
                                                @js($previewUrl),
                                                @js($attachment->original_name)
                                            )"
                                            class="block w-full cursor-zoom-in text-left"
                                        >
                                            <img
                                                src="{{ $previewUrl }}"
                                                alt="{{ $attachment->original_name }}"
                                                class="message-attachment-image"
                                                loading="lazy"
                                            >
                                        </button>
                                    @elseif ($isPdf)
                                        <a
                                            href="{{ $previewUrl }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="flex min-h-[180px] items-center justify-center"
                                        >
                                            <div class="text-center">
                                                <flux:icon.document-text class="mx-auto size-10" />

                                                <div class="mt-2 text-xs text-white/60">
                                                    Preview PDF
                                                </div>
                                            </div>
                                        </a>
                                    @else
                                        <a
                                            href="{{ $downloadUrl }}"
                                            class="flex min-h-[180px] items-center justify-center"
                                        >
                                            <div class="text-center">
                                                <flux:icon.document class="mx-auto size-10" />

                                                <div class="mt-2 text-xs text-white/60">
                                                    Download file
                                                </div>
                                            </div>
                                        </a>
                                    @endif

                                                <div class="message-attachment-footer">

                                                    <span
                                                        class="message-attachment-name"
                                                        title="{{ $attachment->original_name }}"
                                                    >
                                                        {{ $attachment->original_name }}
                                                    </span>

                                                    <a
                                                        href="{{ $downloadUrl }}"
                                                        class="message-attachment-download"
                                                    >
                                                        Download
                                                    </a>

                                                </div>

                                            </div>

                                        @endforeach

                                    </div>

                                @endif

                            </div>

                        </div>
                    </article>

                @empty

                    <div class="empty-conversation">
                        <flux:icon.chat-bubble-left-right class="size-7" />

                        <strong>
                            No messages yet
                        </strong>

                        <span>
                            Send a reply below to start the conversation.
                        </span>
                    </div>

                @endforelse
            </div>

            {{-- Reply --}}
            @if ($ticket->status !== 'closed')

                <div class="reply">

                    <div class="reply-heading">
                        <div>
                            <strong>
                                Reply to support
                            </strong>

                            <span>
                                Send us a message and we'll get back to you.
                            </span>
                        </div>
                    </div>

                    {{-- Message --}}
                    <textarea
                        wire:model="message"
                        rows="6"
                        placeholder="Write your message..."
                        class="reply-textarea @error('message') border-red-500 @enderror"
                    ></textarea>

                    @error('message')
                        <div class="field-error">
                            {{ $message }}
                        </div>
                    @enderror

                    {{-- Attachments --}}
                    <div class="upload-area">

                        <div class="upload-heading">
                            <div>
                                <strong>
                                    Attach images
                                </strong>

                                <span>
                                    JPG, PNG, WEBP, GIF · Max 10 MB each
                                </span>
                            </div>

                            <span class="upload-count">
                                {{ count($attachments) }} / 10
                            </span>
                        </div>

                        <label
                            for="ticket-attachments"
                            class="upload-box"
                        >
                            <flux:icon.paper-clip />

                            <strong>
                                Choose images
                            </strong>

                            <span>
                                Click to browse or drag images here
                            </span>

                            <input
                                id="ticket-attachments"
                                type="file"
                                wire:model="attachments"
                                multiple
                                accept=".jpg,.jpeg,.png,.webp,.gif,image/jpeg,image/png,image/webp,image/gif"
                            >
                        </label>

                        {{-- Upload loading --}}
                        <div
                            wire:loading
                            wire:target="attachments"
                            class="upload-loading"
                        >
                            <div class="upload-spinner"></div>

                            <span>
                                Uploading images...
                            </span>
                        </div>

                        @error('attachments')
                            <div class="field-error">
                                {{ $message }}
                            </div>
                        @enderror

                        {{-- Temporary attachment previews --}}
                        @if (count($attachments) > 0)

                            <div class="attachments-list">

                                @foreach ($attachments as $index => $attachment)

                                    <div
                                        class="attachment"
                                        wire:key="uploaded-attachment-{{ $index }}"
                                    >

                                        <div class="attachment-preview">
                                            <img
                                                src="{{ $attachment->temporaryUrl() }}"
                                                alt="{{ $attachment->getClientOriginalName() }}"
                                            >
                                        </div>

                                        <div class="attachment-info">
                                            <strong>
                                                {{ $attachment->getClientOriginalName() }}
                                            </strong>

                                            <span>
                                                {{ number_format($attachment->getSize() / 1024 / 1024, 2) }}
                                                MB
                                            </span>
                                        </div>

                                        <button
                                            type="button"
                                            class="attachment-remove"
                                            wire:click="removeAttachment({{ $index }})"
                                            aria-label="Remove attachment"
                                        >
                                            <flux:icon.x-mark />
                                        </button>

                                    </div>

                                    @error("attachments.$index")
                                        <div class="field-error">
                                            {{ $message }}
                                        </div>
                                    @enderror

                                @endforeach

                            </div>

                        @endif

                    </div>

                    {{-- Reply footer --}}
                    <div class="reply-footer">

                        <span>
                            Your reply will be added to this ticket.
                        </span>

                        <button
                            type="button"
                            wire:click="sendReply"
                            wire:loading.attr="disabled"
                            wire:target="sendReply"
                            class="button-primary"
                        >

                            <span
                                wire:loading.remove
                                wire:target="sendReply"
                            >
                                <flux:icon.paper-airplane class="size-4" />

                                Send reply
                            </span>

                            <span
                                wire:loading
                                wire:target="sendReply"
                            >
                                Sending...
                            </span>

                        </button>

                    </div>

                </div>

            @else

                <div class="closed-ticket">

                    <flux:icon.lock-closed class="size-5" />

                    <strong>
                        This ticket is closed
                    </strong>

                    <span>
                        Replies are no longer available.
                    </span>

                </div>

            @endif

        </section>

        {{-- Sidebar --}}
        <aside class="ticket-sidebar">

            {{-- Customer --}}
            <section class="ticket-sidebar-card">

                <div class="ticket-sidebar-heading">
                    Customer
                </div>

                <div class="ticket-customer">

                    <div class="ticket-customer-avatar">
                        {{ $customerInitial ?: 'C' }}
                    </div>

                    <div class="ticket-customer-info">

                        <strong>
                            {{ $ticket->name }}
                        </strong>

                        <span>
                            {{ $ticket->email }}
                        </span>

                    </div>

                </div>

            </section>

            {{-- Ticket details --}}
            <section class="ticket-sidebar-card">

                <div class="ticket-sidebar-heading">
                    Ticket details
                </div>

                <div class="ticket-detail-list">

                    <div class="ticket-detail-row">
                        <span>
                            Ticket
                        </span>

                        <strong>
                            {{ $ticket->ticket_number }}
                        </strong>
                    </div>

                    <div class="ticket-detail-row">
                        <span>
                            Status
                        </span>

                        <strong>
                            {{ ucfirst($ticket->status) }}
                        </strong>
                    </div>

                    <div class="ticket-detail-row">
                        <span>
                            Priority
                        </span>

                        <strong>
                            {{ ucfirst($ticket->priority) }}
                        </strong>
                    </div>

                    <div class="ticket-detail-row">
                        <span>
                            Created
                        </span>

                        <strong>
                            {{ $ticket->created_at?->format('M j, Y') }}
                        </strong>
                    </div>

                    <div class="ticket-detail-row">
                        <span>
                            Last activity
                        </span>

                        <strong>
                            {{ $ticket->last_activity_at?->diffForHumans() ?? '—' }}
                        </strong>
                    </div>

                </div>

            </section>

            {{-- Help --}}
            <section class="ticket-sidebar-card ticket-help-card">

                <div class="ticket-help-icon">
                    <flux:icon.sparkles />
                </div>

                <strong>
                    Need help?
                </strong>

                <p>
                    Reply to this ticket and our team will get back to you
                    as soon as possible.
                </p>

            </section>

        </aside>

    </div>

    {{-- Image lightbox --}}
    <template x-teleport="body">

       <div
    x-show="lightbox"
    x-cloak
    x-transition.opacity
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/90 p-4"
    x-on:click.self="closeLightbox()"
>
    <button
        type="button"
        x-on:click="closeLightbox()"
        class="absolute right-5 top-5 flex size-10 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20"
        aria-label="Close preview"
    >
        <flux:icon.x-mark class="size-5" />
    </button>

    <div
        class="flex max-h-[95vh] max-w-[95vw] flex-col items-center gap-3"
        x-on:click.stop
    >
        <img
            :src="lightboxSrc"
            :alt="lightboxName"
            class="max-h-[88vh] max-w-[95vw] rounded-xl object-contain shadow-2xl"
        >

        <div
            x-text="lightboxName"
            class="max-w-full truncate text-xs text-white/60"
        ></div>
    </div>
</div>

    </template>

</div>