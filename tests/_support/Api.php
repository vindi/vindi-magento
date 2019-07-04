<?php

trait Api
{
    /**
     * Perform request to API.
     *
     * @param string $endpoint
     * @param string $method
     * @param array  $data
     *
     * @return array|bool|mixed
     */
    private function request($method, $endpoint, $body = [])
    {
        $method    = 'GET';
        $url = "https://sandbox-app.vindi.com.br/api/v1/$endpoint";
        $body = json_encode($body);
        $ch = curl_init();
        $ch_options = [
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERAGENT      => 'Vindi-Semaphore',
            CURLOPT_SSLVERSION     => 'CURL_SSLVERSION_TLSv1_2',
            CURLOPT_USERPWD        =>  getenv('VINDI_API_KEY'). ':',
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


        if (curl_errno($ch) || $response === false)
            return false;

        curl_close($ch);

        $responseBody = json_decode($body, true);

        if (! $responseBody)
            return false;

        return array(
            'status'        => $statusCode,
            'response_body' => $responseBody
        );
    }


    public function getBill($billId)
    {
        return $this->request('GET', "bills/$billId");
    }
}