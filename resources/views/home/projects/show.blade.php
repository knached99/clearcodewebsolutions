@extends('layouts.home_layout')

@section('title', $work->title . ' — Clear Code Web Solutions')

@section('description', Str::limit(strip_tags($work->description ?? ''), 155))

@push('styles')
    <style>
        /* ============================================================
           PROJECT SHOW PAGE
        ============================================================ */

        .cc-project {
            --lime: #ADD322;
            --black: #050505;
            --surface: #0b0b0e;
            --purple: #7c3aed;

            min-height: 100vh;
            overflow: hidden;

            background:
                radial-gradient(700px circle at 50% 0%,
                    rgba(124, 58, 237, .10),
                    transparent 70%),
                var(--black);

            color: #fff;
        }

        .cc-project *,
        .cc-project *::before,
        .cc-project *::after {
            box-sizing: border-box;
        }

        .cc-project-container {
            width: min(1180px, calc(100% - 40px));
            margin: 0 auto;
        }

        /* ============================================================
           HERO
           ============================================================ */

        .cc-project-hero {
            padding-top: 145px;
            padding-bottom: 80px;
        }

        .cc-project-back {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            color: rgba(255, 255, 255, .38);

            font-size: 12px;
            font-weight: 600;

            text-decoration: none;

            transition: color .25s ease;
        }

        .cc-project-back:hover {
            color: var(--lime);
        }

        .cc-project-back svg {
            width: 16px;
            height: 16px;

            transition: transform .25s ease;
        }

        .cc-project-back:hover svg {
            transform: translateX(-4px);
        }

        .cc-project-hero-content {
            display: grid;
            grid-template-columns: minmax(0, 1fr) 280px;

            gap: 70px;

            margin-top: 60px;
        }

        .cc-project-category {
            display: inline-flex;
            align-items: center;
            gap: 9px;

            margin-bottom: 18px;

            color: var(--lime);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .24em;
            text-transform: uppercase;
        }

        .cc-project-category::before {
            content: "";

            width: 6px;
            height: 6px;

            border-radius: 50%;

            background: var(--lime);

            box-shadow:
                0 0 18px rgba(173, 211, 34, .45);
        }

        .cc-project-title {
            margin: 0;

            max-width: 800px;

            font-size: clamp(42px, 6vw, 76px);
            line-height: .95;

            letter-spacing: -.055em;

            font-weight: 800;
        }

        .cc-project-summary {
            margin-top: 28px;

            max-width: 680px;

            color: rgba(255, 255, 255, .43);

            font-size: 15px;
            line-height: 1.85;
        }

        /* Project meta */

        .cc-project-meta {
            padding-top: 6px;
        }

        .cc-project-meta-item {
            padding: 18px 0;

            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .cc-project-meta-item:last-child {
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .cc-project-meta-label {
            margin-bottom: 7px;

            color: rgba(255, 255, 255, .25);

            font-size: 9px;
            font-weight: 700;

            letter-spacing: .2em;
            text-transform: uppercase;
        }

        .cc-project-meta-value {
            color: rgba(255, 255, 255, .78);

            font-size: 13px;
            line-height: 1.5;
        }

        .cc-project-visit {
            display: inline-flex;
            align-items: center;
            gap: 8px;

            color: #fff;

            font-size: 13px;
            font-weight: 600;

            text-decoration: none;

            transition: color .2s ease;
        }

        .cc-project-visit:hover {
            color: var(--lime);
        }

        .cc-project-visit span {
            color: var(--lime);
        }

        /* ============================================================
           GALLERY
           ============================================================ */

        .cc-project-gallery {
            padding-bottom: 120px;
        }

        .cc-project-gallery-header {
            display: flex;
            align-items: end;
            justify-content: space-between;

            margin-bottom: 28px;
        }

        .cc-project-eyebrow {
            color: var(--lime);

            font-size: 10px;
            font-weight: 700;

            letter-spacing: .25em;
            text-transform: uppercase;
        }

        .cc-project-gallery-title {
            margin: 7px 0 0;

            font-size: 27px;
            line-height: 1.2;

            letter-spacing: -.035em;

            font-weight: 650;
        }

        .cc-project-gallery-count {
            color: rgba(255, 255, 255, .25);

            font-size: 11px;
        }

        /* GRID */

        .cc-project-grid {
            display: grid;

            grid-template-columns: repeat(2, minmax(0, 1fr));

            gap: 18px;
        }

        .cc-project-card {
            position: relative;

            overflow: hidden;

            aspect-ratio: 16 / 11;

            padding: 0;

            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 16px;

            background: #080808;

            cursor: zoom-in;

            isolation: isolate;
        }

        /*
         * Every 3rd image gets a little more visual weight.
         */
        .cc-project-card:nth-child(3n) {
            grid-column: span 2;

            aspect-ratio: 16 / 8;
        }

        .cc-project-card img {
            display: block;

            width: 100%;
            height: 100%;

            object-fit: cover;
            object-position: top center;

            transition:
                transform .7s cubic-bezier(.2, .65, .2, 1),
                filter .5s ease;
        }

        .cc-project-card::after {
            content: "";

            position: absolute;
            inset: 0;

            z-index: 1;

            background:
                linear-gradient(to top,
                    rgba(0, 0, 0, .45),
                    transparent 45%);

            opacity: 0;

            transition: opacity .35s ease;
        }

        .cc-project-card:hover img {
            transform: scale(1.025);
            filter: brightness(.85);
        }

        .cc-project-card:hover::after {
            opacity: 1;
        }

        .cc-project-card-number {
            position: absolute;

            left: 18px;
            bottom: 16px;

            z-index: 2;

            display: flex;
            align-items: center;
            justify-content: center;

            width: 30px;
            height: 30px;

            border: 1px solid rgba(255, 255, 255, .15);
            border-radius: 50%;

            background: rgba(0, 0, 0, .35);

            backdrop-filter: blur(10px);

            color: rgba(255, 255, 255, .75);

            font-size: 9px;

            opacity: 0;
            transform: translateY(8px);

            transition:
                opacity .3s ease,
                transform .3s ease;
        }

        .cc-project-card:hover .cc-project-card-number {
            opacity: 1;
            transform: translateY(0);
        }

        /* Empty gallery */

        .cc-project-empty {
            display: flex;
            align-items: center;
            justify-content: center;

            min-height: 300px;

            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 16px;

            background:
                radial-gradient(circle at center,
                    rgba(124, 58, 237, .12),
                    transparent 60%),
                #080808;
        }

        .cc-project-empty span {
            color: rgba(173, 211, 34, .12);

            font-size: 100px;
            font-weight: 800;

            letter-spacing: -.08em;
        }

        /* ============================================================
           CTA
           ============================================================ */

        .cc-project-cta {
            padding-bottom: 120px;
        }

        .cc-project-cta-box {
            position: relative;

            overflow: hidden;

            padding: 60px;

            border: 1px solid rgba(255, 255, 255, .08);
            border-radius: 22px;

            background:
                radial-gradient(500px circle at 90% 0%,
                    rgba(124, 58, 237, .14),
                    transparent 65%),
                #0b0b0e;
        }

        .cc-project-cta-box::after {
            content: "";

            position: absolute;

            right: -100px;
            bottom: -140px;

            width: 280px;
            height: 280px;

            border-radius: 50%;

            background: rgba(173, 211, 34, .035);

            filter: blur(70px);
        }

        .cc-project-cta-content {
            position: relative;
            z-index: 2;

            max-width: 650px;
        }

        .cc-project-cta-title {
            margin: 12px 0 0;

            font-size: clamp(32px, 4vw, 52px);
            line-height: 1;

            letter-spacing: -.045em;

            font-weight: 750;
        }

        .cc-project-cta-title span {
            color: rgba(255, 255, 255, .3);
        }

        .cc-project-cta-text {
            max-width: 520px;

            margin-top: 17px;

            color: rgba(255, 255, 255, .38);

            font-size: 14px;
            line-height: 1.8;
        }

        .cc-project-cta-button {
            display: inline-flex;
            align-items: center;
            gap: 12px;

            margin-top: 28px;

            padding: 12px 18px;

            border-radius: 999px;

            background: var(--lime);
            color: #050505;

            font-size: 12px;
            font-weight: 700;

            text-decoration: none;

            transition:
                transform .25s ease,
                background .25s ease,
                box-shadow .25s ease;
        }

        .cc-project-cta-button:hover {
            background: #c4ec35;
            color: #333 !important;

            transform: translateY(-2px);

            box-shadow:
                0 15px 40px rgba(173, 211, 34, .12);
        }

        /* ============================================================
           LIGHTBOX
           ============================================================ */

        .cc-lightbox {
            position: fixed;

            inset: 0;

            z-index: 99999;

            display: flex;
            align-items: center;
            justify-content: center;

            padding: 30px;

            background: rgba(2, 2, 3, .94);

            backdrop-filter: blur(18px);
        }

        .cc-lightbox-image-wrap {
            position: relative;

            display: flex;
            align-items: center;
            justify-content: center;

            width: min(1100px, calc(100vw - 140px));
            height: min(82vh, 850px);
        }

        .cc-lightbox-image {
            display: block;

            max-width: 100%;
            max-height: 100%;

            width: auto;
            height: auto;

            object-fit: contain;

            border-radius: 8px;

            box-shadow:
                0 40px 120px rgba(0, 0, 0, .65);
        }

        /* Close */

        .cc-lightbox-close {
            position: fixed;

            top: 24px;
            right: 28px;

            display: flex;
            align-items: center;
            justify-content: center;

            width: 44px;
            height: 44px;

            border: 1px solid rgba(255, 255, 255, .12);
            border-radius: 50%;

            background: rgba(255, 255, 255, .05);

            color: #fff;

            font-size: 22px;

            cursor: pointer;

            transition:
                background .2s ease,
                border-color .2s ease,
                color .2s ease;
        }

        .cc-lightbox-close:hover {
            border-color: rgba(173, 211, 34, .4);
            background: rgba(173, 211, 34, .1);
            color: var(--lime);
        }

        /* Navigation */

        .cc-lightbox-nav {
            position: fixed;
            top: 50%;

            display: flex;
            align-items: center;
            justify-content: center;

            width: 48px;
            height: 48px;

            margin-top: -24px;

            border: 1px solid rgba(255, 255, 255, .1);
            border-radius: 50%;

            background: rgba(255, 255, 255, .04);

            color: white;

            font-size: 20px;

            cursor: pointer;

            transition:
                background .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .cc-lightbox-nav:hover {
            border-color: rgba(173, 211, 34, .35);
            background: rgba(173, 211, 34, .08);
        }

        .cc-lightbox-prev {
            left: 28px;
        }

        .cc-lightbox-prev:hover {
            transform: translateX(-2px);
        }

        .cc-lightbox-next {
            right: 28px;
        }

        .cc-lightbox-next:hover {
            transform: translateX(2px);
        }

        .cc-lightbox-counter {
            position: fixed;

            left: 50%;
            bottom: 24px;

            transform: translateX(-50%);

            color: rgba(255, 255, 255, .4);

            font-size: 11px;
            letter-spacing: .12em;
        }

        /* ============================================================
           RESPONSIVE
           ============================================================ */

        @media (max-width: 900px) {

            .cc-project-hero {
                padding-top: 120px;
            }

            .cc-project-hero-content {
                grid-template-columns: 1fr;

                gap: 35px;
            }

            .cc-project-meta {
                display: grid;
                grid-template-columns: repeat(2, 1fr);

                gap: 0 25px;
            }

            .cc-lightbox-image-wrap {
                width: calc(100vw - 70px);
                height: 78vh;
            }

            .cc-lightbox-prev {
                left: 12px;
            }

            .cc-lightbox-next {
                right: 12px;
            }
        }

        @media (max-width: 640px) {

            .cc-project-container {
                width: min(100% - 28px, 1180px);
            }

            .cc-project-hero {
                padding-top: 105px;
                padding-bottom: 55px;
            }

            .cc-project-hero-content {
                margin-top: 45px;
            }

            .cc-project-title {
                font-size: 43px;
            }

            .cc-project-summary {
                font-size: 14px;
            }

            .cc-project-meta {
                grid-template-columns: 1fr;
            }

            .cc-project-gallery {
                padding-bottom: 80px;
            }

            .cc-project-gallery-header {
                align-items: start;
            }

            .cc-project-gallery-title {
                font-size: 23px;
            }

            .cc-project-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }

            .cc-project-card {
                aspect-ratio: 16 / 11;
                border-radius: 12px;
            }

            .cc-project-card:nth-child(3n) {
                grid-column: span 1;
                aspect-ratio: 16 / 11;
            }

            .cc-project-card-number {
                opacity: 1;
                transform: none;
            }

            .cc-project-cta {
                padding-bottom: 80px;
            }

            .cc-project-cta-box {
                padding: 40px 25px;
                border-radius: 18px;
            }

            .cc-project-cta-title {
                font-size: 34px;
            }

            .cc-lightbox {
                padding: 15px;
            }

            .cc-lightbox-image-wrap {
                width: calc(100vw - 30px);
                height: 75vh;
            }

            .cc-lightbox-close {
                top: 15px;
                right: 15px;

                width: 40px;
                height: 40px;
            }

            .cc-lightbox-nav {
                top: auto;
                bottom: 18px;

                width: 42px;
                height: 42px;

                margin: 0;
            }

            .cc-lightbox-prev {
                left: 20px;
            }

            .cc-lightbox-next {
                right: 20px;
            }

            .cc-lightbox-counter {
                bottom: 32px;
            }
        }
    </style>
@endpush


@section('content')

    @php
        $images = $work->image_urls;
    @endphp


    <main class="cc-project" x-data="{
        images: @js($images),
        open: false,
        current: 0,
    
        show(index) {
            this.current = index;
            this.open = true;
            document.body.style.overflow = 'hidden';
        },
    
        close() {
            this.open = false;
            document.body.style.overflow = '';
        },
    
        next() {
            if (!this.images.length) return;
    
            this.current =
                (this.current + 1) % this.images.length;
        },
    
        prev() {
            if (!this.images.length) return;
    
            this.current =
                (this.current - 1 + this.images.length) %
                this.images.length;
        }
    }" @keydown.escape.window="close()"
        @keydown.arrow-right.window="open && next()" @keydown.arrow-left.window="open && prev()">

        {{-- ========================================================
             HERO
        ========================================================= --}}

        <section class="cc-project-hero">

            <div class="cc-project-container">

                <a href="{{ request()->routeIs('work.show') ? url('/#work') : url('/projects') }}" class="cc-project-back">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 12H5m7 7-7-7 7-7" />
                    </svg>

                    Back to Projects
                </a>


                <div class="cc-project-hero-content">

                    <div>

                        @if ($work->category)
                            <div class="cc-project-category">
                                {{ $work->category }}
                            </div>
                        @endif

                        <h1 class="cc-project-title">
                            {{ $work->title }}
                        </h1>

                        @if ($work->description)
                            <p class="cc-project-summary">
                                {{ $work->description }}
                            </p>
                        @endif

                    </div>


                    <div class="cc-project-meta">

                        @if ($work->category)
                            <div class="cc-project-meta-item">

                                <div class="cc-project-meta-label">
                                    Service
                                </div>

                                <div class="cc-project-meta-value">
                                    {{ $work->category }}
                                </div>

                            </div>
                        @endif


                        <div class="cc-project-meta-item">

                            <div class="cc-project-meta-label">
                                Project
                            </div>

                            <div class="cc-project-meta-value">
                                {{ $work->title }}
                            </div>

                        </div>


                        @if ($work->url)
                            <div class="cc-project-meta-item">

                                <div class="cc-project-meta-label">
                                    Live Site
                                </div>

                                <a href="{{ $work->url }}" target="_blank" rel="noopener noreferrer"
                                    class="cc-project-visit">
                                    Visit Website

                                    <span>↗</span>
                                </a>

                            </div>
                        @endif

                    </div>

                </div>

            </div>

        </section>


        {{-- ========================================================
             GALLERY
        ========================================================= --}}

        <section class="cc-project-gallery">

            <div class="cc-project-container">

                <div class="cc-project-gallery-header">

                    <div>

                        <div class="cc-project-eyebrow">
                            Project Showcase
                        </div>

                        <h2 class="cc-project-gallery-title">
                            A closer look
                        </h2>

                    </div>

                    @if (count($images))
                        <div class="cc-project-gallery-count">
                            {{ count($images) }}
                            {{ Str::plural('image', count($images)) }}
                        </div>
                    @endif

                </div>


                @if (count($images))

                    <div class="cc-project-grid">

                        @foreach ($images as $index => $image)
                            <button type="button" class="cc-project-card" @click="show({{ $index }})"
                                aria-label="View image {{ $index + 1 }}">

                                <img src="{{ $image }}" alt="{{ $work->title }} — image {{ $index + 1 }}"
                                    loading="{{ $index === 0 ? 'eager' : 'lazy' }}">

                                <span class="cc-project-card-number">
                                    {{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                            </button>
                        @endforeach

                    </div>
                @else
                    <div class="cc-project-empty">

                        <span>
                            {{ strtoupper(substr($work->title, 0, 1)) }}
                        </span>

                    </div>

                @endif

            </div>

        </section>


        {{-- ========================================================
             CTA
        ========================================================= --}}

        <section class="cc-project-cta">

            <div class="cc-project-container">

                <div class="cc-project-cta-box">

                    <div class="cc-project-cta-content">

                        <div class="cc-project-eyebrow">
                            Have a project in mind?
                        </div>

                        <h2 class="cc-project-cta-title">
                            Let's create something
                            <span>great.</span>
                        </h2>

                        <p class="cc-project-cta-text">
                            Have an idea for your business, website, or next
                            digital experience? Let's turn it into something
                            worth remembering.
                        </p>

                        <a href="{{ url('/#contact') }}" class="cc-project-cta-button">
                            Start a Project
                            <span>→</span>
                        </a>

                    </div>

                </div>

            </div>

        </section>


        {{-- ========================================================
             LIGHTBOX
        ========================================================= --}}

        <template x-teleport="body">

            <div x-show="open" x-cloak x-transition.opacity.duration.200ms class="cc-lightbox" @click.self="close()"
                role="dialog" aria-modal="true" aria-label="Project image viewer">

                {{-- Close --}}
                <button type="button" class="cc-lightbox-close" @click="close()" aria-label="Close image viewer">
                    ×
                </button>


                {{-- Previous --}}
                <button type="button" class="cc-lightbox-nav cc-lightbox-prev" @click.stop="prev()"
                    x-show="images.length > 1" aria-label="Previous image">
                    ‹
                </button>


                {{-- Image --}}
                <div class="cc-lightbox-image-wrap">

                    <img :src="images[current]" :alt="'{{ addslashes($work->title) }} — image ' + (current + 1)"
                        class="cc-lightbox-image">

                </div>


                {{-- Next --}}
                <button type="button" class="cc-lightbox-nav cc-lightbox-next" @click.stop="next()"
                    x-show="images.length > 1" aria-label="Next image">
                    ›
                </button>


                {{-- Counter --}}
                <div class="cc-lightbox-counter" x-text="(current + 1) + ' / ' + images.length"></div>

            </div>

        </template>

    </main>


@endsection
