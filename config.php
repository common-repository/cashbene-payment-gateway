<?php

return [
    "scripts" => [
        [
            "path" => '/public/dist/js/bundle.js',
            "name" => 'bundle',
            "in_footer" => true
        ]
    ],
    "styles" => [
        [
            "path" => '/public/dist/css/bundle.css',
            "name" => 'bundle.css'
        ]
    ],
    "CASHBENE_PLUGIN_DIR" => __DIR__,
    "CASHBENE_PLUGIN_VERSION" => get_plugin_data(__DIR__ . '/cashbene-gateway.php')['Version'],
    "CASHBENE_PLUGIN_URL" => esc_url(plugins_url('', __FILE__)),
    "CASHBENE_TEMPLATES_DIR" => __DIR__ . '/templates',
    "CASHBENE_FRONTEND_ASSETS_DIR" => __DIR__ . '/public/dist',
    "CASHBENE_COMPONENTS_NAMESPACE" => "Cashbene\GatewayWordpress\App\Component",
    "CASHBENE_API_NAMESPACE" => "Cashbene\GatewayWordpress\App\Api",
    "CASHBENE_API_ROUTE_NAMESPACE" => "cashbene-payment",
    "CASHBENE_API_ROUTE_VERSION" => "v1",
    "DATABASE_OPTIONS_KEY" => "cashbene_gateway_plugin_settings",
];
