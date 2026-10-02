<?php
/**
 * Booking page: search sidebar on the left, matching cars on the right.
 *
 *   booking.php                      → search + car list
 *   booking.php?car=<id>             → same, with that car pinned to the top
 *   booking.php?book=<id>&...        → customer details form for that car
 *   POST booking.php                 → saves the booking, shows the confirmation
 *
 * Bookings are appended to data/bookings.jsonl until a database is wired up.
 */
require __DIR__ . '/includes/helpers.php';

session_start();
if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(16));
}

$data       = require __DIR__ . '/includes/data.php';
$site       = $data['site'];
$categories = $data['categories'];
$fleet      = $data['fleet'];

date_default_timezone_set('Asia/Manila');

const PRICE_FLOOR = 1500;
const PRICE_CEIL  = 13000;
const PRICE_STEP  = 500;

$cars = [];
foreach ($fleet as $car) {
    $cars[car_id($car['image'])] = $car;
}

$typeIcons = [
    'all' => 'car', 'sedan' => 'car', 'suv' => 'car', 'mpv' => 'van',
    'pickup' => 'truck', 'van' => 'van', 'electric' => 'bolt', 'sports' => 'flag',
];
$gearNames = ['AT' => 'Automatic', 'CVT' => 'Automatic (CVT)', 'IVT' => 'Automatic (IVT)', 'MT' => 'Manual'];
$payments  = ['gcash' => 'GCash', 'maya' => 'Maya', 'card' => 'Credit / debit card', 'cash' => 'Cash on pick-up'];

$times = [];
for ($h = 6; $h <= 21; $h++) {
    $times[sprintf('%02d:00', $h)] = $h === 12 ? 'Noon' : date('g:i A', mktime($h, 0));
}

function param($source, $key, $default = '')
{
    return isset($source[$key]) && is_string($source[$key]) ? trim($source[$key]) : $default;
}

function valid_date($value)
{
    $d = DateTime::createFromFormat('!Y-m-d', $value);
    return $d && $d->format('Y-m-d') === $value;
}

// a week costs six days
function rental_total($dailyRate, $days)
{
    return intdiv($days, 7) * $dailyRate * 6 + ($days % 7) * $dailyRate;
}

/* ---------- read the search ---------- */

$src = $_SERVER['REQUEST_METHOD'] === 'POST' ? $_POST : $_GET;

$tomorrow = new DateTime('tomorrow');
$q = [
    'location'    => param($src, 'location', $site['cities'][0]),
    'pickup_date' => param($src, 'pickup_date', $tomorrow->format('Y-m-d')),
    'pickup_time' => param($src, 'pickup_time', '10:00'),
    'return_date' => param($src, 'return_date', (clone $tomorrow)->modify('+3 days')->format('Y-m-d')),
    'return_time' => param($src, 'return_time', '10:00'),
    'min'         => (int) param($src, 'min', PRICE_FLOOR),
    'max'         => (int) param($src, 'max', PRICE_CEIL),
    'type'        => param($src, 'type', 'all'),
    'sort'        => param($src, 'sort', 'asc'),
];

$notices = [];

if (!in_array($q['location'], $site['cities'], true)) {
    $q['location'] = $site['cities'][0];
}
if (!isset($categories[$q['type']])) {
    $q['type'] = 'all';
}
if (!in_array($q['sort'], ['asc', 'desc'], true)) {
    $q['sort'] = 'asc';
}
foreach (['pickup_time', 'return_time'] as $k) {
    if (!isset($times[$q[$k]])) {
        $q[$k] = '10:00';
    }
}
$q['min'] = max(PRICE_FLOOR, min(PRICE_CEIL, $q['min']));
$q['max'] = max(PRICE_FLOOR, min(PRICE_CEIL, $q['max']));
if ($q['min'] > $q['max']) {
    [$q['min'], $q['max']] = [$q['max'], $q['min']];
}

$today = date('Y-m-d');
if (!valid_date($q['pickup_date']) || $q['pickup_date'] < $today) {
    $q['pickup_date'] = $tomorrow->format('Y-m-d');
}
if (!valid_date($q['return_date'])) {
    $q['return_date'] = (new DateTime($q['pickup_date']))->modify('+3 days')->format('Y-m-d');
}

