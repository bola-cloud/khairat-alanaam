<?php

namespace App\Models\Admin;

use App\Models\Subcategory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
  use HasFactory, SoftDeletes;
  protected $fillable = [
    'smartlife_id',
    'en_Category_Name',
    'en_Category_Slug',
    'Status',
    'en_Description',
    'Category_Icon',
    'fr_Category_Name',
    'fr_Category_Slug',
    'fr_Description',
    "order",
    "show_on_home",
    "is_occasion",
    "is_cut"
  ];
  public function products()
  {
    return $this->hasMany(Product::class, 'Category_Id');
  }

  public function subCategories(): HasMany
  {
    return $this->hasMany(Subcategory::class);
  }

  public function getLocalizedNameAttribute()
  {
    $locale = app()->getLocale();
    if ($locale == 'en') {
      return $this->en_Category_Name;
    }
    return $this->fr_Category_Name;
  }

  public function getLocalizedDescriptionAttribute()
  {
    $locale = app()->getLocale();
    if ($locale == 'en') {
      return $this->en_Description;
    }
    return $this->fr_Description;
  }

  public function getLocalizedSlugAttribute()
  {
    $locale = app()->getLocale();
    if ($locale == 'en') {
      return $this->en_Category_Slug;
    }
    return $this->fr_Category_Slug;
  }
}
