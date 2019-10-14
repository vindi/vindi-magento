<?php

class Vindi_Subscription_Helper_Connector extends Mage_Core_Helper_Abstract
{
    use Vindi_Subscription_Trait_LogMessenger;

    /**
     * @var string
     */
    public $lastError = '';

    public function post($endpoint, $body = [])
    {
       return $this->request($endpoint, $body, 'POST');
    }

    public function get($endpoint, $body = [])
    {
       return $this->request($endpoint, $body, 'GET');
    }

    public function put($endpoint, $body = [])
    {
       return $this->request($endpoint, $body, 'PUT');
    }

    public function delete($endpoint, $body = [])
    {
       return $this->request($endpoint, $body, 'DELETE');
    }

    private function request($endpoint, $data = [], $method = 'POST')
    {
        $key = Mage::helper('vindi_subscription')->getKey();
        if (! $key) {
            return false;
        }

        $url = Mage::getStoreConfig('vindi_subscription/general/sandbox_mode') . $endpoint;
        $body = empty($data) ? null : json_encode($data);
        $dataToLog = $this->encrypt($body, $endpoint);
        $requestId = rand();

        $this->log(
            sprintf(
                "[Request #%s]: Novo Request para a API.\n%s %s\n%s",
                $requestId,
                $method,
                $url,
                $dataToLog
            ),
            'vindi_api.log'
        );

        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'Vindi-Magento/' .
                (string) Mage::getConfig()->getModuleConfig('Vindi_Subscription')->version,
            CURLOPT_SSLVERSION     => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_USERPWD        => $key . ':',
            CURLOPT_URL            => $url,
            CURLOPT_CUSTOMREQUEST  => $method
        ];

        if (! empty($body)) {
            $ch_options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $ch_options);

        $response = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

        if (curl_errno($ch) || $response === false) {
            $this->log(
                sprintf(
                    "[Request #%s]: Erro ao fazer request!\n%s",
                    $requestId,
                    print_r($response, true)
                ),
                'vindi_api.log'
            );

            return false;
        }

        curl_close($ch);

        $status = "HTTP Status: $statusCode";
        $this->log(
            sprintf(
                "[Request #%s]: Nova Resposta da API.\n%s\n%s",
                $requestId,
                $status,
                $body
            ),
            'vindi_api.log'
        );

        $responseBody = json_decode($body, true);

        if (! $responseBody) {
            $this->log(
                sprintf(
                    '[Request #%s]: Erro ao recuperar corpo do request! %s',
                    $requestId,
                    print_r($body, true)
                ),
                'vindi_api.log'
            );

            return false;
        }

        if (! $this->checkResponse($responseBody, $endpoint)) {
            return false;
        }

        return $responseBody;
    }

    /**
     * Remove sensitive content.
     *
     * @param array $body | string $endpoint
     *
     * @return array
     */
    public function encrypt($body, $endpoint)
    {
        $dataToLog = $body;

        if ('payment_profiles' === $endpoint) {
            $dataToLog = json_decode($body, true);
            $dataToLog['card_number'] = '**** *' . substr($dataToLog['card_number'], -3);
            $dataToLog['card_cvv'] = '***';
            $dataToLog = json_encode($dataToLog);
        }

        return $dataToLog;
    }

    /**
     * @param array $response
     * @param       $endpoint
     *
     * @return bool
     */
    public function checkResponse($response, $endpoint)
    {
        if (isset($response['errors']) && ! empty($response['errors'])) {
            foreach ($response['errors'] as $error) {
                $message = $this->getErrorMessage($error, $endpoint);

                Mage::getSingleton('core/session')->addError($message);

                $this->lastError = $message;
            }

            return false;
        }

        $this->lastError = '';

        return true;
    }

    /**
     * @param array $error
     * @param       $endpoint
     *
     * @return string
     */
    public function getErrorMessage($error, $endpoint)
    {
        return "Erro em $endpoint: {$error['id']}: {$error['parameter']} - {$error['message']}";
    }
}