$pickupAt = new DateTime($q['pickup_date'] . ' ' . $q['pickup_time']);
$returnAt = new DateTime($q['return_date'] . ' ' . $q['return_time']);
if ($returnAt <= $pickupAt) {
    $returnAt = (clone $pickupAt)->modify('+1 day');
    $q['return_date'] = $returnAt->format('Y-m-d');
    $q['return_time'] = $returnAt->format('H:i');
    $notices[] = 'The return time was before pick-up, so we set it to one day later.';
}
$days = max(1, (int) ceil(($returnAt->getTimestamp() - $pickupAt->getTimestamp()) / 86400));

function search_url($q, $changes = [])
{
    return 'booking.php?' . http_build_query(array_merge($q, $changes));
}

/* ---------- which step are we on ---------- */

$step   = 'list';
$errors = [];
$form   = ['name' => '', 'phone' => '', 'email' => '', 'license' => '', 'address' => '', 'payment' => 'gcash', 'notes' => ''];
$booked = null;

$bookId = param($src, 'book');
if ($bookId !== '' && isset($cars[$bookId])) {
    $step = 'details';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $step === 'details') {
    foreach ($form as $k => $_) {
        $form[$k] = param($_POST, $k, $form[$k]);
    }

    if (!hash_equals($_SESSION['csrf'], param($_POST, 'csrf'))) {
        $errors['form'] = 'Your session expired. Please submit the form again.';
    }
    if (mb_strlen($form['name']) < 2) {
        $errors['name'] = 'Please enter your full name.';
    }
    $digits = preg_replace('/\D/', '', $form['phone']);
    if (!preg_match('/^(09\d{9}|639\d{9})$/', $digits)) {
        $errors['phone'] = 'Enter a mobile number like 0917 123 4567.';
    }
    if (!filter_var($form['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Enter a valid e-mail address.';
    }
    if (mb_strlen($form['license']) < 5) {
        $errors['license'] = "Enter your driver's license number.";
    }
    if (!isset($payments[$form['payment']])) {
        $errors['payment'] = 'Choose how you will pay.';
    }

    if (!$errors) {
        $car    = $cars[$bookId];
        $booked = [
            'ref'        => 'BY-' . strtoupper(bin2hex(random_bytes(3))),
            'created_at' => date('c'),
            'car'        => $bookId,
            'car_name'   => $car['brand'] . ' ' . $car['model'],
            'location'   => $q['location'],
            'pickup'     => $pickupAt->format('Y-m-d H:i'),
            'return'     => $returnAt->format('Y-m-d H:i'),
            'days'       => $days,
            'total'      => rental_total($car['price'], $days),
            'customer'   => $form,
        ];

        $dir = __DIR__ . '/data';
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
        file_put_contents($dir . '/bookings.jsonl', json_encode($booked, JSON_UNESCAPED_UNICODE) . "\n", FILE_APPEND | LOCK_EX);

        $_SESSION['csrf'] = bin2hex(random_bytes(16));
        $step = 'done';
    }
}

/* ---------- the car list ---------- */

$offers = array_filter($cars, function ($car) use ($q) {
    return $car['price'] >= $q['min'] && $car['price'] <= $q['max']
        && ($q['type'] === 'all' || in_array($q['type'], $car['types'], true));
});
uasort($offers, function ($a, $b) use ($q) {
    return $q['sort'] === 'asc' ? $a['price'] <=> $b['price'] : $b['price'] <=> $a['price'];
});

// a car picked on the landing page goes first, even outside the filters
$pinned = param($_GET, 'car');
if ($pinned !== '' && isset($cars[$pinned])) {
    unset($offers[$pinned]);
    $offers = [$pinned => $cars[$pinned]] + $offers;
} else {
    $pinned = '';
}

$selected = $step === 'list' ? null : $cars[$bookId];
$mark     = logo_mark();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Book a Car — <?= e($site['name']) ?></title>
    <meta name="theme-color" content="#0b0b0f">
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 32 32'%3E%3Ccircle cx='16' cy='16' r='16' fill='%230b0b0f'/%3E%3Cpath d='M10.5 9.2 23 16l-12.5 6.8 3.1-6.8z' fill='%23fff'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css?v=3">
    <link rel="stylesheet" href="assets/css/booking.css?v=1">
</head>
<body class="bk-page">

<a class="skip-link" href="#main">Skip to content</a>

<header class="bk-header">
    <div class="container bk-header__inner">
        <a class="logo" href="index.php" aria-label="<?= e($site['name']) ?> home"><?= $mark ?><span><?= e($site['name']) ?></span></a>
        <a class="bk-header__back" href="index.php#fleet"><?= icon('back') ?>Back to cars</a>
    </div>
</header>

<main id="main" class="container bk">

    <!-- ============ SEARCH ============ -->
    <aside class="bk__side">
        <form class="bk-card bk-search" method="get" action="booking.php" data-search>
            <h1 class="bk-card__title">Car rental</h1>

            <?php if ($step !== 'list'): ?>
                <input type="hidden" name="book" value="<?= e($bookId) ?>">
            <?php endif; ?>
            <input type="hidden" name="type" value="<?= e($q['type']) ?>">
            <input type="hidden" name="sort" value="<?= e($q['sort']) ?>">

            <label class="bk-label" for="location">Pick-up location</label>
            <div class="bk-field bk-field--icon">
                <?= icon('pin') ?>
                <select id="location" name="location">
                    <?php foreach ($site['cities'] as $city): ?>
                        <option<?= $city === $q['location'] ? ' selected' : '' ?>><?= e($city) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <span class="bk-label" id="dates-label">Dates</span>
            <div class="bk-dates" role="group" aria-labelledby="dates-label">
                <?php foreach (['pickup' => 'Pick-up', 'return' => 'Return'] as $k => $label): ?>
                    <div class="bk-dates__row">
                        <label class="sr-only" for="<?= $k ?>-date"><?= $label ?> date</label>
                        <input id="<?= $k ?>-date" type="date" name="<?= $k ?>_date" value="<?= e($q[$k . '_date']) ?>" min="<?= e($today) ?>" required data-<?= $k ?>-date>
                        <label class="sr-only" for="<?= $k ?>-time"><?= $label ?> time</label>
                        <select id="<?= $k ?>-time" name="<?= $k ?>_time">
                            <?php foreach ($times as $value => $text): ?>
                                <option value="<?= $value ?>"<?= $value === $q[$k . '_time'] ? ' selected' : '' ?>><?= $text ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                <?php endforeach; ?>
            </div>
            <p class="bk-hint" data-days><?= $days ?> day<?= $days === 1 ? '' : 's' ?> rental</p>

            <span class="bk-label" id="budget-label">Budget <small>per day</small></span>
            <div class="bk-range" role="group" aria-labelledby="budget-label" data-range
                 style="--lo: <?= ($q['min'] - PRICE_FLOOR) / (PRICE_CEIL - PRICE_FLOOR) * 100 ?>%; --hi: <?= ($q['max'] - PRICE_FLOOR) / (PRICE_CEIL - PRICE_FLOOR) * 100 ?>%">
                <output class="bk-range__val bk-range__val--lo" data-out="min"><?= peso($q['min']) ?></output>
                <output class="bk-range__val bk-range__val--hi" data-out="max"><?= peso($q['max']) ?></output>
                <div class="bk-range__track"><span class="bk-range__fill"></span></div>
                <input type="range" name="min" min="<?= PRICE_FLOOR ?>" max="<?= PRICE_CEIL ?>" step="<?= PRICE_STEP ?>" value="<?= $q['min'] ?>" aria-label="Minimum price per day">
                <input type="range" name="max" min="<?= PRICE_FLOOR ?>" max="<?= PRICE_CEIL ?>" step="<?= PRICE_STEP ?>" value="<?= $q['max'] ?>" aria-label="Maximum price per day">
                <div class="bk-range__ticks" aria-hidden="true"></div>
            </div>

            <button class="bk-btn bk-btn--block" type="submit"><?= $step === 'list' ? 'Show me cars' : 'Update dates' ?></button>
        </form>

        <div class="bk-card bk-promo">
            <h2>The Philippines’ favorite cars, delivered to you</h2>
            <p>From Baguio to Davao, <?= e($site['name']) ?> brings a clean, fully insured car to your door or hotel — and picks it up when you’re done.</p>
            <strong class="bk-promo__stat"><?= count($cars) ?></strong>
            <span class="bk-promo__unit">models ready to drive</span>
            <ul class="bk-promo__list">
                <li><?= icon('shield') ?>Fully insured, every model</li>
                <li><?= icon('support') ?>24/7 roadside assistance</li>
                <li><?= icon('wallet') ?>GCash, Maya, card or cash</li>
            </ul>
        </div>
    </aside>

    <!-- ============ RESULTS / DETAILS ============ -->
    <section class="bk-card bk__main" aria-live="polite">

    <?php if ($step === 'list'): ?>

        <header class="bk-results__head">
            <h2>We found <?= count($offers) ?> offer<?= count($offers) === 1 ? '' : 's' ?></h2>
            <a class="bk-sort" href="<?= e(search_url($q, ['sort' => $q['sort'] === 'asc' ? 'desc' : 'asc'])) ?>">
                Sort: <b>by price <?= $q['sort'] === 'asc' ? '↑' : '↓' ?></b>
            </a>
        </header>

        <nav class="bk-tabs" aria-label="Car type">
            <?php foreach ($categories as $key => $label): ?>
                <a class="bk-tab<?= $key === $q['type'] ? ' is-active' : '' ?>" href="<?= e(search_url($q, ['type' => $key])) ?>"<?= $key === $q['type'] ? ' aria-current="page"' : '' ?>>
                    <?= icon($typeIcons[$key]) ?><?= e($label) ?>
                </a>
            <?php endforeach; ?>
        </nav>

        <?php foreach ($notices as $notice): ?>
            <p class="bk-notice"><?= e($notice) ?></p>
        <?php endforeach; ?>

        <?php if (!$offers): ?>
            <div class="bk-empty">
                <p>No cars match that budget and type.</p>
                <a class="bk-btn" href="<?= e(search_url($q, ['type' => 'all', 'min' => PRICE_FLOOR, 'max' => PRICE_CEIL])) ?>">Show all cars</a>
            </div>
        <?php endif; ?>

        <ol class="bk-list">
            <?php foreach ($offers as $id => $car): ?>
                <li class="bk-offer<?= $id === $pinned ? ' is-pinned' : '' ?>" id="car-<?= e($id) ?>">
                    <div class="bk-offer__media">
                        <?= car_img($car['image'], $car['brand'] . ' ' . $car['model'], 'loading="lazy" decoding="async"') ?>
                    </div>
                    <div class="bk-offer__info">
                        <?php if ($id === $pinned): ?><span class="bk-offer__tag">Your pick</span><?php endif; ?>
                        <h3><small><?= e($car['brand']) ?></small> <?= e($car['model']) ?></h3>
                        <ul class="bk-specs">
                            <li><?= icon('gear') ?><?= e($gearNames[$car['gear']] ?? $car['gear']) ?></li>
                            <li><?= icon('fuel') ?><?= e($car['fuel']) ?></li>
                            <li><?= icon('seat') ?><?= (int) $car['seats'] ?> passengers</li>
                        </ul>
                    </div>
                    <div class="bk-offer__price">
                        <b><?= peso($car['price']) ?><small>/day</small></b>
                        <span><?= peso($car['price'] * 6) ?>/week</span>
                        <span class="bk-offer__total"><?= peso(rental_total($car['price'], $days)) ?> for <?= $days ?> day<?= $days === 1 ? '' : 's' ?></span>
                        <a class="bk-btn" href="<?= e(search_url($q, ['book' => $id])) ?>" aria-label="Book the <?= e($car['brand'] . ' ' . $car['model']) ?>">Book now</a>
                    </div>
                </li>
            <?php endforeach; ?>
        </ol>

    <?php else: ?>
        <?php $total = rental_total($selected['price'], $days); ?>

        <?php if ($step === 'details'): ?>
            <header class="bk-results__head">
                <h2>Complete your booking</h2>
                <a class="bk-sort" href="<?= e(search_url($q)) ?>"><?= icon('back') ?>Change car</a>
            </header>
        <?php else: ?>
            <div class="bk-done">
                <span class="bk-done__icon"><?= icon('check') ?></span>
                <h2>Booking received!</h2>
                <p>Thank you, <?= e($booked['customer']['name']) ?>. Your reference number is</p>
                <strong class="bk-done__ref"><?= e($booked['ref']) ?></strong>
                <p>Our team will call or text <b><?= e($booked['customer']['phone']) ?></b> to confirm and send payment instructions to <b><?= e($booked['customer']['email']) ?></b>.</p>
            </div>
        <?php endif; ?>

        <div class="bk-summary">
            <div class="bk-summary__car">
                <?= car_img($selected['image'], $selected['brand'] . ' ' . $selected['model'], 'decoding="async"') ?>
                <div>
                    <small><?= e($selected['brand']) ?></small>
                    <h3><?= e($selected['model']) ?></h3>
                    <ul class="bk-specs bk-specs--inline">
                        <li><?= icon('gear') ?><?= e($gearNames[$selected['gear']] ?? $selected['gear']) ?></li>
                        <li><?= icon('fuel') ?><?= e($selected['fuel']) ?></li>
                        <li><?= icon('seat') ?><?= (int) $selected['seats'] ?></li>
                    </ul>
                </div>
            </div>
            <dl class="bk-summary__rows">
                <div><dt>Pick-up</dt><dd><?= e($pickupAt->format('D, M j · g:i A')) ?></dd></div>
                <div><dt>Return</dt><dd><?= e($returnAt->format('D, M j · g:i A')) ?></dd></div>
                <div><dt>Location</dt><dd><?= e($q['location']) ?></dd></div>
                <div><dt>Rate</dt><dd><?= peso($selected['price']) ?>/day × <?= $days ?> day<?= $days === 1 ? '' : 's' ?><?= $days >= 7 ? ' (weekly rate applied)' : '' ?></dd></div>
                <div class="bk-summary__total"><dt>Total</dt><dd><?= peso($total) ?></dd></div>
            </dl>
        </div>

        <?php if ($step === 'details'): ?>
            <form class="bk-form" method="post" action="<?= e(search_url($q, ['book' => $bookId])) ?>" novalidate data-details>
                <input type="hidden" name="csrf" value="<?= e($_SESSION['csrf']) ?>">
                <?php foreach (['location', 'pickup_date', 'pickup_time', 'return_date', 'return_time'] as $k): ?>
                    <input type="hidden" name="<?= $k ?>" value="<?= e($q[$k]) ?>">
                <?php endforeach; ?>
                <input type="hidden" name="book" value="<?= e($bookId) ?>">

                <?php if (isset($errors['form'])): ?>
                    <p class="bk-notice bk-notice--error"><?= e($errors['form']) ?></p>
                <?php endif; ?>

                <h3 class="bk-form__title">Driver details</h3>
                <div class="bk-form__grid">
                    <?php
                    $fields = [
                        'name'    => ['Full name', 'text', 'name', 'Juan Dela Cruz'],
                        'phone'   => ['Mobile number', 'tel', 'tel', '0917 123 4567'],
                        'email'   => ['E-mail', 'email', 'email', 'you@example.com'],
                        'license' => ["Driver's license no.", 'text', 'off', 'N01-23-456789'],
                    ];
                    foreach ($fields as $k => [$label, $type, $auto, $placeholder]): ?>
                        <div class="bk-form__field<?= isset($errors[$k]) ? ' has-error' : '' ?>">
                            <label class="bk-label" for="f-<?= $k ?>"><?= e($label) ?></label>
                            <input class="bk-input" id="f-<?= $k ?>" type="<?= $type ?>" name="<?= $k ?>" value="<?= e($form[$k]) ?>" placeholder="<?= e($placeholder) ?>" autocomplete="<?= $auto ?>" required<?= isset($errors[$k]) ? ' aria-invalid="true" aria-describedby="err-' . $k . '"' : '' ?>>
                            <?php if (isset($errors[$k])): ?><p class="bk-error" id="err-<?= $k ?>"><?= e($errors[$k]) ?></p><?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                    <div class="bk-form__field bk-form__field--wide">
                        <label class="bk-label" for="f-address">Delivery address <small>optional — leave blank to pick up at our <?= e($q['location']) ?> branch</small></label>
                        <input class="bk-input" id="f-address" type="text" name="address" value="<?= e($form['address']) ?>" placeholder="Hotel, condo or house address" autocomplete="street-address">
                    </div>
                </div>

                <h3 class="bk-form__title">Payment</h3>
                <div class="bk-pay<?= isset($errors['payment']) ? ' has-error' : '' ?>" role="radiogroup" aria-label="Payment method">
                    <?php foreach ($payments as $k => $label): ?>
                        <label class="bk-pay__opt">
                            <input type="radio" name="payment" value="<?= $k ?>"<?= $form['payment'] === $k ? ' checked' : '' ?>>
                            <span><?= e($label) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?php if (isset($errors['payment'])): ?><p class="bk-error"><?= e($errors['payment']) ?></p><?php endif; ?>

                <div class="bk-form__field bk-form__field--wide">
                    <label class="bk-label" for="f-notes">Notes <small>optional</small></label>
                    <textarea class="bk-input" id="f-notes" name="notes" rows="3" placeholder="Flight number, child seat, special requests…"><?= e($form['notes']) ?></textarea>
                </div>

                <div class="bk-form__foot">
                    <p>You won’t be charged yet. We’ll confirm availability first.</p>
                    <button class="bk-btn bk-btn--lg" type="submit">Confirm booking · <?= peso($total) ?></button>
                </div>
            </form>
        <?php else: ?>
            <div class="bk-form__foot">
                <a class="bk-btn bk-btn--ghost" href="index.php">Back to home</a>
                <a class="bk-btn" href="booking.php">Book another car</a>
            </div>
        <?php endif; ?>

    <?php endif; ?>
    </section>
</main>

<script src="assets/js/booking.js?v=1" defer></script>
</body>
</html>
