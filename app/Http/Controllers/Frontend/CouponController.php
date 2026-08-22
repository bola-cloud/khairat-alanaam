<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin\Coupon;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Gloudemans\Shoppingcart\Facades\Cart;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CouponController extends Controller
{
    public function couponApply(Request $request)
    {
        if (Auth::check()) {
            return DB::transaction(function () use ($request) {
                // $couponDetails = Coupon::where('CouponCode', $request->coupon_code)->first();.

                if(Auth::user()->is_admin == 1){
                    $user_id = (int)$request->user_id;
                }else{
                    $user_id = Auth::user()->id;
                }
                $couponDetails = Coupon::where('CouponCode', $request->coupon_code)
                    ->where('usage_count', '>', 0) // Ensure it's not overused
                    ->lockForUpdate() // Prevent race condition
                    ->first();


                if ($couponDetails && $couponDetails->user && $user_id != $couponDetails->user_id) {
                    return redirect()->back()->with('error', __('Coupon does not exits !'));
                }
                // Use numeric subtotal helper to avoid formatted rounding
                $subtotal = floatval(subtotal());
                if (!empty($couponDetails)) {
                    if ($couponDetails->limit_per_user) {
                        $usedCount = \App\Models\Admin\Order::where('User_Id', $user_id)
                            ->where('Coupon_Id', $couponDetails->id)
                            ->count();
                        if ($usedCount >= $couponDetails->limit_per_user) {
                            return redirect()->back()->with('error', __('You have reached the maximum usage limit for this coupon.'));
                        }
                    }
                    if ($couponDetails->Status == 0) {
                        return redirect()->back()->with('error', __('Coupon Code is not Active !'));
                    }
                    $expire_date = $couponDetails->ExpireDate;
                    $current_date = Carbon::now()->toDateString();
                    if ($expire_date < $current_date) {
                        return redirect()->back()->with('error', __('Coupon Code is Expired !'));
                    }
                    if ($subtotal < $couponDetails->Min_Expenses) {
                        return redirect()->back()->with('error', __('You have to expense minimum ' . $couponDetails->Min_Expenses . ' USD'));
                    }
                    $total_amount = 0;
                    //                     $cart=Cart::content();
                    //                    foreach ($cart as $item){
                    //                        $total_amount=$total_amount+($item->price*$item->qty);
                    //                    }
                    $couponAmount = $couponDetails->Amount;

                    Session::put('Coupon_Id', $couponDetails->id);
                    Session::put('CouponAmount', $couponAmount);
                    Session::put('couponCode', $request->coupon_code);

                    return redirect()->back()->with('success', __('Coupon Code Successfully Apply !'));
                }
                return redirect()->back()->with('error', __('Coupon does not exits !'));
            });
        } else {
            return redirect()->back()->with('error', __('You can use coupon only after login!'));
        }
    }
}
