<?php

namespace Cashbene\Core\Api;

use Cashbene\Core\Dto\Regulation\CashbeneRegulation;
use Cashbene\Core\Exception\HttpExceptionInterface;

class RegulationContext extends Context {

    /**
     * Get specified terms
     *
     * @param string $type CashbeneRegulation::TYPE_PRIVACY_POLICY | CashbeneRegulation::TYPE_TERMS_AND_CONDITIONS
     * @return CashbeneRegulation
     * @throws HttpExceptionInterface
     */
    public function getTerm(string $type)
    {
        $response = $this->request->doRequest(
            'GET',
            "/v1/terms/{$type}",
            $this->_gateway->merchantCredentials,
            []
        );

        return $this->serializer->deserialize(
            $response->getContent(false),
            CashbeneRegulation::class,
            'json'
        );
    }
}
