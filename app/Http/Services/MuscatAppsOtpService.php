<?php

namespace App\Http\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MuscatAppsOtpService
{
    protected $url;
    protected $username;
    protected $password;

    protected $testMode;

    public function __construct()
    {
        $this->url = config('services.muscatapps.url');
        $this->username = config('services.muscatapps.username');
        $this->password = config('services.muscatapps.password');
        $this->testMode = config('services.muscatapps.test_mode', false);
    }

    /**
     * Send OTP to the given phone number
     * 
     * @param string $phone
     * @return string|null Returns the RefNo on success, null on failure
     */
    public function sendOtp($phone)
    {
        if ($this->testMode) {
            $refNo = 'TEST_REF_' . rand(100000, 999999);
            Log::info("Muscat Apps OTP (TEST MODE) sent to {$phone}. Use OTP: 123456", ['ref_no' => $refNo]);
            return $refNo;
        }

        try {
            // Remove '+' sign if exists to comply with local numbers or keep it depending on API.
            // Assuming the API takes the number exactly as "99369401"
            $phone = ltrim($phone, '+');
            
            $payload = [
                'Phoneno' => $phone,
                'Username' => $this->username,
                'Password' => $this->password,
                'MsgTemplate' => '{OTP} is your verification code for Khairat Alan3am',
            ];

            $response = Http::post("{$this->url}/api/GenOTP", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                if (isset($data['StatusCode']) && $data['StatusCode'] == "0") {
                    Log::info("Muscat Apps OTP sent successfully to {$phone}", ['ref_no' => $data['RefNo'] ?? null]);
                    return $data['RefNo'] ?? null;
                }
                
                Log::error("Muscat Apps OTP Error: " . ($data['StatusDesc'] ?? 'Unknown Error'), ['response' => $data]);
            } else {
                Log::error("Muscat Apps API HTTP Error: " . $response->status(), ['body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("Muscat Apps OTP Exception: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Verify OTP
     * 
     * @param string $phone
     * @param string $refNo
     * @param string $otp
     * @return bool Returns true on success, false on failure
     */
    public function verifyOtp($phone, $refNo, $otp)
    {
        if ($this->testMode) {
            // In test mode, only OTP '123456' is considered valid
            $isValid = ($otp === '123456');
            Log::info("Muscat Apps Verify OTP (TEST MODE) for {$phone} with OTP {$otp}. Result: " . ($isValid ? 'Success' : 'Failed'));
            return $isValid;
        }

        try {
            $phone = ltrim($phone, '+');

            $payload = [
                'Phoneno' => $phone,
                'Username' => $this->username,
                'Password' => $this->password,
                'RefNo' => $refNo,
                'OTP' => $otp,
            ];

            $response = Http::post("{$this->url}/api/VerifyOTP", $payload);

            if ($response->successful()) {
                $data = $response->json();

                if (isset($data['StatusCode']) && $data['StatusCode'] == "0") {
                    return true;
                }

                Log::error("Muscat Apps Verify OTP Failed: " . ($data['StatusDesc'] ?? 'Unknown Error'), ['response' => $data]);
            } else {
                Log::error("Muscat Apps Verify API HTTP Error: " . $response->status(), ['body' => $response->body()]);
            }
        } catch (\Exception $e) {
            Log::error("Muscat Apps Verify Exception: " . $e->getMessage());
        }

        return false;
    }
}
