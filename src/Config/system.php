<?php

return [
    [
        'key'       => 'sales.carriers.ups',
        'name'      => 'ups::app.admin.system.ups',
        'info'      => 'ups::app.admin.system.ups-description',
        'sort'      => 4,
        'fields'    => [
            [
                'name'          => 'title',
                'title'         => 'ups::app.admin.system.title',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'description',
                'title'         => 'ups::app.admin.system.description',
                'type'          => 'textarea',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'ups_active',
                'title'         => 'ups::app.admin.system.status',
                'type'          => 'boolean',
            ], [
                'name'          => 'mode',
                'title'         => 'ups::app.admin.system.mode',
                'type'          => 'select',
                'validation'    => 'required',
                'options'       => [
                    [
                        'title'     => 'Development',
                        'value'     => 'DEVELOPMENT',
                    ], [
                        'title'     => 'Live',
                        'value'     => "LIVE",
                    ],
                ],
            ], [
                'name'          => 'access_license_key',
                'title'         => 'ups::app.admin.system.access-license-number',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'user_id',
                'title'         => 'ups::app.admin.system.user-id',
                'type'          => 'text',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'password',
                'title'         => 'ups::app.admin.system.password',
                'type'          => 'password',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'shipper_number',
                'title'         => 'ups::app.admin.system.shipper',
                'type'          => 'text',
                'channel_based' => false,
                'locale_based'  => true,
            ], [
                'name'          => 'container',
                'title'         => 'ups::app.admin.system.container',
                'type'          => 'select',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title'     => 'Package',
                        'value'     => '02',
                    ], [
                        'title'     => 'UPS Letter',
                        'value'     => '01',
                    ], [
                        'title'     => 'UPS Tube',
                        'value'     => '03',
                    ], [
                        'title'     => 'UPS Pak',
                        'value'     => '04',
                    ], [
                        'title'     => 'UPS Express Box',
                        'value'     => '21',
                    ],
                ],
            ], [
                'name'          => 'weight_unit',
                'title'         => 'ups::app.admin.system.weight-unit',
                'type'          => 'select',
                'validation'    => 'required',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title'     => 'LBS',
                        'value'     => 'LBS'
                    ], [
                        'title'     => 'KGS',
                        'value'     => 'KGS',
                    ],
                ],
            ], [
                'name'          => 'services',
                'title'         => 'ups::app.admin.system.allowed-methods',
                'type'          => 'multiselect',
                'channel_based' => false,
                'locale_based'  => true,
                'options'       => [
                    [
                        'title'     => 'Next Day Air Early AM',
                        'value'     => '14',
                    ], [
                        'title'     => 'Next Day Air',
                        'value'     => '01',
                    ], [
                        'title'     => 'Next Day Air Saver',
                        'value'     => '13',
                    ], [
                        'title'     => '2nd Day Air AM',
                        'value'     => '59',
                    ], [
                        'title'     => '2nd Day Air',
                        'value'     => '02',
                    ], [
                        'title'     => '3 Day Select',
                        'value'     => '12',
                    ], [
                        'title'     => 'Ups Ground',
                        'value'     => '03',
                    ], [
                        'title'     => 'UPS Worldwide Express',
                        'value'     => '07',
                    ], [
                        'title'     => 'UPS Worldwide Express Plus',
                        'value'     => '54',
                    ], [
                        'title'     => 'UPS Worldwide Expedited',
                        'value'     => '08',
                    ], [
                        'title'     => 'UPS World Wide Saver',
                        'value'     => '65',
                    ],
                ],
            ],
        ],
    ],
];