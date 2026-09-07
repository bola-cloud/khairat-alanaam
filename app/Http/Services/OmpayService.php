<?php

namespace App\Http\Services;

use App\Traits\ConsumesExternalServices;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OmpayService
{
    use ConsumesExternalServices;

    protected $baseUri;
    protected $apiKey;
    protected $apiSecret;

    public function __construct()
    {
        $this->baseUri = config('services.ompay.base_url');
        $this->apiKey = config('services.ompay.api_key');
        $this->apiSecret = config('services.ompay.api_secret');
    }

    public function handlePayment($value, $currency, $referenceNumber = null)
    {
        if (is_null($referenceNumber)) {
            $referenceNumber = 'ORD-' . time() . '-' . rand(1000, 9999);
        }

        $data = [
            'amount' => (float) $value,
            'currency' => strtoupper($currency),
            'return_url' => route('approval'),
            'reference_number' => (string) $referenceNumber,
        ];

        $response = Http::withHeaders([
            'OMPAY-API-Key' => $this->apiKey,
            'OMPAY-API-Secret' => $this->apiSecret,
            'Content-Type' => 'application/json',
        ])->post($this->baseUri . '/api/v1/transactions/bank-hosted', $data);

        if ($response->successful()) {
            $responseData = $response->json();
            
            if (isset($responseData['data']['redirect_url'])) {
                session()->put('ompay_transaction_id', $responseData['data']['transaction_id'] ?? null);
                session()->put('ompay_reference_number', $referenceNumber);
                
                return redirect($responseData['data']['redirect_url']);
            }
        }

        Log::error('OMPAY payment initiation failed', ['response' => $response->body()]);
        
        return redirect()->back()->with('error', 'Unable to initiate payment with OMPAY.');
    }

    public function handleApproval(Request $request = null)
    {
        $data = ['success' => false, 'amount' => '', 'message' => 'We can not capture the payment. Please, Try again!'];

        // Truepay appends transaction_id and reference_number to the return_url
        $transactionId = request()->query('transaction_id') ?? session()->get('ompay_transaction_id');
        $referenceNumber = request()->query('reference_number') ?? session()->get('ompay_reference_number');

        if ($transactionId || $referenceNumber) {
            
            $payload = [];
            if ($transactionId) {
                $payload['transaction_id'] = $transactionId;
            } elseif ($referenceNumber) {
                $payload['reference_number'] = $referenceNumber;
            }

            $response = Http::withHeaders([
                'OMPAY-API-Key' => $this->apiKey,
                'OMPAY-API-Secret' => $this->apiSecret,
                'Content-Type' => 'application/json',
            ])->post($this->baseUri . '/api/v1/transactions/inquiry', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                
                if (isset($responseData['data']['status']) && $responseData['data']['status'] === 'SUCCESSFUL') {
                    $data['success'] = true;
                    $data['amount'] = $responseData['data']['amount'] ?? '';
                    $data['message'] = 'Payment Successful!';
                    
                    // clear session
                    session()->forget('ompay_transaction_id');
                    session()->forget('ompay_reference_number');
                } else {
                    $data['message'] = 'Payment was not successful. Status: ' . ($responseData['data']['status'] ?? 'UNKNOWN');
                }
            } else {
                Log::error('OMPAY inquiry failed', ['response' => $response->body()]);
            }
        }

        return $data;
    }
}
