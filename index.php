<?php
$data       = require __DIR__ . '/includes/data.php';
$site       = $data['site'];
$brands     = $data['brands'];
$categories = $data['categories'];
$fleet      = $data['fleet'];
$features   = $data['features'];

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function peso($amount)
{
    return '₱' . number_format($amount);
}

// a car's id is its image name, e.g. "toyota-fortuner"
function book_url($site, $image)
{
    return $site['booking_page'] . '?car=' . rawurlencode(pathinfo($image, PATHINFO_FILENAME));
}

function brand_logo($slug)
{
    $file = __DIR__ . '/assets/img/logos/' . basename($slug) . '.svg';
    return is_file($file) ? file_get_contents($file) : '';
}

// width/height attributes keep the layout from jumping while images load
function car_img($file, $alt, $attrs = '')
{
    static $sizes = [];
    $path = 'assets/img/cars/' . $file;
    if (!isset($sizes[$file])) {
        $size = @getimagesize(__DIR__ . '/' . $path);
        $sizes[$file] = $size ? sprintf(' width="%d" height="%d"', $size[0], $size[1]) : '';
    }
    return sprintf('<img src="%s" alt="%s"%s %s>', e($path), e($alt), $sizes[$file], $attrs);
}

function icon($name)
{
    $paths = [
        'arrow'    => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M8 3v4M16 3v4M3.5 10h17"/><path d="m9.5 15 1.8 1.8 3.4-3.6"/>',
        'menu'     => '<path d="M4 8h16"/><path d="M4 16h16"/>',
        'seat'     => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
        'gear'     => '<circle cx="6" cy="6" r="1.6"/><circle cx="12" cy="6" r="1.6"/><circle cx="18" cy="6" r="1.6"/><circle cx="6" cy="18" r="1.6"/><circle cx="12" cy="18" r="1.6"/><path d="M6 7.6v8.8M12 7.6v8.8M18 7.6V12H6"/>',
        'fuel'     => '<path d="M4 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16"/><path d="M3 21h12"/><path d="M4 10h10"/><path d="M14 13h1.5a1.5 1.5 0 0 1 1.5 1.5V17a1.5 1.5 0 0 0 3 0V9l-3-3"/>',
        'delivery' => '<path d="M21.5 12a9.5 9.5 0 0 1-16.2 6.7"/><path d="M2.5 12A9.5 9.5 0 0 1 18.7 5.3"/><path d="M19 1.8v3.8h-3.8"/><path d="M5 22.2v-3.8h3.8"/><text x="12" y="15.2" text-anchor="middle" font-size="8.6" font-weight="700" fill="currentColor" stroke="none" font-family="Outfit, sans-serif">24</text>',
        'support'  => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><path d="M4 15a2 2 0 0 0 2 2h1v-5H6a2 2 0 0 0-2 2Z"/><path d="M20 15a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2Z"/><path d="M17 17v.5a3.5 3.5 0 0 1-3.5 3.5H12"/>',
        'shield'   => '<path d="M12 21.5s7.5-3.6 7.5-9.6V5.6L12 2.8 4.5 5.6v6.3c0 6 7.5 9.6 7.5 9.6Z"/><path d="m8.8 12 2.2 2.2 4.3-4.4"/>',
        'wallet'   => '<path d="M18 7V5a2 2 0 0 0-2-2H6a3 3 0 0 0 0 6"/><path d="M3 6v12a3 3 0 0 0 3 3h13a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2H6a3 3 0 0 1-3-3Z"/><path d="M16.5 14h.01"/>',
    ];
    return '<svg class="icon icon--' . $name . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

$mark  ='<svg class="logo__mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><circle cx="16" cy="16" r="16" fill="currentColor"/><path d="M10.5 9.2 23 16l-12.5 6.8 3.1-6.8z" fill="#fff"/></svg>';

$first     = $brands[0];
$brandJson = array_map(function ($b) use ($site) {
    return [
        'name'  => $b['name'],
        'model' => $b['model'],
        'spec'  => $b['spec'],
        'price' => peso($b['price']),
        'color' => $b['color'],
        'book'  => book_url($site, $b['image']),
    ];
}, $brands);
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($site['name']) ?> — <?= e($site['tagline']) ?></title>
    <meta name="description" content="Rent a Toyota, Mitsubishi, Hyundai, BYD, Honda, Nissan, Suzuki or Ford anywhere in the Philippines. Delivered to your door, 24/7.">
    <meta name="theme-color" content="#0b0b0f">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%230b0b0f'/%3E%3Cpath d='M10.5 9.2 23 16l-12.5 6.8 3.1-6.8z' fill='%23fff'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="preload" as="image" href="assets/img/cars/<?= e($first['image']) ?>" fetchpriority="high">
    <link rel="stylesheet" href="assets/css/style.css?v=3">
    <script>document.documentElement.classList.add('js')</script>
</head>
<body id="top">

<a class="skip-link" href="#main">Skip to content</a>

<header class="header" data-header>
    <div class="container header__inner">
        <a class="logo" href="#top" aria-label="<?= e($site['name']) ?> home">
            <?= $mark ?><span><?= e($site['name']) ?></span>
        </a>

        <nav class="nav" id="site-nav" aria-label="Main">
            <a href="#how">How it Works</a>
            <a href="#fleet">Cars</a>
            <a href="#features">Features</a>
            <a href="#contact">Help</a>
            <a class="btn btn--dark nav__app" href="#fleet"><?= icon('calendar') ?>Book Now</a>
        </nav>

        <a class="btn btn--dark btn--sm header__cta" href="#fleet"><?= icon('calendar') ?>Book Now</a>

        <button class="menu-toggle" type="button" aria-expanded="false" aria-controls="site-nav" aria-label="Open menu">
            <span></span><span></span>
        </button>
    </div>
</header>

<main id="main">

    <!-- ============ HERO ============ -->
    <section class="hero" aria-labelledby="hero-title" data-hero>
        <div class="container hero__inner">
            <div class="hero__copy">
                <h1 class="hero__title" id="hero-title">
                    <span class="line"><span>Premium</span></span>
                    <span class="line"><span>Car Rental</span></span>
                    <span class="line">
                        <span>in <span class="rotator" aria-hidden="true">
                            <?php foreach ($site['cities'] as $i => $city): ?>
                                <span class="rotator__word<?= $i === 0 ? ' is-active' : '' ?>"><?= e($city) ?></span>
                            <?php endforeach; ?>
                        </span><span class="sr-only">the Philippines</span></span>
                    </span>
                </h1>
                <p class="hero__lead">
                    Don’t deny yourself the pleasure of driving the best cars from the brands Filipinos trust — delivered to your door, here and now.
                </p>
                <div class="hero__actions">
                    <a class="btn btn--dark" href="#fleet">Book a Car <?= icon('arrow') ?></a>
                    <a class="btn btn--ghost" href="#how">How it works</a>
                </div>
            </div>

            <div class="hero__visual">
                <div class="hero__map" aria-hidden="true">
                    <svg viewBox="0 0 900 600" preserveAspectRatio="xMidYMid slice">
                        <g class="map-streets" transform="rotate(-24 450 300)">
                            <g class="map-minor">
                                <path d="M-300 20H1200M-300 75H1200M-300 128H1200M-300 178H1200M-300 232H1200M-300 312H1200M-300 362H1200M-300 418H1200M-300 530H1200M-300 585H1200M-300 640H1200"/>
                                <path d="M-120-300V900M-48-300V900M22-300V900M95-300V900M162-300V900M232-300V900M300-300V900M352-300V900M455-300V900M512-300V900M580-300V900M645-300V900M705-300V900M815-300V900M880-300V900M948-300V900M1015-300V900"/>
                            </g>
                            <g class="map-major">
                                <path d="M-300 265H1200M-300 478H1200M405-300V900M760-300V900"/>
                            </g>
                        </g>
                        <path class="map-road" d="M-40 470C160 380 330 520 560 400S860 250 960 300"/>
                        <path class="map-road map-road--thin" d="M250-20C300 120 230 250 320 360S470 560 440 640"/>
                        <path class="map-route" id="map-route" d="M330 330C390 240 470 270 540 205S690 120 780 160 870 230 930 190"/>
                        <g class="map-pins">
                            <g class="pin" transform="translate(330 330)"><circle class="pin__pulse" r="7"/><circle class="pin__dot" r="5.5"/></g>
                            <g class="pin" transform="translate(468 112)"><circle class="pin__pulse" r="7"/><circle class="pin__dot" r="5.5"/></g>
                            <g class="pin" transform="translate(612 168)"><circle class="pin__pulse" r="7"/><circle class="pin__dot" r="5.5"/></g>
                            <g class="pin" transform="translate(780 160)"><circle class="pin__pulse" r="7"/><circle class="pin__dot" r="5.5"/></g>
                            <g class="pin" transform="translate(860 300)"><circle class="pin__pulse" r="7"/><circle class="pin__dot" r="5.5"/></g>
                        </g>
                        <path class="map-arrow" d="M-9-7 9 0-9 7-5 0Z">
                            <animateMotion dur="11s" repeatCount="indefinite" rotate="auto" keyPoints="0;1" keyTimes="0;1" calcMode="linear">
                                <mpath href="#map-route"/>
                            </animateMotion>
                        </path>
                    </svg>
                </div>

                <div class="hero__parallax" data-parallax="0.14">
                    <div class="hero__stage" id="hero-stage" role="tabpanel" aria-labelledby="brand-tab-<?= e($first['slug']) ?>">
                        <?php foreach ($brands as $i => $b): ?>
                            <figure class="hero-car<?= $i === 0 ? ' is-active' : '' ?>" data-index="<?= $i ?>" style="--fit: <?= (int) $b['fit'] ?>%"<?= $i === 0 ? '' : ' aria-hidden="true"' ?>>
                                <?= $i === 0
                                    ? car_img($b['image'], $b['name'] . ' ' . $b['model'], 'fetchpriority="high" decoding="async"')
                                    : str_replace('<img src=', '<img data-src=', car_img($b['image'], $b['name'] . ' ' . $b['model'], 'decoding="async"')) ?>
                            </figure>
                        <?php endforeach; ?>
                    </div>

                    <div class="hero-info" data-hero-info>
                        <span class="hero-info__brand" data-field="name"><?= e($first['name']) ?></span>
                        <strong class="hero-info__model" data-field="model"><?= e($first['model']) ?></strong>
                        <span class="hero-info__spec" data-field="spec"><?= e($first['spec']) ?></span>
                        <div class="hero-info__row">
                            <span class="hero-info__price"><b data-field="price"><?= peso($first['price']) ?></b>/day</span>
                            <a class="hero-info__book" href="<?= e(book_url($site, $first['image'])) ?>" data-book-link>Book <?= icon('arrow') ?></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="container">
            <div class="brands-bar">
                <div class="brands" role="tablist" aria-label="Choose a car brand" data-brands>
                    <span class="brands__indicator" aria-hidden="true"></span>
                    <?php foreach ($brands as $i => $b): ?>
                        <button class="brand brand--<?= e($b['slug']) ?><?= $i === 0 ? ' is-active' : '' ?>" type="button" role="tab"
                                id="brand-tab-<?= e($b['slug']) ?>" aria-controls="hero-stage"
                                aria-selected="<?= $i === 0 ? 'true' : 'false' ?>" tabindex="<?= $i === 0 ? '0' : '-1' ?>"
                                data-index="<?= $i ?>" style="--brand: <?= e($b['color']) ?>; --i: <?= $i ?>" title="<?= e($b['name']) ?>">
                            <?= brand_logo($b['slug']) ?>
                            <span class="sr-only"><?= e($b['name']) ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <button class="brands-next" type="button" aria-label="Next brand" data-brands-next>
                    <svg class="brands-next__ring" viewBox="0 0 48 48" aria-hidden="true"><circle cx="24" cy="24" r="22"/><circle class="brands-next__progress" cx="24" cy="24" r="22" pathLength="100"/></svg>
                    <?= icon('arrow') ?>
                </button>
            </div>
        </div>
    </section>

    <!-- ============ APP ============ -->
    <section class="app section" id="how" aria-labelledby="app-title">
        <div class="container app__inner">
            <div class="app__phones" aria-hidden="true">
                <div class="phone-slot phone-slot--back" data-parallax="0.06">
                    <div class="phone phone--back" data-reveal="phone">
                        <div class="phone__float">
                            <div class="phone__screen">
                                <span class="phone__island"></span>
                                <div class="ui-map">
                                    <svg viewBox="0 0 240 230" preserveAspectRatio="xMidYMid slice">
                                        <g stroke="#e9ebef" stroke-width="7" fill="none">
                                            <path d="M-10 40 250 90M-10 120 250 170M-10 200 250 250M40-10 0 250M120-10 80 250M200-10 160 250"/>
                                        </g>
                                        <path class="ui-map__route" d="M44 186C70 150 70 130 110 118S170 90 196 52" fill="none" stroke="#0b0b0f" stroke-width="3" stroke-linecap="round" stroke-dasharray="1 7"/>
                                        <circle cx="196" cy="52" r="6" fill="#0b0b0f"/><circle cx="196" cy="52" r="2.4" fill="#fff"/>
                                        <rect x="30" y="176" width="28" height="18" rx="6" fill="#0b0b0f"/>
                                    </svg>
                                    <span class="ui-top"><i>‹</i><i>⋯</i></span>
                                    <span class="ui-eta">Delivery · 8 min</span>
                                </div>
                                <div class="ui-sheet">
                                    <span class="ui-sheet__grab"></span>
                                    <div class="ui-car">
                                        <div><b>Toyota Fortuner</b><small>7 seats · AT · Diesel</small></div>
                                        <?= car_img('toyota-fortuner.webp', '', 'loading="lazy"') ?>
                                    </div>
                                    <div class="ui-dates">
                                        <div><small>Pick-up</small><b>Oct 12 · 9:00 AM</b></div>
                                        <div><small>Return</small><b>Oct 14 · 9:00 AM</b></div>
                                    </div>
                                    <div class="ui-pay"><small>Total · 2 days · GCash</small><b>₱7,600</b></div>
                                    <div class="ui-actions"><span>+ Add driver</span><span>✓ Insured</span></div>
                                    <span class="ui-btn">Confirm Booking</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="phone-slot phone-slot--front" data-parallax="0.12">
                    <div class="phone phone--front" data-reveal="phone" style="--d: 2">
                        <div class="phone__float">
                            <div class="phone__screen">
                                <span class="phone__island"></span>
                                <div class="ui-tabs"><b>My Bookings</b><span>Cars</span></div>
                                <div class="ui-recent">
                                    <small>Upcoming trip · Confirmed</small>
                                    <?= car_img('byd-seal.webp', '', 'loading="lazy"') ?>
                                    <b>BYD Seal</b>
                                    <span class="ui-chips"><i>Oct 12–14</i><i>BGC</i><i>Paid</i></span>
                                </div>
                                <div class="ui-grid">
                                    <div class="ui-tile ui-tile--map"><span class="ui-play">▶</span></div>
                                    <div class="ui-tile ui-tile--agent"><span class="ui-avatar">MJ</span><small>Your agent</small><b>Mika J.</b></div>
                                </div>
                                <div class="ui-offers">
                                    <div><b>3 offers</b><small>Weekend · Tagaytay</small><i>›</i></div>
                                    <div><b>22 offers</b><small>Airport pickup · NAIA</small><i>›</i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="app__copy">
                <span class="eyebrow" data-reveal>Simple online booking</span>
                <h2 class="section-title" id="app-title" data-reveal style="--d: 1">Book in Minutes</h2>
                <p class="section-lead" data-reveal style="--d: 2">
                    Reserve your car right here on the website — on your phone or laptop, nothing to install.
                </p>
                <ol class="steps">
                    <li class="step" data-reveal style="--d: 3">
                        <span class="step__num">01</span>
                        <div><h3>Choose your car</h3><p>Toyota, Mitsubishi, Hyundai, BYD and more.</p></div>
                    </li>
                    <li class="step" data-reveal style="--d: 4">
                        <span class="step__num">02</span>
                        <div><h3>Pick your dates &amp; location</h3><p>Delivery or pick-up anywhere in Metro Manila.</p></div>
                    </li>
                    <li class="step" data-reveal style="--d: 5">
                        <span class="step__num">03</span>
                        <div><h3>Pay &amp; drive</h3><p>GCash, Maya or card — confirmed right away.</p></div>
                    </li>
                </ol>
                <div class="store-buttons" data-reveal style="--d: 6">
                    <a class="btn btn--dark" href="#fleet"><?= icon('calendar') ?>Book Now</a>
                    <a class="btn btn--ghost" href="#features">Why rent with us</a>
                </div>
            </div>
        </div>
    </section>

    <!-- ============ FLEET ============ -->
    <section class="fleet section" id="fleet" aria-labelledby="fleet-title">
        <div class="container">
            <header class="section-head section-head--center">
                <span class="eyebrow" data-reveal>Only the best cars</span>
                <h2 class="section-title" id="fleet-title" data-reveal style="--d: 1">Our Vehicle Fleet</h2>
                <p class="section-lead" data-reveal style="--d: 2">
                    We give our customers the most incredible driving emotions.<br class="br-desktop">
                    That’s why we only keep the country’s favorite models in our fleet.
                </p>
            </header>

            <div class="chips" role="group" aria-label="Filter cars by type" data-reveal style="--d: 3" data-fleet-filters>
                <?php foreach ($categories as $key => $label): ?>
                    <button class="chip<?= $key === 'all' ? ' is-active' : '' ?>" type="button" data-filter="<?= e($key) ?>" aria-pressed="<?= $key === 'all' ? 'true' : 'false' ?>"><?= e($label) ?></button>
                <?php endforeach; ?>
            </div>

            <div class="fleet__grid" data-fleet-grid>
                <?php foreach ($fleet as $car): ?>
                    <article class="car-card" data-types="<?= e(implode(' ', $car['types'])) ?>">
                        <div class="car-card__media">
                            <?= car_img($car['image'], $car['brand'] . ' ' . $car['model'], 'loading="lazy" decoding="async"') ?>
                        </div>
                        <div class="car-card__body">
                            <div class="car-card__name">
                                <span><?= e($car['brand']) ?></span>
                                <h3><?= e($car['model']) ?></h3>
                            </div>
                            <div class="car-card__price"><b><?= peso($car['price']) ?></b><small>/day</small></div>
                        </div>
                        <ul class="car-card__meta">
                            <li><?= icon('seat') ?><?= (int) $car['seats'] ?> seats</li>
                            <li><?= icon('gear') ?><?= e($car['gear']) ?></li>
                            <li><?= icon('fuel') ?><?= e($car['fuel']) ?></li>
                        </ul>
                        <a class="car-card__book" href="<?= e(book_url($site, $car['image'])) ?>" aria-label="Book the <?= e($car['brand'] . ' ' . $car['model']) ?>"><?= icon('arrow') ?></a>
                    </article>
                <?php endforeach; ?>
            </div>

            <div class="fleet__more">
                <button class="btn btn--outline" type="button" data-fleet-more hidden>
                    <span data-fleet-more-label>Show All (<?= count($fleet) ?> models)</span> <?= icon('arrow') ?>
                </button>
            </div>
        </div>
    </section>

    <!-- ============ FEATURES ============ -->
    <section class="features section" id="features" aria-labelledby="features-title">
        <div class="container">
            <header class="section-head">
                <span class="eyebrow" data-reveal>Taking care of every client</span>
                <h2 class="section-title" id="features-title" data-reveal style="--d: 1">Key Features</h2>
                <p class="section-lead" data-reveal style="--d: 2">
                    We are all about our clients’ comfort and safety.<br class="br-desktop">
                    That’s why we provide the best service you can imagine.
                </p>
            </header>

            <ul class="features__list">
                <?php foreach ($features as $i => $f): ?>
                    <li class="feature" data-reveal="cascade" style="--i: <?= $i ?>">
                        <span class="feature__icon feature__icon--<?= e($f['tone']) ?>"><?= icon($f['icon']) ?></span>
                        <h3 class="feature__title"><?= e($f['title']) ?></h3>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </section>

    <!-- ============ CTA ============ -->
    <section class="cta" id="contact" aria-labelledby="cta-title">
        <div class="container">
            <div class="cta__card" data-reveal="zoom">
                <div class="cta__orbits" aria-hidden="true" data-parallax="-0.08">
                    <svg viewBox="0 0 600 600">
                        <g class="orbit orbit--a"><circle cx="300" cy="300" r="290"/><circle class="orbit__dot" cx="300" cy="10" r="6"/></g>
                        <g class="orbit orbit--b"><circle cx="300" cy="300" r="200"/><circle class="orbit__dot" cx="500" cy="300" r="5"/></g>
                        <g class="orbit orbit--c"><circle cx="300" cy="300" r="115"/><circle class="orbit__dot" cx="300" cy="415" r="4"/></g>
                    </svg>
                </div>
                <h2 class="cta__title" id="cta-title">Drive with <?= e($site['name']) ?> Today</h2>
                <p class="cta__text">Reserve your car online in minutes and we’ll bring it to your door — from Baguio to Batangas.</p>
                <a class="btn btn--light" href="#fleet"><?= icon('calendar') ?>Book Now</a>
            </div>
        </div>
    </section>
</main>

<footer class="footer">
    <div class="container footer__grid">
        <div class="footer__legal">
            <a href="#top">Terms</a>
            <a href="#top">Privacy</a>
        </div>
        <nav class="footer__nav" aria-label="Footer">
            <a href="#how">How it Works</a>
            <a href="#fleet">Cars</a>
            <a href="#features">Features</a>
            <a href="#contact">Help</a>
        </nav>
        <div class="footer__news">
            <h3>Subscribe to News</h3>
            <form class="subscribe" data-subscribe novalidate>
                <label class="sr-only" for="subscribe-email">Your e-mail</label>
                <input id="subscribe-email" type="email" name="email" placeholder="Your e-mail" autocomplete="email" required>
                <button type="submit" aria-label="Subscribe"><?= icon('arrow') ?></button>
            </form>
            <p class="subscribe__msg" data-subscribe-msg role="status"></p>
            <a class="footer__mark" href="#top" aria-label="Back to top"><?= $mark ?></a>
        </div>
    </div>
    <div class="container footer__bottom">
        <p>© <?= date('Y') ?> <?= e($site['name']) ?> Car Rental · Made in the Philippines</p>
        <p>Brand names, logos and vehicle images are trademarks of their respective owners.</p>
    </div>
</footer>

<script type="application/json" id="brand-data"><?= json_encode($brandJson, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) ?></script>
<script src="assets/js/main.js?v=3" defer></script>
</body>
</html>
