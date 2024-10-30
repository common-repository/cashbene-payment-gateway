<?php

namespace Cashbene\GatewayWordpress\App\Api;

use Cashbene\Core\Api\RegulationContext;
use Cashbene\Core\Dto\Regulation\MerchantRegulation;
use Cashbene\Core\Exception\HttpExceptionInterface;
use Cashbene\Core\Exception\ResourceNotFoundErrorException;
use Cashbene\GatewayWordpress\App\Utils\Shop;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;

class Regulation extends BaseEndpoint
{
    public function routes(): array
    {
        return [
            ['/regulations/cashbene/(?P<type>\S+)', 'GET', 'getCashbeneTerm'],
            ['/regulations/merchant/(?P<type>\S+)', 'GET', 'getMerchantTerm'],
        ];
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see RegulationContext::getTerm()
     */
    public function getCashbeneTerm(\WP_REST_Request $data)
    {
        try {
            $result = $this->cashbeneGateway->regulationContext()->getTerm($data->get_param('type'));
            if ($data->get_header('Content-Type') != 'application/json') {
                $this->decodePdfBase64ToFile($result->file, $result->termsType);
            }
        } catch (NotEncodableValueException $httpException) {
            return $this->success([]);
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        return $this->success($result);
    }

    /**
     * @param \WP_REST_Request $data
     * @return \WP_REST_Response
     * @throws HttpExceptionInterface
     * @see MerchantRegulation::TYPE_PRIVACY_POLICY_PAGE, MerchantRegulation::TYPE_TERMS_AND_CONDITIONS_PAGE
     */
    public function getMerchantTerm(\WP_REST_Request $data)
    {
        try {
            $result = Shop::getMerchantTerm($data->get_param('type'));
        } catch (HttpExceptionInterface $httpException) {
            return $this->error($httpException);
        }

        if ($result === null) {
            return $this->error(new ResourceNotFoundErrorException(), 404);
        }

        $dto = $this->serializer->denormalize(
            $result,
            MerchantRegulation::class,
            'array'
        );

        return $this->success($dto);
    }

    /**
     * Stream pdf file to browser.
     * Can't be escaped because it's a pdf file. It's loaded from trusted external api.
     *
     * @param $pdfContent
     * @param $name
     */
    protected function decodePdfBase64ToFile($pdfContent, $name) {
        $pdfToStream = base64_decode($pdfContent);

        header("Content-type: application/octet-stream");
        header("Content-Type: application/pdf");
        header("Content-Disposition: inline; filename=$name.pdf");
        echo $pdfToStream;
    }

}
