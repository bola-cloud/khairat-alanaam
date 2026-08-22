<?php

namespace App\Models\Admin;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Recipe extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'en_title',
        'fr_title',
        'slug',
        'en_description',
        'fr_description',
        'time_to_cook',
        'difficulty',
        'image',
        'status',
    ];

    public function getLocalizedTitleAttribute()
    {
        return app()->getLocale() == 'en' ? $this->en_title : ($this->fr_title ?: $this->en_title);
    }

    public function getLocalizedDescriptionAttribute()
    {
        return app()->getLocale() == 'en' ? $this->en_description : ($this->fr_description ?: $this->en_description);
    }
}
