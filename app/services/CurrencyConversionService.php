<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;
//use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request;

class CurrencyConversionService
{     
    use ConsumeExternalService;

    protected $baseUri;

    protected $apiKey;
 

    public function __construct()
    {
        $this->baseUri = config("services.currency_conversion.base_uri");
        $this->apiKey = config("services.currency_conversion.api_key");
               
         
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
         $queryParams['apiKey'] = $this->resolveAccessToken();
         
    }

    public function decodeResponse($response)
    {
        
        echo '<pre>';  print_r(json_decode($response));

    }
    public function resolveAccessToken()
    {
        return $this->apiKey;
         
    }
    public function convertCurrency($from, $to)
    {
         $response = $this->makeRequest(
             'GET',
             '/api/v7/convert',
             [
                 'q'=> "{$from}_{$to}",
                 'compact' => 'ultra'
             ],
         );
         return json_decode($response)->{strtoupper("{$from}_{$to}")};
          
    } 

    
}