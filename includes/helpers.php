<?php
/**
 * View helpers shared by the landing page and the booking page.
 */

define('APP_ROOT', dirname(__DIR__));

function e($value)
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function peso($amount)
{
    return '₱' . number_format($amount);
}

// a car's id is its image name, e.g. "toyota-fortuner"
function car_id($image)
{
    return pathinfo($image, PATHINFO_FILENAME);
}

function book_url($site, $image)
{
    return $site['booking_page'] . '?car=' . rawurlencode(car_id($image));
}

function brand_logo($slug)
{
    $file = APP_ROOT . '/assets/img/logos/' . basename($slug) . '.svg';
    return is_file($file) ? file_get_contents($file) : '';
}

// width/height attributes keep the layout from jumping while images load
function car_img($file, $alt, $attrs = '')
{
    static $sizes = [];
    $path = 'assets/img/cars/' . $file;
    if (!isset($sizes[$file])) {
        $size = @getimagesize(APP_ROOT . '/' . $path);
        $sizes[$file] = $size ? sprintf(' width="%d" height="%d"', $size[0], $size[1]) : '';
    }
    return sprintf('<img src="%s" alt="%s"%s %s>', e($path), e($alt), $sizes[$file], $attrs);
}

function icon($name)
{
    $paths = [
        'arrow'    => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'back'     => '<path d="M19 12H5"/><path d="m11 6-6 6 6 6"/>',
        'calendar' => '<rect x="3.5" y="5" width="17" height="15.5" rx="3"/><path d="M8 3v4M16 3v4M3.5 10h17"/><path d="m9.5 15 1.8 1.8 3.4-3.6"/>',
        'menu'     => '<path d="M4 8h16"/><path d="M4 16h16"/>',
        'seat'     => '<circle cx="12" cy="8" r="3.5"/><path d="M5 20a7 7 0 0 1 14 0"/>',
        'gear'     => '<circle cx="6" cy="6" r="1.6"/><circle cx="12" cy="6" r="1.6"/><circle cx="18" cy="6" r="1.6"/><circle cx="6" cy="18" r="1.6"/><circle cx="12" cy="18" r="1.6"/><path d="M6 7.6v8.8M12 7.6v8.8M18 7.6V12H6"/>',
        'fuel'     => '<path d="M4 21V5a2 2 0 0 1 2-2h6a2 2 0 0 1 2 2v16"/><path d="M3 21h12"/><path d="M4 10h10"/><path d="M14 13h1.5a1.5 1.5 0 0 1 1.5 1.5V17a1.5 1.5 0 0 0 3 0V9l-3-3"/>',
        'pin'      => '<path d="M12 21s-7-6.2-7-11.5a7 7 0 0 1 14 0C19 14.8 12 21 12 21Z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'check'    => '<path d="m5 12.5 4.5 4.5L19 7.5"/>',
        'car'      => '<path d="M3 16v-3.2l2-4.6A2 2 0 0 1 6.8 7h10.4a2 2 0 0 1 1.8 1.2l2 4.6V16a1 1 0 0 1-1 1h-1"/><path d="M5 17H4a1 1 0 0 1-1-1"/><path d="M9 17h6"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M3.5 12.5h17"/>',
        'van'      => '<path d="M3 17V7a2 2 0 0 1 2-2h10.5l5.5 6v6a1 1 0 0 1-1 1h-1"/><path d="M9 17h6M5 17H4"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M3 11h18M10 5v6"/>',
        'truck'    => '<path d="M2.5 16V8.5A1.5 1.5 0 0 1 4 7h7v5h10.5v4a1 1 0 0 1-1 1h-1"/><path d="M11 7l2.5 5"/><path d="M9 17h6M5 17H3.5a1 1 0 0 1-1-1"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/>',
        'bolt'     => '<path d="M13 2.5 5 13.5h6.5L10.5 21.5 19 10h-6.5Z"/>',
        'flag'     => '<path d="M5 21V4"/><path d="M5 4h11l-2 4 2 4H5"/>',
        'delivery' => '<path d="M21.5 12a9.5 9.5 0 0 1-16.2 6.7"/><path d="M2.5 12A9.5 9.5 0 0 1 18.7 5.3"/><path d="M19 1.8v3.8h-3.8"/><path d="M5 22.2v-3.8h3.8"/><text x="12" y="15.2" text-anchor="middle" font-size="8.6" font-weight="700" fill="currentColor" stroke="none" font-family="Outfit, sans-serif">24</text>',
        'support'  => '<path d="M4 15v-3a8 8 0 0 1 16 0v3"/><path d="M4 15a2 2 0 0 0 2 2h1v-5H6a2 2 0 0 0-2 2Z"/><path d="M20 15a2 2 0 0 1-2 2h-1v-5h1a2 2 0 0 1 2 2Z"/><path d="M17 17v.5a3.5 3.5 0 0 1-3.5 3.5H12"/>',
        'shield'   => '<path d="M12 21.5s7.5-3.6 7.5-9.6V5.6L12 2.8 4.5 5.6v6.3c0 6 7.5 9.6 7.5 9.6Z"/><path d="m8.8 12 2.2 2.2 4.3-4.4"/>',
        'wallet'   => '<path d="M18 7V5a2 2 0 0 0-2-2H6a3 3 0 0 0 0 6"/><path d="M3 6v12a3 3 0 0 0 3 3h13a2 2 0 0 0 2-2v-8a2 2 0 0 0-2-2H6a3 3 0 0 1-3-3Z"/><path d="M16.5 14h.01"/>',
    ];
    return '<svg class="icon icon--' . $name . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $paths[$name] . '</svg>';
}

function logo_mark()
{
    return '<svg class="logo__mark" viewBox="0 0 32 32" aria-hidden="true" focusable="false"><circle cx="16" cy="16" r="16" fill="currentColor"/><path d="M10.5 9.2 23 16l-12.5 6.8 3.1-6.8z" fill="#fff"/></svg>';
}
