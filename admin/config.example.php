<?php
// Shared settings for Market Connect.
// Keep API keys here instead of scattered across files, so there is one
// place to update them.

// Get a free key at https://openweathermap.org/api
// (sign up -> click your name top-right -> "My API keys")
define('OPENWEATHERMAP_API_KEY', 'YOUR_OPENWEATHERMAP_API_KEY_HERE');

// Used later once real email sending is wired up.
define('ALERT_FROM_EMAIL', 'noreply@marketconnect.local');
define('ALERT_FROM_NAME', 'Market Connect');

// The 10 crops Market Connect currently supports. Used to build the
// produce dropdown on the seller and buyer dashboards. These names must
// match the `ProduceName` values seeded in the `produce` table exactly.
$market_connect_produce_list = [
    'Maize',
    'Soybeans',
    'Groundnuts',
    'Common Beans',
    'Sweet Potatoes',
    'Rice',
    'Chilli Pepper',
    'Tobacco',
    'Pigeon Peas',
    'Tomatoes',
];
?>
