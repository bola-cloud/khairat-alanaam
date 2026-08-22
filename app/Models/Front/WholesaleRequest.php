<?php

namespace App\Models\Front;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WholesaleRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'company_name',
        'contact_name',
        'contact_phone',
        'estimated_qty',
        'services',
        'notes',
        'cr_or_signboard',
        'status',
    ];

    protected $casts = [
        'services' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
