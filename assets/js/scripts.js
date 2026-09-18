/* Lucid Auto Haus — front-end behaviour (no dependencies beyond Bootstrap) */
(function () {
    'use strict';

    /* Sticky header shadow + back-to-top */
    var header = document.getElementById('siteHeader');
    var toTop = document.getElementById('backToTop');
    function onScroll() {
        var y = window.scrollY || window.pageYOffset;
        if (header) header.classList.toggle('is-scrolled', y > 20);
        if (toTop) toTop.classList.toggle('is-visible', y > 600);
    }
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
    if (toTop) toTop.addEventListener('click', function () { window.scrollTo({ top: 0, behavior: 'smooth' }); });

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
                document.body.style.overflow = 'hidden';
            });
            function closeLb() { lightbox.classList.remove('is-open'); document.body.style.overflow = ''; }
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
