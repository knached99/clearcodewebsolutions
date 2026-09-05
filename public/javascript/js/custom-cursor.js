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

    let cursorX = mouseX;
    let cursorY = mouseY;


    let magneticX = 0;
    let magneticY = 0;


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
       MOUSE MOVE
    ========================================================== */

    document.addEventListener(
        'mousemove',
        (event) => {

            mouseX = event.clientX;
            mouseY = event.clientY;


            const target =
                event.target instanceof Element
                    ? event.target
                    : null;


            if (!target) {
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

            cursor.classList.add(
                'is-clicking'
            );


            createRipple(
                event.clientX,
                event.clientY
            );
        }
    );


    document.addEventListener(
        'mouseup',
        () => {

            cursor.classList.remove(
                'is-clicking'
            );
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
       SMOOTH CURSOR MOVEMENT
    ========================================================== */

    function animate() {

        const targetX =
            mouseX +
            magneticX;


        const targetY =
            mouseY +
            magneticY;


        cursorX +=
            (targetX - cursorX) * 0.28;


        cursorY +=
            (targetY - cursorY) * 0.28;


        cursor.style.transform =
            `translate3d(
                ${cursorX}px,
                ${cursorY}px,
                0
            )`;


        requestAnimationFrame(
            animate
        );
    }


    /* =========================================================
       INITIAL POSITION
    ========================================================== */

    cursorX =
        window.innerWidth / 2;

    cursorY =
        window.innerHeight / 2;

    mouseX =
        cursorX;

    mouseY =
        cursorY;


    animate();

})();