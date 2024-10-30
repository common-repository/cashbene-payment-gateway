<?php

namespace Cashbene\GatewayWordpress\App\Component;

use Cashbene\GatewayWordpress\Kernel\App;
use Cashbene\GatewayWordpress\Kernel\Initializer\ComponentInitializationInterface;

abstract class BaseComponent implements ComponentInitializationInterface
{
    protected $hookInitializer;

    public function __construct()
    {
        $this->hookInitializer = App::get('hookInitializer');
    }

    abstract public function boot();
}
