<?php
/**
 * Landing page content.
 *
 * Everything the landing page shows lives here so it can later be swapped for
 * database queries (e.g. a `cars` table) without touching the markup.
 * Prices are sample daily self-drive rates in Philippine pesos.
 */

return [
    'site' => [
        'name'    => 'Biyahe',
        'tagline' => 'Premium car rental in the Philippines',
        'cities'  => ['Manila', 'Cebu', 'Davao', 'Baguio', 'Iloilo'],
        // Reservation page of the booking system. Each car's "Book" button
        // links here as booking.php?car=<car-id> (e.g. ?car=toyota-fortuner).
        'booking_page' => 'booking.php',
    ],

    // Brands shown in the hero switcher. `fit` = how wide the car sits in the
    // hero stage (side profiles fill it, taller 3/4 shots sit a little smaller).
    'brands' => [
        ['slug' => 'toyota',     'name' => 'Toyota',     'color' => '#EB0A1E', 'model' => 'Fortuner',      'spec' => '7-seater SUV · Diesel · AT',      'image' => 'toyota-fortuner.webp',          'price' => 3800, 'fit' => 100],
        ['slug' => 'mitsubishi', 'name' => 'Mitsubishi', 'color' => '#E60012', 'model' => 'Montero Sport', 'spec' => '7-seater SUV · Diesel · AT',      'image' => 'mitsubishi-montero-sport.webp', 'price' => 3500, 'fit' => 70],
        ['slug' => 'hyundai',    'name' => 'Hyundai',    'color' => '#002C5E', 'model' => 'IONIQ 5 N',     'spec' => 'Electric performance · AWD',      'image' => 'hyundai-ioniq-5-n.webp',        'price' => 7500, 'fit' => 94],
        ['slug' => 'byd',        'name' => 'BYD',        'color' => '#D70C19', 'model' => 'Seal',          'spec' => 'Electric sedan · 5 seats',        'image' => 'byd-seal.webp',                 'price' => 4500, 'fit' => 82],
        ['slug' => 'honda',      'name' => 'Honda',      'color' => '#E40521', 'model' => 'Civic',         'spec' => 'Sedan · Turbo · CVT',             'image' => 'honda-civic.webp',              'price' => 3000, 'fit' => 100],
        ['slug' => 'nissan',     'name' => 'Nissan',     'color' => '#C3002F', 'model' => 'Z',             'spec' => 'Sports coupe · V6 twin-turbo',    'image' => 'nissan-z.webp',                 'price' => 9000, 'fit' => 100],
        ['slug' => 'suzuki',     'name' => 'Suzuki',     'color' => '#E30613', 'model' => 'Dzire',         'spec' => 'Sedan · Fuel saver · AT',         'image' => 'suzuki-dzire.webp',             'price' => 1800, 'fit' => 68],
        ['slug' => 'ford',       'name' => 'Ford',       'color' => '#00274E', 'model' => 'Everest',       'spec' => '7-seater SUV · Diesel · AT',      'image' => 'ford-everest.webp',             'price' => 4000, 'fit' => 98],
    ],

    'categories' => [
        'all'      => 'All',
        'sedan'    => 'Sedan',
        'suv'      => 'SUV',
        'mpv'      => 'MPV',
        'pickup'   => 'Pickup',
        'van'      => 'Van',
        'electric' => 'Electric',
        'sports'   => 'Sports',
    ],

    // The first seven cars are the ones featured under "All".
    'fleet' => [
        ['brand' => 'Toyota',     'model' => 'Fortuner',      'types' => ['suv'],              'image' => 'toyota-fortuner.webp',          'price' => 3800,  'seats' => 7,  'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'Mitsubishi', 'model' => 'Montero Sport', 'types' => ['suv'],              'image' => 'mitsubishi-montero-sport.webp', 'price' => 3500,  'seats' => 7,  'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'BYD',        'model' => 'Seal',          'types' => ['sedan', 'electric'], 'image' => 'byd-seal.webp',                'price' => 4500,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Electric'],
        ['brand' => 'Honda',      'model' => 'Civic',         'types' => ['sedan'],            'image' => 'honda-civic.webp',              'price' => 3000,  'seats' => 5,  'gear' => 'CVT', 'fuel' => 'Gasoline'],
        ['brand' => 'Toyota',     'model' => 'Hiace',         'types' => ['van'],              'image' => 'toyota-hiace.webp',             'price' => 4500,  'seats' => 15, 'gear' => 'MT', 'fuel' => 'Diesel'],
        ['brand' => 'Hyundai',    'model' => 'IONIQ 5 N',     'types' => ['electric', 'sports'], 'image' => 'hyundai-ioniq-5-n.webp',      'price' => 7500,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Electric'],
        ['brand' => 'Nissan',     'model' => 'Z',             'types' => ['sports'],           'image' => 'nissan-z.webp',                 'price' => 9000,  'seats' => 2,  'gear' => 'AT', 'fuel' => 'Gasoline'],
        ['brand' => 'Toyota',     'model' => 'Vios',          'types' => ['sedan'],            'image' => 'toyota-vios.webp',              'price' => 2000,  'seats' => 5,  'gear' => 'CVT', 'fuel' => 'Gasoline'],
        ['brand' => 'Suzuki',     'model' => 'Dzire',         'types' => ['sedan'],            'image' => 'suzuki-dzire.webp',             'price' => 1800,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Gasoline'],
        ['brand' => 'Toyota',     'model' => 'Camry',         'types' => ['sedan'],            'image' => 'toyota-camry.webp',             'price' => 4200,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Hybrid'],
        ['brand' => 'Ford',       'model' => 'Everest',       'types' => ['suv'],              'image' => 'ford-everest.webp',             'price' => 4000,  'seats' => 7,  'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'Honda',      'model' => 'CR-V',          'types' => ['suv'],              'image' => 'honda-cr-v.webp',               'price' => 3800,  'seats' => 7,  'gear' => 'CVT', 'fuel' => 'Gasoline'],
        ['brand' => 'Hyundai',    'model' => 'Santa Fe',      'types' => ['suv'],              'image' => 'hyundai-santa-fe.webp',         'price' => 4200,  'seats' => 7,  'gear' => 'AT', 'fuel' => 'Hybrid'],
        ['brand' => 'Toyota',     'model' => 'Land Cruiser',  'types' => ['suv'],              'image' => 'toyota-land-cruiser.webp',      'price' => 12000, 'seats' => 7,  'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'Nissan',     'model' => 'Patrol',        'types' => ['suv'],              'image' => 'nissan-patrol.webp',            'price' => 10000, 'seats' => 7,  'gear' => 'AT', 'fuel' => 'Gasoline'],
        ['brand' => 'BYD',        'model' => 'Atto 3',        'types' => ['suv', 'electric'],  'image' => 'byd-atto-3.webp',               'price' => 3500,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Electric'],
        ['brand' => 'BYD',        'model' => 'Sealion 7',     'types' => ['suv', 'electric'],  'image' => 'byd-sealion-7.webp',            'price' => 4800,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Electric'],
        ['brand' => 'Toyota',     'model' => 'Innova Zenix',  'types' => ['mpv'],              'image' => 'toyota-innova-zenix.webp',      'price' => 3200,  'seats' => 7,  'gear' => 'CVT', 'fuel' => 'Hybrid'],
        ['brand' => 'Mitsubishi', 'model' => 'Xpander',       'types' => ['mpv'],              'image' => 'mitsubishi-xpander.webp',       'price' => 2500,  'seats' => 7,  'gear' => 'AT', 'fuel' => 'Gasoline'],
        ['brand' => 'Hyundai',    'model' => 'Stargazer',     'types' => ['mpv'],              'image' => 'hyundai-stargazer.webp',        'price' => 2400,  'seats' => 7,  'gear' => 'IVT', 'fuel' => 'Gasoline'],
        ['brand' => 'BYD',        'model' => 'eMAX 7',        'types' => ['mpv', 'electric'],  'image' => 'byd-emax-7.webp',               'price' => 3800,  'seats' => 7,  'gear' => 'AT', 'fuel' => 'Electric'],
        ['brand' => 'Toyota',     'model' => 'Hilux',         'types' => ['pickup'],           'image' => 'toyota-hilux.webp',             'price' => 3200,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'Mitsubishi', 'model' => 'Triton',        'types' => ['pickup'],           'image' => 'mitsubishi-triton.webp',        'price' => 3200,  'seats' => 5,  'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'Hyundai',    'model' => 'Staria',        'types' => ['van'],              'image' => 'hyundai-staria.webp',           'price' => 6000,  'seats' => 11, 'gear' => 'AT', 'fuel' => 'Diesel'],
        ['brand' => 'Toyota',     'model' => 'Alphard',       'types' => ['van'],              'image' => 'toyota-alphard.webp',           'price' => 12500, 'seats' => 7,  'gear' => 'CVT', 'fuel' => 'Hybrid'],
        ['brand' => 'Toyota',     'model' => 'GR Supra',      'types' => ['sports'],           'image' => 'toyota-gr-supra.webp',          'price' => 9500,  'seats' => 2,  'gear' => 'AT', 'fuel' => 'Gasoline'],
    ],

    'features' => [
        ['icon' => 'delivery', 'tone' => 'green', 'title' => '24-hour car delivery'],
        ['icon' => 'support',  'tone' => 'rose',  'title' => '24/7 roadside assistance'],
        ['icon' => 'shield',   'tone' => 'blue',  'title' => 'Fully insured, every model'],
        ['icon' => 'wallet',   'tone' => 'sand',  'title' => 'Pay with GCash, Maya or card'],
    ],
];
