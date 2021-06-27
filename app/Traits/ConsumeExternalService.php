<?php

//DATOS PAYPAL
// User comprados: 
//exampleArg@examplearg.com | cesar001
//DATOS VENDEDOR PAYPAL
//examplebuss@example.com | cesar001

//$paypal = new App\Services\PayPalService;
//$paypal->makeRequest('GET', '/v1/invoicing/invoices/');

namespace App\Traits;
use GuzzleHttp\Client;

trait ConsumeExternalService
{
    public function makeRequest($method, $requestUrl, $queryParams = [], 
    $formParams = [], $headers = [], $isJsonRequest = false)
    {
        $client = new Client ([
            'base_uri' => $this->baseUri,
        ]);

        if(method_exists($this, 'resolveAuthorization')){
            $this->resolveAuthorization($queryParams, $formParams, $headers);
        }

        $response = $client->request($method, $requestUrl, [
            $isJsonRequest ? 'json' : 'form_params' => $formParams,
            'headers' => $headers,
            'query' => $queryParams,
        ]);

        $response = $response->getBody()->getContents();

        if(method_exists($this, 'decodeResponse')){
            $this->decodeResponse($response);
        }        

        return $response;

    }
    
}