<?php

return [
    'item' => [
        'paths' => [
            'large' => base_path('public/upload/product/large/'),
            'medium' => base_path('public/upload/product/medium/'),
            'small' => base_path('public/upload/product/small/'),
        ],
        'sizes' => [
            'large' => ['width' => 620, 'height' => 400],
            'medium' => ['width' => 400, 'height' => 260],
            'small' => ['width' => 120, 'height' => 120],
        ],
    ],
    'multi_images' => [
        'paths' => [
            'multiple' => base_path('public/upload/product/multiple/'),
        ],
        'sizes' => [
            'multiple' => ['width' => 120, 'height' => 120],
        ],
    ],
    'profile' => [
        'customer' => [
            'large' => base_path('public/upload/customer-profile/large/'),
            'medium' => base_path('public/upload/customer-profile/medium/'),
            'small' => base_path('public/upload/customer-profile/small/'),
        ],
        'admin' => [
            'large' => base_path('public/upload/admin-profile/large/'),
            'medium' => base_path('public/upload/admin-profile/medium/'),
            'small' => base_path('public/upload/admin-profile/small/'),
        ],
        'client' => [
            'large' => base_path('public/upload/client-profile/large/'),
            'medium' => base_path('public/upload/client-profile/medium/'),
            'small' => base_path('public/upload/client-profile/small/'),
        ],
        'delivery' => [
            'large' => base_path('public/upload/delivery-profile/large/'),
            'medium' => base_path('public/upload/delivery-profile/medium/'),
            'small' => base_path('public/upload/delivery-profile/small/'),
        ],
    ],
    'resize' => [
        'large' => ['width' => 100, 'height' => 100],
        'medium' => ['width' => 80, 'height' => 80],
        'small' => ['width' => 60, 'height' => 60],
    ],
    'document' => [
        'customer_document_paths' => [
            'large' => base_path('public/upload/customer-document/large/'),
            'medium' => base_path('public/upload/customer-document/medium/'),
            'small' => base_path('public/upload/customer-document/small/'),
        ],
        'client_document_paths' => [
            'large' => base_path('public/upload/client-document/large/'),
            'medium' => base_path('public/upload/client-document/medium/'),
            'small' => base_path('public/upload/client-document/small/'),
        ],
        'delivery_document_paths' => [
            'large' => base_path('public/upload/delivery-document/large/'),
            'medium' => base_path('public/upload/delivery-document/medium/'),
            'small' => base_path('public/upload/delivery-document/small/'),
        ],
        'doc_resize' => [
            'large' => ['width' => 1200, 'height' => 1500],
            'medium' => ['width' => 800, 'height' => 1000],
            'small' => ['width' => 200, 'height' => 200],
        ],
    ],
    'vehicle' => [
        'paths' => [
            'large' => base_path('public/upload/vehicle/large/'),
            'medium' => base_path('public/upload/vehicle/medium/'),
            'small' => base_path('public/upload/vehicle/small/'),
        ],
        'sizes' => [
            'large' => ['width' => 300, 'height' => 200],
            'medium' => ['width' => 200, 'height' => 100],
            'small' => ['width' => 100, 'height' => 60],
        ],
    ],
    'logo' => [ 
        'paths' => [
            'logo' => base_path('public/upload/site-setting/'),
        ],
        'sizes' => [
            'logo' => ['width' => 150, 'height' => 60],
        ],
    ],
    'about' => [ 
        'paths' => [
            'about' => base_path('public/upload/about/'),
        ],
        'sizes' => [
            'about' => ['width' => 800, 'height' => 450],
        ],
    ],
    'category' => [ 
        'paths' => [
            'category' => base_path('public/upload/category/'),
        ],
        'sizes' => [
            'category' => ['width' => 100, 'height' => 100],
        ],
    ],
    'brand' => [ 
        'paths' => [
            'brand' => base_path('public/upload/brand/'),
        ],
        'sizes' => [
            'brand' => ['width' => 100, 'height' => 100],
        ],
    ],
    'hero' => [ 
        'paths' => [
            'hero' => base_path('public/upload/hero/'),
        ],
        'sizes' => [
            'hero' => ['width' => 1864, 'height' => 1450],
        ],
    ],
];

