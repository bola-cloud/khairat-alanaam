<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function otpSignInPost(Request $request)
    {
      $validated =   $request->validate([
            'phone_number' => 'required',
            'code' => 'required',
        ]);
        $full_phone = $validated['code'] . $validated['phone_number'];
        $phone_without_plus = ltrim($full_phone, '+');
        
        $muscatOtpService = new \App\Http\Services\MuscatAppsOtpService();
        $refNo = $muscatOtpService->sendOtp($phone_without_plus);

        if ($refNo) {
            Otp::create([
                'phone_number' => $full_phone,
                'otp' => $refNo,
            ]);
            return response()->json(['message' => 'OTP sent successfully'], 200);
        } else {
            return response()->json(['error' => 'Failed to send OTP'], 500);
        }
    }

    public function otpVerifyPost(Request $request)
    {
       $validated =  $request->validate([
            'phone_number' => 'required',
            'name' => 'required',
            'code' => 'required',
            'otp' => 'required',
        ]);

        $phone_number = $validated['phone_number'];
        $name = $validated['name'];
        $entered_otp = $validated['otp'];
        $full_phone = $validated['code'] . $validated['phone_number'];
        $otp_record = Otp::where('phone_number', $full_phone)->latest()->first();
        
        $isValid = false;
        if ($otp_record) {
            $muscatOtpService = new \App\Http\Services\MuscatAppsOtpService();
            $phone_without_plus = ltrim($full_phone, '+');
            $isValid = $muscatOtpService->verifyOtp($phone_without_plus, $otp_record->otp, $entered_otp);
        }

        if ($isValid) {
            $otp_record->delete();
            $user = User::where('Number', $full_phone)->where("is_admin", 0)->first();

            if (!$user) {
                $user = User::create([
                    'name' => $name,
                    'email' => 'default' . Str::random(6). '@default.com',
                    'password' => Hash::make($full_phone),
                    'Number' => $full_phone,
                    'code' => $validated['code'],
                ]);
            }

            $token = $user->createToken('authTokenSharaaApp')->plainTextToken;

            return response()->json(['token' => $token, 'message' => 'Login Successfully'], 200);
        } else {
            return response()->json(['error' => 'Invalid OTP'], 401);
        }
    }
}
