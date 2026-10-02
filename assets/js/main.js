(function () {
    'use strict';

    var root = document.documentElement;
    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var canAnimate = !reduceMotion && typeof Element.prototype.animate === 'function';

    function $(selector, scope) { return (scope || document).querySelector(selector); }
    function $$(selector, scope) { return Array.prototype.slice.call((scope || document).querySelectorAll(selector)); }

    /* ------------------------------------------------------------------
       Intro — wait (briefly) for the web font so the headline doesn't
       reflow halfway through its animation.
       ------------------------------------------------------------------ */
    var fontsReady = document.fonts && document.fonts.ready
        ? Promise.race([document.fonts.ready, new Promise(function (r) { setTimeout(r, 700); })])
        : Promise.resolve();

    fontsReady.then(function () {
        requestAnimationFrame(function () {
            root.classList.add('is-ready', 'is-intro');
            setTimeout(function () { root.classList.remove('is-intro'); }, 2400);
        });
    });

    /* ------------------------------------------------------------------
       Header: frosted background once the page scrolls, mobile menu.
       ------------------------------------------------------------------ */
    var header = $('[data-header]');
    var menuToggle = $('.menu-toggle');

    function onScrollHeader() {
        header.classList.toggle('is-scrolled', window.scrollY > 8);
    }
    onScrollHeader();
    window.addEventListener('scroll', onScrollHeader, { passive: true });

    function setMenu(open) {
        header.classList.toggle('menu-open', open);
        menuToggle.setAttribute('aria-expanded', String(open));
        menuToggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    }
    menuToggle.addEventListener('click', function () {
        setMenu(menuToggle.getAttribute('aria-expanded') !== 'true');
    });
    $$('#site-nav a').forEach(function (link) {
        link.addEventListener('click', function () { setMenu(false); });
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && header.classList.contains('menu-open')) {
            setMenu(false);
            menuToggle.focus();
        }
    });

    /* ------------------------------------------------------------------
       Rotating city in the headline.
       ------------------------------------------------------------------ */
    var words = $$('.rotator__word');
    if (words.length > 1 && !reduceMotion) {
        var wordIndex = 0;
        setInterval(function () {
            if (document.hidden) return;
            var leaving = words[wordIndex];
            wordIndex = (wordIndex + 1) % words.length;
            leaving.classList.remove('is-active');
            leaving.classList.add('is-leaving');
            words[wordIndex].classList.add('is-active');
            setTimeout(function () { leaving.classList.remove('is-leaving'); }, 800);
        }, 2800);
    }

    /* ------------------------------------------------------------------
       Hero brand switcher.
       Autoplay timing is driven by the CSS progress-ring animation on the
       "next" button: when the ring completes we move on, and pausing the
       ring (hover, focus, off-screen, hidden tab) pauses the carousel.
       ------------------------------------------------------------------ */
    var hero = $('[data-hero]');
    var brandData = JSON.parse($('#brand-data').textContent);
    var tabs = $$('.brand');
    var cars = $$('.hero-car');
    var tablist = $('[data-brands]');
    var indicator = $('.brands__indicator');
    var nextBtn = $('[data-brands-next]');
    var progress = $('.brands-next__progress');
    var stage = $('#hero-stage');
    var info = $('[data-hero-info]');
    var bookLink = $('[data-book-link]', info);
    var mapSvg = $('.hero__map svg');
    var current = 0;
    var pauseReasons = {};

    function loadCar(i) {
        var img = cars[i] && cars[i].querySelector('img');
        if (img && img.dataset.src) {
            img.src = img.dataset.src;
            img.removeAttribute('data-src');
        }
        return img;
    }

    function placeIndicator(animate) {
        var tab = tabs[current];
        if (!animate) indicator.style.transition = 'none';
        indicator.style.width = tab.offsetWidth + 'px';
        indicator.style.height = tab.offsetHeight + 'px';
        indicator.style.transform = 'translate(' + tab.offsetLeft + 'px,' + tab.offsetTop + 'px)';
        if (!animate) {
            void indicator.offsetWidth;
            indicator.style.transition = '';
        }
    }

    function showCar(i) {
        cars.forEach(function (car, k) {
            if (k === i) {
                clearTimeout(car._leaveTimer);
                car.classList.remove('is-leaving');
                car.classList.add('is-active');
                car.removeAttribute('aria-hidden');
            } else if (car.classList.contains('is-active')) {
                car.classList.remove('is-active');
                car.classList.add('is-leaving');
                car.setAttribute('aria-hidden', 'true');
                car._leaveTimer = setTimeout(function () { car.classList.remove('is-leaving'); }, 750);
            }
        });
    }

    function updateInfo(brand) {
        var fields = $$('[data-field]', info);
        bookLink.href = brand.book;
        bookLink.setAttribute('aria-label', 'Book the ' + brand.name + ' ' + brand.model);
        function fill() {
            fields.forEach(function (el) { el.textContent = brand[el.dataset.field]; });
        }
        if (!canAnimate) { fill(); return; }
        var out = info.animate(
            [{ opacity: 1, transform: 'none' }, { opacity: 0, transform: 'translateY(-8px)' }],
            { duration: 200, easing: 'ease-in', fill: 'forwards' }
        );
        out.onfinish = function () {
            fill();
            info.animate(
                [{ opacity: 0, transform: 'translateY(10px)' }, { opacity: 1, transform: 'none' }],
                { duration: 650, easing: 'cubic-bezier(.16,1,.3,1)', delay: 120 }
            );
            out.cancel();
        };
    }

    function restartProgress() {
        nextBtn.classList.remove('is-playing');
        void progress.getBoundingClientRect();
        if (!reduceMotion) nextBtn.classList.add('is-playing');
    }

    function setPaused(reason, paused) {
        if (paused) pauseReasons[reason] = true;
        else delete pauseReasons[reason];
        nextBtn.classList.toggle('is-paused', Object.keys(pauseReasons).length > 0);
    }

    function select(i, fromUser) {
        i = (i + tabs.length) % tabs.length;
        if (i === current) return;
        current = i;

        tabs.forEach(function (tab, k) {
            var on = k === i;
            tab.classList.toggle('is-active', on);
            tab.setAttribute('aria-selected', String(on));
            tab.tabIndex = on ? 0 : -1;
        });
        stage.setAttribute('aria-labelledby', tabs[i].id);
        placeIndicator(true);

        // keep the active logo in view when the row scrolls (mobile)
        if (tablist.scrollWidth > tablist.clientWidth) {
            tablist.scrollTo({
                left: tabs[i].offsetLeft - (tablist.clientWidth - tabs[i].offsetWidth) / 2,
                behavior: reduceMotion ? 'auto' : 'smooth'
            });
        }

        hero.style.setProperty('--accent', brandData[i].color);
        if (mapSvg) {
            mapSvg.style.transform = 'translate3d(' + (-i * 16) + 'px,' + (i % 2 ? -10 : 8) + 'px,0) scale(1.08)';
        }

        var img = loadCar(i);
        if (img && !img.complete && img.decode) {
            var target = i;
            img.decode().then(function () { if (current === target) showCar(target); }, function () { showCar(target); });
        } else {
            showCar(i);
        }

        updateInfo(brandData[i]);
        restartProgress();
        if (fromUser) setPaused('hover', false);
    }

    tabs.forEach(function (tab, k) {
        tab.addEventListener('click', function () { select(k, true); });
    });
    nextBtn.addEventListener('click', function () { select(current + 1, true); });
    progress.addEventListener('animationend', function () { select(current + 1); });

    tablist.addEventListener('keydown', function (e) {
        var keys = { ArrowRight: current + 1, ArrowLeft: current - 1, Home: 0, End: tabs.length - 1 };
        if (!(e.key in keys)) return;
        e.preventDefault();
        select(keys[e.key], true);
        tabs[current].focus();
    });

    // swipe the car on touch screens
    var touchX = null;
    stage.addEventListener('touchstart', function (e) { touchX = e.touches[0].clientX; }, { passive: true });
    stage.addEventListener('touchend', function (e) {
        if (touchX === null) return;
        var dx = e.changedTouches[0].clientX - touchX;
        if (Math.abs(dx) > 45) select(current + (dx < 0 ? 1 : -1), true);
        touchX = null;
    });

    // pause the carousel while people look at it or it can't be seen
    var brandsBar = $('.brands-bar');
    [brandsBar, $('.hero__visual')].forEach(function (el) {
        el.addEventListener('mouseenter', function () { setPaused('hover', true); });
        el.addEventListener('mouseleave', function () { setPaused('hover', false); });
    });
    brandsBar.addEventListener('focusin', function () { setPaused('focus', true); });
    brandsBar.addEventListener('focusout', function (e) {
        if (!brandsBar.contains(e.relatedTarget)) setPaused('focus', false);
    });
    document.addEventListener('visibilitychange', function () { setPaused('hidden', document.hidden); });
    if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
            setPaused('offscreen', !entries[0].isIntersecting);
        }, { threshold: 0.25 }).observe(hero);
    }

    hero.style.setProperty('--accent', brandData[0].color);
    placeIndicator(false);
    fontsReady.then(function () { placeIndicator(false); });
    window.addEventListener('resize', function () { placeIndicator(false); });
    setTimeout(restartProgress, 1800);

    // fetch the other cars once the page has settled so switching is instant
    window.addEventListener('load', function () {
        (window.requestIdleCallback || setTimeout)(function () {
            cars.forEach(function (_, k) { loadCar(k); });
        });
    });

    if (reduceMotion) {
        $$('.hero__map svg').forEach(function (svg) { if (svg.pauseAnimations) svg.pauseAnimations(); });
    }

    /* ------------------------------------------------------------------
       Reveal on scroll.
       ------------------------------------------------------------------ */
    var revealables = $$('[data-reveal]');
    if ('IntersectionObserver' in window && !reduceMotion) {
        var revealer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                revealer.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.15 });
        revealables.forEach(function (el) { revealer.observe(el); });
    } else {
        revealables.forEach(function (el) { el.classList.add('is-visible'); });
    }

    /* ------------------------------------------------------------------
       Parallax — positive speeds move faster than the page, negative
       slower. Measured on the parent so our own transform isn't counted.
       ------------------------------------------------------------------ */
    var parallaxEls = $$('[data-parallax]');
    if (parallaxEls.length && !reduceMotion) {
        var ticking = false;
        var updateParallax = function () {
            ticking = false;
            var vh = window.innerHeight;
            parallaxEls.forEach(function (el) {
                var box = el.parentElement.getBoundingClientRect();
                if (box.bottom < -vh || box.top > vh * 2) return;
                var offset = box.top + box.height / 2 - vh / 2;
                el.style.setProperty('--py', (offset * parseFloat(el.dataset.parallax)).toFixed(1) + 'px');
            });
        };
        window.addEventListener('scroll', function () {
            if (!ticking) { ticking = true; requestAnimationFrame(updateParallax); }
        }, { passive: true });
        window.addEventListener('resize', updateParallax);
        updateParallax();
    }

    /* ------------------------------------------------------------------
       Fleet filter: three large + four small cards, "Show all" expands.
       ------------------------------------------------------------------ */
    var grid = $('[data-fleet-grid]');
    var chips = $$('[data-filter]');
    var moreBtn = $('[data-fleet-more]');
    var moreLabel = $('[data-fleet-more-label]');
    var cards = $$('.car-card', grid);
    var LIMIT = 7;
    var filter = 'all';
    var expanded = false;
    var filterRun = 0;

    function matching() {
        return cards.filter(function (card) {
            return filter === 'all' || card.dataset.types.split(' ').indexOf(filter) !== -1;
        });
    }

    function layoutFleet() {
        var list = matching();
        var shown = expanded ? list : list.slice(0, LIMIT);
        cards.forEach(function (card) {
            card.hidden = shown.indexOf(card) === -1;
            card.classList.toggle('is-large', !expanded && shown.indexOf(card) > -1 && shown.indexOf(card) < 3);
        });
        moreBtn.hidden = list.length <= LIMIT;
        moreBtn.classList.toggle('is-expanded', expanded);
        moreLabel.textContent = expanded ? 'Show less' : 'Show All (' + list.length + ' models)';
        return shown;
    }

    function cardsIn(list) {
        if (!canAnimate) return;
        list.forEach(function (card, k) {
            card.animate(
                [{ opacity: 0, transform: 'translateY(28px) scale(.97)' }, { opacity: 1, transform: 'none' }],
                { duration: 700, delay: k * 60, easing: 'cubic-bezier(.16,1,.3,1)', fill: 'backwards' }
            );
        });
    }

    function refreshFleet() {
        var run = ++filterRun;
        var visible = cards.filter(function (card) { return !card.hidden; });
        if (!canAnimate || !visible.length) { cardsIn(layoutFleet()); return; }
        var outs = visible.map(function (card) {
            return card.animate(
                [{ opacity: 1, transform: 'none' }, { opacity: 0, transform: 'translateY(-10px) scale(.98)' }],
                { duration: 180, easing: 'ease-in', fill: 'forwards' }
            );
        });
        Promise.all(outs.map(function (a) { return a.finished; })).then(function () {
            if (run !== filterRun) return;
            var shown = layoutFleet();
            outs.forEach(function (a) { a.cancel(); });
            cardsIn(shown);
        }, function () { /* superseded by a newer click */ });
    }

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            if (chip.dataset.filter === filter) return;
            filter = chip.dataset.filter;
            expanded = false;
            chips.forEach(function (c) {
                var on = c === chip;
                c.classList.toggle('is-active', on);
                c.setAttribute('aria-pressed', String(on));
            });
            refreshFleet();
        });
    });

    moreBtn.addEventListener('click', function () {
        expanded = !expanded;
        refreshFleet();
        if (!expanded) $('#fleet').scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth' });
    });

    var initialCards = layoutFleet();
    if ('IntersectionObserver' in window && canAnimate) {
        grid.classList.add('is-pending');
        var gridObserver = new IntersectionObserver(function (entries) {
            if (!entries[0].isIntersecting) return;
            gridObserver.disconnect();
            grid.classList.remove('is-pending');
            cardsIn(initialCards);
        }, { threshold: 0.1 });
        gridObserver.observe(grid);
    }

    /* ------------------------------------------------------------------
       Newsletter — front-end only; point the form at your backend later.
       ------------------------------------------------------------------ */
    var form = $('[data-subscribe]');
    var formMsg = $('[data-subscribe-msg]');
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var input = form.elements.email;
        var ok = input.value.trim() !== '' && input.checkValidity();
        formMsg.classList.toggle('is-error', !ok);
        formMsg.textContent = ok ? 'Salamat! You’re on the list.' : 'Please enter a valid e-mail address.';
        if (ok) form.reset();
    });
})();
