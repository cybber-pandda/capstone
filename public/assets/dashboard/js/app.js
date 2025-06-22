"use strict";

(function () {
    // Root css-variable value
    const getCssVariableValue = function (variableName) {
        let hex = getComputedStyle(document.documentElement).getPropertyValue(
            variableName
        );
        if (hex && hex.length > 0) {
            hex = hex.trim();
        }
        return hex;
    };

    // Global variables
    window.config = {
        colors: {
            primary: getCssVariableValue("--bs-primary"),
            secondary: getCssVariableValue("--bs-secondary"),
            success: getCssVariableValue("--bs-success"),
            info: getCssVariableValue("--bs-info"),
            warning: getCssVariableValue("--bs-warning"),
            danger: getCssVariableValue("--bs-danger"),
            light: getCssVariableValue("--bs-light"),
            dark: getCssVariableValue("--bs-dark"),
            gridBorder: "rgba(77, 138, 240, .15)",
        },
        fontFamily: "'Roboto', Helvetica, sans-serif",
    };

    const body = document.body;
    const sidebar = document.querySelector(".sidebar");
    const sidebarBody = document.querySelector(".sidebar .sidebar-body");
    const horizontalMenu = document.querySelector(".horizontal-menu");

    // Initializing bootstrap tooltip
    const tooltipTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="tooltip"]')
    );
    const tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Initializing bootstrap popover
    const popoverTriggerList = [].slice.call(
        document.querySelectorAll('[data-bs-toggle="popover"]')
    );
    const popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });

    // Applying perfect-scrollbar
    if (document.querySelector(".sidebar .sidebar-body")) {
        const sidebarBodyScroll = new PerfectScrollbar(".sidebar-body");
    }

    // Sidebar toggle to sidebar-folded
    const sidebarTogglers = document.querySelectorAll(".sidebar-toggler");
    // there are two sidebar togglers.
    // 1: on sidebar - for min-width 992px (laptop, desktop)
    // 2: on navbar - for max-width 991px (mobile phone, tablet)
    if (sidebarTogglers.length) {
        sidebarTogglers.forEach((toggler) => {
            toggler.addEventListener("click", function (e) {
                e.preventDefault();
                document
                    .querySelector(".sidebar .sidebar-toggler")
                    .classList.toggle("active");
                if (window.matchMedia("(min-width: 992px)").matches) {
                    body.classList.toggle("sidebar-folded");
                } else if (window.matchMedia("(max-width: 991px)").matches) {
                    body.classList.toggle("sidebar-open");
                }
            });
        });

        // To avoid layout issues, remove body and toggler classes on window resize.
        window.addEventListener(
            "resize",
            function (event) {
                body.classList.remove("sidebar-folded", "sidebar-open");
                document
                    .querySelector(".sidebar .sidebar-toggler")
                    .classList.remove("active");
            },
            true
        );
    }

    //  sidebar-folded on min-width:992px and max-width: 1199px (in lg only not in xl)
    // Warning!!! this results apex chart width issue
    //
    // const desktopMedium = window.matchMedia('(min-width:992px) and (max-width: 1199px)');
    // function iconSidebar() {
    //   if (desktopMedium.matches) {
    //     body.classList.add('sidebar-folded');
    //   } else {
    //     body.classList.remove('sidebar-folded');
    //   }
    // }
    // window.addEventListener('resize', iconSidebar)
    // iconSidebar();

    // Add "active" class to nav-link based on url dynamically
    function addActiveClass(element) {
        const current = window.location.pathname.replace(/\/+$/, ""); // Normalize trailing slash

        // Get parents of the 'el' with a selector (class, id, etc..)
        function getParents(el, selector) {
            const parents = [];
            while ((el = el.parentNode) && el !== document) {
                if (!selector || el.matches(selector)) parents.push(el);
            }
            return parents;
        }

        const href = element.getAttribute("href");

        // Skip if href is empty or starts with #
        if (!href || href === "#" || href.startsWith("javascript")) return;

        const linkPath = new URL(href, window.location.origin).pathname.replace(
            /\/+$/,
            ""
        );

        if (
            linkPath === current ||
            (current === "/" && linkPath.includes("index.html"))
        ) {
            const elParents = getParents(element, ".nav-item");
            if (elParents.length) {
                elParents[elParents.length - 1].classList.add("active");
            }

            if (getParents(element, ".sub-menu").length) {
                const collapse = element.closest(".collapse");
                if (collapse) collapse.classList.add("show");
                element.classList.add("active");
            }

            if (getParents(element, ".submenu-item").length) {
                element.classList.add("active");
                const activeParent = element.closest(
                    ".nav-item.active .submenu"
                );
                if (activeParent) {
                    element
                        .closest(".nav-item.active")
                        .classList.add("show-submenu");
                }
            }
        }
    }

    // current url [Eg: dashboard.html]
    const current = location.pathname
        .split("")
        .slice(-1)[0]
        .replace(/^\/|\/$/g, "");

    if (sidebar) {
        const sidebarNavLinks = document.querySelectorAll(".sidebar .nav li a");
        sidebarNavLinks.forEach((navLink) => {
            addActiveClass(navLink);
        });
    }

    if (horizontalMenu) {
        const navbarNavLinks = document.querySelectorAll(
            ".horizontal-menu .nav li a"
        );
        navbarNavLinks.forEach((navLink) => {
            addActiveClass(navLink);
        });
    }

    // Open & fold sidebar-folded on mouse enter and leave
    if (sidebarBody) {
        sidebarBody.addEventListener("mouseenter", function () {
            if (body.classList.contains("sidebar-folded")) {
                body.classList.add("open-sidebar-folded");
            }
        });

        sidebarBody.addEventListener("mouseleave", function () {
            if (body.classList.contains("sidebar-folded")) {
                body.classList.remove("open-sidebar-folded");
            }
        });
    }

    // Close sidebar on click outside in phone/tablet
    const mainWrapper = document.querySelector(".main-wrapper");
    if (sidebar) {
        document.addEventListener("touchstart", function (e) {
            if (
                e.target === mainWrapper &&
                body.classList.contains("sidebar-open")
            ) {
                body.classList.remove("sidebar-open");
                document
                    .querySelector(".sidebar .sidebar-toggler")
                    .classList.remove("active");
            }
        });
    }

    // Horizontal menu in small screen devices (mobile/tablet)
    if (horizontalMenu) {
        const horizontalMenuToggleButton = document.querySelector(
            '[data-toggle="horizontal-menu-toggle"]'
        );
        const bottomNavbar = document.querySelector(
            ".horizontal-menu .bottom-navbar"
        );
        if (horizontalMenuToggleButton) {
            horizontalMenuToggleButton.addEventListener("click", function () {
                bottomNavbar.classList.toggle("header-toggled");
                horizontalMenuToggleButton.classList.toggle("open");
                body.classList.toggle("header-open"); // used for creating backdrop
            });

            // To avoid layout issues, remove body and toggler classes on window resize.
            window.addEventListener(
                "resize",
                function (event) {
                    bottomNavbar.classList.remove("header-toggled");
                    horizontalMenuToggleButton.classList.remove("open");
                    body.classList.remove("header-open");
                },
                true
            );
        }
    }

    // Horizontal menu nav-item click submenu show/hide on mobile/tablet
    if (horizontalMenu) {
        const navItems = document.querySelectorAll(
            ".horizontal-menu .page-navigation >.nav-item"
        );
        if (window.matchMedia("(max-width: 991px)").matches) {
            navItems.forEach(function (navItem) {
                navItem.addEventListener("click", function () {
                    if (!this.classList.contains("show-submenu")) {
                        navItems.forEach(function (navItem) {
                            navItem.classList.remove("show-submenu");
                        });
                    }
                    this.classList.toggle("show-submenu");
                });
            });
        }
    }

    // Horizontal menu fixed on scroll on Demo2
    if (horizontalMenu) {
        window.addEventListener("scroll", function () {
            if (window.matchMedia("(min-width: 992px)").matches) {
                if (window.scrollY >= 60) {
                    horizontalMenu.classList.add("fixed-on-scroll");
                } else {
                    horizontalMenu.classList.remove("fixed-on-scroll");
                }
            }
        });
    }

    // Prevent body scrolling while sidebar scroll
    //
    // if (sidebarBody) {
    //   sidebarBody.addEventListener('mouseover', function () {
    //     body.classList.add('overflow-hidden');
    //   });
    //   sidebarBody.addEventListener('mouseout', function () {
    //     body.classList.remove('overflow-hidden');
    //   });
    // }

    // Setup clipboard.js plugin (https://github.com/zenorocha/clipboard.js)
    const clipboardButtons = document.querySelectorAll(".btn-clipboard");

    if (clipboardButtons.length) {
        clipboardButtons.forEach((btn) => {
            btn.addEventListener("mouseover", function () {
                this.innerText = "Copy to clipboard";
            });
            btn.addEventListener("mouseout", function () {
                this.innerText = "Copy";
            });
        });

        const clipboard = new ClipboardJS(".btn-clipboard");

        clipboard.on("success", function (e) {
            e.trigger.innerHTML = "Copied";
            setTimeout(function () {
                e.trigger.innerHTML = "Copy";
                e.clearSelection();
            }, 800);
        });
    }

    // Buy Now & Doc buttons [only for server]
    // const buyNowWrapper  = document.createElement('div'),
    //       docLink        = document.createElement('a'),
    //       docLinkIcon    = document.createElement('i'),
    //       buyNowLink     = document.createElement('a'),
    //       buyNowLinkIcon = document.createElement('i'),
    //       docLinkHref    = 'https://nobleui.com/html/documentation/docs.html',
    //       buyNowLinkHref = 'https://1.envato.market/nobleui_html';

    // buyNowWrapper.classList.add('buy-now-wrapper');

    // docLink.classList.add('btn', 'btn-primary');
    // docLink.setAttribute('href', docLinkHref);
    // docLink.setAttribute('target', '_blank');
    // docLinkIcon.classList.add('icon-lg');
    // docLinkIcon.setAttribute('data-lucide', 'file-text');

    // buyNowLink.classList.add('btn', 'btn-danger', 'ms-2');
    // buyNowLink.innerText = 'Buy Now';
    // buyNowLink.setAttribute('href', buyNowLinkHref);
    // buyNowLink.setAttribute('target', '_blank');
    // buyNowLinkIcon.classList.add('icon-lg', 'me-1');
    // buyNowLinkIcon.setAttribute('data-lucide', 'shopping-cart');

    // docLink.append(docLinkIcon);
    // buyNowLink.prepend(buyNowLinkIcon);
    // buyNowWrapper.append(docLink, buyNowLink);
    // body.append(buyNowWrapper);

    // Enable lucide icons with SVG markup
    lucide.createIcons();
})();
