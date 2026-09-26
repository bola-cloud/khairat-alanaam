<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserAuthRequest;
use App\Http\Requests\UserChangePasswordRequest;
use App\Models\SeoSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function userSignIn()
    {
        if (Auth::check()) {
            if (auth()->user()->is_admin == 1) {
                return redirect()->route('admin.dashboard');
            } else {
                return redirect()->route('front');
            }
        }
        $seo = SeoSetting::where('slug', 'sign-in')->first();
        $data['title'] = $seo ? $seo->title : 'Sign In';
        $data['description'] = $seo ? $seo->description : '';
        $data['keywords'] = $seo ? $seo->keywords : '';
        // Return new-design sign in view
        return view('front.auth.newdesign_signin', $data);
    }
    public function userSignInPost(Request $request)
    {
        $rules = [
            'country_code' => 'required',
            'phone' => 'required',
            'name' => 'required',
        ];
        $request->validate($rules);

        $login_id = $request->input('country_code') . $request->input('phone');
        
        // Search for user by Number (Phone)
        $user = User::where('Number', $login_id)->where('is_admin', 0)->first();

        if (!$user) {
            // Auto register the user if they don't exist
            $user = User::create([
                'name' => $request->input('name'),
                'Number' => $login_id,
                'email' => $login_id . '@example.com', // Dummy email if required
                'password' => Hash::make(Str::random(16)),
                'status' => ACTIVE,
            ]);
        } else {
            // Update name if changed
            $user->name = $request->input('name');
            $user->save();
        }

        if ($user->status == INACTIVE) {
            return redirect()->route('front')->with('error', __('User is blocked by admin.'));
        }

        // Send OTP via Muscat Apps SMS
        $muscatOtpService = new \App\Http\Services\MuscatAppsOtpService();
        $refNo = $muscatOtpService->sendOtp($user->Number);

        if ($refNo) {
            // Store the reference number in the code column
            $user->code = $refNo;
            $user->save();
            
            session(['verify_target' => $user->Number]);
            session(['verification_method' => 'sms']); // Changed to sms
            
            return redirect()->route('user.verify.email')->with('success', __('Please verify your account with the OTP sent to your phone.'));
        } else {
            return redirect()->back()->with('error', __('Failed to send OTP. Please try again later.'));
        }
    }

    public function userSignUp()
    {

        // Return the new-design registration view
        $seo = SeoSetting::where('slug', 'sign-up')->first();
        $data['title'] = $seo ? $seo->title : 'Sign Up';
        $data['description'] = $seo ? $seo->description : '';
        $data['keywords'] = $seo ? $seo->keywords : '';
        return view('front.auth.newdesign_register', $data);
    }

    public function loginModal(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $user = User::where('email', $request->email)->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                if ($user->status == INACTIVE) {
                    return redirect()->route('front')->with('error', __('User is blocked by admin.'));
                }
                if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
                    if (Auth::user()->is_admin == 0) {
                        return redirect()->back()->with('success', 'Login Successfully');
                    } else {
                        Auth::logout();
                        return redirect()->back()->with('error', __('Something went wrong!'));
                    }
                }
            }
        }
        return redirect()->back()->with('error', __('Wrong Credential'));
    }
    public function userSignUpPost(UserAuthRequest $request)
    {
        $full_phone = $request->country_code . $request->phone;
        
        $user = User::create([
            'name' => $request->name,
            'Number' => $full_phone,
            'code' => null, // Will be updated after OTP generation
        ]);

        if ($user) {
            // Create customer in SmartLife ERP
            if (config('smartlife.sync_enabled')) {
                try {
                    $smartLifeService = new \App\Services\SmartLifeErpService();
                    $customerPhone = $full_phone;

                    if ($customerPhone) {
                        $customerResult = $smartLifeService->createCustomer($user->name, $customerPhone);

                        if ($customerResult && isset($customerResult['success']) && $customerResult['success'] === true) {
                            $user->smartlife_customer_id = $customerResult['id'];
                            $user->save();

                            \Illuminate\Support\Facades\Log::info('SmartLife customer created during registration', [
                                'user_id' => $user->id,
                                'smartlife_customer_id' => $customerResult['id']
                            ]);
                        }
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::error('Failed to create SmartLife customer during registration', [
                        'user_id' => $user->id,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Send OTP via Muscat Apps SMS
            $muscatOtpService = new \App\Http\Services\MuscatAppsOtpService();
            $refNo = $muscatOtpService->sendOtp($full_phone);

            if ($refNo) {
                $user->code = $refNo;
                $user->save();

                session(['verification_method' => 'sms']);
                session(['verify_target' => $full_phone]);

                return redirect()->route('user.verify.email')->with('success', __('Sign Up Successfully! Please verify your account with the OTP sent to your phone.'));
            } else {
                return redirect()->route('user.verify.email')->with('error', __('Sign Up Successfully! But failed to send OTP. Please try resending the OTP.'));
            }
        } else {
            return redirect()->route('user.sign.up')->with('error', __('Something went wrong!'));
        }
    }

    public function showVerifyEmail()
    {
        if (!session('verify_target')) {
            return redirect()->route('login');
        }
        
        $data['title'] = __('Verify Your Account');
        $data['targetPhone'] = session('verify_target');
        $data['postRoute'] = route('user.verify.email.post');
        $data['method'] = session('verification_method');
        
        return view('v2.auth.otp', $data);
    }

    public function verifyEmailPost(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:5',
        ]);

        $target = session('verify_target');
        $method = session('verification_method');
        
        if (!$target) {
            return redirect()->route('login');
        }

        if ($method == 'email') {
            $user = User::where('email', $target)->first();
        } else {
            $user = User::where('Number', $target)->first();
        }

        if ($user) {
            $isValid = false;

            if ($method == 'email') {
                $isValid = ($user->code === $request->otp);
            } else {
                // Method is SMS/WhatsApp, use Muscat Apps OTP Verification
                $muscatOtpService = new \App\Http\Services\MuscatAppsOtpService();
                $isValid = $muscatOtpService->verifyOtp($target, $user->code, $request->otp);
            }

            if ($isValid) {
                $user->email_verified_at = Carbon::now();
                $user->code = null; // Clear OTP RefNo
                $user->save();

                Auth::login($user);
                session()->forget(['verify_target', 'verification_method']);

                return redirect()->route('front')->with('success', __('Account verified successfully!'));
            }
        }

        return redirect()->back()->with('error', __('Invalid OTP. Please try again.'));
    }

    public function resendOtp()
    {
        $target = session('verify_target');
        $method = session('verification_method');
        
        if (!$target) {
            return redirect()->route('login');
        }

        if ($method == 'email') {
            $user = User::where('email', $target)->first();
        } else {
            $user = User::where('Number', $target)->first();
        }

        if (!$user) {
            return redirect()->route('login');
        }

        try {
            $appName = config('app.name', 'HiSpeed');
            if ($method == 'email') {
                $otp = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);
                $user->code = $otp;
                $user->save();
                
                Mail::send('front.auth.otp_mail', ['otp' => $otp, 'user' => $user], function ($message) use ($user, $appName) {
                    $message->to($user->email);
                    $message->subject($appName . ' - Email Verification OTP');
                });
                
                return redirect()->back()->with('success', __('OTP has been resent.'));
            } else {
                // Send OTP via Muscat Apps SMS
                $muscatOtpService = new \App\Http\Services\MuscatAppsOtpService();
                $refNo = $muscatOtpService->sendOtp($target);

                if ($refNo) {
                    $user->code = $refNo;
                    $user->save();
                    return redirect()->back()->with('success', __('OTP has been resent to your phone.'));
                } else {
                    return redirect()->back()->with('error', __('Failed to resend OTP. Please try again later.'));
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Resend failed: ' . $e->getMessage());
            return redirect()->back()->with('error', __('Failed to resend OTP. Please try again later.'));
        }
    }
    public function userLogout()
    {
        if (Auth::check()) {
            Auth::logout();
            return redirect()->route('front');
        }
        return redirect()->back()->with('error', __('Something went wrong!'));
    }
    public function userChangePassword(UserChangePasswordRequest $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|same:confirm_password|min:6',
            'confirm_password' => 'required',
        ]);

        $user = User::find(Auth::user()->id);
        $userPassword = $user->password;

        if (!Hash::check($request->current_password, $userPassword)) {
            return redirect()->back()->with('error', __('Current Password Not Match!'));
        }
        $user->password = Hash::make($request->new_password);
        $user->save();
        return redirect()->back()->with('success', __('Password change successfully!'));
    }
    //forget password
    public function userForgetPasswordGet()
    {
        $seo = SeoSetting::where('slug', 'forget-password')->first();
        $data['title'] = $seo ? $seo->title : 'Forget Password';
        $data['description'] = $seo ? $seo->description : '';
        $data['keywords'] = $seo ? $seo->keywords : '';
        return view('v2.auth.forget_password_email', $data);
    }

    public function userForgetPasswordPost(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $otp = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        // Delete old tokens for this email to prevent multiple valid links
        DB::table('password_resets')->where('email', $request->email)->delete();

        DB::table('password_resets')->insert([
            'email' => $request->email,
            'token' => $otp,
            'created_at' => Carbon::now()
        ]);

        $appName = config('app.name', 'HiSpeed');
        try {
            Mail::send('emails.otp_reset', ['otp' => $otp], function ($message) use ($request, $appName) {
                $message->to($request->email);
                $message->subject($appName . ' - Password Reset OTP');
            });
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Reset Email failed: ' . $e->getMessage());
        }

        session(['reset_email' => $request->email]);

        return redirect()->route('forget.password.otp')->with('success', __('We have e-mailed your password reset OTP!'));
    }

    public function userForgetPasswordOtp()
    {
        if (!session('reset_email')) {
            return redirect()->route('forget.password.get');
        }
        $data['title'] = __('Verify OTP');
        $data['email'] = session('reset_email');
        return view('v2.auth.forget_password_otp', $data);
    }

    public function userForgetPasswordOtpVerify(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:5',
        ]);

        $email = session('reset_email');
        if (!$email) {
            return redirect()->route('forget.password.get');
        }

        $resetRecord = DB::table('password_resets')
            ->where('email', $email)
            ->where('token', $request->otp)
            ->first();

        if (!$resetRecord) {
            return back()->with('error', __('Invalid OTP. Please try again.'));
        }

        // Set verified session flag
        session(['otp_verified' => true]);

        return redirect()->route('reset.password.get')->with('success', __('OTP Verified. Please set a new password.'));
    }

    public function userShowResetPasswordForm()
    {
        if (!session('reset_email') || !session('otp_verified')) {
            return redirect()->route('forget.password.get');
        }

        $seo = SeoSetting::where('slug', 'reset-password')->first();
        $data['title'] = $seo ? $seo->title : 'Reset Password';
        $data['description'] = $seo ? $seo->description : '';
        $data['keywords'] = $seo ? $seo->keywords : '';
        return view('v2.auth.forget_password_reset', $data);
    }

    public function submitResetPasswordForm(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $email = session('reset_email');
        if (!$email || !session('otp_verified')) {
            return redirect()->route('forget.password.get');
        }

        $userUpdate = User::where('email', $email)
            ->update(['password' => Hash::make($request->password)]);

        if ($userUpdate) {
            DB::table('password_resets')->where(['email' => $email])->delete();
            session()->forget(['reset_email', 'otp_verified']);
            return redirect()->route('reset.password.success');
        }

        return redirect()->back()->with('error', __('Your password could not be changed. Please try again.'));
    }

    public function userResetPasswordSuccess()
    {
        $data['title'] = __('Password Reset Successfully');
        return view('v2.auth.forget_password_success', $data);
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $user = Socialite::driver('google')->user();
            $finduser = User::where('google_id', $user->id)->orWhere('email', $user->email)->first();

            if ($finduser) {
                if ($finduser->status == INACTIVE) {
                    return redirect()->route('front')->with('error', __('User is blocked by admin.'));
                }
                Auth::login($finduser);
                return redirect()->intended(route('front'))->with('success', __('Login Successfully!'));
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->avatar,
                    'google_id' => $user->id,
                    'password' => Hash::make('123456')
                ]);
                Auth::login($newUser);
                return redirect()->intended(route('front'))->with('success', __('Login Successfully!'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        try {
            $user = Socialite::driver('facebook')->user();

            $finduser = User::where('facebook_id', $user->id)->first();

            if ($finduser) {
                if ($finduser->status == INACTIVE) {
                    return redirect()->route('front')->with('error', __('User is blocked by admin.'));
                }
                Auth::login($finduser);
                return redirect()->intended(route('front'))->with('success', __('Login Successfully!'));
            } else {
                $newUser = User::create([
                    'name' => $user->name,
                    'email' => $user->email,
                    'image' => $user->avatar,
                    'facebook_id' => $user->id,
                    'password' => Hash::make('123456')
                ]);
                Auth::login($newUser);
                return redirect()->intended(route('front'))->with('success', __('Login Successfully!'));
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Something went wrong!'));
        }
    }

    public function otpSignInPost(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required',
            'country_code' => 'required',
        ]);



        // Generate a random 6-digit OTP
        $otp = str_pad(random_int(0, 99999), 5, '0', STR_PAD_LEFT);

        // Store OTP in session for later verification
        session(['whatsapp_otp' => $otp]);

        $phone_without_plus = ltrim($request->input('full_phone'), '+');


        // Send OTP via WhatsApp (Bypassed)
        // $response = Http::asForm()->post('https://whatsapi.hispeed.om/api/v1/whatsapp/send_otp', [
        //     'phone_number' => $phone_without_plus,
        //     'otp' => $otp,
        //     'language' => app()->getLocale()
        // ]);

        \Illuminate\Support\Facades\Log::info("WhatsApp OTP Signin (BYPASSED) for {$phone_without_plus}: {$otp}");

        // if ($response->successful()) {
            return redirect()->route('user.otp.verify.get', [
                'phone_number' => $request->input('full_phone'),
                'name' => $request->input('name'),
                'country_code' => $request->input('country_code')
            ]);
        // } else {
        //     return redirect()->back()->with('error', 'Failed to send OTP. Please try again.');
        // }
    }

    public function otpVerify(Request $request)
    {
        $data['phone_number'] = $request->phone_number;
        $data['country_code'] = $request->country_code;
        $data['name'] = $request->name;

        $data['targetPhone'] = $request->phone_number;
        $data['postRoute'] = route('user.otp.verify');

        return view('v2.auth.otp', $data);
    }

    public function otpVerifyPost(Request $request)
    {
        $request->validate([
            'phone_number' => 'required',
            'otp' => 'required|digits:5',
            'name' => 'required',
        ]);

        $phone_number = $request->input('phone_number');
        $country_code = $request->input('country_code');
        $name = $request->input('name');
        $phone_without_country_code = ltrim($phone_number, '+' . $country_code);
        $entered_otp = $request->input('otp');
        $stored_otp = session('whatsapp_otp');

        // dd($entered_otp, $stored_otp);

        if ($entered_otp === $stored_otp) {
            // OTP is valid
            session()->forget('whatsapp_otp'); // Clear the OTP from session

            $user = User::where('Number', $phone_number)->where("is_admin", 0)->first();

            if ($user) {
                Auth::login($user);
                return redirect()->intended(route('front'))->with('success', 'Login Successfully');
            } else {
                $user = User::create([
                    'name' => $name,
                    'email' => 'default' . $phone_number . '@default.com',
                    'password' => Hash::make($phone_number),
                    'code' => $country_code,
                    'Number' => $phone_number,
                ]);

                if ($user) {
                    Auth::login($user);
                    return redirect()->intended(route('front'))->with('success', __('Sign Up Successfully !'));
                }
            }
        } else {
            return redirect()->back()->with('error', 'Invalid OTP');
        }
    }

    public function completeRegistration()
    {
        return view('front.auth.completeRegistration');
    }

    public function completeRegistrationPost(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email',
            'phone_number' => 'required',
            'password' => 'required|confirmed',
        ]);

        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone_number' => $request->input('phone_number'),
            'password' => Hash::make($request->input('password')),
        ]);

        if ($user) {
            return redirect()->route('front')->with('success', 'Registration Successfully');
        } else {
            return redirect()->back()->with('error', 'Something went wrong');
        }
    }
}
