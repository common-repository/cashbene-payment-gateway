<?php

namespace Cashbene\GatewayWordpress\Kernel;

use Cashbene\GatewayWordpress\Kernel\Initializer\BindInterface;
use Cashbene\GatewayWordpress\Kernel\Initializer\EndpointInitializationInterface;
use Cashbene\GatewayWordpress\Kernel\Initializer\HookInitializer;
use Cashbene\GatewayWordpress\Kernel\Initializer\ComponentInitializationInterface;
use Cashbene\GatewayWordpress\Kernel\Loader\GatewayLoader;
use Cashbene\GatewayWordpress\Kernel\Loader\ScriptsLoader;
use Cashbene\GatewayWordpress\Kernel\Loader\TemplateLoader;

session_start();
$config = require __DIR__ . '/../config.php';

foreach ($config as $key =>  $item) {
    App::bind($key, json_decode(
        json_encode($item)
    ));
}

unset($config);

App::bind('hookInitializer', new HookInitializer());
App::bind('templateLoader', new TemplateLoader());
App::bind('cashbeneGateway', (new GatewayLoader())->getGateway());

(new ScriptsLoader())
    ->setScripts(App::get('scripts'))
    ->setStyles(App::get('styles'))
    ->load();

function initApp() {
    $apiRouteNamespace = App::get('CASHBENE_API_ROUTE_NAMESPACE');
    $apiRouteVersion = App::get('CASHBENE_API_ROUTE_VERSION');

    $componentsNamespace = App::get('CASHBENE_COMPONENTS_NAMESPACE');
    $componentsNamespaceLength = strlen($componentsNamespace);

    $endpointsNamespace = App::get('CASHBENE_API_NAMESPACE');
    $endpointsNamespaceLength = strlen($endpointsNamespace);

    $declaredClasses = require App::get('CASHBENE_PLUGIN_DIR') . '/vendor/composer/autoload_classmap.php';

    foreach ($declaredClasses as $class => $file) {
        $isComponent = substr($class, 0, $componentsNamespaceLength) === $componentsNamespace;
        $isEndpoint = substr($class, 0, $endpointsNamespaceLength) === $endpointsNamespace;

        if (($isComponent || $isEndpoint) && class_exists($class)) {
            $reflection = new \ReflectionClass($class);

            if ($reflection->isAbstract() || $reflection->isInterface()) {
                continue;
            }

            if ($isComponent && $reflection->implementsInterface(ComponentInitializationInterface::class)) {
                $instance = new $class;
                $instance->boot();
            }

            if ($isEndpoint && $reflection->implementsInterface(EndpointInitializationInterface::class)) {
                add_action('rest_api_init', function () use ($class, $apiRouteNamespace, $apiRouteVersion) {
                    $instance = new $class;
                    $routes = $instance->routes();

                    foreach ($routes as $route) {
                        $options = !empty($route[3]) && is_array($route[3]) ? $route[3] : [];
                        $options['methods'] = $route[1];
                        $options['callback'] = [$instance, $route[2]];
                        $options['permission_callback'] = '__return_true';
                        $version = isset($route[4]) && is_string($route[4]) ? $route[4] : $apiRouteVersion;

                        register_rest_route(
                            $apiRouteNamespace . '/' . $version, $route[0], $options
                        );
                    }
                });
            }

            if ($reflection->implementsInterface(BindInterface::class)) {
                if (isset($instance) === false) {
                    $instance = new $class;
                }

                App::bind($instance->bindName(), $instance);
            }

            unset($instance);
        }
    }
}

initApp();

