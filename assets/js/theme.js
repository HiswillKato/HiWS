/* The colour and the small behaviours every page shares.

   The theme itself is chosen before the first paint by the tiny script in the
   head. This file only wires the buttons, follows the system while the visitor
   has not chosen, and drives the back-to-top button and the reveal animation.
   Every block checks first that its element is on the page. */

(function () {
    'use strict';

    var KEY = 'hiws-theme';
    var root = document.documentElement;

    /* The stored choice, or null when the visitor has never picked one — which
       is when the operating system gets to decide. */
    var stored = function () {
        try {
            return localStorage.getItem(KEY);
        } catch (error) {
            return null;
        }
    };

    var paint = function (theme) {
        root.setAttribute('data-theme', theme);
        root.setAttribute('data-bs-theme', theme);
    };

    /* Both copies of the switch — the one beside the toggler and the one inside
       the menu — are on the page at once, so they are set together. */
    var toggles = document.querySelectorAll('.theme-toggle');

    var reflect = function () {
        var dark = root.getAttribute('data-theme') === 'dark';

        toggles.forEach(function (toggle) {
            var moon = toggle.querySelector('.fa-moon');
            var sun = toggle.querySelector('.fa-sun');

            if (moon) { moon.style.display = dark ? '' : 'none'; }
            if (sun) { sun.style.display = dark ? 'none' : ''; }

            toggle.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
        });
    };

    toggles.forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            var theme = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';

            paint(theme);
            reflect();

            try {
                localStorage.setItem(KEY, theme);
            } catch (error) {
                /* Private browsing can block storage. The page still works. */
            }
        });
    });

    reflect();

    /* Until a choice is stored, a change of mind in the system settings still
       reaches the page. Older browsers only offer addListener. */
    if (window.matchMedia) {
        var follow = function (event) {
            if (stored() === null) {
                paint(event.matches ? 'dark' : 'light');
                reflect();
            }
        };
        var media = window.matchMedia('(prefers-color-scheme: dark)');

        if (media.addEventListener) {
            media.addEventListener('change', follow);
        } else if (media.addListener) {
            media.addListener(follow);
        }
    }

    /* Back to top, which appears once the page has been scrolled a while. */
    var top = document.getElementById('top');

    if (top) {
        window.addEventListener('scroll', function () {
            top.hidden = window.scrollY < 450;
        }, { passive: true });

        top.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    /* Reveal on scroll: each marked element fades up the first time it is seen. */
    var reveals = document.querySelectorAll('.reveal');

    if (reveals.length && 'IntersectionObserver' in window) {
        var watcher = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('in');
                    watcher.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });

        reveals.forEach(function (item) { watcher.observe(item); });
    } else {
        reveals.forEach(function (item) { item.classList.add('in'); });
    }
})();
