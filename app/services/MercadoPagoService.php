<?php

namespace App\Services;

use App\Models\Currency;
use App\Traits\ConsumeExternalService;
use App\Services\CurrencyConversionService;
//use GuzzleHttp\Psr7\Request;
use Illuminate\Http\Request;

class MercadoPagoService
{     
    use ConsumeExternalService;

    protected $baseUri;

    protected $key;

    protected $secret;

    protected $baseCurrency;

    protected $converter;


    public function __construct(CurrencyConversionService $converter)
    {
        $this->baseUri = config('services.mercadopago.base_uri');
        $this->key = config('services.mercadopago.key');
        $this->secret = config('services.mercadopago.secret');
        $this->baseCurrency = config('services.mercadopago.base_currency'); 
        
        $this->converter = $converter;
         
    }

    public function resolveAuthorization(&$queryParams, &$formParams, &$headers)
    {
        $queryParams['access_token'] = $this->resolveAccessToken();
         
    }

    public function decodeResponse($response)
    {
        
        return  json_decode($response);

    }
    public function resolveAccessToken()
    {
         return $this->secret;
    }

    public function handlePayment(Request $request)
    {
       //dd($request->all());

       $request->validate([
        'card_network' => 'required',
        'card_token' => 'required',
        'email' => 'required',
        ]);

        $payment = $this->createPayment(
            $request->value,
            $request->currency,
            $request->card_network,
            $request->card_token,
            $request->email,
        );

        if (json_decode($payment)->status === "approved") {
            $name = json_decode($payment)->payer->first_name;
            $currency = strtoupper(json_decode($payment)->currency_id);
            $amount = number_format(json_decode($payment)->transaction_amount, 0, ',', '.');

            $originalAmount = $request->value;
            $originalCurrency = strtoupper($request->currency);

            return redirect()
                ->route('home')
                ->withSuccess(['payment' => "Thanks, {$name}. We received your {$originalAmount}{$originalCurrency} payment ({$amount}{$currency})."]);
        }

        return redirect()
            ->route('home')
            ->withErrors('We were unable to confirm your payment. Try again, please');
       
    }

    public function createPayment($value, $currency, $cardNetwork, $cardToken, $email, $installments = 1)
    {
        return $this->makeRequest(
            'POST',
            '/v1/payments',
            [],
            [
                'payer' => [
                    'email' => $email,
                ],
                'binary_mode' => true,
                'transaction_amount' => round($value * $this->resolveFactor($currency)),
                'payment_method_id' => $cardNetwork,
                'token' => $cardToken,
                'installments' => $installments,
                'statement_descriptor' => config('app.name'),
            ],
            [],
            $isJsonRequest = true,
        );
    }

    public function resolveFactor($currency)
    {
        return $this->converter
            ->convertCurrency($currency, $this->baseCurrency);
    }
    
}