<div x-data="{
    htmlIsDark: document.documentElement.classList.contains('dark'),
    observer: null,

    deleteOpen: false,
    deleteId: null,
    deleteTitle: '',

    init() {
        this.observer = new MutationObserver(() => {
            this.htmlIsDark = document.documentElement.classList.contains('dark');
        });

        this.observer.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class'],
        });

        this.$nextTick(() => {
            this.htmlIsDark = document.documentElement.classList.contains('dark');
        });
    },

    get isLightMode() {
        return !this.htmlIsDark;
    },

    get modalModeClass() {
        return this.isLightMode ? 'cc-light-modal' : '';
    },

    openDelete(id, title) {
        this.deleteId = id;
        this.deleteTitle = title;
        this.deleteOpen = true;
    },

    closeDelete() {
        this.deleteOpen = false;
        this.deleteId = null;
        this.deleteTitle = '';
    },
}" :class="{ 'light': isLightMode }"
    class="cc-admin-projects min-h-screen transition-colors duration-300">

    <style>
        /* =========================================================
           PROJECTS ADMIN — FULL CSS
           Dark + Light Mode
           ========================================================= */

        /* =========================================================
           BASE
           ========================================================= */

        .cc-admin-projects {
            --lime: #ADD322;
            --lime-dark: #7fa800;
            --purple: #8b5cf6;
            --violet: #6d28d9;
            --bg: #050408;
            --panel: #0d0b13;
            color: #fff;
            background:
                radial-gradient(900px circle at 8% -5%,
                    rgba(139, 92, 246, .20),
                    transparent 55%),
                radial-gradient(700px circle at 92% 4%,
                    rgba(109, 40, 217, .16),
                    transparent 52%),
                radial-gradient(650px circle at 52% 45%,
                    rgba(91, 33, 182, .075),
                    transparent 65%),
                radial-gradient(500px circle at 15% 100%,
                    rgba(173, 211, 34, .045),
                    transparent 60%),
                linear-gradient(135deg,
                    #030205 0%,
                    #08060d 30%,
                    #0a0711 52%,
                    #06050a 78%,
                    #030305 100%);
            isolation: isolate;
        }

        /* =========================================================
           BACKGROUND
           ========================================================= */

        .cc-admin-projects .noise {
            position: fixed;
            inset: 0;
            z-index: -1;
            pointer-events: none;
            background:
                radial-gradient(550px circle at 25% 18%,
                    rgba(139, 92, 246, .12),
                    transparent 70%),
                radial-gradient(450px circle at 80% 28%,
                    rgba(173, 211, 34, .035),
                    transparent 70%),
                linear-gradient(115deg,
                    transparent 0%,
                    rgba(255, 255, 255, .012) 45%,
                    transparent 70%);
        }

        /* =========================================================
           GLASS
           ========================================================= */

        .cc-admin-projects .glass {
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .045),
                    rgba(255, 255, 255, .018));
            border: 1px solid rgba(255, 255, 255, .075);
            box-shadow:
                0 25px 80px rgba(0, 0, 0, .28),
                inset 0 1px 0 rgba(255, 255, 255, .035);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* =========================================================
           PROJECT CARDS
           ========================================================= */

        .cc-admin-projects .project-card {
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .045),
                    rgba(255, 255, 255, .012));
            border: 1px solid rgba(255, 255, 255, .075);
            box-shadow:
                0 25px 70px rgba(0, 0, 0, .28),
                inset 0 1px 0 rgba(255, 255, 255, .025);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition:
                transform .35s ease,
                border-color .35s ease,
                box-shadow .35s ease;
        }

        .cc-admin-projects .project-card:hover {
            transform: translateY(-5px);
            border-color: rgba(173, 211, 34, .22);
            box-shadow:
                0 35px 90px rgba(0, 0, 0, .42),
                0 0 0 1px rgba(173, 211, 34, .025),
                0 0 50px rgba(139, 92, 246, .035);
        }

        .cc-admin-projects .project-image {
            transition:
                transform .65s cubic-bezier(.2, .7, .2, 1),
                filter .4s ease;
        }

        .cc-admin-projects .project-card:hover .project-image {
            transform: scale(1.045);
            filter: brightness(1.08);
        }

        /* =========================================================
           ACTIONS
           ========================================================= */

        .cc-admin-projects .action-button {
            transition:
                background .2s ease,
                color .2s ease,
                border-color .2s ease,
                transform .2s ease;
        }

        .cc-admin-projects .action-button:hover {
            transform: translateY(-1px);
        }

        /* =========================================================
           INPUTS
           ========================================================= */

        .cc-project-input {
            background:
                linear-gradient(180deg,
                    rgba(255, 255, 255, .028),
                    rgba(255, 255, 255, .012));
            border: 1px solid rgba(255, 255, 255, .075);
            color: white;
            transition:
                border-color .25s ease,
                background .25s ease,
                box-shadow .25s ease;
        }

        .cc-project-input:hover {
            border-color: rgba(255, 255, 255, .13);
            background: rgba(255, 255, 255, .035);
        }

        .cc-project-input:focus {
            border-color: rgba(173, 211, 34, .45);
            background: rgba(0, 0, 0, .28);
            box-shadow:
                0 0 0 3px rgba(173, 211, 34, .045),
                0 10px 35px rgba(0, 0, 0, .15);
            outline: none;
        }

        .cc-project-input::placeholder {
            color: rgba(255, 255, 255, .18);
        }

        .cc-project-input::-webkit-scrollbar {
            width: 5px;
        }

        .cc-project-input::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 999px;
        }

        /* =========================================================
           MODAL
           ========================================================= */

        .cc-modal-backdrop {
            background:
                radial-gradient(circle at 50% 0%,
                    rgba(139, 92, 246, .14),
                    transparent 35%),
                rgba(0, 0, 0, .82);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .cc-modal-panel {
            background:
                linear-gradient(145deg,
                    rgba(20, 17, 28, .98),
                    rgba(8, 7, 12, .99));
            box-shadow:
                0 40px 120px rgba(0, 0, 0, .65),
                0 0 0 1px rgba(255, 255, 255, .035);
        }

        .cc-input {
            background: rgba(0, 0, 0, .24);
            border: 1px solid rgba(255, 255, 255, .09);
            transition:
                border-color .2s ease,
                box-shadow .2s ease,
                background .2s ease;
        }

        .cc-input:focus {
            background: rgba(0, 0, 0, .34);
            border-color: rgba(173, 211, 34, .65);
            box-shadow:
                0 0 0 3px rgba(173, 211, 34, .08);
            outline: none;
        }

        /* =========================================================
           SCROLLBAR
           ========================================================= */

        .cc-scrollbar::-webkit-scrollbar {
            width: 7px;
        }

        .cc-scrollbar::-webkit-scrollbar-track {
            background: transparent;
        }

        .cc-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, .12);
            border-radius: 999px;
        }

        .cc-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, .2);
        }

        /* =========================================================
           STATUS
           ========================================================= */

        .cc-admin-projects .status-dot {
            box-shadow: 0 0 14px currentColor;
        }

        /* =========================================================
           IMAGE PREVIEWS
           ========================================================= */

        .cc-image-preview {
            position: relative;
            overflow: hidden;
            border-radius: .75rem;
            background: #000;
            border: 1px solid rgba(255, 255, 255, .08);
        }

        .cc-image-preview img {
            display: block;
            width: 100%;
            aspect-ratio: 4 / 3;
            object-fit: cover;
        }

        /* =========================================================
           DESCRIPTION PREVIEW
           ========================================================= */

        .cc-admin-projects .description-preview {
            position: relative;
            max-height: 4.5rem;
            overflow: hidden;
            font-size: .875rem;
            line-height: 1.5rem;
            color: #d1d5db;
        }

        .cc-admin-projects .description-preview>* {
            margin-top: 0;
            margin-bottom: .4rem;
        }

        .cc-admin-projects .description-preview>*:last-child {
            margin-bottom: 0;
        }

        .cc-admin-projects .description-preview h1,
        .cc-admin-projects .description-preview h2,
        .cc-admin-projects .description-preview h3,
        .cc-admin-projects .description-preview h4 {
            font-weight: 700;
            color: inherit;
        }

        .cc-admin-projects .description-preview ul,
        .cc-admin-projects .description-preview ol {
            padding-left: 1.25rem;
        }

        .cc-admin-projects .description-preview a {
            color: #ADD322;
            text-decoration: underline;
            text-underline-offset: 2px;
        }

        .cc-admin-projects .description-preview::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 2rem;
            pointer-events: none;
            background: linear-gradient(to bottom,
                    rgba(13, 11, 19, 0),
                    #0d0b13);
        }

        .cc-admin-projects .description-preview::before {
            content: '…';
            position: absolute;
            z-index: 2;
            right: .15rem;
            bottom: -.1rem;
            font-size: 1rem;
            font-weight: 700;
            line-height: 1;
            color: rgba(255, 255, 255, .75);
        }

        /* =========================================================
           LIGHT MODE
           ========================================================= */

        .cc-admin-projects.light {
            color: #171717;
            background:
                radial-gradient(900px circle at 8% -5%,
                    rgba(139, 92, 246, .10),
                    transparent 55%),
                radial-gradient(700px circle at 92% 4%,
                    rgba(109, 40, 217, .07),
                    transparent 52%),
                radial-gradient(650px circle at 52% 45%,
                    rgba(91, 33, 182, .035),
                    transparent 65%),
                radial-gradient(500px circle at 15% 100%,
                    rgba(173, 211, 34, .035),
                    transparent 60%),
                linear-gradient(135deg,
                    #f8f8fa 0%,
                    #f2f1f6 30%,
                    #f7f7f9 55%,
                    #eeeeF2 100%);
        }

        /* =========================================================
           LIGHT MODE — BACKGROUND
           ========================================================= */

        .cc-admin-projects.light .noise {
            background:
                radial-gradient(550px circle at 25% 18%,
                    rgba(139, 92, 246, .055),
                    transparent 70%),
                radial-gradient(450px circle at 80% 28%,
                    rgba(173, 211, 34, .035),
                    transparent 70%),
                linear-gradient(115deg,
                    transparent 0%,
                    rgba(0, 0, 0, .012) 45%,
                    transparent 70%);
        }

        /* =========================================================
           LIGHT MODE — TEXT
           ========================================================= */

        .cc-admin-projects.light .text-white {
            color: #171717 !important;
        }

        .cc-admin-projects.light .text-gray-200 {
            color: #73747a !important;
        }

        .cc-admin-projects.light .text-gray-300 {
            color: #5f6065 !important;
        }

        .cc-admin-projects.light .text-gray-500 {
            color: #77787d !important;
        }

        .cc-admin-projects.light .text-gray-600 {
            color: #686970 !important;
        }

        /* Preserve text over project images */

        .cc-admin-projects.light .project-card .absolute .text-white,
        .cc-admin-projects.light .project-card .relative .text-white,
        .cc-admin-projects.light .project-card .text-white\/40 {
            color: #fff !important;
        }

        /* Modal text */

        .cc-admin-projects.light .cc-modal-panel .text-white {
            color: #171717 !important;
        }

        .cc-admin-projects.light .cc-modal-panel .text-gray-300 {
            color: #5f6065 !important;
        }

        .cc-admin-projects.light .cc-modal-panel .text-gray-200 {
            color: #73747a !important;
        }

        /* =========================================================
           LIGHT MODE — BRAND
           ========================================================= */

        .cc-admin-projects.light .text-\[\#ADD322\] {
            color: #7fa800 !important;
        }

        .cc-admin-projects.light .bg-\[\#ADD322\] {
            color: #111 !important;
        }

        /* =========================================================
           LIGHT MODE — GLASS
           ========================================================= */

        .cc-admin-projects.light .glass {
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .88),
                    rgba(255, 255, 255, .62));
            border-color: rgba(0, 0, 0, .075);
            box-shadow:
                0 20px 60px rgba(0, 0, 0, .07),
                inset 0 1px 0 rgba(255, 255, 255, .95);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        /* =========================================================
           LIGHT MODE — PROJECT CARDS
           ========================================================= */

        .cc-admin-projects.light .project-card {
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .92),
                    rgba(255, 255, 255, .72));
            border-color: rgba(0, 0, 0, .075);
            box-shadow:
                0 20px 55px rgba(0, 0, 0, .08),
                inset 0 1px 0 rgba(255, 255, 255, .95);
        }

        .cc-admin-projects.light .project-card:hover {
            border-color: rgba(127, 168, 0, .25);
            box-shadow:
                0 30px 75px rgba(0, 0, 0, .13),
                0 0 0 1px rgba(127, 168, 0, .035);
        }

        /* =========================================================
           LIGHT MODE — BORDERS
           ========================================================= */

        .cc-admin-projects.light .border-white\/5 {
            border-color: rgba(0, 0, 0, .06) !important;
        }

        .cc-admin-projects.light .border-white\/10 {
            border-color: rgba(0, 0, 0, .09) !important;
        }

        .cc-admin-projects.light .border-white\/15 {
            border-color: rgba(0, 0, 0, .13) !important;
        }

        .cc-admin-projects.light .border-white\/\[0\.06\] {
            border-color: rgba(0, 0, 0, .06) !important;
        }

        .cc-admin-projects.light .border-white\/\[0\.07\] {
            border-color: rgba(0, 0, 0, .075) !important;
        }

        .cc-admin-projects.light .border-white\/\[0\.08\] {
            border-color: rgba(0, 0, 0, .08) !important;
        }

        .cc-admin-projects.light .border-white\/\[0\.09\] {
            border-color: rgba(0, 0, 0, .09) !important;
        }

        /* =========================================================
           LIGHT MODE — WHITE BACKGROUNDS
           ========================================================= */

        .cc-admin-projects.light .bg-white\/5 {
            background-color: rgba(0, 0, 0, .035) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.02\] {
            background-color: rgba(0, 0, 0, .018) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.025\] {
            background-color: rgba(0, 0, 0, .022) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.03\] {
            background-color: rgba(0, 0, 0, .028) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.035\] {
            background-color: rgba(0, 0, 0, .03) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.05\] {
            background-color: rgba(0, 0, 0, .035) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.07\] {
            background-color: rgba(0, 0, 0, .045) !important;
        }

        .cc-admin-projects.light .bg-white\/\[0\.08\] {
            background-color: rgba(0, 0, 0, .05) !important;
        }

        /* =========================================================
           LIGHT MODE — BLACK BACKGROUNDS
           ========================================================= */

        .cc-admin-projects.light .bg-black {
            background-color: #e9e9e9;
        }

        .cc-admin-projects.light .project-card .bg-black,
        .cc-admin-projects.light .project-card .bg-black\/60,
        .cc-admin-projects.light .project-card .bg-black\/70,
        .cc-admin-projects.light .project-card .bg-black\/75,
        .cc-admin-projects.light .project-card .bg-black\/85 {
            color: #fff !important;
        }

        /* =========================================================
           LIGHT MODE — INPUTS
           ========================================================= */

        .cc-admin-projects.light .cc-project-input {
            background:
                linear-gradient(180deg,
                    rgba(255, 255, 255, .88),
                    rgba(248, 248, 250, .9));
            border-color: rgba(0, 0, 0, .10);
            color: #171717;
        }

        .cc-admin-projects.light .cc-project-input:hover {
            border-color: rgba(0, 0, 0, .16);
            background: #fff;
        }

        .cc-admin-projects.light .cc-project-input:focus {
            border-color: rgba(127, 168, 0, .55);
            background: #fff;
            box-shadow:
                0 0 0 3px rgba(127, 168, 0, .08),
                0 10px 35px rgba(0, 0, 0, .08);
        }

        .cc-admin-projects.light .cc-project-input::placeholder {
            color: rgba(0, 0, 0, .35) !important;
        }

        .cc-admin-projects.light input,
        .cc-admin-projects.light textarea,
        .cc-admin-projects.light select {
            color: #171717;
        }

        .cc-admin-projects.light input::placeholder,
        .cc-admin-projects.light textarea::placeholder {
            color: rgba(0, 0, 0, .35) !important;
        }

        /* =========================================================
           LIGHT MODE — CC INPUT
           ========================================================= */

        .cc-admin-projects.light .cc-input {
            background: rgba(255, 255, 255, .75);
            border-color: rgba(0, 0, 0, .10);
            color: #171717;
        }

        .cc-admin-projects.light .cc-input:focus {
            background: #fff;
            border-color: rgba(127, 168, 0, .6);
            box-shadow:
                0 0 0 3px rgba(127, 168, 0, .08);
        }

        /* =========================================================
           LIGHT MODE — DROPDOWNS
           ========================================================= */

        .cc-admin-projects.light [x-show="open"] {
            background: #fff !important;
            color: #171717;
            border-color: rgba(0, 0, 0, .09) !important;
            box-shadow:
                0 25px 60px rgba(0, 0, 0, .14);
        }

        .cc-admin-projects.light [x-show="open"] .text-white {
            color: #171717 !important;
        }

        .cc-admin-projects.light [x-show="open"] .text-gray-300 {
            color: #5f6065 !important;
        }

        .cc-admin-projects.light [x-show="open"] .text-gray-200 {
            color: #73747a !important;
        }

        .cc-admin-projects.light [x-show="open"] .hover\\:bg-white\\/\\[0\\.05\\]:hover {
            background-color: rgba(0, 0, 0, .035) !important;
        }

        /* =========================================================
           LIGHT MODE — IMAGE PLACEHOLDERS
           ========================================================= */

        .cc-admin-projects.light .bg-black {
            background-color: #e9e9e9;
        }

        .cc-admin-projects.light .project-card .bg-black {
            background-color: #000;
        }

        .cc-admin-projects.light .cc-image-preview {
            background: #e9e9e9;
            border-color: rgba(0, 0, 0, .09);
        }

        /* =========================================================
           LIGHT MODE — SCROLLBARS
           ========================================================= */

        .cc-admin-projects.light .cc-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, .14);
        }

        .cc-admin-projects.light .cc-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(0, 0, 0, .22);
        }

        .cc-admin-projects.light .cc-project-input::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, .14);
        }

        /* =========================================================
           LIGHT MODE — DESCRIPTION
           ========================================================= */

        .cc-admin-projects.light .description-preview {
            color: #5f6065;
        }

        .cc-admin-projects.light .description-preview a {
            color: #7fa800;
        }

        .cc-admin-projects.light .description-preview::after {
            background: linear-gradient(to bottom,
                    rgba(255, 255, 255, 0),
                    rgba(255, 255, 255, .92));
        }

        .cc-admin-projects.light .description-preview::before {
            color: rgba(23, 23, 23, .55);
        }

        /* =========================================================
           LIGHT MODE — MODAL
           ========================================================= */

        .cc-admin-projects.light .cc-modal-backdrop {
            background:
                radial-gradient(circle at 50% 0%,
                    rgba(139, 92, 246, .10),
                    transparent 35%),
                rgba(0, 0, 0, .42);
            backdrop-filter: blur(18px);
            -webkit-backdrop-filter: blur(18px);
        }

        .cc-admin-projects.light .cc-modal-panel {
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .99),
                    rgba(247, 247, 249, .99));
            color: #171717;
            border-color: rgba(0, 0, 0, .09);
            box-shadow:
                0 40px 100px rgba(0, 0, 0, .18),
                0 0 0 1px rgba(255, 255, 255, .75);
        }

        .cc-light-modal .cc-modal-panel {
            background:
                linear-gradient(145deg,
                    rgba(255, 255, 255, .99),
                    rgba(247, 247, 249, .99));
            color: #171717;
            border-color: rgba(0, 0, 0, .09);
            box-shadow:
                0 40px 100px rgba(0, 0, 0, .18),
                0 0 0 1px rgba(255, 255, 255, .75);
        }

        .cc-light-modal .cc-modal-panel .text-white {
            color: #171717 !important;
        }

        .cc-light-modal .cc-modal-panel .text-gray-300 {
            color: #5f6065 !important;
        }

        .cc-light-modal .cc-modal-panel .text-gray-200 {
            color: #73747a !important;
        }

        /* =========================================================
           LIGHT MODE — MODAL FOOTER
           ========================================================= */

        .cc-admin-projects.light .cc-modal-panel .bg-black\/10 {
            background-color: rgba(0, 0, 0, .025) !important;
        }

        /* =========================================================
           LIGHT MODE — BUTTON TEXT
           ========================================================= */

        .cc-admin-projects.light button.text-white {
            color: #171717 !important;
        }

        .cc-admin-projects.light .hover\\:text-white:hover {
            color: #171717 !important;
        }

        /* =========================================================
           LIGHT MODE — STATUS
           ========================================================= */

        .cc-admin-projects.light .status-dot {
            color: #7fa800 !important;
            background-color: #7fa800 !important;
        }

        /* =========================================================
           LIGHT MODE — IMAGE OVERLAY TEXT
           ========================================================= */

        .cc-admin-projects.light .project-card .text-white\/40 {
            color: rgba(255, 255, 255, .55) !important;
        }

        /* =========================================================
           MOBILE
           ========================================================= */

        @media (max-width: 640px) {
            .cc-admin-projects .mobile-full {
                border-radius: 0;
            }

            .cc-admin-projects .project-card:hover {
                transform: none;
            }

            .cc-admin-projects .project-card:hover .project-image {
                transform: none;
                filter: none;
            }
        }

        /* =========================================================
           REDUCED MOTION
           ========================================================= */

        @media (prefers-reduced-motion: reduce) {

            .cc-admin-projects *,
            .cc-admin-projects *::before,
            .cc-admin-projects *::after {
                scroll-behavior: auto !important;
                transition-duration: .01ms !important;
                animation-duration: .01ms !important;
                animation-iteration-count: 1 !important;
            }
        }

        /* =========================================================
           ALPINE CLOAK
           ========================================================= */

        [x-cloak] {
            display: none !important;
        }

        /* =========================================================
           CKEDITOR 5
           ========================================================= */

        .cc-project-editor .ck.ck-editor {
            width: 100%;
        }

        .cc-project-editor .ck.ck-toolbar {
            background: rgba(255, 255, 255, .035) !important;
            border: 1px solid rgba(255, 255, 255, .09) !important;
            border-bottom: 0 !important;
            border-radius: .75rem .75rem 0 0 !important;
        }

        .cc-project-editor .ck-editor__editable {
            min-height: 190px;
            max-height: 350px;
            overflow-y: auto;
            background: rgba(255, 255, 255, .018) !important;
            color: #fff !important;
            border: 1px solid rgba(255, 255, 255, .09) !important;
            border-radius: 0 0 .75rem .75rem !important;
            box-shadow: none !important;
        }

        .cc-project-editor .ck-editor__editable.ck-focused {
            border-color: rgba(173, 211, 34, .55) !important;
            box-shadow:
                0 0 0 3px rgba(173, 211, 34, .06) !important;
        }

        .cc-project-editor .ck.ck-button {
            color: #d1d5db !important;
        }

        .cc-project-editor .ck.ck-button:hover,
        .cc-project-editor .ck.ck-button:focus,
        .cc-project-editor .ck.ck-button.ck-on {
            background: rgba(173, 211, 34, .08) !important;
            color: #ADD322 !important;
        }
    </style>

    {{-- =========================================================
         BACKGROUND
    ========================================================= --}}

    <div class="pointer-events-none fixed inset-0 -z-10 noise"></div>

    <div class="pointer-events-none fixed inset-0 -z-10 overflow-hidden">
        <div class="absolute -left-40 -top-40 h-[650px] w-[650px] rounded-full bg-[#8b5cf6]/10 blur-[140px]"></div>

        <div class="absolute right-[-220px] top-[5%] h-[600px] w-[600px] rounded-full bg-[#6d28d9]/10 blur-[150px]">
        </div>

        <div
            class="absolute bottom-[-250px] left-[20%] h-[500px] w-[500px] rounded-full bg-[#ADD322]/[0.025] blur-[150px]">
        </div>
    </div>

    {{-- =========================================================
         CONTENT
    ========================================================= --}}

    <div class="mx-auto max-w-[1500px] px-4 py-6 sm:px-6 lg:px-10 lg:py-10">

        {{-- =========================================================
             HEADER
        ========================================================= --}}

        <header class="mb-8 lg:mb-12">

            <div class="flex flex-col gap-6 sm:flex-row sm:items-end sm:justify-between">

                <div>

                    <div class="mb-4 flex items-center gap-3">

                        <span class="h-2 w-2 rounded-full bg-[#ADD322] status-dot text-[#ADD322]"></span>

                        <span class="text-[11px] font-bold uppercase tracking-[0.3em] text-[#ADD322]">
                            Administration
                        </span>

                    </div>

                    <h1 class="text-4xl font-black tracking-[-0.04em] sm:text-5xl">
                        Projects
                    </h1>

                    <p class="mt-3 max-w-xl text-sm leading-6 text-gray-300 sm:text-base">
                        Manage your portfolio, project imagery, visibility,
                        categories and presentation.
                    </p>

                </div>

                <button type="button" wire:click="create"
                    class="group inline-flex cursor-pointer items-center justify-center gap-3 rounded-2xl bg-[#ADD322] px-5 py-3.5 text-sm font-black text-black shadow-[0_12px_40px_rgba(173,211,34,.12)] transition hover:-translate-y-0.5 hover:shadow-[0_18px_50px_rgba(173,211,34,.2)]">
                    <span class="flex h-6 w-6 items-center justify-center rounded-lg bg-black/10 text-lg">
                        +
                    </span>

                    Add Project
                </button>

            </div>

            {{-- STATS --}}

            <div class="mt-8 grid grid-cols-2 gap-3 sm:grid-cols-4">

                <div class="glass rounded-2xl p-4">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                        Total
                    </div>

                    <div class="mt-2 text-2xl font-black">
                        {{ $works->count() }}
                    </div>
                </div>

                <div class="glass rounded-2xl p-4">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                        Published
                    </div>

                    <div class="mt-2 text-2xl font-black text-[#ADD322]">
                        {{ $works->where('is_published', true)->count() }}
                    </div>
                </div>

                <div class="glass rounded-2xl p-4">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                        Drafts
                    </div>

                    <div class="mt-2 text-2xl font-black text-gray-300">
                        {{ $works->where('is_published', false)->count() }}
                    </div>
                </div>

                <div class="glass rounded-2xl p-4">
                    <div class="text-[10px] font-bold uppercase tracking-[0.2em] text-gray-300">
                        Portfolio
                    </div>

                    <div class="mt-2 text-2xl font-black">
                        {{ $works->sum(fn($work) => count($work->images ?? [])) }}
                    </div>
                </div>

            </div>

        </header>

        {{-- =========================================================
             PROJECT GRID
        ========================================================= --}}

        <section>

            <div class="mb-5 flex items-center justify-between">

                <div>
                    <h2 class="text-lg font-bold tracking-tight">
                        All Projects
                    </h2>

                    <p class="mt-1 text-xs text-gray-300">
                        Your portfolio collection
                    </p>
                </div>

                <span
                    class="rounded-full border border-white/10 bg-white/[0.025] px-3 py-1.5 text-xs font-medium text-gray-300">
                    {{ $works->count() }}
                    {{ \Illuminate\Support\Str::plural('project', $works->count()) }}
                </span>

            </div>

            <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-3">

                @forelse ($works as $work)

                    <article wire:key="work-{{ $work->id }}" class="project-card group overflow-hidden rounded-3xl">

                        {{-- =================================================
                             PROJECT IMAGE
                        ================================================== --}}

                        <div class="relative aspect-[16/10] overflow-hidden bg-black">

                            @if ($work->thumbnail_url)
                                <img src="{{ $work->thumbnail_url }}" alt="{{ $work->title }}"
                                    class="project-image h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center">

                                    <div class="text-center">

                                        <div class="text-3xl text-gray-200">
                                            ◇
                                        </div>

                                        <div class="mt-2 text-xs uppercase tracking-widest text-gray-200">
                                            No image
                                        </div>

                                    </div>

                                </div>
                            @endif

                            <div
                                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-black/10">
                            </div>

                            {{-- STATUS --}}

                            <div class="absolute left-4 top-4">

                                @if ($work->is_published)
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-[#ADD322]/20 bg-black/60 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-[#ADD322] backdrop-blur-md">

                                        <span class="h-1.5 w-1.5 rounded-full bg-[#ADD322]"></span>

                                        Published

                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-black/60 px-3 py-1.5 text-[10px] font-bold uppercase tracking-wider text-gray-300 backdrop-blur-md">

                                        <span class="h-1.5 w-1.5 rounded-full bg-gray-500"></span>

                                        Draft

                                    </span>
                                @endif

                            </div>

                            {{-- IMAGE COUNT --}}

                            @if (count($work->images ?? []))
                                <div
                                    class="absolute right-4 top-4 rounded-full border border-white/10 bg-black/60 px-3 py-1.5 text-[10px] font-bold text-white backdrop-blur-md">

                                    {{ count($work->images) }}

                                    {{ \Illuminate\Support\Str::plural('image', count($work->images)) }}

                                </div>
                            @endif

                            {{-- CATEGORY / POSITION --}}

                            <div class="absolute bottom-4 left-4 right-4 flex items-end justify-between gap-4">

                                <span class="text-[10px] font-black uppercase tracking-[0.2em] text-[#ADD322]">
                                    {{ $work->category }}
                                </span>

                                <span class="text-[10px] font-medium text-white/40">
                                    #{{ str_pad(($work->sort_order ?? 0) + 1, 2, '0', STR_PAD_LEFT) }}
                                </span>

                            </div>

                        </div>

                        {{-- =================================================
                             CONTENT
                        ================================================== --}}

                        <div class="p-5">

                            <h3 class="truncate text-xl font-bold tracking-tight">
                                {{ $work->title }}
                            </h3>

                            @if ($work->description)
                                @php
                                    /*
                                     * CKEditor can save empty paragraphs such as:
                                     *
                                     * <p>&nbsp;</p>
                                     * <p>&nbsp;</p>
                                     * <p>&nbsp;</p>
                                     * <h2>Actual content...</h2>
                                     *
                                     * Remove only the empty paragraphs/br tags at
                                     * the beginning so the preview starts with
                                     * actual content.
                                     */
                                    $description = preg_replace(
                                    '/^(?:\s*(?:<p>\s*(?:(?:&nbsp;|\x{A0}|<br\s*\/@endphp)\s*)*<\/p>|<br\s*\/?>)\s*)+/iu',
    '',
    (string) $work->description,
);
?>

                                <div class="description-preview mt-2">
                                    {!! $description !!}
                                </div>
                            @else
                                <p class="mt-2 text-sm text-gray-200">
                                    No description added.
                                </p>
                            @endif

                            {{-- ACTIONS --}}

                            <div class="mt-5 flex items-center justify-between border-t border-white/[0.06] pt-4">

                                <button type="button" wire:click="togglePublished({{ $work->id }})"
                                    class="action-button cursor-pointer text-xs font-semibold text-gray-300 hover:text-[#ADD322]">
                                    {{ $work->is_published ? 'Unpublish' : 'Publish' }}
                                </button>

                                <div class="flex items-center gap-1">

                                    <button type="button" wire:click="edit({{ $work->id }})"
                                        class="action-button cursor-pointer rounded-lg px-3 py-2 text-xs font-bold text-gray-300 hover:bg-white/5 hover:text-white">
                                        Edit
                                    </button>

                                    <button type="button"
                                        @click.stop="openDelete({{ $work->id }}, @js($work->title))"
                                        class="action-button cursor-pointer rounded-lg px-3 py-2 text-xs font-bold text-red-400/80 hover:bg-red-500/10 hover:text-red-400">
                                        Delete
                                    </button>

                                </div>

                            </div>

                        </div>

                    </article>

                @empty

                    <div class="glass col-span-full rounded-3xl px-6 py-24 text-center">

                        <div
                            class="mx-auto flex h-16 w-16 items-center justify-center rounded-2xl border border-white/10 bg-white/[0.03] text-2xl text-[#ADD322]">
                            +
                        </div>

                        <h2 class="mt-6 text-2xl font-black">
                            Your portfolio is empty
                        </h2>

                        <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-300">
                            Create your first project and start building
                            your portfolio collection.
                        </p>

                        <button type="button" wire:click="create"
                            class="mt-7 rounded-xl bg-[#ADD322] px-5 py-3 text-sm font-black text-black shadow-[0_12px_40px_rgba(173,211,34,.12)] transition hover:-translate-y-0.5 hover:cursor-pointer hover:shadow-[0_18px_50px_rgba(173,211,34,.2)]">
                            Add your first project
                        </button>

                    </div>
                @endforelse

            </div>

        </section>

    </div>

    {{-- =============================================================
         GLOBAL DELETE CONFIRMATION MODAL
    ============================================================= --}}

    <template x-teleport="body">

        <div x-show="deleteOpen" x-cloak x-transition.opacity.duration.200ms
            class="fixed inset-0 z-[100000] flex items-center justify-center overflow-y-auto px-4 py-6"
            :class="modalModeClass" @keydown.escape.window="closeDelete()">

            {{-- BACKDROP --}}

            <div class="absolute inset-0 bg-black/85 backdrop-blur-xl" @click="closeDelete()">
            </div>

            {{-- MODAL --}}

            <div x-show="deleteOpen" x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="translate-y-4 scale-[.96] opacity-0"
                x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-y-0 scale-100 opacity-100"
                x-transition:leave-end="translate-y-2 scale-[.98] opacity-0"
                class="cc-modal-panel relative w-full max-w-md overflow-hidden rounded-[26px] border border-white/[0.09]"
                @click.stop>

                <div
                    class="pointer-events-none absolute -right-20 -top-24 h-64 w-64 rounded-full bg-red-500/[0.08] blur-[80px]">
                </div>

                <div
                    class="pointer-events-none absolute -bottom-24 -left-20 h-56 w-56 rounded-full bg-[#8b5cf6]/[0.07] blur-[80px]">
                </div>

                <div class="relative p-6 sm:p-7">

                    <div
                        class="flex h-14 w-14 items-center justify-center rounded-2xl border border-red-500/15 bg-red-500/[0.07] text-red-400">

                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="1.7">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 6l-1 14a1 1 0 0 1-1 .9H7a1 1 0 0 1-1-.9L5 6" />

                            <path stroke-linecap="round" stroke-linejoin="round" d="M10 11v5M14 11v5" />

                        </svg>

                    </div>

                    <div class="mt-5">

                        <div class="text-[10px] font-bold uppercase tracking-[0.24em] text-red-400/70">
                            Delete project
                        </div>

                        <h3 class="mt-2 text-xl font-black tracking-tight text-white">
                            Are you sure?
                        </h3>

                        <p class="mt-2 text-sm leading-6 text-gray-300">

                            You're about to permanently delete

                            <span class="font-semibold text-white" x-text="deleteTitle">
                            </span>.

                            This action cannot be undone.

                        </p>

                    </div>

                    <div class="mt-5 flex gap-3 rounded-xl border border-red-500/10 bg-red-500/[0.035] px-3.5 py-3">

                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-400/80" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="1.8">

                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4" />

                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 17h.01" />

                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10.3 3.8 2.7 17a2 2 0 0 0 1.73 3h15.14a2 2 0 0 0 1.73-3L13.7 3.8a2 2 0 0 0-3.4 0Z" />

                        </svg>

                        <p class="text-[11px] leading-5 text-gray-300">
                            Any images and project data associated with this
                            project may also be removed.
                        </p>

                    </div>

                    <div class="mt-6 flex flex-col-reverse gap-2.5 sm:flex-row sm:justify-end">

                        <button type="button" @click.stop="closeDelete()"
                            class="h-11 rounded-xl border border-white/[0.08] bg-white/[0.02] px-5 text-xs font-bold text-gray-300 transition hover:border-white/[0.14] hover:bg-white/[0.05] hover:text-white">
                            Cancel
                        </button>

                        <button type="button"
                            @click="
                                if (deleteId) {
                                    const id = deleteId;
                                    closeDelete();
                                    $wire.delete(id);
                                }
                            "
                            wire:loading.attr="disabled"
                            class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-red-500 px-5 text-xs font-black text-white shadow-[0_10px_35px_rgba(239,68,68,.12)] transition hover:-translate-y-0.5 hover:bg-red-400 hover:shadow-[0_15px_45px_rgba(239,68,68,.18)] disabled:cursor-not-allowed disabled:opacity-50">

                            <svg wire:loading.remove class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="1.8">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 6h18" />

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2" />

                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 6l-1 14a1 1 0 0 1-1 .9H7a1 1 0 0 1-1-.9L5 6" />

                            </svg>

                            <span wire:loading.remove>
                                Delete project
                            </span>

                            <span wire:loading>
                                Deleting...
                            </span>

                        </button>

                    </div>

                </div>

            </div>

        </div>

    </template>

    {{-- =============================================================
         CREATE / EDIT MODAL
    ============================================================= --}}

    @if ($showForm)

        <div x-data="{ open: true }" x-show="open" x-cloak x-transition.opacity.duration.200ms
            class="cc-modal-backdrop fixed inset-0 z-[9999] overflow-y-auto px-3 py-4 sm:px-6 sm:py-8"
            @click.self="$wire.cancel()" @keydown.escape.window="$wire.cancel()">

            <div class="flex min-h-full items-center justify-center">

                <div x-show="open" x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="translate-y-4 scale-[.97] opacity-0"
                    x-transition:enter-end="translate-y-0 scale-100 opacity-100"
                    class="cc-modal-panel relative w-full max-w-4xl overflow-hidden rounded-[24px] border border-white/[0.09]"
                    @click.stop>

                    <div
                        class="pointer-events-none absolute left-1/2 top-0 h-32 w-2/3 -translate-x-1/2 bg-[#8b5cf6]/10 blur-[70px]">
                    </div>

                    <div class="pointer-events-none absolute right-10 top-0 h-20 w-32 bg-[#ADD322]/[0.04] blur-[50px]">
                    </div>

                    {{-- HEADER --}}

                    <div
                        class="relative flex items-center justify-between border-b border-white/[0.07] px-5 py-4 sm:px-6">

                        <div class="flex min-w-0 items-center gap-3">

                            <div
                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl border border-[#ADD322]/15 bg-[#ADD322]/[0.07] text-[#ADD322]">

                                @if ($editingId)
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.7">

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 20h9" />

                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M16.5 3.5a2.121 2.121 0 0 1 3 3L8 18l-4 1 1-4L16.5 3.5Z" />

                                    </svg>
                                @else
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="1.7">

                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14" />

                                    </svg>
                                @endif

                            </div>

                            <div class="min-w-0">

                                <div class="text-[9px] font-bold uppercase tracking-[0.24em] text-[#ADD322]">
                                    {{ $editingId ? 'Editing' : 'Portfolio' }}
                                </div>

                                <h2 class="truncate text-base font-bold tracking-tight text-white">
                                    {{ $editingId ? 'Edit project' : 'New project' }}
                                </h2>

                            </div>

                        </div>

                        <button type="button" wire:click="cancel" @click.stop
                            class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-white/[0.08] text-gray-300 transition hover:border-white/15 hover:bg-white/5 hover:text-white">

                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8">

                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6l12 12M18 6 6 18" />

                            </svg>

                        </button>

                    </div>

                    {{-- FORM --}}

                    <form wire:submit="save" class="relative">

                        <div class="cc-scrollbar max-h-[calc(100vh-145px)] overflow-y-auto">

                            <div class="grid lg:grid-cols-[1.15fr_.85fr]">

                                {{-- LEFT SIDE --}}

                                <div class="space-y-5 p-5 sm:p-6 lg:border-r lg:border-white/[0.06]">

                                    {{-- TITLE --}}

                                    <div>

                                        <label class="mb-2 flex items-center justify-between">

                                            <span
                                                class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                                Project title
                                            </span>

                                            <span class="text-[9px] text-gray-200">
                                                Required
                                            </span>

                                        </label>

                                        <input type="text" wire:model="title" placeholder="e.g. Ali Academy CT"
                                            class="cc-project-input h-12 w-full rounded-xl px-4 text-sm font-medium text-white placeholder:text-gray-200">

                                        @error('title')
                                            <p class="mt-1.5 text-[11px] text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>

                                    {{-- CATEGORY --}}

                                    <div x-data="{
                                        open: false,
                                        selected: @entangle('category'),
                                        options: [
                                            'Website Design',
                                            'Website Development',
                                            'Mobile App Development',
                                            'E-Commerce',
                                            'Website Redesign',
                                            'Maintenance & Support',
                                            'Other'
                                        ]
                                    }" class="relative" @click.outside="open = false"
                                        @keydown.escape.window="open = false">

                                        <label
                                            class="mb-2 block text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                            Project type
                                        </label>

                                        <button type="button" @click="open = !open"
                                            class="cc-project-input flex h-12 w-full items-center justify-between rounded-xl px-4 text-left"
                                            :class="open ? 'border-[#ADD322]/40 shadow-[0_0_0_3px_rgba(173,211,34,.05)]' : ''">

                                            <div class="flex min-w-0 items-center gap-3">

                                                <span
                                                    class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-[#ADD322]/[0.07] text-[#ADD322]">

                                                    <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                                        stroke="currentColor" stroke-width="1.8">

                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M4 6h16M4 12h16M4 18h16" />

                                                    </svg>

                                                </span>

                                                <span class="truncate text-sm"
                                                    :class="selected ? 'text-white' : 'text-gray-200'"
                                                    x-text="selected || 'Select project type'">
                                                </span>

                                            </div>

                                            <svg class="h-4 w-4 shrink-0 text-gray-300 transition"
                                                :class="open ? 'rotate-180 text-[#ADD322]' : ''" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="1.8">

                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="m6 9 6 6 6-6" />

                                            </svg>

                                        </button>

                                        <div x-show="open" x-cloak x-transition
                                            class="absolute left-0 right-0 z-[100] mt-2 overflow-hidden rounded-xl border border-white/10 bg-[#111019] p-1.5 shadow-2xl shadow-black/60">

                                            <template x-for="option in options" :key="option">

                                                <button type="button" @click="selected = option; open = false"
                                                    class="flex w-full items-center justify-between rounded-lg px-3 py-2.5 text-left text-xs text-gray-300 transition hover:bg-[#ADD322]/[0.07] hover:text-white"
                                                    :class="selected === option ? 'bg-[#ADD322]/[0.08] text-white' : ''">

                                                    <span x-text="option"></span>

                                                    <template x-if="selected === option">
                                                        <span class="text-[#ADD322]">
                                                            ✓
                                                        </span>
                                                    </template>

                                                </button>

                                            </template>

                                        </div>

                                        @error('category')
                                            <p class="mt-1.5 text-[11px] text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>

                                    {{-- DESCRIPTION --}}

                                    <div>

                                        <label
                                            class="mb-2 block text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                            Description
                                        </label>

                                        <div wire:ignore x-data="projectEditor" class="cc-project-editor">

                                            <div x-ref="editor"></div>

                                        </div>

                                        @error('description')
                                            <p class="mt-1.5 text-[11px] text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>

                                    {{-- URL --}}

                                    <div>

                                        <label
                                            class="mb-2 block text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">

                                            Live URL

                                            <span class="ml-1 normal-case tracking-normal text-gray-200">
                                                optional
                                            </span>

                                        </label>

                                        <div class="relative">

                                            <span
                                                class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-200">
                                                ↗
                                            </span>

                                            <input type="url" wire:model="url" placeholder="https://example.com"
                                                class="cc-project-input h-12 w-full rounded-xl pl-10 pr-4 text-sm text-white placeholder:text-gray-200">

                                        </div>

                                        @error('url')
                                            <p class="mt-1.5 text-[11px] text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                    </div>

                                </div>

                                {{-- RIGHT SIDE --}}

                                <div class="space-y-6 border-t border-white/[0.06] p-5 sm:p-6 lg:border-t-0">

                                    {{-- IMAGE UPLOAD --}}

                                    <div>

                                        <div class="mb-2 flex items-center justify-between">

                                            <label
                                                class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                                Project media
                                            </label>

                                            <span class="text-[9px] text-gray-200">
                                                10MB / image
                                            </span>

                                        </div>

                                        <label
                                            class="group relative flex min-h-[135px] cursor-pointer flex-col items-center justify-center overflow-hidden rounded-2xl border border-dashed border-white/10 bg-white/[0.018] px-5 text-center transition hover:border-[#ADD322]/30 hover:bg-[#ADD322]/[0.025]">

                                            <div
                                                class="absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(173,211,34,.045),transparent_60%)] opacity-0 transition group-hover:opacity-100">
                                            </div>

                                            <div
                                                class="relative flex h-10 w-10 items-center justify-center rounded-xl border border-white/[0.07] bg-white/[0.03] text-gray-300 transition group-hover:border-[#ADD322]/20 group-hover:bg-[#ADD322]/[0.07] group-hover:text-[#ADD322]">

                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.7">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 16V4m0 0L8 8m4-4 4 4" />

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M5 12v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6" />

                                                </svg>

                                            </div>

                                            <span
                                                class="relative mt-3 text-xs font-semibold text-gray-300 group-hover:text-white">
                                                Add project images
                                            </span>

                                            <span class="relative mt-1 text-[10px] text-gray-200">
                                                PNG · JPG · WEBP
                                            </span>

                                            <input type="file" wire:model="images" multiple
                                                accept="image/png,image/jpeg,image/webp" class="hidden">

                                        </label>

                                        <div wire:loading wire:target="images"
                                            class="mt-2 flex items-center gap-2 text-[10px] text-[#ADD322]">

                                            <svg class="h-3.5 w-3.5 animate-spin" viewBox="0 0 24 24" fill="none">

                                                <circle cx="12" cy="12" r="9" stroke="currentColor"
                                                    stroke-width="2" stroke-opacity=".25" />

                                                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2"
                                                    stroke-linecap="round" />

                                            </svg>

                                            Uploading images...

                                        </div>

                                        @error('images')
                                            <p class="mt-1.5 text-[11px] text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                        @error('images.*')
                                            <p class="mt-1.5 text-[11px] text-red-400">
                                                {{ $message }}
                                            </p>
                                        @enderror

                                        {{-- NEW IMAGE PREVIEWS --}}

                                        @if (is_array($images) && count($images))

                                            <div class="mt-4">

                                                <div class="mb-2 flex items-center justify-between">

                                                    <div>

                                                        <div
                                                            class="text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                                            New images
                                                        </div>

                                                        <p class="mt-1 text-[12px] text-gray-200">
                                                            These images will be uploaded when you save.
                                                        </p>

                                                    </div>

                                                    <span
                                                        class="rounded-full border border-[#ADD322]/15 bg-[#ADD322]/[0.05] px-2 py-1 text-[9px] font-bold text-[#ADD322]">
                                                        {{ count($images) }}
                                                    </span>

                                                </div>

                                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">

                                                    @foreach ($images as $index => $image)
                                                        <div wire:key="new-image-{{ $index }}-{{ method_exists($image, 'getClientOriginalName') ? md5($image->getClientOriginalName()) : $index }}"
                                                            class="cc-image-preview group">

                                                            <img src="{{ $image->temporaryUrl() }}"
                                                                alt="New image preview">

                                                            <div
                                                                class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-black/10 opacity-0 transition group-hover:opacity-100">
                                                            </div>

                                                            <span
                                                                class="absolute bottom-2 left-2 rounded-md border border-[#ADD322]/20 bg-black/75 px-1.5 py-1 text-[8px] font-bold uppercase tracking-wider text-[#ADD322] opacity-0 backdrop-blur-md transition group-hover:opacity-100">
                                                                New
                                                            </span>

                                                            <button type="button"
                                                                wire:click="removeNewImage({{ $index }})"
                                                                wire:loading.attr="disabled"
                                                                class="absolute right-2 top-2 z-20 flex h-8 w-8 items-center justify-center rounded-lg border border-red-400/20 bg-black/85 text-red-400 opacity-100 shadow-lg backdrop-blur-md transition hover:border-red-400/50 hover:bg-red-500/20 hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-50"
                                                                title="Remove selected image"
                                                                aria-label="Remove selected image">

                                                                <svg class="h-4 w-4" viewBox="0 0 24 24"
                                                                    fill="none" stroke="currentColor"
                                                                    stroke-width="2">

                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round"
                                                                        d="M6 6l12 12M18 6 6 18" />

                                                                </svg>

                                                            </button>

                                                            <span
                                                                class="absolute bottom-2 right-2 rounded-md bg-black/70 px-1.5 py-1 text-[8px] font-bold text-white/60">
                                                                #{{ $index + 1 }}
                                                            </span>

                                                        </div>
                                                    @endforeach

                                                </div>

                                            </div>

                                        @endif

                                    </div>

                                    {{-- EXISTING IMAGES --}}

                                    @if ($editingId)

                                        @php
                                            $editingWork = $works->firstWhere('id', $editingId);

                                            if (!$editingWork) {
                                                $editingWork = \App\Models\Work::find($editingId);
                                            }

                                            $existingImages = $editingWork?->images ?? [];
                                        @endphp

                                        @if (count($existingImages))

                                            <div>

                                                <div class="mb-2 flex items-center justify-between">

                                                    <div>

                                                        <label
                                                            class="block text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                                            Existing media
                                                        </label>

                                                        <p class="mt-1 text-[12px] text-gray-200">
                                                            Remove images before saving if you no longer want them.
                                                        </p>

                                                    </div>

                                                    <span
                                                        class="rounded-full border border-white/10 bg-white/[0.025] px-2 py-1 text-[9px] font-bold text-gray-300">

                                                        {{ count($existingImages) }}

                                                        {{ \Illuminate\Support\Str::plural('image', count($existingImages)) }}

                                                    </span>

                                                </div>

                                                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">

                                                    @foreach ($existingImages as $image)
                                                        @php
                                                            $removed = in_array($image, $removeImages ?? [], true);

                                                            $imageUrl = \Illuminate\Support\Facades\Storage::disk(
                                                                'public',
                                                            )->url($image);
                                                        @endphp

                                                        <div wire:key="existing-image-{{ md5($image) }}"
                                                            class="cc-image-preview group transition {{ $removed ? 'border-red-500/30 opacity-50' : '' }}">

                                                            <img src="{{ $imageUrl }}"
                                                                class="{{ $removed ? 'grayscale' : '' }}"
                                                                alt="{{ $editingWork?->title ?? 'Project image' }}">

                                                            @if (!$removed)
                                                                <div
                                                                    class="pointer-events-none absolute inset-0 bg-gradient-to-t from-black/75 via-transparent to-black/10 opacity-0 transition group-hover:opacity-100">
                                                                </div>

                                                                <span
                                                                    class="absolute bottom-2 left-2 rounded-md border border-white/10 bg-black/75 px-1.5 py-1 text-[8px] font-bold uppercase tracking-wider text-gray-300 opacity-0 backdrop-blur-md transition group-hover:opacity-100">
                                                                    Existing
                                                                </span>

                                                                <button type="button"
                                                                    wire:click="removeExistingImage(@js($image))"
                                                                    wire:loading.attr="disabled"
                                                                    class="absolute right-2 top-2 z-20 flex h-8 w-8 items-center justify-center rounded-lg border border-red-400/20 bg-black/85 text-red-400 opacity-100 shadow-lg backdrop-blur-md transition hover:border-red-400/50 hover:bg-red-500/20 hover:text-red-300 disabled:cursor-not-allowed disabled:opacity-50 sm:opacity-0 sm:group-hover:opacity-100"
                                                                    title="Remove image"
                                                                    aria-label="Remove existing image">

                                                                    <svg class="h-4 w-4" viewBox="0 0 24 24"
                                                                        fill="none" stroke="currentColor"
                                                                        stroke-width="2">

                                                                        <path stroke-linecap="round"
                                                                            stroke-linejoin="round"
                                                                            d="M6 6l12 12M18 6 6 18" />

                                                                    </svg>

                                                                </button>
                                                            @else
                                                                <div
                                                                    class="absolute inset-0 flex flex-col items-center justify-center bg-black/60 backdrop-blur-[2px]">

                                                                    <div
                                                                        class="flex h-8 w-8 items-center justify-center rounded-lg border border-red-500/20 bg-red-500/[0.08] text-red-400">

                                                                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                                                                            fill="none" stroke="currentColor"
                                                                            stroke-width="1.8">

                                                                            <path stroke-linecap="round"
                                                                                stroke-linejoin="round"
                                                                                d="M6 6l12 12M18 6 6 18" />

                                                                        </svg>

                                                                    </div>

                                                                    <span
                                                                        class="mt-2 text-[8px] font-bold uppercase tracking-[0.12em] text-red-400">
                                                                        Marked for removal
                                                                    </span>

                                                                </div>

                                                                <button type="button"
                                                                    wire:click="restoreExistingImage(@js($image))"
                                                                    wire:loading.attr="disabled"
                                                                    class="absolute inset-x-2 bottom-2 z-20 rounded-lg border border-white/10 bg-black/90 px-2 py-2 text-[9px] font-bold text-white backdrop-blur-md transition hover:border-[#ADD322]/25 hover:bg-[#ADD322]/10 hover:text-[#ADD322]">

                                                                    Restore image

                                                                </button>
                                                            @endif

                                                        </div>
                                                    @endforeach

                                                </div>

                                            </div>

                                        @endif

                                    @endif

                                    {{-- PROJECT OPTIONS --}}

                                    <div class="space-y-5">

                                        {{-- PLACEMENT --}}

                                        <div x-data="{
                                            open: false,
                                            selected: @entangle('sortAfterId')
                                        }" class="relative" @click.outside="open = false"
                                            @keydown.escape.window="open = false">

                                            <label
                                                class="mb-2 block text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                                Project placement
                                            </label>

                                            <button type="button" @click="open = !open"
                                                class="cc-project-input flex min-h-[52px] w-full items-center justify-between rounded-xl px-3.5 py-2.5 text-left"
                                                :class="open ? 'border-[#ADD322]/40 shadow-[0_0_0_3px_rgba(173,211,34,.05)]' :
                                                    ''">

                                                <div class="flex min-w-0 items-center gap-3">

                                                    <span
                                                        class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg border border-[#ADD322]/10 bg-[#ADD322]/[0.06] text-[#ADD322]">

                                                        <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="1.7">

                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M8 6h13M8 12h13M8 18h13" />

                                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                                d="M3 6h.01M3 12h.01M3 18h.01" />

                                                        </svg>

                                                    </span>

                                                    <div class="min-w-0">

                                                        <div class="truncate text-xs font-semibold text-white"
                                                            x-text="
                                                                selected === null ||
                                                                selected === '' ||
                                                                selected === undefined
                                                                    ? 'Place at beginning'
                                                                    : (
                                                                        @js(
    $works
        ->filter(fn($item) => !$editingId || $item->id != $editingId)
        ->mapWithKeys(
            fn($item) => [
                (string) $item->id => 'After: ' . $item->title,
            ],
        )
        ->all(),
)[String(selected)]
                                                                        ?? 'Place at end'
                                                                    )
                                                            ">
                                                        </div>

                                                        <div class="mt-0.5 text-[9px] text-gray-300">
                                                            Choose where this project appears in your portfolio
                                                        </div>

                                                    </div>

                                                </div>

                                                <svg class="h-4 w-4 shrink-0 text-gray-300 transition"
                                                    :class="open ? 'rotate-180 text-[#ADD322]' : ''"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.8">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m6 9 6 6 6-6" />

                                                </svg>

                                            </button>

                                            <div x-show="open" x-cloak x-transition
                                                class="absolute left-0 right-0 z-[100] mt-2 overflow-hidden rounded-2xl border border-white/10 bg-[#111019] shadow-2xl shadow-black/70">

                                                <div class="cc-scrollbar max-h-[320px] overflow-y-auto p-1.5">

                                                    {{-- BEGINNING --}}

                                                    <button type="button" @click="selected = null; open = false"
                                                        class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-[#ADD322]/[0.07]"
                                                        :class="selected === null ||
                                                            selected === '' ||
                                                            selected === undefined ?
                                                            'bg-[#ADD322]/[0.08]' :
                                                            ''">

                                                        <div
                                                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-[#ADD322]/10 bg-[#ADD322]/[0.05]">

                                                            <svg class="h-4 w-4 text-[#ADD322]" viewBox="0 0 24 24"
                                                                fill="none" stroke="currentColor"
                                                                stroke-width="1.7">

                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="M5 12h14" />

                                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                                    d="m12 5 7 7-7 7" />

                                                            </svg>

                                                        </div>

                                                        <div class="min-w-0 flex-1">

                                                            <div class="text-xs font-semibold text-white">
                                                                Place at beginning
                                                            </div>

                                                            <div class="mt-0.5 text-[9px] text-gray-300">
                                                                Show this project first
                                                            </div>

                                                        </div>

                                                        <span
                                                            x-show="
                                                                selected === null ||
                                                                selected === '' ||
                                                                selected === undefined
                                                            "
                                                            class="text-sm text-[#ADD322]">
                                                            ✓
                                                        </span>

                                                    </button>

                                                    {{-- EXISTING PROJECTS --}}

                                                    @php
                                                        $placementWorks = $works
                                                            ->filter(
                                                                fn($item) => !$editingId || $item->id != $editingId,
                                                            )
                                                            ->sortBy([['sort_order', 'asc'], ['id', 'asc']])
                                                            ->values();
                                                    @endphp

                                                    @foreach ($placementWorks as $placementWork)
                                                        <button type="button"
                                                            @click="selected = {{ $placementWork->id }}; open = false"
                                                            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-white/[0.05]"
                                                            :class="String(selected) === '{{ $placementWork->id }}' ?
                                                                'bg-white/[0.055]' :
                                                                ''">

                                                            <div
                                                                class="relative h-10 w-12 shrink-0 overflow-hidden rounded-lg border border-white/[0.08] bg-black">

                                                                @if ($placementWork->thumbnail_url)
                                                                    <img src="{{ $placementWork->thumbnail_url }}"
                                                                        alt="{{ $placementWork->title }}"
                                                                        class="h-full w-full object-cover">
                                                                @else
                                                                    <div
                                                                        class="flex h-full w-full items-center justify-center text-gray-200">

                                                                        <svg class="h-4 w-4" viewBox="0 0 24 24"
                                                                            fill="none" stroke="currentColor"
                                                                            stroke-width="1.5">

                                                                            <rect x="3" y="3" width="18"
                                                                                height="18" rx="2" />

                                                                            <path d="m3 16 5-5 4 4 3-3 6 6" />

                                                                        </svg>

                                                                    </div>
                                                                @endif

                                                            </div>

                                                            <div class="min-w-0 flex-1">

                                                                <div
                                                                    class="truncate text-xs font-semibold text-gray-300 group-hover:text-white">
                                                                    After: {{ $placementWork->title }}
                                                                </div>

                                                                <div
                                                                    class="mt-0.5 flex items-center gap-2 text-[9px] text-gray-300">

                                                                    <span>
                                                                        Position {{ $loop->iteration + 1 }}
                                                                    </span>

                                                                    <span class="text-gray-800">
                                                                        •
                                                                    </span>

                                                                    <span>
                                                                        {{ $placementWork->category }}
                                                                    </span>

                                                                </div>

                                                            </div>

                                                            <span
                                                                x-show="String(selected) === '{{ $placementWork->id }}'"
                                                                class="shrink-0 text-sm text-[#ADD322]">
                                                                ✓
                                                            </span>

                                                        </button>
                                                    @endforeach

                                                    {{-- END --}}

                                                    @if ($placementWorks->count())
                                                        @php
                                                            $lastPlacementWork = $placementWorks->last();
                                                        @endphp

                                                        <div class="my-1 border-t border-white/[0.06]"></div>

                                                        <button type="button"
                                                            @click="selected = {{ $lastPlacementWork->id }}; open = false"
                                                            class="group flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left transition hover:bg-[#ADD322]/[0.05]"
                                                            :class="String(selected) === '{{ $lastPlacementWork->id }}' ?
                                                                'bg-[#ADD322]/[0.07]' :
                                                                ''">

                                                            <div
                                                                class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg border border-white/[0.07] bg-white/[0.025]">

                                                                <svg class="h-4 w-4 text-gray-300 group-hover:text-[#ADD322]"
                                                                    viewBox="0 0 24 24" fill="none"
                                                                    stroke="currentColor" stroke-width="1.7">

                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" d="M5 12h14" />

                                                                    <path stroke-linecap="round"
                                                                        stroke-linejoin="round" d="m12 5 7 7-7 7" />

                                                                </svg>

                                                            </div>

                                                            <div class="min-w-0 flex-1">

                                                                <div class="text-xs font-semibold text-white">
                                                                    Place at end
                                                                </div>

                                                                <div class="mt-0.5 text-[9px] text-gray-300">
                                                                    Show this project after all other projects
                                                                </div>

                                                            </div>

                                                            <span
                                                                x-show="String(selected) === '{{ $lastPlacementWork->id }}'"
                                                                class="text-sm text-[#ADD322]">
                                                                ✓
                                                            </span>

                                                        </button>
                                                    @endif

                                                </div>

                                            </div>

                                            <p class="mt-2 text-[12px] leading-4 text-gray-200">
                                                The project order determines how projects are displayed across your
                                                portfolio.
                                            </p>

                                        </div>

                                        {{-- VISIBILITY --}}

                                        <div>

                                            <label
                                                class="mb-2 block text-[10px] font-bold uppercase tracking-[0.18em] text-gray-300">
                                                Visibility
                                            </label>

                                            <button type="button"
                                                wire:click="$set('is_published', {{ $is_published ? 'false' : 'true' }})"
                                                class="flex h-11 w-full items-center justify-between rounded-xl border px-3.5 transition {{ $is_published ? 'border-[#ADD322]/20 bg-[#ADD322]/[0.05]' : 'border-white/[0.08] bg-white/[0.02]' }}">

                                                <span class="flex items-center gap-2">

                                                    <span
                                                        class="h-1.5 w-1.5 rounded-full {{ $is_published ? 'bg-[#ADD322] shadow-[0_0_10px_#ADD322]' : 'bg-gray-600' }}">
                                                    </span>

                                                    <span
                                                        class="text-[11px] font-semibold {{ $is_published ? 'text-[#ADD322]' : 'text-gray-300' }}">
                                                        {{ $is_published ? 'Published' : 'Draft' }}
                                                    </span>

                                                </span>

                                                <svg class="h-3.5 w-3.5 {{ $is_published ? 'text-[#ADD322]' : 'text-gray-200' }}"
                                                    viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                    stroke-width="1.8">

                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="m9 18 6-6-6-6" />

                                                </svg>

                                            </button>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                        {{-- FOOTER --}}

                        <div
                            class="flex flex-col-reverse gap-2 border-t border-white/[0.07] bg-black/10 px-5 py-4 sm:flex-row sm:items-center sm:justify-between sm:px-6">

                            <button type="button" wire:click="cancel" @click.stop
                                class="rounded-xl px-4 py-2.5 text-xs font-semibold text-gray-300 transition hover:bg-white/5 hover:text-white">
                                Cancel
                            </button>

                            <button type="submit" wire:loading.attr="disabled" wire:target="save"
                                class="group relative inline-flex h-11 items-center justify-center overflow-hidden rounded-xl bg-[#ADD322] px-6 text-xs font-black text-black shadow-[0_10px_30px_rgba(173,211,34,.1)] transition hover:-translate-y-0.5 hover:shadow-[0_15px_40px_rgba(173,211,34,.18)] disabled:cursor-not-allowed disabled:opacity-50">

                                <span class="relative z-10" wire:loading.remove wire:target="save">
                                    {{ $editingId ? 'Save changes' : 'Create project' }}
                                </span>

                                <span wire:loading wire:target="save" class="relative z-10">
                                    Saving...
                                </span>

                                <span
                                    class="absolute inset-0 translate-y-full bg-white/20 transition duration-300 group-hover:translate-y-0">
                                </span>

                            </button>

                        </div>

                    </form>

                </div>

            </div>

        </div>

    @endif

</div>

@script
    <script>
        Alpine.data('projectEditor', () => ({
            editor: null,

            async init() {
                await this.$nextTick();

                if (!window.createProjectEditor) {
                    console.error('Project editor function is unavailable.');
                    return;
                }

                this.editor = await window.createProjectEditor(
                    this.$refs.editor,
                    $wire.description,
                    (html) => {
                        $wire.$set('description', html, false);
                    }
                );
            },

            destroy() {
                if (!this.editor) {
                    return;
                }

                this.editor.destroy();
                this.editor = null;
            },
        }));
    </script>
@endscript
