<div
    class="guest-ticket-page"

    x-data="{
        lightbox: false,
        lightboxSrc: '',
        lightboxName: '',

        showToTop: false,

        highlightedMessage: @js($highlightMessageId),

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
        },

        scrollToTop() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        },

        scrollToMessage(id) {
            const element = document.getElementById(
                'ticket-message-' + id
            );

            if (!element) {
                return;
            }

            setTimeout(() => {
                element.scrollIntoView({
                    behavior: 'smooth',
                    block: 'center'
                });

                element.classList.remove(
                    'message-highlight-active'
                );

                void element.offsetWidth;

                element.classList.add(
                    'message-highlight-active'
                );

                setTimeout(() => {
                    element.classList.remove(
                        'message-highlight-active'
                    );
                }, 8000);
            }, 450);
        },

        copyMessageLink(id) {
            const url =
                window.location.origin +
                window.location.pathname +
                '?message=' +
                id;

            if (
                navigator.clipboard &&
                window.isSecureContext
            ) {
                navigator.clipboard.writeText(url);
                return;
            }

            const textarea =
                document.createElement('textarea');

            textarea.value = url;

            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';

            document.body.appendChild(textarea);

            textarea.select();

            try {
                document.execCommand('copy');
            } catch (error) {
                console.error(
                    'Unable to copy message link.',
                    error
                );
            }

            textarea.remove();
        }
    }"

   x-init="
    const handleScroll = () => {
        showToTop = window.scrollY > 400;
    };

    handleScroll();

    window.addEventListener(
        'scroll',
        handleScroll,
        { passive: true }
    );

    const scrollToBottom = () => {
        window.scrollTo({
            top: document.documentElement.scrollHeight,
            behavior: 'smooth'
        });
    };

    if (highlightedMessage) {

        scrollToMessage(highlightedMessage);

    } else {

        setTimeout(() => {
            scrollToBottom();

            setTimeout(() => {
                scrollToBottom();
            }, 500);

        }, 450);
    }
"

    x-on:keydown.escape.window="closeLightbox()"
