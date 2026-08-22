<?php

namespace App\Models\Admin;

use App\Models\ProductReview;
use App\Models\Subcategory;
use App\Models\WeightProduct;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Intervention\Image\Facades\Image;

class Product extends Model
{
    use HasFactory, Sluggable, SoftDeletes;

    // ... (Fillable array omitted for brevity, keeping original content)

    public function getVirtualStockAttribute()
    {
        return $this->Quantity;
    }

    protected $fillable = [
        'Category_Id',
        'smartlife_id',
        'barcode',
        'en_Product_Name',
        'en_Product_Slug',
        'fr_Product_Name',
        'fr_Product_Slug',
        'en_About',
        'fr_About',
        'ItemTag',
        'Price',
        'cost',
        'Discount',
        'Discount_Price',
        'Quantity',
        'alert_quantity',
        'unit',
        'Sold',
        'Primary_Image',
        'Image2',
        'Image3',
        'Image4',
        'Image5',
        'Featured_Product',
        'Best_Selling',
        'New_Arrival',
        'Today_Special',
        'On_Sale',
        'Status',
        'en_Description',
        'fr_Description',
        'en_ShippingReturn',
        'fr_ShippingReturn',
        'en_AdditionalInformation',
        'fr_AdditionalInformation',
        'Voucher',
        'digital_type',
        'digital_file',
        'digital_link',
        'license_name',
        'license_key',
        'affiliate_link',
        'type',
        'product_type',
        'show_pos',
        'synced_from_smartlife',
        'points',
        'subcategory_id',
        'is_package'
    ];

    protected $casts = [
        'Price' => 'decimal:3',
        'cost' => 'decimal:3',
        'Discount' => 'decimal:2',
        'Discount_Price' => 'decimal:3',
        'Featured_Product' => 'boolean',
        'Best_Selling' => 'boolean',
        'New_Arrival' => 'boolean',
        'Today_Special' => 'boolean',
        'On_Sale' => 'boolean',
        'Status' => 'boolean',
        'show_pos' => 'boolean',
        'synced_from_smartlife' => 'boolean',
    ];
    /**
     * Return the sluggable configuration array for this model.
     *
     * @return array
     */
    public function sluggable(): array
    {
        return [
            'en_Product_Slug' => [
                'source' => 'en_Product_Name'
            ],
            'fr_Product_Slug' => [
                'source' => 'fr_Product_Name'
            ],
        ];
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'Category_Id');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'Brand_Id');
    }


    public function colors()
    {
        return $this->belongsToMany(Color::class, 'color_product');
    }
    public function sizes()
    {
        return $this->belongsToMany(Size::class, 'size_product', 'Product_Id', 'Size_Id')->withPivot('price', 'weight');
    }


    public function product_tags()
    {
        return $this->hasMany(ProductTag::class, 'product_id');
    }

    public function product_reviews()
    {
        return $this->hasMany(ProductReview::class, 'product_id');
    }

    public function additions()
    {
        return $this->hasMany(Addition::class);
    }

    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function weights()
    {
        return $this->hasMany(WeightProduct::class, 'Product_Id');
    }
    public function order_details()
    {
        return $this->hasMany(OrderDetails::class, 'Product_Id');
    }
    public function resizeImage()
    {
        $originalPath = public_path(ProductImage() . $this->Primary_Image);
        if (!file_exists($originalPath)) {
            return '';
        }
        $mimeType = mime_content_type($originalPath);
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];

        if (!in_array($mimeType, $allowedMimeTypes)) {
            return '';
        }

        $resizedImageDir = public_path(ProductImage() . 'resized_images/');
        $resizedImagePath = $resizedImageDir . basename($originalPath);
        if (file_exists($resizedImagePath)) {
            return asset(ProductImage() . 'resized_images/' . basename($originalPath));
        }
        $image = Image::make($originalPath);
        $image->resize(900, 900, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        if (!file_exists($resizedImageDir)) {
            mkdir($resizedImageDir, 0775, true);
        }
        $image->save($resizedImagePath);
        return asset(ProductImage() . 'resized_images/' . basename($originalPath));
    }
    public function scopeWithDiscount($query)
    {
        return $query->where('Discount', '>', 0);
    }

    public function scopeAvailable($query)
    {
        // Standard products must have Quantity > 0
        return $query->where('Status', 1)->where('Quantity', '>', 0);
    }

    public function getLocalizedNameAttribute()
    {
        $locale = app()->getLocale();
        if ($locale == 'en') {
            return $this->en_Product_Name;
        }
        return $this->fr_Product_Name;
    }

    public function getLocalizedAboutAttribute()
    {
        $locale = app()->getLocale();
        if ($locale == 'en') {
            return $this->en_About;
        }
        return $this->fr_About;
    }

    public function getLocalizedDescriptionAttribute()
    {
        $locale = app()->getLocale();
        if ($locale == 'en') {
            return $this->en_Description;
        }
        return $this->fr_Description;
    }
}
