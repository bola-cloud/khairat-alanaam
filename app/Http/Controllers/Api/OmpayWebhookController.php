<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OmpayWebhookController extends Controller
{
    public function handleWebhook(Request $request)
    {
        Log::info('OMPAY Webhook Received', $request->all());

        // Truepay documentation states they usually send the transaction_id or reference_number
        $transactionId = $request->input('transaction_id');
        $referenceNumber = $request->input('reference_number');

        if (!$transactionId && !$referenceNumber) {
            return response()->json(['success' => false, 'message' => 'Missing transaction_id or reference_number'], 400);
        }

        // We make a direct inquiry to verify the webhook authenticity and get the latest status
        $payload = [];
        if ($transactionId) {
            $payload['transaction_id'] = $transactionId;
        } elseif ($referenceNumber) {
            $payload['reference_number'] = $referenceNumber;
        }
        
        $baseUri = config('services.ompay.base_url');
        $apiKey = config('services.ompay.api_key');
        $apiSecret = config('services.ompay.api_secret');
        
        $response = Http::withHeaders([
            'OMPAY-API-Key' => $apiKey,
            'OMPAY-API-Secret' => $apiSecret,
            'Content-Type' => 'application/json',
        ])->post($baseUri . '/api/v1/transactions/inquiry', $payload);

        if ($response->successful()) {
            $responseData = $response->json();
            
            if (isset($responseData['data']['status']) && $responseData['data']['status'] === 'SUCCESSFUL') {
                $orderNumber = $responseData['data']['reference_number'] ?? $referenceNumber;
                
                $order = Order::where('Order_Number', $orderNumber)->first();
                if ($order && !$order->Is_Order_Successful) {
                    $order->update([
                        'Payment_Status' => defined('PAYMENT_SUCCESS') ? PAYMENT_SUCCESS : 'paid', 
                        'Is_Order_Successful' => true
                    ]);
                    Log::info("OMPAY Webhook: Order {$orderNumber} successfully marked as PAID");
                } else {
                    Log::info("OMPAY Webhook: Order {$orderNumber} already paid or not found");
                }
                
                return response()->json(['success' => true]);
            }
        }

        return response()->json(['success' => false, 'message' => 'Payment not verified'], 400);
    }
}
