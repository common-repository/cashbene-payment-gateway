<?php

namespace Cashbene\GatewayWordpress\App\Api;

use Cashbene\Core\Utils\Configuration;

class Integration extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/integration/environment', 'GET', 'getEnvironment'],
            ['/integration/payu-pos-id', 'GET', 'getPayuPosId']
        ];
    }

    /**
     * @return \WP_REST_Response
     * @see Configuration::getEnvironment()
     */
    public function getEnvironment()
    {
        return $this->success(['environment' => $this->cashbeneGateway->configuration->getEnvironment()]);
    }

    /**
     * @return \WP_REST_Response
     * @see Configuration::getPayuPosId()
     */
    public function getPayuPosId() {
        $environment = $this->cashbeneGateway->configuration->getEnvironment();
        return $this->success(['pos_id' => $this->cashbeneGateway->configuration->getPayuPosId($environment)]);
    }
}
