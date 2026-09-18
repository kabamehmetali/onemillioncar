/* Lucid Auto Haus — front-end behaviour (no dependencies beyond Bootstrap) */
(function () {
    'use strict';

    /* ---------------------------------------------------------------- chrome
       Header scroll state, scroll-progress filament and back-to-top — one
       rAF-batched scroll listener for the whole page. */
    var root = document.documentElement;
    var header = document.getElementById('siteHeader');
    var toTop = document.getElementById('backToTop');
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    var scrollTicking = false;
    var wasScrolled = null;
    var syncDatum = function () {};   /* assigned by the datum block below */
    var datumPin = null;              /* label the datum is parked on while the flyout is open */

    /* One body scroll lock shared by the drawer and the vehicle lightbox, so the
       two can never fight over document.body.style. */
    var lockDepth = 0;
    var lockedY = 0;
    function lockBodyScroll() {
        if (lockDepth++ > 0) return;
        lockedY = window.scrollY || window.pageYOffset || 0;
        document.body.style.position = 'fixed';
        document.body.style.top = (-lockedY) + 'px';
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
    }
    function unlockBodyScroll() {
        if (lockDepth === 0) return;
        if (--lockDepth > 0) return;
        var behaviour = root.style.scrollBehavior;
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        root.style.scrollBehavior = 'auto';      /* defeat html{scroll-behavior:smooth} */
        window.scrollTo(0, lockedY);
        root.style.scrollBehavior = behaviour;
        applyScrollState();
    }

    function applyScrollState() {
        scrollTicking = false;
        if (lockDepth > 0) return;
        var y = window.scrollY || window.pageYOffset || 0;
        var span = Math.max(1, document.documentElement.scrollHeight - window.innerHeight);
        var isScrolled = y > 24;
        if (header) header.style.setProperty('--scroll-progress', String(Math.min(1, Math.max(0, y / span))));
        if (isScrolled !== wasScrolled) {
            wasScrolled = isScrolled;
            root.toggleAttribute('data-scrolled', isScrolled);
            if (header) header.classList.toggle('is-scrolled', isScrolled);
        }
        if (toTop) toTop.classList.toggle('is-visible', y > 600);
    }
    function requestScrollState() {
        if (scrollTicking) return;
        scrollTicking = true;
        requestAnimationFrame(applyScrollState);
    }
    window.addEventListener('scroll', requestScrollState, { passive: true });
    window.addEventListener('resize', requestScrollState, { passive: true });
    applyScrollState();
    if (toTop) {
        toTop.addEventListener('click', function () {
            window.scrollTo({ top: 0, behavior: reduceMotion.matches ? 'auto' : 'smooth' });
        });
    }

    /* ------------------------------------------------------------ nav datum
       One red hairline that travels between items instead of fading in per
       item. Only transform and opacity animate, so it stays on the compositor;
       the width is carried as scaleX of a 100px base. */
    var rail = document.getElementById('navRail');
    if (rail) {
        var activeLabel = rail.querySelector('.nav-rail-link.active .nav-label');

        var moveDatum = function (label) {
            if (!label || !label.getClientRects().length) {
                rail.style.setProperty('--datum-o', '0');
                return;
            }
            var r = label.getBoundingClientRect();
            var railRect = rail.getBoundingClientRect();
            if (!r.width) { rail.style.setProperty('--datum-o', '0'); return; }
            rail.style.setProperty('--datum-x', (r.left - railRect.left).toFixed(2) + 'px');
            rail.style.setProperty('--datum-w', (r.width / 100).toFixed(4));
            rail.style.setProperty('--datum-o', '1');
        };
        syncDatum = function () { moveDatum(datumPin || activeLabel); };

        rail.querySelectorAll('.nav-cell').forEach(function (cell) {
            var label = cell.querySelector('.nav-label');
            cell.addEventListener('mouseenter', function () { moveDatum(label); });
            cell.addEventListener('focusin', function () { moveDatum(label); });
        });
        rail.addEventListener('mouseleave', function () { syncDatum(); });
        rail.addEventListener('focusout', function (ev) {
            if (!rail.contains(ev.relatedTarget)) syncDatum();
        });

        /* Re-place the datum WITHOUT animating it (a resize or a late font swap
           must not look like a hover), then re-arm travel. The forced reflow
           between the two steps is synchronous, so a dropped frame can never
           leave the indicator stranded without transitions. */
        var resyncDatum = function () {
            rail.classList.remove('is-ready');
            syncDatum();
            void rail.offsetWidth;
            rail.classList.add('is-ready');
        };
        resyncDatum();

        var reflow = null;
        var resyncSoon = function () {
            if (reflow) cancelAnimationFrame(reflow);
            reflow = requestAnimationFrame(resyncDatum);
        };
        window.addEventListener('resize', resyncSoon, { passive: true });
        window.addEventListener('orientationchange', resyncSoon);
        if (document.fonts && document.fonts.ready && document.fonts.ready.then) {
            document.fonts.ready.then(resyncDatum).catch(function () {});
        }
    }

    /* --------------------------------------------------------- inventory flyout
       Desktop only. The link still goes to /inventory; a separate chevron button
       owns the disclosure, so the menu is unambiguous to a screen reader and
       reachable from the keyboard. */
    var flyTrigger = document.getElementById('navInventoryTrigger');
    var flyPanel = flyTrigger && document.getElementById(flyTrigger.getAttribute('aria-controls') || '');
    var flyCell = flyTrigger && flyTrigger.closest('.nav-cell');
    if (flyTrigger && flyPanel && flyCell) {
        var flyLabel = flyCell.querySelector('.nav-label');
        var flyCard = flyPanel.querySelector('.flyout-card');
        var flyTrack = flyPanel.querySelector('.nav-flyout-track');
        var wide = window.matchMedia('(min-width: 992px)');
        var openTimer = null;
        var closeTimer = null;

        var placeFlyout = function () {
            if (!flyCard || !flyTrack) return;
            var cell = flyCell.getBoundingClientRect();
            var track = flyTrack.getBoundingClientRect();
            var w = flyCard.offsetWidth;
            var x = (cell.left + cell.width / 2) - track.left - w / 2;
            var max = Math.max(0, track.width - w);
            flyCard.style.setProperty('--fly-x', Math.round(Math.min(Math.max(x, 0), max)) + 'px');
        };
        var flyIsOpen = function () { return flyPanel.classList.contains('is-open'); };
        /* CSS delays `visibility` so the panel can fade out; `inert` takes it out
           of the tab order and the a11y tree straight away. */
        var flySetInert = function (on) {
            if (on) {
                flyPanel.setAttribute('inert', '');
                flyPanel.setAttribute('aria-hidden', 'true');
            } else {
                flyPanel.removeAttribute('inert');
                flyPanel.removeAttribute('aria-hidden');
            }
        };
        flySetInert(true);
        var openFly = function () {
            if (!wide.matches || flyIsOpen()) return;
            clearTimeout(closeTimer);
            placeFlyout();
            flySetInert(false);
            flyPanel.classList.add('is-open');
            flyTrigger.setAttribute('aria-expanded', 'true');
            datumPin = flyLabel;
            syncDatum();
        };
        var closeFly = function (refocus) {
            clearTimeout(openTimer);
            if (!flyIsOpen()) return;
            flyPanel.classList.remove('is-open');
            flyTrigger.setAttribute('aria-expanded', 'false');
            /* move focus out BEFORE inerting, or the browser drops it on <body> */
            if (refocus || flyPanel.contains(document.activeElement)) flyTrigger.focus();
            flySetInert(true);
            datumPin = null;
            syncDatum();
        };
        var openSoon = function () { clearTimeout(closeTimer); clearTimeout(openTimer); openTimer = setTimeout(openFly, 70); };
        var closeSoon = function () { clearTimeout(openTimer); clearTimeout(closeTimer); closeTimer = setTimeout(function () { closeFly(false); }, 190); };

        flyCell.addEventListener('mouseenter', openSoon);
        flyCell.addEventListener('mouseleave', closeSoon);
        if (flyCard) {
            flyCard.addEventListener('mouseenter', function () { clearTimeout(closeTimer); });
            flyCard.addEventListener('mouseleave', closeSoon);
        }

        flyTrigger.addEventListener('click', function (ev) {
            ev.preventDefault();
            clearTimeout(openTimer);
            if (flyIsOpen()) { closeFly(false); } else { openFly(); }
        });
        flyTrigger.addEventListener('keydown', function (ev) {
            if (ev.key === 'ArrowDown') {
                ev.preventDefault();
                openFly();
                var first = flyPanel.querySelector('.flyout-link');
                if (first) first.focus();
            } else if (ev.key === 'Escape' && flyIsOpen()) {
                ev.preventDefault();
                closeFly(true);
            }
        });
        document.addEventListener('keydown', function (ev) {
            if (ev.key !== 'Escape' || !flyIsOpen()) return;
            ev.preventDefault();
            closeFly(flyPanel.contains(document.activeElement) || document.activeElement === flyTrigger);
        });
        document.addEventListener('focusin', function (ev) {
            if (!flyIsOpen()) return;
            if (!flyPanel.contains(ev.target) && !flyCell.contains(ev.target)) closeFly(false);
        });
        document.addEventListener('pointerdown', function (ev) {
            if (!flyIsOpen()) return;
            var inCard = flyCard && flyCard.contains(ev.target);
            if (!inCard && !flyCell.contains(ev.target)) closeFly(false);
        }, true);
        window.addEventListener('resize', function () {
            if (!wide.matches) closeFly(false); else placeFlyout();
        }, { passive: true });
    }

    /* --------------------------------------------------------- mobile drawer
       A right-hand panel: Escape / scrim / link closes it, focus is moved in and
       restored out, the rest of the page is made inert, and the document is
       scroll-locked with the iOS-safe fixed-body technique. */
    var drawer = document.getElementById('navDrawer');
    var drawerToggle = document.getElementById('navToggle');
    var drawerScrim = document.getElementById('navScrim');
    var drawerCloseBtn = document.getElementById('navDrawerClose');
    if (drawer && drawerToggle) {
        var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
        var lastFocused = null;
        var inerted = [];
        var supportsInert = 'inert' in HTMLElement.prototype;

        var drawerFocusables = function () {
            /* a collapsed .drawer-sub is visibility:hidden, so its links are not
               focusable — they must not become the trap's first/last stop. */
            return Array.prototype.filter.call(drawer.querySelectorAll(FOCUSABLE), function (el) {
                if (!el.getClientRects().length) return false;
                var cs = window.getComputedStyle(el);
                return cs.visibility !== 'hidden' && cs.display !== 'none';
            });
        };
        /* inert where supported; aria-hidden alongside it so engines without
           inert (iOS < 15.5) still hide the page from the virtual cursor. */
        var setOutsideInert = function (on) {
            if (on) {
                inerted = [];
                Array.prototype.forEach.call(document.body.children, function (el) {
                    if (el === drawer || el === drawerScrim || el.tagName === 'SCRIPT') return;
                    if (el.hasAttribute('aria-hidden') || el.inert) return;
                    if (supportsInert) el.inert = true;
                    el.setAttribute('aria-hidden', 'true');
                    inerted.push(el);
                });
            } else {
                inerted.forEach(function (el) {
                    if (supportsInert) el.inert = false;
                    el.removeAttribute('aria-hidden');
                });
                inerted = [];
            }
        };
        var drawerIsOpen = function () { return drawer.classList.contains('is-open'); };

        var onDrawerKey = function (ev) {
            if (!drawerIsOpen()) return;
            if (ev.key === 'Escape') { ev.preventDefault(); closeDrawer(true); return; }
            if (ev.key !== 'Tab') return;
            var items = drawerFocusables();
            if (!items.length) { ev.preventDefault(); drawer.focus(); return; }
            var first = items[0];
            var last = items[items.length - 1];
            var inside = drawer.contains(document.activeElement);
            if (ev.shiftKey && (!inside || document.activeElement === first)) { ev.preventDefault(); last.focus(); }
            else if (!ev.shiftKey && (!inside || document.activeElement === last)) { ev.preventDefault(); first.focus(); }
        };

        function openDrawer() {
            if (drawerIsOpen()) return;
            lastFocused = document.activeElement;
            lockBodyScroll();
            drawer.removeAttribute('inert');
            if (drawerScrim) drawerScrim.classList.add('is-open');
            drawer.classList.add('is-open');
            drawerToggle.setAttribute('aria-expanded', 'true');
            drawerToggle.setAttribute('aria-label', 'Close menu');
            /* focus first, then hide the rest — never aria-hide the element that
               currently holds focus. */
            (drawerCloseBtn || drawerFocusables()[0] || drawer).focus({ preventScroll: true });
            setOutsideInert(true);
            document.addEventListener('keydown', onDrawerKey, true);
        }
        function closeDrawer(restoreFocus) {
            if (!drawerIsOpen()) return;
            drawer.classList.remove('is-open');
            if (drawerScrim) drawerScrim.classList.remove('is-open');
            drawerToggle.setAttribute('aria-expanded', 'false');
            drawerToggle.setAttribute('aria-label', 'Open menu');
            setOutsideInert(false);
            unlockBodyScroll();
            document.removeEventListener('keydown', onDrawerKey, true);
            if (restoreFocus !== false) {
                /* lastFocused is <body> when the drawer was opened by a
                   programmatic or touch-without-focus click — never leave focus
                   stranded inside the closed panel. */
                var back = (lastFocused && lastFocused.focus && lastFocused !== document.body) ? lastFocused : drawerToggle;
                back.focus({ preventScroll: true });
            }
            lastFocused = null;
            /* CSS delays `visibility` by the slide duration; inert immediately. */
            drawer.setAttribute('inert', '');
        }

        drawerToggle.addEventListener('click', function () {
            if (drawerIsOpen()) { closeDrawer(true); } else { openDrawer(); }
        });
        if (drawerCloseBtn) drawerCloseBtn.addEventListener('click', function () { closeDrawer(true); });
        if (drawerScrim) drawerScrim.addEventListener('click', function () { closeDrawer(true); });
        drawer.querySelectorAll('a[href]').forEach(function (link) {
            link.addEventListener('click', function () { closeDrawer(false); });
        });

        /* nested inventory disclosure inside the drawer */
        drawer.querySelectorAll('[data-drawer-expand]').forEach(function (btn) {
            var panel = document.getElementById(btn.getAttribute('aria-controls') || '');
            if (!panel) return;
            btn.addEventListener('click', function () {
                var open = btn.getAttribute('aria-expanded') === 'true';
                btn.setAttribute('aria-expanded', open ? 'false' : 'true');
                btn.setAttribute('aria-label', open ? 'Show inventory categories' : 'Hide inventory categories');
                panel.classList.toggle('is-open', !open);
                /* measured, so the list can never be clipped by a fixed cap */
                panel.style.maxHeight = open ? '' : panel.scrollHeight + 'px';
            });
        });

        var wideMq = window.matchMedia('(min-width: 992px)');
        var onWideChange = function (ev) { if (ev.matches) closeDrawer(drawer.contains(document.activeElement)); };
        if (wideMq.addEventListener) wideMq.addEventListener('change', onWideChange);
        else if (wideMq.addListener) wideMq.addListener(onWideChange);

        /* returning via the back button must never leave the body scroll-locked */
        window.addEventListener('pageshow', function (ev) { if (ev.persisted) closeDrawer(false); });
    }

    /* Scroll reveal */
    var revealEls = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && revealEls.length) {
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (en.isIntersecting) { en.target.classList.add('is-visible'); io.unobserve(en.target); }
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
        revealEls.forEach(function (el) { io.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* Vehicle gallery + lightbox */
    var gallery = document.querySelector('[data-gallery]');
    if (gallery) {
        var images = JSON.parse(gallery.getAttribute('data-gallery'));
        var mainImg = gallery.querySelector('.gallery-main img');
        var counter = gallery.querySelector('.gallery-count');
        var thumbs = gallery.querySelectorAll('.gallery-thumbs button');
        var lightbox = document.getElementById('lightbox');
        var lbImg = lightbox ? lightbox.querySelector('img') : null;
        var index = 0;

        function show(i) {
            index = (i + images.length) % images.length;
            mainImg.style.opacity = '0';
            setTimeout(function () {
                mainImg.src = images[index];
                mainImg.style.opacity = '1';
            }, 120);
            if (counter) counter.textContent = (index + 1) + ' / ' + images.length;
            thumbs.forEach(function (t, k) { t.classList.toggle('active', k === index); });
            if (lbImg && lightbox.classList.contains('is-open')) lbImg.src = images[index];
        }
        thumbs.forEach(function (t, k) { t.addEventListener('click', function () { show(k); }); });
        gallery.querySelectorAll('.gallery-main .gallery-nav').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                show(index + (btn.classList.contains('next') ? 1 : -1));
            });
        });
        if (lightbox && lbImg) {
            gallery.querySelector('.gallery-main').addEventListener('click', function () {
                lbImg.src = images[index];
                lightbox.classList.add('is-open');
                lockBodyScroll();
            });
            function closeLb() {
                if (!lightbox.classList.contains('is-open')) return;
                lightbox.classList.remove('is-open');
                unlockBodyScroll();
            }
            lightbox.querySelector('.lightbox-close').addEventListener('click', closeLb);
            lightbox.addEventListener('click', function (e) { if (e.target === lightbox) closeLb(); });
            lightbox.querySelectorAll('.gallery-nav').forEach(function (btn) {
                btn.addEventListener('click', function (e) { e.stopPropagation(); show(index + (btn.classList.contains('next') ? 1 : -1)); });
            });
            document.addEventListener('keydown', function (e) {
                if (!lightbox.classList.contains('is-open')) return;
                if (e.key === 'Escape') closeLb();
                if (e.key === 'ArrowRight') show(index + 1);
                if (e.key === 'ArrowLeft') show(index - 1);
            });
        }
        /* Touch swipe on the main image */
        var startX = null;
        mainImg.addEventListener('touchstart', function (e) { startX = e.touches[0].clientX; }, { passive: true });
        mainImg.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 40) show(index + (dx < 0 ? 1 : -1));
            startX = null;
        });
    }

    /* Payment calculator(s) */
    document.querySelectorAll('[data-calculator]').forEach(function (calc) {
        var price = calc.querySelector('[name="calc_price"]');
        var down = calc.querySelector('[name="calc_down"]');
        var trade = calc.querySelector('[name="calc_trade"]');
        var rate = calc.querySelector('[name="calc_rate"]');
        var term = calc.querySelector('[name="calc_term"]');
        var freq = calc.querySelector('[name="calc_freq"]');
        var tax = calc.querySelector('[name="calc_tax"]');
        var out = calc.querySelector('[data-calc-payment]');
        var outFreq = calc.querySelector('[data-calc-freq]');
        var outFinanced = calc.querySelector('[data-calc-financed]');
        var outInterest = calc.querySelector('[data-calc-interest]');
        var outTotal = calc.querySelector('[data-calc-total]');
        var outTax = calc.querySelector('[data-calc-tax]');

        function money(n) { return '$' + Math.round(n).toLocaleString('en-CA'); }
        function num(el) { return el ? parseFloat(String(el.value).replace(/[^0-9.]/g, '')) || 0 : 0; }
        function update() {
            var p = num(price);
            var taxRate = tax && tax.checked ? 0.13 : 0;
            var taxAmt = p * taxRate;
            var principal = Math.max(0, p + taxAmt - num(down) - num(trade));
            var months = parseInt(term ? term.value : 72, 10) || 72;
            var r = (num(rate) / 100) / 12;
            var monthly = r > 0 ? principal * r / (1 - Math.pow(1 + r, -months)) : principal / months;
            var perYear = { monthly: 12, biweekly: 26, weekly: 52 };
            var f = freq ? freq.value : 'biweekly';
            var payment = monthly * 12 / (perYear[f] || 26);
            var total = monthly * months;
            if (out) out.textContent = money(payment);
            if (outFreq) outFreq.textContent = f === 'monthly' ? 'per month' : (f === 'weekly' ? 'per week' : 'bi-weekly');
            if (outFinanced) outFinanced.textContent = money(principal);
            if (outInterest) outInterest.textContent = money(Math.max(0, total - principal));
            if (outTotal) outTotal.textContent = money(total);
            if (outTax) outTax.textContent = money(taxAmt);
            calc.querySelectorAll('[data-range-out]').forEach(function (o) {
                var src = calc.querySelector('[name="' + o.getAttribute('data-range-out') + '"]');
                if (!src) return;
                var v = num(src);
                o.textContent = o.hasAttribute('data-money') ? money(v) : (o.hasAttribute('data-percent') ? v.toFixed(2) + '%' : v + ' months');
            });
        }
        calc.querySelectorAll('input, select').forEach(function (el) {
            el.addEventListener('input', update);
            el.addEventListener('change', update);
        });
        update();
    });

    /* Inventory: auto-submit selects, keep the page scrolled to the grid */
    var filterForm = document.getElementById('filterForm');
    if (filterForm) {
        filterForm.querySelectorAll('select').forEach(function (sel) {
            sel.addEventListener('change', function () { filterForm.requestSubmit ? filterForm.requestSubmit() : filterForm.submit(); });
        });
        var sortSel = document.getElementById('sortSelect');
        if (sortSel) {
            sortSel.addEventListener('change', function () {
                var url = new URL(window.location.href);
                url.searchParams.set('sort', sortSel.value);
                url.searchParams.delete('page');
                window.location.href = url.toString();
            });
        }
    }

    /* Copy link buttons */
    document.querySelectorAll('[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.getAttribute('data-copy');
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function () {
                    var icon = btn.querySelector('i');
                    if (icon) { icon.className = 'fa-solid fa-check'; setTimeout(function () { icon.className = 'fa-solid fa-link'; }, 1800); }
                });
            }
        });
    });

    /* Bootstrap client-side validation styling */
    document.querySelectorAll('form.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        });
    });

    /* Animated counters */
    var counters = document.querySelectorAll('[data-count]');
    if (counters.length && 'IntersectionObserver' in window) {
        var cio = new IntersectionObserver(function (entries) {
            entries.forEach(function (en) {
                if (!en.isIntersecting) return;
                var el = en.target;
                var target = parseFloat(el.getAttribute('data-count'));
                var suffix = el.getAttribute('data-suffix') || '';
                var decimals = (String(target).split('.')[1] || '').length;
                var start = null;
                function step(ts) {
                    if (!start) start = ts;
                    var p = Math.min(1, (ts - start) / 1400);
                    var eased = 1 - Math.pow(1 - p, 3);
                    var val = target * eased;
                    el.textContent = (decimals ? val.toFixed(decimals) : Math.round(val).toLocaleString('en-CA')) + suffix;
                    if (p < 1) requestAnimationFrame(step);
                }
                requestAnimationFrame(step);
                cio.unobserve(el);
            });
        }, { threshold: 0.5 });
        counters.forEach(function (c) { cio.observe(c); });
    }
})();
