<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Advertise extends Model
{
    use HasFactory;
    protected $fillable=[
        'Image_One',
        'Link_One',
        'Image_Two',
        // new hero-friendly fields
        'image',
        'en_title',
        'en_subtitle',
        'en_badge',
        'ar_title',
        'ar_subtitle',
        'ar_badge',
        // keep legacy french fields for compatibility
        'fr_title',
        'fr_subtitle',
        'link',
        'en_small_description',
        'ar_small_description',
        'display_order',
        'status',
        'location'
    ];
}
