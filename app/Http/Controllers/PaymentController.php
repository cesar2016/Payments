<?php

namespace App\Http\Controllers;

use App\Resolvers\PaymentPlatformResolver;
use App\Services\MercadoPagoService;
use App\Services\PayPalService;
use App\Services\StripeService;
use Illuminate\Http\Request;
//use GuzzleHttp\Psr7\Request;

class PaymentController extends Controller
{

   
    protected $paymentPlatformResolver;

    public function __construct(PaymentPlatformResolver $paymentPlatformResolver)
                                
    {
        
        $this->middleware('auth');        
        //$this->$paymentPlatformResolver =  $paymentPlatformResolver;
        
    }

    public function pay(Request $request)
    {

        $rules = [
            'value' => ['required', 'numeric', 'min:5'],
            'currency' => ['required', 'exists:currencies,iso'],
            'payment_platform' => ['required', 'exists:payment_platforms,id'],
        ];        

        $request->validate($rules);

        /* $paymentPlatform = $this->paymentPlatformResolver
        ->resolveService($request->payment_platform); */ 
        
        if ($request->payment_platform == 1) {
            $paymentPlatform = resolve(PayPalService::class);
            session()->put('paymentPlatformId', $request->payment_platform);
            return  $paymentPlatform->handlePayment($request);
        }   
        if ($request->payment_platform == 2) {
            
            $paymentPlatform = resolve(StripeService::class);
            session()->put('paymentPlatformId', $request->payment_platform);
            
            return  $paymentPlatform->handlePayment($request);
        }  
        if ($request->payment_platform == 3) {                
            
            $paymentPlatform = resolve(MercadoPagoService::class);
            session()->put('paymentPlatformId', $request->payment_platform);            
            
            return $paymentPlatform->handlePayment($request);

             
        }          
        

    }

    public function approval()
    {
        if (session()->has('paymentPlatformId')) {

        $paymentPlatform = $this->paymentPlatformResolver
        ->resolveService(session()->get('paymentPlatformId'));

        return  $paymentPlatform->handlePayment();
        }

        return redirect()
        ->route('home')
        ->withErrors('No podemos obtener la plataforma que elegiste');
        
    }

    public function cancelled()
    {
         
        return redirect()
        ->route('home')
        ->withErrors('You cancelled the payment');

    }
}
