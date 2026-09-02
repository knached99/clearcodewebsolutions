document.addEventListener("DOMContentLoaded", function () {

    const navbar = document.getElementById("cc-navbar");
    const toggle = document.getElementById("cc-navbar-toggle");
    const close = document.getElementById("cc-mobile-close");
    const overlay = document.getElementById("cc-mobile-overlay");
    const drawer = document.getElementById("cc-mobile-menu");

    if (!navbar || !toggle) {
        return;
    }


    /* =====================================================
       NAVBAR SCROLL
    ===================================================== */

    function handleScroll() {

        if (window.scrollY > 30) {
            navbar.classList.add("scrolled");
        } else {
            navbar.classList.remove("scrolled");
        }

    }

    window.addEventListener("scroll", handleScroll);

    handleScroll();


    /* =====================================================
       OPEN MENU
    ===================================================== */

    function openMenu() {

        navbar.classList.add("menu-open");

        toggle.setAttribute(
            "aria-expanded",
            "true"
        );

        toggle.setAttribute(
            "aria-label",
            "Close navigation"
        );

        drawer.setAttribute(
            "aria-hidden",
            "false"
        );

        document.body.classList.add(
            "cc-menu-is-open"
        );

    }


    /* =====================================================
       CLOSE MENU
    ===================================================== */

    function closeMenu() {

        navbar.classList.remove("menu-open");

        toggle.setAttribute(
            "aria-expanded",
            "false"
        );

        toggle.setAttribute(
            "aria-label",
            "Open navigation"
        );

        drawer.setAttribute(
            "aria-hidden",
            "true"
        );

        document.body.classList.remove(
            "cc-menu-is-open"
        );

    }


    /* =====================================================
       TOGGLE
    ===================================================== */

    toggle.addEventListener("click", function () {

        if (navbar.classList.contains("menu-open")) {
            closeMenu();
        } else {
            openMenu();
        }

    });


    /* =====================================================
       CLOSE BUTTON
    ===================================================== */

    if (close) {

        close.addEventListener(
            "click",
            closeMenu
        );

    }


    /* =====================================================
       OVERLAY
    ===================================================== */

    if (overlay) {

        overlay.addEventListener(
            "click",
            closeMenu
        );

    }


    /* =====================================================
       MOBILE LINKS
    ===================================================== */

    const mobileLinks =
        drawer.querySelectorAll("a");

    mobileLinks.forEach(function (link) {

        link.addEventListener(
            "click",
            function () {

                closeMenu();

            }
        );

    });


    /* =====================================================
       ESCAPE KEY
    ===================================================== */

    document.addEventListener(
        "keydown",
        function (event) {

            if (
                event.key === "Escape" &&
                navbar.classList.contains("menu-open")
            ) {

                closeMenu();

            }

        }
    );


    /* =====================================================
       RESIZE
    ===================================================== */

    window.addEventListener(
        "resize",
        function () {

            if (
                window.innerWidth > 900 &&
                navbar.classList.contains("menu-open")
            ) {

                closeMenu();

            }

        }
    );

});