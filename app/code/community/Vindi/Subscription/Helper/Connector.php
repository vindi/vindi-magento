<?php

class Vindi_Subscription_Helper_Connector
{
    use Vindi_Subscription_Trait_LogMessenger;

    public function post($endpoint, $body)
    {
       $this->request($endpoint, $body, 'POST');
    }

    public function get($endpoint, $body)
    {
       $this->request($endpoint, $body, 'GET');
    }

    public function put($endpoint, $body)
    {
       $this->request($endpoint, $body, 'PUT');
    }

    public function delete($endpoint, $body)
    {
       $this->request($endpoint, $body, 'DELETE');
    }

    private function request($endpoint, $method = 'POST', $data = [], $dataToLog = null)
    {
        $key = Mage::helper('vindi_subscription')->getKey();
        if (! $key) {
            return false;
        }

        $url = Mage::getStoreConfig('vindi_subscription/general/sandbox_mode') . $endpoint;
        $body = $this->buildBody($data);

        $requestId = rand();

        $dataToLog = null !== $dataToLog ? $this->buildBody($dataToLog) : $body;

        $this->log(sprintf("[Request #%s]: Novo Request para a API.\n%s %s\n%s", $requestId, $method, $url,
            $dataToLog), 'vindi_api.log');

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

        if (!empty($body)) {
            $ch_options[CURLOPT_POSTFIELDS] = $body;
        }

        curl_setopt_array($ch, $ch_options);

        $response = curl_exec($ch);

        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $body = substr($response, curl_getinfo($ch, CURLINFO_HEADER_SIZE));

        if (curl_errno($ch) || $response === false) {
            $this->log(sprintf("[Request #%s]: Erro ao fazer request!\n%s", $requestId, print_r($response, true)));

            return false;
        }

        curl_close($ch);

        $status = "HTTP Status: $statusCode";
        $this->log(sprintf("[Request #%s]: Nova Resposta da API.\n%s\n%s", $requestId, $status, $body));

        $responseBody = json_decode($body, true);

        if (! $responseBody) {
            $this->log(sprintf('[Request #%s]: Erro ao recuperar corpo do request! %s', $requestId,
                print_r($body, true)));

            return false;
        }

        if (! $this->checkResponse($responseBody, $endpoint)) {
            return false;
        }

        return $responseBody;
    }

    /**
     * Build HTTP Query.
     *
     * @param array $data
     *
     * @return string
     */
    private function buildBody($data)
    {
        $body = null;

        if(!empty($data)) {
            $body = json_encode($data);
        }

        return $body;
    }

}
