<?php

namespace App\Models\Admin;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    use HasFactory;
    protected $fillable = [
        'CouponCode',
        'Amount',
        'Min_Expenses',
        'ExpireDate',
        'usage_count',
        'user_id',
        'limit_per_user'
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
