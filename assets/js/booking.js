(function () {
    'use strict';

    function $(selector, scope) { return (scope || document).querySelector(selector); }

    /* -----------------------------------------------------------------
       Budget: two range inputs share one track and can't cross.
       ----------------------------------------------------------------- */
    var range = $('[data-range]');
    if (range) {
        var lo = $('input[name="min"]', range);
        var hi = $('input[name="max"]', range);
        var outLo = $('[data-out="min"]', range);
        var outHi = $('[data-out="max"]', range);
        var floor = Number(lo.min), span = Number(lo.max) - floor;

        var peso = function (n) { return '₱' + Number(n).toLocaleString('en-PH'); };
        var pct = function (n) { return ((n - floor) / span * 100) + '%'; };

        var update = function (moved) {
            if (Number(lo.value) > Number(hi.value)) {
                if (moved === lo) lo.value = hi.value; else hi.value = lo.value;
            }
            range.style.setProperty('--lo', pct(lo.value));
            range.style.setProperty('--hi', pct(hi.value));
            outLo.textContent = peso(lo.value);
            outHi.textContent = peso(hi.value);
            range.classList.toggle('is-close', (hi.value - lo.value) / span < .22);
            // whichever thumb was touched last stays on top, so they can't get stuck together
            lo.style.zIndex = moved === lo ? 2 : 1;
            hi.style.zIndex = moved === lo ? 1 : 2;
        };

        lo.addEventListener('input', function () { update(lo); });
        hi.addEventListener('input', function () { update(hi); });
        update(hi);
    }

    /* -----------------------------------------------------------------
       Dates: return can't be before pick-up; show the rental length.
       ----------------------------------------------------------------- */
    var form = $('[data-search]');
    if (form) {
        var pickDate = $('[data-pickup-date]', form);
        var retDate = $('[data-return-date]', form);
        var pickTime = $('#pickup-time', form);
        var retTime = $('#return-time', form);
        var daysOut = $('[data-days]', form);

        var at = function (d, t) { return new Date(d.value + 'T' + t.value); };

        var sync = function () {
            retDate.min = pickDate.value;
            if (retDate.value && retDate.value < pickDate.value) retDate.value = pickDate.value;

            var ms = at(retDate, retTime) - at(pickDate, pickTime);
            if (isNaN(ms)) return;
            if (ms <= 0) {
                daysOut.textContent = 'Return must be after pick-up';
                daysOut.style.color = '#c62828';
                return;
            }
            var days = Math.max(1, Math.ceil(ms / 864e5));
            daysOut.textContent = days + ' day' + (days === 1 ? '' : 's') + ' rental';
            daysOut.style.color = '';
        };

        [pickDate, retDate, pickTime, retTime].forEach(function (el) {
            el.addEventListener('change', sync);
        });
        sync();
    }

    /* -----------------------------------------------------------------
       A car picked on the landing page: bring it into view.
       ----------------------------------------------------------------- */
    var pinned = $('.bk-offer.is-pinned');
    if (pinned && window.innerWidth <= 900) {
        pinned.scrollIntoView({ block: 'start' });
    }
})();
