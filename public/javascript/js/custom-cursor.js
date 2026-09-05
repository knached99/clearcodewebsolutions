(() => {
    'use strict';

    const cursor = document.getElementById('cc-cursor');

    if (!cursor) {
        return;
    }

    const finePointer = window.matchMedia(
        '(hover: hover) and (pointer: fine)'
    );

    if (!finePointer.matches) {
        return;
    }

    /* =========================================================
       POSITION
    ========================================================== */
    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;

    let cursorX = -100;
    let cursorY = -100;

    let magneticX = 0;
    let magneticY = 0;

    let hasPointer = false;

    /* =========================================================
       INTERACTIVE ELEMENTS
    ========================================================== */
    const interactiveSelector = [
        'a',
        'button',
        'select',
        'summary',
        'label',
        '[role="button"]',
        '[onclick]',
        '[wire\\:click]',
        '[wire\\:submit]',
        '[x-on\\:click]',
        '[x-on\\:mousedown]',
        '[data-cursor-hover]',
        '.clearcode-team__flip-card'
    ].join(',');

    /* =========================================================
       TEXT INPUTS
    ========================================================== */
    const textSelector = [
        'textarea',
        '[contenteditable="true"]',
        'input:not([type="button"])',
        'input:not([type="submit"])',
        'input:not([type="checkbox"])',
        'input:not([type="radio"])',
        'input:not([type="range"])'
    ].join(',');

    /* =========================================================
       SET CURSOR POSITION
       Direct positioning = no trailing/lagging animation
    ========================================================== */
    function updateCursorPosition() {
        cursorX = mouseX + magneticX;
        cursorY = mouseY + magneticY;

        cursor.style.transform =
            `translate3d(
                ${cursorX}px,
                ${cursorY}px,
                0
            )`;
    }

    /* =========================================================
       RESET CURSOR WHEN PAGE LOSES FOCUS
    ========================================================== */
    function resetCursor() {
        cursor.classList.remove(
            'is-pointer',
            'is-text',
            'is-clicking',
            'is-ready'
        );

        magneticX = 0;
        magneticY = 0;

        hasPointer = false;

        cursorX = -100;
        cursorY = -100;

        cursor.style.transform =
            'translate3d(-100px, -100px, 0)';
    }

    /* =========================================================
       MOUSE MOVE
    ========================================================== */
    document.addEventListener(
        'mousemove',
        (event) => {
            mouseX = event.clientX;
            mouseY = event.clientY;

            if (!hasPointer) {
                hasPointer = true;

                cursor.classList.add(
                    'is-ready'
                );
            }

            const target =
                event.target instanceof Element
                    ? event.target
                    : null;

            if (!target) {
                updateCursorPosition();
                return;
            }

            /* =================================================
               TEXT INPUT
            ================================================= */
            const textElement =
                target.closest(textSelector);

            if (textElement) {
                cursor.classList.remove(
                    'is-pointer'
                );

                cursor.classList.add(
                    'is-text'
                );

                magneticX *= 0.7;
                magneticY *= 0.7;

                updateCursorPosition();

                return;
            }

            cursor.classList.remove(
                'is-text'
            );

            /* =================================================
               INTERACTIVE / POINTER
            ================================================= */
            const interactiveElement =
                target.closest(
                    interactiveSelector
                );

            if (!interactiveElement) {
                cursor.classList.remove(
                    'is-pointer'
                );

                magneticX *= 0.76;
                magneticY *= 0.76;

                updateCursorPosition();

                return;
            }

            /* =================================================
               SHOW MICKEY GLOVE
            ================================================= */
            cursor.classList.add(
                'is-pointer'
            );

            /* =================================================
               MAGNETIC MOVEMENT
            ================================================= */
            const rect =
                interactiveElement.getBoundingClientRect();

            const centerX =
                rect.left +
                rect.width / 2;

            const centerY =
                rect.top +
                rect.height / 2;

            const offsetX =
                centerX -
                event.clientX;

            const offsetY =
                centerY -
                event.clientY;

            const distance =
                Math.sqrt(
                    offsetX * offsetX +
                    offsetY * offsetY
                );

            const radius =
                Math.max(
                    55,
                    Math.min(
                        120,
                        Math.max(
                            rect.width,
                            rect.height
                        )
                    )
                );

            if (distance < radius) {
                const strength =
                    (
                        1 -
                        distance / radius
                    ) * 0.075;

                magneticX =
                    offsetX * strength;

                magneticY =
                    offsetY * strength;
            } else {
                magneticX *= 0.76;
                magneticY *= 0.76;
            }

            /* =================================================
               INSTANT POSITION UPDATE
            ================================================= */
            updateCursorPosition();
        },
        {
            passive: true
        }
    );

    /* =========================================================
       CLICK
    ========================================================== */
    document.addEventListener(
        'mousedown',
        (event) => {
            /*
             * Explicitly keep the custom cursor active.
             * Prevents the native OS cursor from appearing
             * during the mouse-down event.
             */
            if (hasPointer) {
                cursor.classList.add(
                    'is-ready'
                );
            }

            cursor.classList.add(
                'is-clicking'
            );

            createRipple(
                event.clientX,
                event.clientY
            );
        },
        {
            passive: true
        }
    );

    /* =========================================================
       MOUSE UP
    ========================================================== */
    document.addEventListener(
        'mouseup',
        () => {
            cursor.classList.remove(
                'is-clicking'
            );
        },
        {
            passive: true
        }
    );

    /* =========================================================
       RIPPLE
    ========================================================== */
    function createRipple(x, y) {
        const ripple =
            document.createElement('span');

        ripple.className =
            'cc-cursor-ripple';

        ripple.style.left =
            `${x}px`;

        ripple.style.top =
            `${y}px`;

        document.body.appendChild(
            ripple
        );

        ripple.addEventListener(
            'animationend',
            () => {
                ripple.remove();
            },
            {
                once: true
            }
        );
    }

    /* =========================================================
       TAB / WINDOW FOCUS
    ========================================================== */
    window.addEventListener(
        'blur',
        resetCursor
    );

    document.addEventListener(
        'visibilitychange',
        () => {
            if (document.hidden) {
                resetCursor();
            }
        }
    );

    window.addEventListener(
        'pagehide',
        resetCursor
    );

    window.addEventListener(
        'pageshow',
        resetCursor
    );

    /* =========================================================
       INITIAL STATE
    ========================================================== */
    resetCursor();
})();