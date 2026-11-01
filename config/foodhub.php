<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Order tax rate
    |--------------------------------------------------------------------------
    |
    | Applied to cart subtotal at checkout and when creating orders.
    | Example: 0.05 = 5%
    |
    */
    'tax_rate' => (float) env('FOODHUB_TAX_RATE', 0.05),

    'contact_inbox_email' => env('FOODHUB_CONTACT_INBOX_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@foodhub.test')),

    'site' => [
        'name' => env('FOODHUB_SITE_NAME', 'FoodHub'),
        'tagline' => env('FOODHUB_TAGLINE', 'Order online or book a table at your favourite restaurant.'),
        'address' => env('FOODHUB_ADDRESS', 'Kolkata, India'),
        'phone' => env('FOODHUB_PHONE', '+91 98765 43210'),
        'email' => env('FOODHUB_EMAIL', 'hello@foodhub.test'),
    ],

    'about' => [
        'headline' => 'Welcome to FoodHub',
        'paragraphs' => [
            'FoodHub connects hungry customers with local restaurants. Browse menus, order for delivery or pickup, and reserve a table in a few clicks.',
            'Restaurant owners can list their kitchen, manage menus and orders, and grow their business on our platform.',
        ],
        'years_experience' => 5,
        'restaurant_count_label' => 'Partner restaurants',
    ],

    'team' => [
        [
            'name' => 'Priya Sharma',
            'role' => 'Head Chef',
            'image' => 'img/team-1.jpg',
        ],
        [
            'name' => 'Arjun Mehta',
            'role' => 'Sous Chef',
            'image' => 'img/team-2.jpg',
        ],
        [
            'name' => 'Sneha Das',
            'role' => 'Pastry Chef',
            'image' => 'img/team-3.jpg',
        ],
        [
            'name' => 'Rahul Verma',
            'role' => 'Restaurant Manager',
            'image' => 'img/team-4.jpg',
        ],
    ],

    'legal' => [
        'privacy' => <<<'TEXT'
We respect your privacy. FoodHub collects account details, order history, and messages you submit through our contact form solely to operate the service.

We do not sell your personal data. Payment processing is handled by our payment partner; we store order totals and payment status only.

You may request deletion of your account by contacting us at the address shown on our Contact page.
TEXT,
        'terms' => <<<'TEXT'
By using FoodHub you agree to place orders and bookings in good faith. Restaurants may confirm or decline table reservations.

Prices and availability are set by each restaurant. Refunds for paid orders follow the restaurant's policy and applicable law.

FoodHub is provided as-is. For support, use the Contact page or email listed in the footer.
TEXT,
    ],

];
