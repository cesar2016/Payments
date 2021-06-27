<?php

namespace App\Services;

use App\Traits\ConsumeExternalService;
//use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request;

class PayPalService
{     
    use ConsumeExternalService;

    protected $baseUri;

    protected $clienId;

    protected $clientSecret;


    public function __construct()
    {
        $this->baseUri = config("services.paypal.base_uri");
        $this->clienId = config("services.paypal.client_id");
        $this->clientSecret = config("services.paypal.client_secret");         
         
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $headers['Authorization'] = $this->resolveAccessToken();
         
    }

    public function decodeResponse($response)
    {
        
        return  json_decode($response);

    }
    public function resolveAccessToken()
    {
        $credentials = base64_encode("{$this->clienId}:{$this->clientSecret}");     

        return "Basic {$credentials}";
    }

    public function handlePayment(Request $request)
    {
        $order = $this->createOrder($request->value, $request->currency);
         
        $orderObjet = json_decode($order);
        $orderLinks =  collect($orderObjet->links);

        $approve = $orderLinks->where('rel', 'approve')->first(); 

        session()->put('approvalId', $orderObjet->id);

        return redirect($approve->href);
       
    }

    public function handleApproval($value, $currency)
    {
        if (session()->has('approvalId')) {
            $approvalId = session()->get('aprovalId');
            $payment = $this->capturePayment($approvalId);
 
            
            return redirect()
            ->route('home')
            ->withSuccess('Thanks for your buy');


        }

        return redirect()
        ->route('home')
        ->withErrors('No se puedo capturar el pago, try againt PLEASE');
        
    }

    public function createOrder($value, $currency)
    {
        //$credentials = base64_encode("{$this->clienId}:{$this->clientSecret}");     

        return $this->makeRequest(
            'POST',
            '/v2/checkout/orders',
            [],
            [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    0 => [
                        'amount'=> [
                            'currency_code' => strtoupper($currency),
                            'value'=> $value,
                        ]
                    ]
                ],
                'aplication_context'=> [
                    'brand_name'=> config('app.name'),
                    'shipping_preference'=> 'NO_SHIPPING',
                    'user_action'=> 'PAY_NOW',
                    'return_url'=> route('approval'),
                    'return_url'=> route('cancelled'),
                ]
            ],
            [],
            $isJsonRequest = true,
        );
    }

    /* [href] => https://www.sandbox.paypal.com/checkoutnow?token=3UU45756351145624
    [rel] => approve
    [method] => GET */
    //id pago 3UU45756351145624

    public function capturePayment($approvalId) 
    {
        return $this->makeRequest(
            'POST',
            "/v2/checkout/orders/{$approvalId}/capture",
            [],
            [],
            [
                'content-type'=> 'application/json'
            ],         
        );
    }
    
}