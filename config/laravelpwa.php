<?php

return [
    'name' => 'The Pain House',
    'manifest' => [
        'name' => env('APP_NAME', 'The Pain House'),
        'short_name' => 'The Pain House',
        'start_url' => '/',
        'background_color' => '#1a1a1a',
        'theme_color' => '#4f46e5',
        'display' => 'standalone',
        'orientation'=> 'portrait',
        'status_bar'=> 'black',
        'icons' => [
            '72x72' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '96x96' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '128x128' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '144x144' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '152x152' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '192x192' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '384x384' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
            '512x512' => [
                'path' => '/images/hl.png',
                'purpose' => 'any'
            ],
        ],
        'splash' => [
            '640x1136' => '/images/hl.png',
            '750x1334' => '/images/hl.png',
            '828x1792' => '/images/hl.png',
            '1125x2436' => '/images/hl.png',
            '1242x2208' => '/images/hl.png',
            '1242x2688' => '/images/hl.png',
            '1536x2048' => '/images/hl.png',
            '1668x2224' => '/images/hl.png',
            '1668x2388' => '/images/hl.png',
            '2048x2732' => '/images/hl.png',
        ],
        'shortcuts' => [
            [
                'name' => 'Dashboard',
                'description' => 'View your workout dashboard',
                'url' => '/dashboard',
                'icons' => [
                    "src" => "/images/hl.png",
                    "purpose" => "any"
                ]
            ],
            [
                'name' => 'Start Workout',
                'description' => 'Start a new workout session',
                'url' => '/workout/start',
                'icons' => [
                    "src" => "/images/hl.png",
                    "purpose" => "any"
                ]
            ],
            [
                'name' => 'Progress',
                'description' => 'View your progress',
                'url' => '/progress',
                'icons' => [
                    "src" => "/images/hl.png",
                    "purpose" => "any"
                ]
            ]
        ],
        'custom' => []
    ]
];