// Seed2Greens - AJAX Page Navigation
// Improves navigation by fetching only the <main id="main-content"> of the
// target page and swapping it in, instead of performing a full reload.
// The header/navbar stays persistent. Browser history (pushState/popstate)
// is preserved and critical pages (cart, checkout, auth, admin, ...) keep
// their normal full-page navigation so functionality is never broken.

(function () {
    'use strict';

    // Pages that are safe to load via AJAX (only their <main> content changes).
    var SAFE_PAGES = [
        'index.php', 'products.php', 'category.php', 'about.php',
        'contact.php', 'search.php', 'product.php'
    ];

    // Pages that MUST keep normal (full) navigation to preserve functionality
    // such as cart, checkout, payment, auth, wishlist, admin, receipts, etc.
    var EXCLUDE_PAGES = [
        'cart.php', 'checkout.php', 'login.php', 'register.php', 'logout.php',
        'profile.php', 'orders.php', 'order-details.php', 'wishlist.php'
    ];

    var mainEl = null;
    var isNavigating = false;
    var cache = {};

    function getMain() {
        if (!mainEl) {
            mainEl = document.getElementById('main-content');
        }
        return mainEl;
    }

    function pathInfo(href) {
        var a = document.createElement('a');
        a.href = href;
        var path = a.pathname || '/';
        var segments = path.split('/').filter(Boolean);
        var file = segments.length ? segments[segments.length - 1] : 'index.php';
        return {
            href: a.href,
            pathname: a.pathname,
            file: file,
            search: a.search,
            hash: a.hash,
            isAdmin: /\/admin(\/|$)/.test(a.pathname),
            external: !!a.host && a.host !== window.location.host
        };
    }

    function shouldIntercept(info) {
        if (info.external) return false;
        if (info.isAdmin) return false;
        if (EXCLUDE_PAGES.indexOf(info.file) !== -1) return false;
        if (SAFE_PAGES.indexOf(info.file) === -1) return false;
        return true;
    }

    // Subtle top progress bar (uses the site's existing green) — created on the
    // fly so no existing CSS/layout is touched. Only visible while fetching.
    var bar = null;
    function showLoading() {
        if (!bar) {
            bar = document.createElement('div');
            bar.setAttribute('aria-hidden', 'true');
            bar.style.cssText =
                'position:fixed;top:0;left:0;height:3px;width:0;z-index:9999;' +
                'background:#2e7d32;opacity:0;transition:width .2s ease,opacity .2s ease;' +
                'pointer-events:none;';
            document.body.appendChild(bar);
        }
        bar.style.opacity = '1';
        bar.style.width = '35%';
    }

    function hideLoading() {
        if (!bar) return;
        bar.style.width = '100%';
        setTimeout(function () {
            if (bar) {
                bar.style.opacity = '0';
                bar.style.width = '0';
            }
        }, 150);
    }

    function updateActiveNav(url) {
        var info = pathInfo(url);

        // Map a page to the nav link that should be highlighted.
        var activeFile = info.file;
        if (activeFile === 'product.php' || activeFile === 'search.php') {
            activeFile = 'products.php';
        } else if (activeFile === 'category.php') {
            activeFile = 'products.php';
        }

        document.querySelectorAll('.nav-menu a').forEach(function (link) {
            var rawHref = link.getAttribute('href');
            if (!rawHref || rawHref.charAt(0) === '#') {
                link.classList.remove('active');
                var liSkip = link.closest('li');
                if (liSkip) liSkip.classList.remove('active');
                return;
            }
            var lInfo = pathInfo(rawHref);
            var match = (lInfo.file === activeFile);
            link.classList.toggle('active', match);
            var li = link.closest('li');
            if (li) li.classList.toggle('active', match);
        });
    }

    function runPageScripts() {
        if (window.Seed2Greens && typeof window.Seed2Greens.initContent === 'function') {
            window.Seed2Greens.initContent();
        }
    }

    function swapContent(html, url, push) {
        var doc = new DOMParser().parseFromString(html, 'text/html');
        var newMain = doc.getElementById('main-content');
        if (!newMain) return false;

        var main = getMain();
        if (!main) return false;

        main.innerHTML = newMain.innerHTML;
        if (doc.title) document.title = doc.title;

        updateActiveNav(url);
        window.scrollTo(0, 0);
        runPageScripts();

        if (push !== false) {
            history.pushState({ url: url }, '', url);
        }
        return true;
    }

    function navigate(url, push, isPop) {
        if (isNavigating) return;

        var info = pathInfo(url);
        var current = pathInfo(window.location.href);

        // Same page (ignoring hash): just scroll if there is a hash, else ignore.
        // Skipped for history (popstate) navigations because the browser has
        // already updated location.href, so a real content swap is required.
        if (!isPop && info.file === current.file && info.search === current.search) {
            if (info.hash) {
                var target = document.querySelector(info.hash);
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                    if (push !== false) history.pushState({ url: url }, '', url);
                }
            }
            return;
        }

        isNavigating = true;
        showLoading();

        if (cache[url]) {
            var ok = swapContent(cache[url], url, push);
            hideLoading();
            isNavigating = false;
            if (ok) return;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            // res.url reflects the final URL after any server redirect
            // (e.g. category.php without an id redirects to products.php).
            return res.text().then(function (html) {
                return { html: html, finalUrl: res.url };
            });
        }).then(function (data) {
            cache[url] = data.html;
            var success = swapContent(data.html, data.finalUrl, push);
            hideLoading();
            isNavigating = false;
            if (!success) {
                window.location.href = url;
            }
        }).catch(function () {
            hideLoading();
            isNavigating = false;
            // Fallback to full navigation — never breaks the user.
            window.location.href = url;
        });
    }

    // Intercept internal link clicks.
    document.addEventListener('click', function (e) {
        if (e.defaultPrevented || e.button !== 0) return;
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;

        var link = e.target.closest('a');
        if (!link) return;

        var href = link.getAttribute('href');
        if (!href) return;
        if (link.target && link.target !== '_self') return;
        if (link.hasAttribute('download')) return;
        if (link.hasAttribute('data-no-ajax')) return;
        if (href.charAt(0) === '#') return;

        var info = pathInfo(href);
        if (!shouldIntercept(info)) return;

        e.preventDefault();
        navigate(info.href, true);
    });

    // Back / Forward buttons.
    window.addEventListener('popstate', function () {
        var url = (history.state && history.state.url) ? history.state.url : window.location.href;
        navigate(url, false, true);
    });

    // Invalidate cache when leaving the SPA-like session (e.g. full reload).
    window.addEventListener('beforeunload', function () {
        cache = {};
    });
})();
