<?php

return [
    'distros' => [
        'ubuntu-14-04-x64',
        'docker'
    ],
    'regions' => [
        'ams2' => [
            'name' => 'Amsterdam 2'
        ],
        'ams3' => [
            'name' => 'Amsterdam 3'
        ],
        'nyc1' => [
            'name' => 'New York 1'
        ],
        'nyc2' => [
            'name' => 'New York 2'
        ],
        'nyc3' => [
            'name' => 'New York 3'
        ],
        'sgp1' => [
            'name' => 'Singapore 1'
        ],
        'sfo1' => [
            'name' => 'San Francisco 1'
        ],
        'lon1' => [
            'name' => 'London 1'
        ],
        'fra1' => [
            'name' => 'Frankfurt 1'
        ],
        'tor1' => [
            'name' => 'Toronto 1'
        ],
    ],
    'sizes' => [
        '512mb' => [
            'slug' => '512mb',
            'available' => 1,
            'memory' => 512,
            'vcpus' => 1,
            'disk' => 20,
            'transfer' => 1,
            'priceMonthly' => 5,
            'priceHourly' => 0.00744,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams1',
                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '1gb' => [
            'slug' => '1gb',
            'available' => 1,
            'memory' => 1024,
            'vcpus' => 1,
            'disk' => 30,
            'transfer' => 2,
            'priceMonthly' => 10,
            'priceHourly' => 0.01488,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams1',
                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '2gb' => [
            'slug' => '2gb',
            'available' => 1,
            'memory' => 2048,
            'vcpus' => 2,
            'disk' => 40,
            'transfer' => 3,
            'priceMonthly' => 20,
            'priceHourly' => 0.02976,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams1',
                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '4gb' => [
            'slug' => '4gb',
            'available' => 1,
            'memory' => 4096,
            'vcpus' => 2,
            'disk' => 60,
            'transfer' => 4,
            'priceMonthly' => 40,
            'priceHourly' => 0.05952,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams1',
                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '8gb' => [
            'slug' => '8gb',
            'available' => 1,
            'memory' => 8192,
            'vcpus' => 4,
            'disk' => 80,
            'transfer' => 5,
            'priceMonthly' => 80,
            'priceHourly' => 0.11905,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams1',
                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '16gb' => [
            'slug' => '16gb',
            'available' => 1,
            'memory' => 16384,
            'vcpus' => 8,
            'disk' => 160,
            'transfer' => 6,
            'priceMonthly' => 160,
            'priceHourly' => 0.2381,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams1',
                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '32gb' => [
            'slug' => '32gb',
            'available' => 1,
            'memory' => 32768,
            'vcpus' => 12,
            'disk' => 320,
            'transfer' => 7,
            'priceMonthly' => 320,
            'priceHourly' => 0.47619,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '48gb' => [
            'slug' => '48gb',
            'available' => 1,
            'memory' => 49152,
            'vcpus' => 16,
            'disk' => 480,
            'transfer' => 8,
            'priceMonthly' => 480,
            'priceHourly' => 0.71429,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
        '64gb' => [
            'slug' => '64gb',
            'available' => 1,
            'memory' => 65536,
            'vcpus' => 20,
            'disk' => 640,
            'transfer' => 9,
            'priceMonthly' => 640,
            'priceHourly' => 0.95238,
            'regions' => [
                'nyc1',
                'nyc2',
                'nyc3',

                'ams2',
                'ams3',

                'sgp1',
                'sfo1',
                'lon1',
                'fra1',
                'tor1',
            ]
        ],
    ]
];
