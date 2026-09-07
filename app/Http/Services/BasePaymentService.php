<?php

namespace App\Http\Services;

use App\Http\Services\MollieService;
use Illuminate\Support\Facades\Log;

class BasePaymentService
{
    public  $get_way = null;
    private $provider = null;
    public function __construct($object)
    {
        $this->provider = $object['payment_method'];
        if ($this->provider == MOLLIE) {
            $this->get_way = new MollieService($object);
        } elseif ($this->provider == PAYSTACK) {
            $this->get_way = new PaystackService($object);
        } elseif ($this->provider == INSTAMOJO) {
            $this->get_way = new InstamojoService($object);
        } elseif ($this->provider == OMPAY) {
            $this->get_way = new OmpayService();
        }
    }

    public function makePayment($amount, $post_data = null)
    {
        $res = $this->get_way->makePayment($amount, $post_data);
        Log::info($res);
        return $res;
    }

    public function paymentConfirmation($payment_id, $payer_id = null)
    {
        if (is_null($payer_id)) {
            return $this->get_way->paymentConfirmation($payment_id);
        }
        return $this->get_way->paymentConfirmation($payment_id, $payer_id);
    }
}