>

    @php
        /*
        |--------------------------------------------------------------------------
        | MESSAGE DATA
        |--------------------------------------------------------------------------
        */

        $messagesCollection = collect(
            $ticket->messages ?? []
        )
            ->sortBy('created_at')
            ->values();

        $messageCount =
            $messagesCollection->count();

        $statusValue = strtolower(
            (string) ($ticket->status ?? 'open')
        );

        $priorityValue = strtolower(
            (string) ($ticket->priority ?? 'normal')
        );

        $customerName = (string) (
            $ticket->name ?? 'Customer'
        );

        $customerInitial = strtoupper(
            substr(
                trim($customerName),
                0,
                1
            )
        );

        $isClosed =
            $statusValue === 'closed';
    @endphp


    {{-- =========================================================
       DECORATIVE BACKGROUND
    ========================================================== --}}

    <div
        class="ticket-background"
        aria-hidden="true"
    >
        <div class="purple-orb orb-one"></div>
        <div class="purple-orb orb-two"></div>
        <div class="purple-orb orb-three"></div>

        <div class="ticket-grid"></div>
    </div>


    {{-- =========================================================
       MAIN CONTAINER
    ========================================================== --}}

    <div class="ticket-container">


        {{-- =====================================================
           BACK TO WEBSITE
        ====================================================== --}}

        <div class="ticket-navigation">

            <a
                href="{{ route('home') }}"
                class="ticket-back"
            >
                <span class="ticket-back-icon">
                    <i class="fa-solid fa-arrow-left"></i>
                </span>

                <span>
                    Back to website
                </span>
            </a>

        </div>


        {{-- =====================================================
           HERO
        ====================================================== --}}

        <section class="ticket-hero">

            <div class="hero-glow"></div>

            <div class="hero-top">

                <div class="ticket-brand">

                    <div class="ticket-brand-icon">
                        <i class="fa-solid fa-headset"></i>
                    </div>

                    <div>

                        <div class="ticket-brand-label">
                            CUSTOMER SUPPORT
                        </div>

                        <div class="ticket-number">
                            {{ $ticket->ticket_number }}
                        </div>

                    </div>

                </div>


                <div class="hero-statuses">

                    <span
                        class="
                            status-pill
                            status-{{ $statusValue }}
                        "
                    >

                        <span class="status-dot"></span>

                        {{ ucfirst($statusValue ?: 'open') }}

                    </span>


                    @if ($priorityValue)

                        <span
                            class="
                                priority-pill
                                priority-{{ $priorityValue }}
                            "
                        >

                            <i class="fa-solid fa-bolt"></i>

                            {{ ucfirst($priorityValue) }}

                        </span>

                    @endif

                </div>

            </div>


            <div class="hero-content">

                <div class="hero-title-wrap">

                    <span class="hero-eyebrow">
                        SUPPORT TICKET
                    </span>

                    <h1 class="hero-title">
                        {{ $ticket->subject }}
                    </h1>

                    <p class="hero-description">
                        Continue the conversation with our
                        support team below.
                    </p>

                </div>


                <div class="hero-meta">

                    <div class="hero-meta-item">

                        <div class="hero-meta-icon">
                            <i class="fa-regular fa-comments"></i>
                        </div>

                        <div>

                            <span>
                                Messages
                            </span>

                            <strong>
                                {{ $messageCount }}
                            </strong>

                        </div>

                    </div>


                    <div class="hero-meta-item">

                        <div class="hero-meta-icon">
                            <i class="fa-regular fa-calendar"></i>
                        </div>

                        <div>

                            <span>
                                Created
                            </span>

                            <strong>
                                {{ $ticket->created_at?->format('M d, Y') }}
                            </strong>

                        </div>

                    </div>

                </div>

            </div>

        </section>


        {{-- =====================================================
           CONTENT LAYOUT
        ====================================================== --}}

        <div class="ticket-layout">


            {{-- =================================================
               CONVERSATION
            ================================================== --}}

            <main class="conversation-card">


                {{-- =================================================
                   CONVERSATION HEADER
                ================================================== --}}

                <div class="conversation-header">

                    <div>

                        <div class="section-kicker">

                            <span></span>

                            LIVE CONVERSATION

                        </div>

                        <h2>
                            Messages
                        </h2>

                    </div>


                    <div class="conversation-count">

                        {{ $messageCount }}

                        {{ $messageCount === 1
                            ? 'message'
                            : 'messages'
                        }}

                    </div>

                </div>


                {{-- =================================================
                   MESSAGES
                ================================================== --}}

                <div class="messages-container">

                    @forelse (
                        $messagesCollection
                        as $message
                    )

                        @php

                            $authorName = (string) (
                                $message->author_name
                                ?: 'Support'
                            );

                            $authorType = strtolower(
                                (string)
                                $message->author_type
                            );

                            /*
                            |--------------------------------------------------------------------------
                            | IMPORTANT
                            |--------------------------------------------------------------------------
                            | guest = customer / sender
                            | anything else = support / recipient
                            |
                            | Therefore:
                            |
                            | SUPPORT  -> LEFT
                            | CUSTOMER -> RIGHT
                            |--------------------------------------------------------------------------
                            */

                            $isCustomer =
                                $authorType === 'guest';

                            $initial = strtoupper(
                                substr(
                                    trim($authorName),
                                    0,
                                    1
                                )
                            );

                            $messageBody = (string) (
                                $message->message ?? ''
                            );

                            $messageDate =
                                $message->created_at
                                    ? $message->created_at->format(
                                        'M d, Y · h:i A'
                                    )
                                    : '';

                            $attachments =
                                $message->attachments
                                ?? collect();

                            $isHighlighted =
                                $highlightMessageId
                                &&
                                (int)
                                $highlightMessageId
                                ===
                                (int)
                                $message->id;

                        @endphp


                        {{-- =================================================
                           MESSAGE
                        ================================================== --}}

                        <article
                            id="ticket-message-{{ $message->id }}"
                            class="
                                ticket-message
                                {{ $isCustomer
                                    ? 'customer-message'
                                    : 'support-message'
                                }}
                                {{ $isHighlighted
                                    ? 'message-highlight'
                                    : ''
                                }}
                            "
                            data-message-id="{{ $message->id }}"
                        >


                            {{-- =================================================
                               NEW REPLY INDICATOR
                            ================================================== --}}

                            @if ($isHighlighted)

                                <div
                                    class="new-reply-indicator"
                                    x-show="
                                        highlightedMessage ==
                                        {{ $message->id }}
                                    "
                                >

                                    <i class="fa-solid fa-sparkles"></i>

                                    New reply

                                    <button
                                        type="button"
                                        @click="
                                            highlightedMessage = null;

                                            const message =
                                                $el.closest(
                                                    '.ticket-message'
                                                );

                                            message?.classList.remove(
                                                'message-highlight'
                                            );

                                            message?.classList.remove(
                                                'message-highlight-active'
                                            );
                                        "
                                        aria-label="Dismiss highlight"
                                    >

                                        <i class="fa-solid fa-xmark"></i>

                                    </button>

                                </div>

                            @endif


                            {{-- =================================================
                               MESSAGE ROW
                            ================================================== --}}

                            <div class="message-row">


                                {{-- =================================================
                                   AVATAR
                                ================================================== --}}

                                <div class="message-avatar">

                                    @if ($isCustomer)

                                        {{ $initial ?: 'C' }}

                                    @else

                                        <i class="fa-solid fa-headset"></i>

                                    @endif

                                </div>


                                {{-- =================================================
                                   MESSAGE MAIN
                                ================================================== --}}

                                <div class="message-main">


                                    {{-- =================================================
                                       AUTHOR
                                    ================================================== --}}

                                    <div class="message-top">

                                        <div class="message-author">

                                            <span class="author-name">
                                                {{ $authorName }}
                                            </span>

                                            <span class="author-role">
                                                {{ $isCustomer
                                                    ? 'You'
                                                    : 'Support'
                                                }}
                                            </span>

                                        </div>

                                    </div>


                                    {{-- =================================================
                                       BUBBLE
                                    ================================================== --}}

                                    <div class="message-bubble">

                                        @if (
                                            trim($messageBody) !== ''
                                        )

                                            <div class="message-content">
                                                {!! nl2br(e($messageBody)) !!}
                                            </div>

                                        @endif


                                        {{-- =================================================
                                           ATTACHMENTS
                                        ================================================== --}}

                                        @if (
                                            $attachments->isNotEmpty()
                                        )

                                            <div class="attachments-grid">

                                                @foreach (
                                                    $attachments
                                                    as $attachment
                                                )

                                                    @php

                                                        $attachmentPath =
                                                            (string) (
                                                                $attachment->path
                                                                ?? ''
                                                            );

                                                        $attachmentName =
                                                            (string) (
                                                                $attachment->original_name
                                                                ?? 'Attachment'
                                                            );

                                                        $attachmentUrl =
                                                            $attachmentPath
                                                                ? asset(
                                                                    'storage/' .
                                                                    ltrim(
                                                                        $attachmentPath,
                                                                        '/'
                                                                    )
                                                                )
                                                                : '';

                                                    @endphp


                                                    @if ($attachmentUrl)

                                                        <button
                                                            type="button"
                                                            class="attachment-card"
                                                            @click="
                                                                openLightbox(
                                                                    @js($attachmentUrl),
                                                                    @js($attachmentName)
                                                                )
                                                            "
                                                        >

                                                            <img
                                                                src="{{ $attachmentUrl }}"
                                                                alt="{{ $attachmentName }}"
                                                                loading="lazy"
                                                            >

                                                            <span class="attachment-hover">

                                                                <i class="fa-solid fa-expand"></i>

                                                            </span>

                                                        </button>

                                                    @endif

                                                @endforeach

                                            </div>

                                        @endif

                                    </div>


                                    {{-- =================================================
                                       MESSAGE FOOTER
                                    ================================================== --}}

                                    <div class="message-footer">

                                        @if ($messageDate)

                                            <time
                                                class="message-date"
                                                datetime="{{ $message->created_at?->toISOString() }}"
                                            >
                                                {{ $messageDate }}
                                            </time>

                                        @endif


                                        <button
                                            type="button"
                                            class="message-link"
                                            title="Copy message link"
                                            @click="
                                                copyMessageLink(
                                                    {{ $message->id }}
                                                )
                                            "
                                        >

                                            <i class="fa-solid fa-link"></i>

                                            #{{ $message->id }}

                                        </button>

                                    </div>

                                </div>

                            </div>

                        </article>

                    @empty

                        <div class="empty-conversation">

                            <div class="empty-icon">
                                <i class="fa-regular fa-comments"></i>
                            </div>

                            <h3>
                                No messages yet
                            </h3>

                            <p>
                                Your conversation will appear
                                here once a message is sent.
                            </p>

                        </div>

                    @endforelse

                </div>


                {{-- =================================================
                   REPLY SECTION
                ================================================== --}}

                @if (! $isClosed)

                    <div class="reply-section">

                        <div class="reply-header">

                            <div>

                                <div class="section-kicker">

                                    <span></span>

                                    SEND A REPLY

                                </div>

                                <h3>
                                    Continue the conversation
                                </h3>

                            </div>


                            <div class="reply-secure">

                                <i class="fa-solid fa-lock"></i>

                                Secure

                            </div>

                        </div>


                        {{-- SUCCESS MESSAGE --}}

                        @if (session()->has('ticket-success'))

                            <div class="success-message">

                                <div class="success-icon">
                                    <i class="fa-solid fa-check"></i>
                                </div>

                                <div>

                                    <strong>
                                        Message sent
                                    </strong>

                                    <span>
                                        {{ session('ticket-success') }}
                                    </span>

                                </div>

                            </div>

                        @endif


                        {{-- REPLY BOX --}}

                        <div
                            class="reply-box"
                            wire:loading.class="is-sending"
                            wire:target="sendReply"
                        >

                            <textarea
                                id="ticket-message"
                                class="reply-textarea"
                                wire:model.defer="message"
                                placeholder="Write your message here..."
                                maxlength="10000"
                            ></textarea>


                            <div class="reply-toolbar">

                                <div class="reply-tools">

                                    <label
                                        class="attachment-input"
                                    >

                                        <input
                                            type="file"
                                            wire:model="attachments"
                                            multiple
                                            accept="
                                                image/jpeg,
                                                image/png,
                                                image/webp,
                                                image/gif
                                            "
                                        >

                                        <i class="fa-solid fa-paperclip"></i>

                                        <span>
                                            Attach images
                                        </span>

                                    </label>

                                </div>


                                <button
                                    type="button"
                                    class="send-button"
                                    wire:click="sendReply"
                                    wire:loading.attr="disabled"
                                    wire:target="sendReply"
                                >

                                    <span
                                        wire:loading.remove
                                        wire:target="sendReply"
                                    >

                                        <i class="fa-solid fa-paper-plane"></i>

                                        Send reply

                                    </span>


                                    <span
                                        wire:loading
                                        wire:target="sendReply"
                                    >

                                        <i class="fa-solid fa-spinner fa-spin"></i>

                                        Sending...

                                    </span>

                                </button>

                            </div>

                        </div>


                        {{-- =================================================
                           UPLOAD PREVIEW
                        ================================================== --}}

                        @if (count($attachments))

                            <div class="upload-preview">

                                @foreach (
                                    $attachments
                                    as $index => $attachment
                                )

                                    <div class="upload-item">

                                        <div class="upload-preview-image">

                                            @if (
                                                method_exists(
                                                    $attachment,
                                                    'temporaryUrl'
                                                )
                                            )

                                                <img
                                                    src="{{ $attachment->temporaryUrl() }}"
                                                    alt="Attachment preview"
                                                >

                                            @endif

                                        </div>


                                        <button
                                            type="button"
                                            wire:click="removeAttachment({{ $index }})"
                                            aria-label="Remove attachment"
                                        >

                                            <i class="fa-solid fa-xmark"></i>

                                        </button>

                                    </div>

                                @endforeach

                            </div>

                        @endif


                        {{-- MESSAGE ERROR --}}

                        @error('message')

                            <div class="field-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </div>

                        @enderror


                        {{-- ATTACHMENT ERROR --}}

                        @error('attachments.*')

                            <div class="field-error">

                                <i class="fa-solid fa-circle-exclamation"></i>

                                {{ $message }}

                            </div>

                        @enderror

                    </div>

                @else

                    {{-- =================================================
                       CLOSED TICKET
                    ================================================== --}}

                    <div class="closed-ticket">

                        <div class="closed-icon">
                            <i class="fa-solid fa-lock"></i>
                        </div>

                        <div>

                            <strong>
                                This ticket is closed
                            </strong>

                            <span>
                                Replies are disabled for this conversation.
                            </span>

                        </div>

                    </div>

                @endif

            </main>


            {{-- =================================================
               SIDEBAR
            ================================================== --}}

            <aside class="ticket-sidebar">


                {{-- =================================================
                   CUSTOMER
                ================================================== --}}

                <div class="sidebar-card customer-card">

                    <div class="sidebar-label">
                        CUSTOMER
                    </div>


                    <div class="customer-profile">

                        <div class="customer-avatar">
                            {{ $customerInitial ?: 'C' }}
                        </div>


                        <div class="customer-info">

                            <strong>
                                {{ $customerName }}
                            </strong>

                            @if ($ticket->email)

                                <span>
                                    {{ $ticket->email }}
                                </span>

                            @endif

                        </div>

                    </div>

                </div>


                {{-- =================================================
                   TICKET DETAILS
                ================================================== --}}

                <div class="sidebar-card">

                    <div class="sidebar-label">
                        TICKET DETAILS
                    </div>


                    <div class="detail-list">

                        <div class="detail-row">

                            <span>
                                <i class="fa-solid fa-hashtag"></i>
                                Ticket
                            </span>

                            <strong>
                                {{ $ticket->ticket_number }}
                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                <i class="fa-regular fa-calendar"></i>
                                Created
                            </span>

                            <strong>
                                {{ $ticket->created_at?->format('M d, Y') }}
                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                <i class="fa-solid fa-circle"></i>
                                Status
                            </span>

                            <strong class="detail-status">
                                {{ ucfirst($statusValue) }}
                            </strong>

                        </div>


                        <div class="detail-row">

                            <span>
                                <i class="fa-solid fa-bolt"></i>
                                Priority
                            </span>

                            <strong>
                                {{ ucfirst($priorityValue ?: 'normal') }}
                            </strong>

                        </div>

                    </div>

                </div>


                {{-- =================================================
                   HELP
                ================================================== --}}

                <div class="sidebar-help">

                    <div class="help-glow"></div>

                    <div class="help-icon">
                        <i class="fa-solid fa-headset"></i>
                    </div>

                    <h3>
                        Need help?
                    </h3>

                    <p>
                        Send us a message and our support
                        team will get back to you.
                    </p>

                    <div class="help-status">

                        <span></span>

                        Support is available

                    </div>

                </div>

            </aside>

        </div>

    </div>


    {{-- =========================================================
       LIGHTBOX
    ========================================================== --}}

    <div
        class="ticket-lightbox"
        x-show="lightbox"
        x-cloak
        x-transition.opacity
        @click.self="closeLightbox()"
    >

        <button
            type="button"
            class="lightbox-close"
            @click="closeLightbox()"
            aria-label="Close"
        >
            <i class="fa-solid fa-xmark"></i>
        </button>


        <div class="lightbox-content">

            <img
                :src="lightboxSrc"
                :alt="lightboxName"
            >

            <div
                class="lightbox-caption"
                x-text="lightboxName"
            ></div>

        </div>

    </div>


    {{-- =========================================================
       BACK TO TOP
    ========================================================== --}}

    <button
        type="button"
        class="ticket-to-top"
        x-show="showToTop"
        x-cloak
        x-transition.opacity
        @click="scrollToTop()"
        aria-label="Back to top"
    >

        <i class="fa-solid fa-arrow-up"></i>

    </button>


    <style>

        /* =========================================================
           BASE
        ========================================================== */

        [x-cloak] {
            display: none !important;
        }

        .guest-ticket-page {
            position: relative;
            isolation: isolate;

            min-height: 100vh;

            padding: 24px 20px 100px;

            overflow: hidden;

            color: #fff;

            background:
                radial-gradient(
                    circle at 15% 10%,
                    rgba(124, 58, 237, .14),
                    transparent 28%
                ),
                radial-gradient(
                    circle at 85% 15%,
                    rgba(168, 85, 247, .10),
                    transparent 25%
                ),
                #070609;
        }


        /* =========================================================
           BACKGROUND
        ========================================================== */

        .ticket-background {
            position: fixed;

            inset: 0;

            z-index: -1;

            pointer-events: none;

            overflow: hidden;
        }

        .ticket-grid {
            position: absolute;

            inset: 0;

            opacity: .035;

            background-image:
                linear-gradient(
                    rgba(255,255,255,.5) 1px,
                    transparent 1px
                ),
                linear-gradient(
                    90deg,
                    rgba(255,255,255,.5) 1px,
                    transparent 1px
                );

            background-size: 70px 70px;
        }

        .purple-orb {
            position: absolute;

            border-radius: 999px;

            filter: blur(100px);

            opacity: .20;
        }

        .orb-one {
            width: 420px;
            height: 420px;

            top: -180px;
            left: -160px;

            background: #7c3aed;
        }

        .orb-two {
            width: 350px;
            height: 350px;

            right: -160px;
            top: 35%;

            background: #a855f7;
        }

        .orb-three {
            width: 280px;
            height: 280px;

            left: 35%;
            bottom: -180px;

            background: #6d28d9;
        }


        /* =========================================================
           CONTAINER
        ========================================================== */

        .ticket-container {
            position: relative;

            z-index: 10;

            width: 100%;
            max-width: 1450px;

            margin: 0 auto;
        }


        /* =========================================================
           BACK TO WEBSITE
        ========================================================== */

        .ticket-navigation {
            position: relative;

            z-index: 100;

            display: flex;

            align-items: center;

            width: 100%;

            margin-bottom: 18px;

            pointer-events: auto;
        }

        .ticket-back {
            position: relative;

            z-index: 101;

            display: inline-flex;

            align-items: center;

            gap: 9px;

            padding: 8px 13px 8px 8px;

            border:
                1px solid rgba(255,255,255,.08);

            border-radius: 999px;

            color: #aaa0b3;

            background:
                rgba(15,12,19,.92);

            box-shadow:
                0 8px 25px rgba(0,0,0,.25);

            text-decoration: none;

            font-size: 11px;
            font-weight: 700;

            pointer-events: auto;

            cursor: pointer;

            transition:
                color .2s ease,
                background .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .ticket-back:hover {
            color: #fff;

            border-color:
                rgba(168,85,247,.35);

            background:
                rgba(124,58,237,.16);

            transform:
                translateX(-2px);
        }

        .ticket-back-icon {
            display: grid;

            place-items: center;

            width: 25px;
            height: 25px;

            border-radius: 50%;

            color: #d8b4fe;

            background:
                rgba(124,58,237,.15);
        }


        /* =========================================================
           HERO
        ========================================================== */

        .ticket-hero {
            position: relative;

            overflow: hidden;

            margin-bottom: 24px;

            padding: 30px;

            border:
                1px solid rgba(168,85,247,.18);

            border-radius: 26px;

            background:
                linear-gradient(
                    135deg,
                    rgba(30,20,45,.96),
                    rgba(11,9,15,.94)
                );

            box-shadow:
                0 30px 90px rgba(0,0,0,.45),
                inset 0 1px rgba(255,255,255,.04);
        }

        .hero-glow {
            position: absolute;

            width: 500px;
            height: 300px;

            right: -180px;
            top: -180px;

            background: #7c3aed;

            filter: blur(100px);

            opacity: .13;

            pointer-events: none;
        }

        .hero-top,
        .hero-content {
            position: relative;

            z-index: 1;
        }

        .hero-top {
            display: flex;

            justify-content: space-between;

            align-items: center;

            gap: 20px;
        }

        .ticket-brand {
            display: flex;

            align-items: center;

            gap: 14px;
        }

        .ticket-brand-icon {
            display: grid;

            place-items: center;

            width: 50px;
            height: 50px;

            border-radius: 15px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #9333ea,
                    #6d28d9
                );

            box-shadow:
                0 10px 35px
                rgba(124,58,237,.35);
        }

        .ticket-brand-label,
        .section-kicker,
        .sidebar-label {
            font-size: 10px;

            font-weight: 800;

            letter-spacing: .18em;

            text-transform: uppercase;

            color: #9d8cae;
        }

        .ticket-number {
            margin-top: 3px;

            font-size: 14px;

            font-weight: 800;

            letter-spacing: .05em;

            color: #f1eaff;
        }

        .hero-statuses {
            display: flex;

            gap: 8px;

            flex-wrap: wrap;

            justify-content: flex-end;
        }

        .status-pill,
        .priority-pill {
            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 8px 12px;

            border-radius: 999px;

            font-size: 11px;

            font-weight: 800;

            border:
                1px solid rgba(255,255,255,.08);

            background:
                rgba(255,255,255,.035);
        }

        .status-dot {
            width: 7px;
            height: 7px;

            border-radius: 50%;

            background: #a855f7;

            box-shadow:
                0 0 12px #a855f7;
        }

        .status-closed .status-dot {
            background: #71717a;

            box-shadow: none;
        }

        .priority-high,
        .priority-urgent {
            color: #f0abfc;

            border-color:
                rgba(217,70,239,.2);

            background:
                rgba(217,70,239,.08);
        }

        .hero-content {
            display: flex;

            align-items: flex-end;

            justify-content: space-between;

            gap: 30px;

            margin-top: 45px;
        }

        .hero-eyebrow {
            color: #a855f7;

            font-size: 11px;

            font-weight: 900;

            letter-spacing: .22em;
        }

        .hero-title {
            max-width: 850px;

            margin: 8px 0 10px;

            font-size:
                clamp(28px, 4vw, 48px);

            line-height: 1.08;

            letter-spacing: -.04em;

            font-weight: 900;

            color: #fff;

            overflow-wrap: anywhere;
        }

        .hero-description {
            margin: 0;

            color: #9c93a6;

            font-size: 14px;
        }

        .hero-meta {
            display: flex;

            gap: 10px;

            flex-shrink: 0;
        }

        .hero-meta-item {
            display: flex;

            align-items: center;

            gap: 10px;

            min-width: 130px;

            padding: 12px;

            border:
                1px solid rgba(255,255,255,.06);

            border-radius: 14px;

            background:
                rgba(255,255,255,.025);
        }

        .hero-meta-icon {
            display: grid;

            place-items: center;

            width: 36px;
            height: 36px;

            border-radius: 10px;

            color: #c084fc;

            background:
                rgba(124,58,237,.12);
        }

        .hero-meta-item span,
        .hero-meta-item strong {
            display: block;
        }

        .hero-meta-item span {
            margin-bottom: 2px;

            font-size: 9px;

            text-transform: uppercase;

            letter-spacing: .12em;

            color: #77717e;
        }

        .hero-meta-item strong {
            font-size: 12px;

            color: #eee8f4;
        }


        /* =========================================================
           LAYOUT
        ========================================================== */

        .ticket-layout {
            display: grid;

            grid-template-columns:
                minmax(0, 1fr) 320px;

            gap: 24px;

            align-items: start;
        }


        /* =========================================================
           CARDS
        ========================================================== */

        .conversation-card,
        .sidebar-card {
            border:
                1px solid rgba(168,85,247,.12);

            background:
                linear-gradient(
                    145deg,
                    rgba(22,17,28,.96),
                    rgba(9,8,12,.96)
                );

            box-shadow:
                0 25px 70px rgba(0,0,0,.32),
                inset 0 1px rgba(255,255,255,.025);
        }

        .conversation-card {
            overflow: hidden;

            border-radius: 24px;
        }

        .conversation-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            padding: 25px 28px;

            border-bottom:
                1px solid rgba(255,255,255,.055);
        }

        .section-kicker {
            display: flex;

            align-items: center;

            gap: 8px;
        }

        .section-kicker span {
            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #a855f7;

            box-shadow:
                0 0 10px #a855f7;
        }

        .conversation-header h2 {
            margin: 7px 0 0;

            font-size: 22px;

            font-weight: 850;

            color: #fff;
        }

        .conversation-count {
            padding: 7px 11px;

            border-radius: 999px;

            color: #c084fc;

            background:
                rgba(124,58,237,.10);

            border:
                1px solid rgba(168,85,247,.12);

            font-size: 11px;

            font-weight: 800;
        }


        /* =========================================================
           IMESSAGE STYLE
        ========================================================== */

        .messages-container {
            padding: 18px 28px 32px;
        }

        .ticket-message {
            position: relative;

            width: 100%;

            padding: 13px 0;

            scroll-margin-top: 120px;

            transition:
                background .3s ease,
                box-shadow .3s ease,
                transform .3s ease;
        }

        .message-row {
            display: flex;

            align-items: flex-end;

            gap: 9px;

            width: 100%;
        }


        /* =========================================================
           SUPPORT = LEFT
        ========================================================== */

        .support-message .message-row {
            justify-content: flex-start;
        }

        .support-message .message-avatar {
            order: 1;
        }

        .support-message .message-main {
            order: 2;

            align-items: flex-start;

            margin-right: auto;
        }


        /* =========================================================
           CUSTOMER = RIGHT
        ========================================================== */

        .customer-message .message-row {
            justify-content: flex-end;
        }

        .customer-message .message-main {
            order: 1;

            align-items: flex-end;

            margin-left: auto;
        }

        .customer-message .message-avatar {
            order: 2;
        }


        /* =========================================================
           AVATARS
        ========================================================== */

        .message-avatar {
            flex: 0 0 38px;

            display: grid;

            place-items: center;

            width: 38px;
            height: 38px;

            border-radius: 50%;

            color: #fff;

            font-size: 11px;

            font-weight: 900;

            background:
                linear-gradient(
                    145deg,
                    #29232f,
                    #151219
                );

            border:
                1px solid rgba(255,255,255,.08);

            box-shadow:
                0 5px 18px rgba(0,0,0,.25);
        }

        .support-message .message-avatar {
            color: #fff;

            background:
                linear-gradient(
                    145deg,
                    #a855f7,
                    #6d28d9
                );

            border-color:
                rgba(216,180,254,.30);

            box-shadow:
                0 7px 24px
                rgba(124,58,237,.28);
        }

        .customer-message .message-avatar {
            color: #d8b4fe;

            border-color:
                rgba(168,85,247,.22);

            background:
                linear-gradient(
                    145deg,
                    rgba(124,58,237,.30),
                    rgba(49,30,69,.90)
                );
        }


        /* =========================================================
           MESSAGE MAIN
        ========================================================== */

        .message-main {
            min-width: 0;

            width: auto;

            max-width:
                min(76%, 720px);

            display: flex;

            flex-direction: column;
        }


        /* =========================================================
           AUTHOR
        ========================================================== */

        .message-top {
            display: flex;

            align-items: center;

            margin-bottom: 5px;

            padding: 0 6px;
        }

        .support-message .message-top {
            justify-content: flex-start;
        }

        .customer-message .message-top {
            justify-content: flex-end;
        }

        .message-author {
            display: flex;

            align-items: center;

            gap: 7px;
        }

        .author-name {
            color: #d9d1df;

            font-size: 10px;

            font-weight: 800;
        }

        .author-role {
            padding: 3px 6px;

            border-radius: 999px;

            color: #a78bbd;

            background:
                rgba(168,85,247,.07);

            font-size: 7px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: .09em;
        }


        /* =========================================================
           BUBBLE BASE
        ========================================================== */

        .message-bubble {
            position: relative;

            width: fit-content;

            max-width: 100%;

            padding: 12px 16px;

            border: 1px solid transparent;

            box-shadow:
                0 4px 18px rgba(0,0,0,.15);

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .message-bubble:hover {
            transform:
                translateY(-1px);
        }


        /* =========================================================
           SUPPORT BUBBLE
           LEFT / RECIPIENT
        ========================================================== */

        .support-message .message-bubble {
            border-radius:
                6px 20px 20px 20px;

            border-color:
                rgba(255,255,255,.08);

            background:
                linear-gradient(
                    145deg,
                    #28232e,
                    #1b1720
                );

            color: #e9e2ee;

            box-shadow:
                0 7px 24px
                rgba(0,0,0,.22);
        }

        .support-message .message-bubble::before {
            content: '';

            position: absolute;

            left: -6px;
            bottom: 0;

            width: 14px;
            height: 14px;

            background: #28232e;

            clip-path:
                polygon(
                    100% 0,
                    100% 100%,
                    0 100%
                );
        }


        /* =========================================================
           CUSTOMER BUBBLE
           RIGHT / SENDER
        ========================================================== */

        .customer-message .message-bubble {
            border-radius:
                20px 6px 20px 20px;

            border-color:
                rgba(216,180,254,.20);

            background:
                linear-gradient(
                    145deg,
                    #9333ea,
                    #6d28d9
                );

            color: #fff;

            box-shadow:
                0 8px 28px
                rgba(109,40,217,.24);
        }

        .customer-message .message-bubble::before {
            content: '';

            position: absolute;

            right: -6px;
            bottom: 0;

            width: 14px;
            height: 14px;

            background: #6d28d9;

            clip-path:
                polygon(
                    0 0,
                    100% 100%,
                    0 100%
                );
        }


        /* =========================================================
           MESSAGE TEXT
        ========================================================== */

        .message-content {
            max-width: 100%;

            overflow-wrap: anywhere;

            word-break: break-word;

            white-space: normal;

            font-size: 14px;

            line-height: 1.65;
        }

        .support-message .message-content {
            color: #e7dfeb;
        }

        .customer-message .message-content {
            color: #fff;
        }


        /* =========================================================
           MESSAGE FOOTER
        ========================================================== */

        .message-footer {
            display: flex;

            align-items: center;

            gap: 8px;

            margin-top: 4px;

            padding: 0 6px;
        }

        .support-message .message-footer {
            justify-content: flex-start;
        }

        .customer-message .message-footer {
            justify-content: flex-end;
        }

        .message-date {
            color: #625b68;

            font-size: 8px;
        }

        .message-link {
            display: inline-flex;

            align-items: center;

            gap: 4px;

            padding: 0;

            border: 0;

            color: #514b58;

            background: transparent;

            font-size: 8px;

            cursor: pointer;

            transition:
                color .2s ease;
        }

        .message-link:hover {
            color: #b56cf5;
        }


        /* =========================================================
           HIGHLIGHTED MESSAGE
        ========================================================== */

        .ticket-message.message-highlight {
            z-index: 5;

            margin-top: 14px;
            margin-bottom: 14px;

            padding:
                15px 16px;

            border:
                1px solid
                rgba(192,132,252,.38);

            border-radius: 20px;

            background:
                radial-gradient(
                    circle at 50% 50%,
                    rgba(168,85,247,.13),
                    transparent 65%
                );

            box-shadow:
                0 0 0 1px
                rgba(168,85,247,.08),

                0 0 45px
                rgba(124,58,237,.15);

            animation:
                highlightedMessagePulse
                2s ease-in-out 0s 3;
        }

        .ticket-message.message-highlight
        .message-bubble {
            box-shadow:
                0 0 35px
                rgba(168,85,247,.28);
        }

        .ticket-message.message-highlight-active {
            z-index: 6;

            animation:
                highlightedMessagePulse
                1.5s ease-in-out 0s 3;
        }

        .ticket-message.message-highlight-active
        .message-bubble {
            box-shadow:
                0 0 40px
                rgba(192,132,252,.35);
        }

        .new-reply-indicator {
            position: absolute;

            top: -9px;

            left: 50%;

            transform:
                translateX(-50%);

            z-index: 20;

            display: flex;

            align-items: center;

            gap: 7px;

            padding:
                6px 9px 6px 8px;

            border:
                1px solid
                rgba(216,180,254,.30);

            border-radius: 999px;

            color: #f3e8ff;

            background:
                linear-gradient(
                    135deg,
                    #9333ea,
                    #581c87
                );

            box-shadow:
                0 8px 25px
                rgba(88,28,135,.35);

            font-size: 8px;

            font-weight: 900;

            text-transform: uppercase;

            letter-spacing: .1em;

            white-space: nowrap;
        }

        .new-reply-indicator i {
            color: #e9d5ff;
        }

        .new-reply-indicator button {
            display: grid;

            place-items: center;

            width: 18px;
            height: 18px;

            margin-left: 2px;

            padding: 0;

            border: 0;

            border-radius: 50%;

            color: #fff;

            background:
                rgba(255,255,255,.10);

            cursor: pointer;

            transition:
                background .2s ease;
        }

        .new-reply-indicator button:hover {
            background:
                rgba(255,255,255,.20);
        }

        @keyframes highlightedMessagePulse {

            0%,
            100% {
                box-shadow:
                    0 0 0 1px
                    rgba(168,85,247,.08),

                    0 0 45px
                    rgba(124,58,237,.16);
            }

            50% {
                box-shadow:
                    0 0 0 2px
                    rgba(192,132,252,.20),

                    0 0 75px
                    rgba(168,85,247,.34);
            }
        }


        /* =========================================================
           ATTACHMENTS
        ========================================================== */

        .attachments-grid {
            display: grid;

            grid-template-columns:
                repeat(
                    auto-fit,
                    minmax(115px, 1fr)
                );

            gap: 7px;

            margin-top: 10px;

            max-width: 420px;
        }

        .attachment-card {
            position: relative;

            overflow: hidden;

            width: 100%;
            height: 115px;

            padding: 0;

            border:
                1px solid
                rgba(255,255,255,.10);

            border-radius: 12px;

            background: #111;

            cursor: pointer;
        }

        .attachment-card img {
            width: 100%;
            height: 100%;

            object-fit: cover;

            transition:
                transform .35s ease;
        }

        .attachment-card:hover img {
            transform:
                scale(1.06);
        }

        .attachment-hover {
            position: absolute;

            inset: 0;

            display: grid;

            place-items: center;

            color: #fff;

            background:
                rgba(88,28,135,.55);

            opacity: 0;

            transition:
                opacity .25s ease;
        }

        .attachment-card:hover
        .attachment-hover {
            opacity: 1;
        }


        /* =========================================================
           EMPTY
        ========================================================== */

        .empty-conversation {
            padding: 80px 20px;

            text-align: center;
        }

        .empty-icon {
            display: grid;

            place-items: center;

            width: 70px;
            height: 70px;

            margin: 0 auto 18px;

            border-radius: 20px;

            color: #a855f7;

            background:
                rgba(124,58,237,.09);

            border:
                1px solid
                rgba(168,85,247,.12);

            font-size: 25px;
        }

        .empty-conversation h3 {
            margin: 0 0 7px;

            font-size: 18px;

            color: #eee8f4;
        }

        .empty-conversation p {
            margin: 0;

            color: #6f6875;

            font-size: 13px;
        }


        /* =========================================================
           REPLY
        ========================================================== */

        .reply-section {
            padding: 28px;

            border-top:
                1px solid
                rgba(255,255,255,.055);

            background:
                linear-gradient(
                    180deg,
                    rgba(124,58,237,.035),
                    rgba(0,0,0,.08)
                );
        }

        .reply-header {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 20px;

            margin-bottom: 16px;
        }

        .reply-header h3 {
            margin: 7px 0 0;

            color: #fff;

            font-size: 17px;

            font-weight: 850;
        }

        .reply-secure {
            display: flex;

            align-items: center;

            gap: 6px;

            color: #655e6b;

            font-size: 10px;
        }

        .reply-secure i {
            color: #a855f7;
        }

        .reply-box {
            overflow: hidden;

            border:
                1px solid
                rgba(168,85,247,.13);

            border-radius: 18px;

            background:
                rgba(5,4,7,.65);

            transition:
                border-color .2s ease,
                box-shadow .2s ease;
        }

        .reply-box:focus-within {
            border-color:
                rgba(168,85,247,.35);

            box-shadow:
                0 0 0 3px
                rgba(124,58,237,.08),

                0 20px 50px
                rgba(0,0,0,.25);
        }

        .reply-box.is-sending {
            opacity: .75;
        }

        .reply-textarea {
            display: block;

            width: 100%;

            min-height: 170px;

            padding: 20px;

            resize: vertical;

            outline: none;

            border: 0;

            color: #eee8f4;

            background: transparent;

            font: inherit;

            font-size: 14px;

            line-height: 1.8;
        }

        .reply-textarea::placeholder {
            color: #514b57;
        }

        .reply-toolbar {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 15px;

            padding: 12px;

            border-top:
                1px solid
                rgba(255,255,255,.05);
        }

        .reply-tools {
            display: flex;

            align-items: center;
        }

        .attachment-input {
            display: inline-flex;

            align-items: center;

            gap: 8px;

            padding: 9px 11px;

            border-radius: 9px;

            color: #8d8592;

            background:
                rgba(255,255,255,.025);

            font-size: 11px;

            cursor: pointer;

            transition:
                all .2s ease;
        }

        .attachment-input:hover {
            color: #d8b4fe;

            background:
                rgba(124,58,237,.10);
        }

        .attachment-input input {
            display: none;
        }

        .send-button {
            min-width: 130px;

            padding: 11px 18px;

            border: 0;

            border-radius: 11px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #9333ea,
                    #6d28d9
                );

            box-shadow:
                0 10px 25px
                rgba(109,40,217,.22);

            font-size: 12px;

            font-weight: 850;

            cursor: pointer;

            transition:
                transform .2s ease,
                box-shadow .2s ease;
        }

        .send-button:hover {
            transform:
                translateY(-1px);

            box-shadow:
                0 14px 35px
                rgba(109,40,217,.32);
        }

        .send-button:disabled {
            opacity: .55;

            cursor: not-allowed;

            transform: none;
        }

        .success-message {
            display: flex;

            align-items: center;

            gap: 12px;

            margin-bottom: 14px;

            padding: 12px 14px;

            border:
                1px solid
                rgba(168,85,247,.12);

            border-radius: 12px;

            background:
                rgba(124,58,237,.06);
        }

        .success-icon {
            display: grid;

            place-items: center;

            width: 30px;
            height: 30px;

            border-radius: 9px;

            color: #fff;

            background: #7e22ce;
        }

        .success-message strong,
        .success-message span {
            display: block;
        }

        .success-message strong {
            color: #e9d5ff;

            font-size: 11px;
        }

        .success-message span {
            margin-top: 2px;

            color: #766d7d;

            font-size: 10px;
        }

        .field-error {
            display: flex;

            align-items: center;

            gap: 7px;

            margin-top: 8px;

            color: #e879f9;

            font-size: 11px;
        }

        .upload-preview {
            display: flex;

            flex-wrap: wrap;

            gap: 8px;

            margin-top: 10px;
        }

        .upload-item {
            position: relative;

            width: 65px;
            height: 65px;

            overflow: hidden;

            border-radius: 10px;

            border:
                1px solid
                rgba(168,85,247,.15);
        }

        .upload-preview-image,
        .upload-preview-image img {
            width: 100%;
            height: 100%;
        }

        .upload-preview-image img {
            object-fit: cover;
        }

        .upload-item button {
            position: absolute;

            top: 4px;
            right: 4px;

            display: grid;

            place-items: center;

            width: 19px;
            height: 19px;

            padding: 0;

            border: 0;

            border-radius: 50%;

            color: #fff;

            background:
                rgba(0,0,0,.7);

            cursor: pointer;
        }


        /* =========================================================
           CLOSED
        ========================================================== */

        .closed-ticket {
            display: flex;

            align-items: center;

            gap: 13px;

            margin: 25px 28px;

            padding: 17px;

            border:
                1px solid
                rgba(255,255,255,.06);

            border-radius: 14px;

            background:
                rgba(255,255,255,.025);
        }

        .closed-icon {
            display: grid;

            place-items: center;

            width: 40px;
            height: 40px;

            border-radius: 11px;

            color: #77717d;

            background:
                rgba(255,255,255,.04);
        }

        .closed-ticket strong,
        .closed-ticket span {
            display: block;
        }

        .closed-ticket strong {
            color: #c5bdca;

            font-size: 12px;
        }

        .closed-ticket span {
            margin-top: 3px;

            color: #625b68;

            font-size: 10px;
        }


        /* =========================================================
           SIDEBAR
        ========================================================== */

        .ticket-sidebar {
            display: flex;

            flex-direction: column;

            gap: 16px;

            position: sticky;

            top: 25px;
        }

        .sidebar-card {
            padding: 20px;

            border-radius: 19px;
        }

        .customer-profile {
            display: flex;

            align-items: center;

            gap: 12px;

            margin-top: 16px;
        }

        .customer-avatar {
            display: grid;

            place-items: center;

            width: 44px;
            height: 44px;

            border-radius: 13px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #9333ea,
                    #581c87
                );

            font-size: 13px;

            font-weight: 900;

            box-shadow:
                0 8px 25px
                rgba(124,58,237,.20);
        }

        .customer-info {
            min-width: 0;
        }

        .customer-info strong,
        .customer-info span {
            display: block;

            overflow: hidden;

            text-overflow: ellipsis;
        }

        .customer-info strong {
            color: #eee8f4;

            font-size: 12px;
        }

        .customer-info span {
            margin-top: 3px;

            color: #69626f;

            font-size: 10px;
        }

        .detail-list {
            margin-top: 12px;
        }

        .detail-row {
            display: flex;

            align-items: center;

            justify-content: space-between;

            gap: 10px;

            padding: 12px 0;

            border-bottom:
                1px solid
                rgba(255,255,255,.04);
        }

        .detail-row:last-child {
            border-bottom: 0;

            padding-bottom: 0;
        }

        .detail-row span {
            display: flex;

            align-items: center;

            gap: 7px;

            color: #69626f;

            font-size: 10px;
        }

        .detail-row span i {
            color: #8b5cf6;

            width: 12px;
        }

        .detail-row strong {
            color: #c9c1ce;

            font-size: 10px;

            text-align: right;
        }

        .detail-status {
            color: #c084fc !important;
        }


        /* =========================================================
           HELP
        ========================================================== */

        .sidebar-help {
            position: relative;

            overflow: hidden;

            padding: 23px;

            border:
                1px solid
                rgba(168,85,247,.16);

            border-radius: 20px;

            background:
                linear-gradient(
                    145deg,
                    rgba(76,29,149,.24),
                    rgba(16,10,22,.96)
                );
        }

        .help-glow {
            position: absolute;

            width: 130px;
            height: 130px;

            top: -70px;
            right: -50px;

            border-radius: 50%;

            background: #a855f7;

            filter: blur(50px);

            opacity: .18;
        }

        .help-icon {
            position: relative;

            display: grid;

            place-items: center;

            width: 42px;
            height: 42px;

            border-radius: 12px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #9333ea,
                    #6d28d9
                );
        }

        .sidebar-help h3 {
            position: relative;

            margin: 15px 0 7px;

            color: #fff;

            font-size: 16px;
        }

        .sidebar-help p {
            position: relative;

            margin: 0;

            color: #827a88;

            font-size: 11px;

            line-height: 1.7;
        }

        .help-status {
            position: relative;

            display: flex;

            align-items: center;

            gap: 7px;

            margin-top: 16px;

            color: #a69baa;

            font-size: 9px;

            font-weight: 700;
        }

        .help-status span {
            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: #a855f7;

            box-shadow:
                0 0 10px #a855f7;
        }


        /* =========================================================
           LIGHTBOX
        ========================================================== */

        .ticket-lightbox {
            position: fixed;

            inset: 0;

            z-index: 9999;

            display: grid;

            place-items: center;

            padding: 30px;

            background:
                rgba(3,2,5,.94);

            backdrop-filter:
                blur(15px);
        }

        .lightbox-content {
            max-width: 1100px;

            max-height: 90vh;

            text-align: center;
        }

        .lightbox-content img {
            display: block;

            max-width: 100%;

            max-height: 78vh;

            margin: 0 auto;

            border-radius: 15px;

            box-shadow:
                0 30px 100px
                rgba(0,0,0,.65);
        }

        .lightbox-caption {
            margin-top: 12px;

            color: #aaa0b2;

            font-size: 11px;
        }

        .lightbox-close {
            position: absolute;

            top: 22px;
            right: 22px;

            display: grid;

            place-items: center;

            width: 42px;
            height: 42px;

            border:
                1px solid
                rgba(255,255,255,.08);

            border-radius: 50%;

            color: #fff;

            background:
                rgba(255,255,255,.05);

            cursor: pointer;

            transition:
                background .2s ease,
                transform .2s ease;
        }

        .lightbox-close:hover {
            background:
                rgba(168,85,247,.20);

            transform:
                rotate(90deg);
        }


        /* =========================================================
           BACK TO TOP
        ========================================================== */

        .ticket-to-top {
            position: fixed;

            right: 24px;
            bottom: 24px;

            z-index: 500;

            display: grid;

            place-items: center;

            width: 44px;
            height: 44px;

            border:
                1px solid
                rgba(168,85,247,.2);

            border-radius: 13px;

            color: #fff;

            background:
                linear-gradient(
                    135deg,
                    #7e22ce,
                    #581c87
                );

            box-shadow:
                0 15px 35px
                rgba(0,0,0,.4);

            cursor: pointer;

            transition:
                transform .2s ease;
        }

        .ticket-to-top:hover {
            transform:
                translateY(-3px);
        }


        /* =========================================================
           RESPONSIVE
        ========================================================== */

        @media (max-width: 1100px) {

            .ticket-layout {
                grid-template-columns:
                    minmax(0, 1fr) 280px;
            }

            .hero-content {
                align-items: flex-start;

                flex-direction: column;
            }

            .hero-meta {
                width: 100%;
            }

        }


        @media (max-width: 850px) {

            .guest-ticket-page {
                padding:
                    18px 12px 80px;
            }

            .ticket-layout {
                grid-template-columns: 1fr;
            }

            .ticket-sidebar {
                position: static;

                display: grid;

                grid-template-columns:
                    repeat(2, 1fr);
            }

            .sidebar-help {
                grid-column: span 2;
            }

            .ticket-hero {
                padding: 22px;

                border-radius: 20px;
            }

        }


        @media (max-width: 600px) {

            .ticket-navigation {
                margin-bottom: 13px;
            }

            .ticket-back {
                font-size: 10px;
            }

            .hero-top {
                align-items: flex-start;

                flex-direction: column;
            }

            .hero-statuses {
                justify-content: flex-start;
            }

            .hero-title {
                font-size: 30px;
            }

            .hero-meta {
                display: grid;

                grid-template-columns:
                    1fr 1fr;
            }

            .hero-meta-item {
                min-width: 0;
            }

            .conversation-header,
            .reply-section {
                padding: 20px;
            }

            .messages-container {
                padding:
                    10px 12px 20px;
            }

            .ticket-message {
                padding: 10px 0;
            }

            .message-row {
                gap: 7px;
            }

            .message-avatar {
                flex-basis: 32px;

                width: 32px;
                height: 32px;

                font-size: 9px;
            }

            .message-main {
                max-width: 82%;
            }

            .message-top {
                padding: 0 4px;
            }

            .author-name {
                font-size: 9px;
            }

            .author-role {
                display: none;
            }

            .message-bubble {
                padding: 10px 13px;
            }

            .message-content {
                font-size: 13px;

                line-height: 1.6;
            }

            .message-footer {
                padding: 0 4px;
            }

            .message-date {
                font-size: 7px;
            }

            .attachments-grid {
                grid-template-columns:
                    repeat(
                        2,
                        minmax(0, 1fr)
                    );

                max-width: 300px;
            }

            .attachment-card {
                height: 100px;
            }

            .reply-toolbar {
                align-items: stretch;

                flex-direction: column;
            }

            .send-button {
                width: 100%;
            }

            .ticket-sidebar {
                display: flex;
            }

            .ticket-message.message-highlight {
                margin-left: -5px;
                margin-right: -5px;

                padding-left: 8px;
                padding-right: 8px;
            }

            .new-reply-indicator {
                font-size: 7px;
            }

        }


        /* =========================================================
           REDUCED MOTION
        ========================================================== */

        @media (prefers-reduced-motion: reduce) {

            *,
            *::before,
            *::after {
                scroll-behavior: auto !important;

                animation-duration:
                    .01ms !important;

                animation-iteration-count:
                    1 !important;

                transition-duration:
                    .01ms !important;
            }

        }

    </style>

</div>