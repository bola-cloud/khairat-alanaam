<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductRequest;
use App\Models\Admin\Category;
use App\Models\Admin\Color;
use App\Models\Admin\OrderDetails;
use App\Models\Admin\Product;
use App\Models\Admin\ProductTag;
use App\Models\Admin\Size;
use App\Models\ItemTag;
use App\Models\ProductTagList;
use App\Models\Subcategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function product(Request $request)
    {
        if ($request->ajax()) {
            // load sizes & weights so we can compute a fallback price when `Price` is empty
            $data = Product::query()
                ->where(function ($query) {
                    $query->whereHas('category', function ($q) {
                        $q->whereNotIn('en_Category_Slug', ['packages', 'offers']);
                    })
                    ->orWhereNull('Category_Id');
                })
                ->with('category', 'brand', 'sizes', 'weights')
                ->orderByDesc('id')
                ->get();
            return DataTables::of($data)
                ->addColumn('select', function ($data) {
                    return '<div class="form-check"><input type="checkbox" class="form-check-input product-select" value="' . $data->id . '"></div>';
                })
                ->addColumn('action', function ($data) {
                    // SmartLife Integration: Products are synced from ERP - editing disabled
                    $btn = '<div class="action__buttons" style="display: flex; gap: 8px; justify-content: start;">';

                    // View + Edit buttons
                    $editTitle = __('Edit');
                    $editIcon = 'fa-pen-to-square';
                    $btnStyle = 'font-size: 1.1rem; padding: 6px 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);';

                    if ($data->type == PRODUCT_PHYSICAL) {
                        $btn = $btn . '<a href="' . route('admin.product.edit', ['product_type' => 'physical', 'id' => $data->id]) . '" class="btn-action" title="' . $editTitle . '" style="' . $btnStyle . '"><i class="fa-solid ' . $editIcon . '"></i></a>';
                    } elseif ($data->type == PRODUCT_DIGITAL) {
                        $btn = $btn . '<a href="' . route('admin.product.edit', ['product_type' => 'digital', 'id' => $data->id]) . '" class="btn-action" title="' . $editTitle . '" style="' . $btnStyle . '"><i class="fa-solid ' . $editIcon . '"></i></a>';
                    } elseif ($data->type == PRODUCT_LICENSE) {
                        $btn = $btn . '<a href="' . route('admin.product.edit', ['product_type' => 'license', 'id' => $data->id]) . '" class="btn-action" title="' . $editTitle . '" style="' . $btnStyle . '"><i class="fa-solid ' . $editIcon . '"></i></a>';
                    } else {
                        $btn = $btn . '<a href="' . route('admin.product.edit', ['product_type' => 'affiliate', 'id' => $data->id]) . '" class="btn-action" title="' . $editTitle . '" style="' . $btnStyle . '"><i class="fa-solid ' . $editIcon . '"></i></a>';
                    }

                    if ($data->Status == 1) {
                        $btn = $btn . '<a href="' . route('admin.product.inactive', $data->id) . '" class="btn-action" style="' . $btnStyle . '"><i class="fas fa-toggle-on text-success"></i></a>';
                    } else {
                        $btn = $btn . '<a href="' . route('admin.product.active', $data->id) . '" class="btn-action" style="' . $btnStyle . '"><i class="fas fa-toggle-off text-secondary"></i></a>';
                    }



                    $btn = $btn . '</div>';
                    return $btn;
                })
                ->editColumn('PrimaryImage', function ($data) {
                    $url = resolve_product_image($data->Primary_Image);
                    return '<img src="' . $url . '" border="0" width="50" class="img-rounded" align="center" onerror="this.onerror=null;this.src=\'' . asset('assets/elketar/coffee.png') . '\';" />';
                })
                ->editColumn('ProductName', function ($data) {
                    return $data->localized_name;
                })
                ->addColumn('Barcode', function ($data) {
                    return $data->barcode;
                })
                ->editColumn('Category', function ($data) {
                    return $data->category?->localized_name;
                })
                ->editColumn('subcategory', function ($data) {
                    return $data->subcategory?->localized_name;
                })
                ->addColumn('Type', function ($data) {
                    $html = '';
                    $html .= '<span class="badge badge-lg" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 0.4rem 0.8rem; font-size: 0.85rem;"><i class="fas fa-box mr-1"></i>' . __('Standard') . '</span>';
                    return $html;
                })
                ->editColumn('Price', function ($data) {
                    // compute a sensible base price: use product Price, otherwise fall back to weights/sizes
                    $basePrice = $data->Price ?? 0;
                    if (empty($basePrice) || $basePrice == 0) {
                        if (!empty($data->weights) && $data->weights->count()) {
                            $basePrice = $data->weights->first()->price ?? 0;
                        } elseif (!empty($data->sizes) && $data->sizes->count()) {
                            $firstSize = $data->sizes->first();
                            $basePrice = $firstSize?->pivot->price ?? 0;
                        }
                    }

                    $dp = $data->Discount_Price ?? 0;
                    $discount = $data->Discount ?? 0;

                    // decide displayed price: discounted when applicable, otherwise base price
                    $displayPrice = ($discount > 0 && $dp) ? $dp : $basePrice;

                    if (empty($displayPrice) || $displayPrice == 0) {
                        return '<span class="badge badge-secondary" style="padding: 0.4rem 0.6rem; font-size: 0.85rem;"><i class="fas fa-minus"></i></span>';
                    }

                    // format prices using existing helper if available
                    $formattedDisplay = function_exists('currencyConverter') ? currencyConverter($displayPrice) : number_format($displayPrice, 3);
                    $formattedBase = function_exists('currencyConverter') ? currencyConverter($basePrice) : number_format($basePrice, 3);

                    if ($discount > 0 && $dp && $basePrice && $basePrice != $displayPrice) {
                        $btn = '<div class="d-flex flex-column align-items-start">';
                        $btn .= '<span class="badge badge-success" style="padding: 0.4rem 0.7rem; font-size: 0.85rem; font-weight: 600;"><i class="fas fa-tag mr-1"></i>' . $formattedDisplay . '</span>';
                        $btn .= '<span class="badge badge-danger mt-1" style="padding: 0.3rem 0.5rem; font-size: 0.75rem; text-decoration: line-through;">' . $formattedBase . '</span>';
                        $btn .= '</div>';
                        return $btn;
                    }

                    return '<span class="badge badge-success" style="padding: 0.4rem 0.7rem; font-size: 0.85rem; font-weight: 600;"><i class="fas fa-coins mr-1"></i>' . $formattedDisplay . '</span>';
                })
                ->editColumn('Status', function ($data) {
                    if ($data->Status == 1) {
                        return '<span class="badge badge-pill" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-check-circle mr-1"></i>' . __('Active') . '</span>';
                    } else {
                        return '<span class="badge badge-pill" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-times-circle mr-1"></i>' . __('Inactive') . '</span>';
                    }
                })
                ->editColumn('type', function ($data) {
                    if ($data->type == PRODUCT_PHYSICAL) {
                        return __('Physical');
                    } elseif ($data->type == PRODUCT_DIGITAL) {
                        return __('Digital');
                    } elseif ($data->type == PRODUCT_LICENSE) {
                        return __('License');
                    } elseif ($data->type == PRODUCT_AFFILIATE) {
                        return __('Affiliate');
                    }
                })
                ->rawColumns(['select', 'action', 'PrimaryImage', 'Category', 'Price', 'Status', 'Type'])
                ->addIndexColumn()
                ->make(true);
        }
        $data['title'] = __('Product List');
        // Retrieve persistent last sync time
        $data['lastSync'] = \App\Models\Setting::where('slug', 'last_smartlife_sync')->value('value');
        return view('admin.pages.product.index', $data);
    }
    public function productCreate()
    {
        $data['title'] = __('Product Create');
        $data['product'] = Product::get();
        $data['category'] = Category::get();
        $data['subcategories'] = Subcategory::get();
        $data['tags'] = ProductTagList::get();
        $data['item_tags'] = ItemTag::get();
        return view('admin.pages.product.create', $data);
    }
    public function physicalProductCreate()
    {
        $data['title'] = __('Physical Product Create');
        $data['product'] = Product::get();
        $data['category'] = Category::with('subcategories')->get();
        $data['tags'] = ProductTagList::get();
        $data['item_tags'] = ItemTag::get();
        return view('admin.pages.product.physical', $data);
    }
    public function digitalProductCreate()
    {
        $data['title'] = __('Digital Product Create');
        $data['category'] = Category::get();
        $data['tags'] = ProductTagList::get();
        $data['item_tags'] = ItemTag::get();
        return view('admin.pages.product.digital', $data);
    }
    public function licenseProductCreate()
    {
        $data['title'] = __('License Product Create');
        $data['product'] = Product::get();
        $data['category'] = Category::get();
        $data['tags'] = ProductTagList::get();
        $data['item_tags'] = ItemTag::get();
        return view('admin.pages.product.license', $data);
    }
    public function affiliateProductCreate()
    {
        $data['title'] = __('Affiliate Product Create');
        $data['product'] = Product::get();
        $data['category'] = Category::get();
        $data['tags'] = ProductTagList::get();
        $data['item_tags'] = ItemTag::get();
        return view('admin.pages.product.affiliate', $data);
    }

    public function productStore(ProductRequest $request)
    {
        $data = $request->except([
            'primary_image',
            'image_two',
            'image_three',
            'image_four',
            'image_five',
            'status',
            'feature',
            'best_sale',
            'on_sale',
            'on_arrival',
            'today_special',
            'digital_file',
            'digital_link',
            'license_name',
            'license_key',
            'affiliate_link',
        ]);
        if (!empty($request->primary_image)) {
            $data['primary_image'] = fileUpload($request['primary_image'], ProductImage());
        } else {
            // return redirect()->back()->with('error', __('Image is  required'));
        }
        if (!empty($request->image_two)) {
            $data['img_two'] = fileUpload($request['image_two'], ProductImage());
        } else {
            // return redirect()->back()->with('error', __('Image is  required'));
        }

        if (!empty($request->image_three)) {
            $data['img_three'] = fileUpload($request['image_three'], ProductImage());
        } else {
            // return redirect()->back()->with('error', __('Image is  required'));
        }
        if (!empty($request->image_four)) {
            $data['img_four'] = fileUpload($request['image_four'], ProductImage());
        } else {
            // return redirect()->back()->with('error', __('Image is  required'));
        }
        if (!empty($request->image_five)) {
            $data['img_five'] = fileUpload($request['image_five'], ProductImage());
        } else {
            // return redirect()->back()->with('error', __('Image is  required'));
        }

        $data['status'] = 1;
        $data['feature'] = 0;
        $data['best_sale'] = 0;
        $data['on_sale'] = 0;
        $data['on_arrival'] = 0;
        $data['today_special'] = 0;

        // Ensure text fields that have NOT NULL DB constraints are set to a non-null default
        $data['en_description'] = $data['en_description'] ?? '';
        $data['fr_description'] = $data['fr_description'] ?? '';
        $data['en_about'] = $data['en_about'] ?? '';
        $data['fr_about'] = $data['fr_about'] ?? '';
        $data['en_shippingreturn'] = $data['en_shippingreturn'] ?? '';
        $data['fr_shippingreturn'] = $data['fr_shippingreturn'] ?? '';
        $data['en_additionalinformation'] = $data['en_additionalinformation'] ?? '';
        $data['fr_additionalinformation'] = $data['fr_additionalinformation'] ?? '';
        $data['price'] = $data['price'] ?? 0;
        $data['discount_price'] = $data['discount_price'] ?? 0;
        $data['discount'] = $data['discount'] ?? 0;
        $data['is_package'] = $request->is_package ? 1 : 0;

        if ($request->product_type == PRODUCT_PHYSICAL) {
            $create_product = $this->physicalProductAdd($data);
        } elseif ($request->product_type == PRODUCT_DIGITAL) {
            if ($request->digital_type == 'file') {
                if (!empty($request->digital_file)) {
                    $data['digital_file'] = fileUpload($request['digital_file'], PRODUCT_DIGITAL_PRODUCT);
                    $data['digital_link'] = null;
                } else {
                    return redirect()->back()->with('error', __('File is  required'));
                }
            } else {
                $data['digital_file'] = null;
                $data['digital_link'] = $request->digital_link;
            }
            $create_product = $this->digitalProductAdd($data);
        } elseif ($request->product_type == PRODUCT_LICENSE) {
            $data['license_name'] = $request->license_name;
            $data['license_key'] = $request->license_key;
            if ($request->digital_type == 'file') {
                if (!empty($request->digital_file)) {
                    $data['digital_file'] = fileUpload($request['digital_file'], PRODUCT_DIGITAL_PRODUCT);
                    $data['digital_link'] = null;
                } else {
                    return redirect()->back()->with('error', __('File is  required'));
                }
            } else {
                $data['digital_file'] = null;
                $data['digital_link'] = $request->digital_link;
            }
            $create_product = $this->licenseProductAdd($data);
        } elseif ($request->product_type == PRODUCT_AFFILIATE) {
            $data['affiliate_link'] = $request->affiliate_link;
            $create_product = $this->affiliateProductAdd($data);
        }

        if ($create_product['success'] == true) {
            return redirect()->route('admin.product')->with('success', __('Successfully Product Created!'));
        }
        return redirect()->route('admin.product')->with('error', __('Something went wrong!'));
    }

    public function physicalProductAdd($data)
    {
        $result = ['success' => false];
        if (Product::where('en_product_slug', $data['en_product_slug'])->count() > 0) {
            $enSlug = $data['en_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $enSlug = $data['en_product_slug'];
        }
        if (Product::where('fr_product_slug', $data['fr_product_slug'])->count() > 0) {
            $frSlug = $data['fr_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $frSlug = $data['fr_product_slug'];
        }
        $product = Product::create([
            'en_Product_Name' => $data['en_product_name'],
            'en_Product_Slug' => $enSlug,
            'Brand_Id' => null,
            'Category_Id' => $data['en_category_name'],
            'Price' => $data['price'],
            'Discount' => $data['discount'],
            'Discount_Price' => $data['discount_price'],
            'en_About' => $data['en_about'] ?? '',
            'en_Description' => $data['en_description'],
            'en_ShippingReturn' => $data['en_shippingreturn'] ?? "",
            'en_AdditionalInformation' => $data['en_additionalinformation'] ?? "",
            'fr_Product_Name' => $data['fr_product_name'],
            'fr_Product_Slug' => $frSlug,
            'fr_About' => $data['fr_about'] ?? '',
            'fr_Description' => $data['fr_description'],
            'fr_ShippingReturn' => $data['fr_shippingreturn'] ?? "",
            'fr_AdditionalInformation' => $data['fr_additionalinformation'] ?? "",
            'Quantity' => $data['qty'] ?? 0,
            // 'ItemTag' => $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'] ?? null,
            'Image3' => $data['img_three'] ?? null,
            'Image4' => $data['img_four'] ?? null,
            'Image5' => $data['img_five'] ?? null,

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'Voucher' => $this->generateRandomString(6),
            'points' => $data['points'] ?? 0,
            'subcategory_id' => $data['subcategory_id'],
            'is_package' => $data['is_package'] ?? 0
        ]);
        if (!empty($product)) {
            if (isset($data['product_tag'])) {
                foreach ($data['product_tag'] as $rpt) {
                    ProductTag::create([
                        'tag' => explode("_", $rpt)[0],
                        'tag_ar' => explode("_", $rpt)[1],
                        'product_id' => $product->id,
                    ]);
                }
            }
            if (isset($data['color'])) {
                $colorsid = $data['color'];
                $product->colors()->sync($colorsid);
            }

            $sizes = request('size', []);
            $prices = request('size_price', []);
            $weights = request('size_weight', []);

            $newPrSizes = [];
            foreach ($sizes as $key => $size) {
                if ($size != null) {
                    $newPrSizes[$size] = [
                        'price' => isset($prices[$key]) ? $prices[$key] : 0,
                        'weight' => !empty($weights[$key]) ? $weights[$key] : 0
                    ];
                }
            }

            $product->sizes()->sync($newPrSizes);

            // if (isset($data['size'])) {
            //     $sizeid = $data['size'];
            //     $product->sizes()->sync($sizeid);
            // }



            $result['success'] = true;
        }
        return $result;
    }

    public function digitalProductAdd($data)
    {
        $result = ['success' => false];
        if (Product::where('en_product_slug', $data['en_product_slug'])->count() > 0) {
            $enSlug = $data['en_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $enSlug = $data['en_product_slug'];
        }
        if (Product::where('fr_product_slug', $data['fr_product_slug'])->count() > 0) {
            $frSlug = $data['fr_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $frSlug = $data['fr_product_slug'];
        }
        $product = Product::create([
            'en_Product_Name' => $data['en_product_name'],
            'en_Product_Slug' => $enSlug,
            'Brand_Id' => $data['en_brand_name'],
            'Category_Id' => $data['en_category_name'],
            'Price' => $data['price'],
            'Discount' => $data['discount'],
            'Discount_Price' => $data['discount_price'],
            'en_About' => $data['en_about'],
            'en_Description' => $data['en_description'],
            'en_ShippingReturn' => $data['en_shippingreturn'],
            'en_AdditionalInformation' => $data['en_additionalinformation'],
            'fr_Product_Name' => $data['fr_product_name'],
            'fr_Product_Slug' => $frSlug,
            'fr_About' => $data['fr_about'],
            'fr_Description' => $data['fr_description'],
            'fr_ShippingReturn' => $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => $data['fr_additionalinformation'],
            'Quantity' => $data['qty'],
            'ItemTag' => $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'] ?? null,
            'Image3' => $data['img_three'] ?? null,
            'Image4' => $data['img_four'] ?? null,
            'Image5' => $data['img_five'] ?? null,

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'Voucher' => $this->generateRandomString(6),
            'digital_type' => $data['digital_type'],
            'digital_file' => $data['digital_file'],
            'digital_link' => $data['digital_link'],
            'type' => PRODUCT_DIGITAL,
        ]);
        if (!empty($product)) {
            if (isset($data['product_tag'])) {
                foreach ($data['product_tag'] as $rpt) {
                    ProductTag::create([
                        'tag' => $rpt,
                        'product_id' => $product->id,
                    ]);
                }
            }
            $result['success'] = true;
        }
        return $result;
    }

    public function licenseProductAdd($data)
    {
        $result = ['success' => false];
        if (Product::where('en_product_slug', $data['en_product_slug'])->count() > 0) {
            $enSlug = $data['en_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $enSlug = $data['en_product_slug'];
        }
        if (Product::where('fr_product_slug', $data['fr_product_slug'])->count() > 0) {
            $frSlug = $data['fr_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $frSlug = $data['fr_product_slug'];
        }
        $product = Product::create([
            'en_Product_Name' => $data['en_product_name'],
            'en_Product_Slug' => $enSlug,
            'Brand_Id' => $data['en_brand_name'],
            'Category_Id' => $data['en_category_name'],
            'Price' => $data['price'],
            'Discount' => $data['discount'],
            'Discount_Price' => $data['discount_price'],
            'en_About' => $data['en_about'],
            'en_Description' => $data['en_description'],
            'en_ShippingReturn' => $data['en_shippingreturn'],
            'en_AdditionalInformation' => $data['en_additionalinformation'],
            'fr_Product_Name' => $data['fr_product_name'],
            'fr_Product_Slug' => $frSlug,
            'fr_About' => $data['fr_about'],
            'fr_Description' => $data['fr_description'],
            'fr_ShippingReturn' => $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => $data['fr_additionalinformation'],
            'Quantity' => $data['qty'],
            'ItemTag' => $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'],
            'Image3' => $data['img_three'],
            'Image4' => $data['img_four'],
            'Image5' => $data['img_five'],

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'Voucher' => $this->generateRandomString(6),
            'digital_type' => $data['digital_type'],
            'digital_file' => $data['digital_file'],
            'digital_link' => $data['digital_link'],
            'license_name' => $data['license_name'],
            'license_key' => $data['license_key'],
            'type' => PRODUCT_LICENSE,
        ]);
        if (!empty($product)) {
            foreach ($data['product_tag'] as $rpt) {
                ProductTag::create([
                    'tag' => $rpt,
                    'product_id' => $product->id,
                ]);
            }

            $result['success'] = true;
        }
        return $result;
    }

    public function affiliateProductAdd($data)
    {
        $result = ['success' => false];
        if (Product::where('en_product_slug', $data['en_product_slug'])->count() > 0) {
            $enSlug = $data['en_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $enSlug = $data['en_product_slug'];
        }
        if (Product::where('fr_product_slug', $data['fr_product_slug'])->count() > 0) {
            $frSlug = $data['fr_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $frSlug = $data['fr_product_slug'];
        }
        $product = Product::create([
            'en_Product_Name' => $data['en_product_name'],
            'en_Product_Slug' => $enSlug,
            'Brand_Id' => $data['en_brand_name'],
            'Category_Id' => $data['en_category_name'],
            'Price' => $data['price'],
            'Discount' => $data['discount'],
            'Discount_Price' => $data['discount_price'],
            'en_About' => $data['en_about'],
            'en_Description' => $data['en_description'],
            'en_ShippingReturn' => $data['en_shippingreturn'],
            'en_AdditionalInformation' => $data['en_additionalinformation'],
            'fr_Product_Name' => $data['fr_product_name'],
            'fr_Product_Slug' => $frSlug,
            'fr_About' => $data['fr_about'],
            'fr_Description' => $data['fr_description'],
            'fr_ShippingReturn' => $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => $data['fr_additionalinformation'],
            'Quantity' => $data['qty'],
            'ItemTag' => $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'],
            'Image3' => $data['img_three'],
            'Image4' => $data['img_four'],
            'Image5' => $data['img_five'],

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'Voucher' => $this->generateRandomString(6),
            'affiliate_link' => $data['affiliate_link'],
            'type' => PRODUCT_AFFILIATE,
        ]);
        if (!empty($product)) {
            foreach ($data['product_tag'] as $rpt) {
                ProductTag::create([
                    'tag' => $rpt,
                    'product_id' => $product->id,
                ]);
            }

            $result['success'] = true;
        }
        return $result;
    }

    public function productDelete($id)
    {
        $order_count = OrderDetails::where('Product_Id', $id)->count();
        if ($order_count != 0) {
            return redirect()->route('admin.product')->with('error', __('This product is already order by some one! First delete those.'));
        }
        $delete = Product::Where('id', $id);
        if ($delete) {
            $delete->delete();
            return redirect()->route('admin.product')->with('success', __('Successfully Deleted !'));
        }
        return redirect()->route('admin.product')->with('error', __('Does Not Delete!'));
    }
    public function productActive($id)
    {
        $inactive = Product::find($id)->update(['Status' => 1]);
        if ($inactive) {
            return redirect()->route('admin.product')->with('success', __('Successfully Active !'));
        }
        return redirect()->route('admin.product')->with('success', __('Does not Updated !'));
    }
    public function productInactive($id)
    {
        $inactive = Product::find($id)->update(['Status' => 0]);
        if ($inactive) {
            return redirect()->route('admin.product')->with('success', __('Successfully Inactive !'));
        }
        return redirect()->route('admin.product')->with('success', __('Does not Updated !'));
    }

    public function productEdit($product_type, $id)
    {
        $data['title'] = __('Product Edit');
        $data['product'] = Product::with('brand', 'category', 'colors', 'sizes', 'product_tags', 'subcategory')->where('id', $id)->first();
        $data['colors'] = Color::latest()->get();
        $data['sizes'] = Size::latest()->get();
        $data['size_price'] = $data['product']->sizes->pluck('pivot.size_id', 'pivot.price');
        $data['tags'] = ProductTagList::get();
        $data['item_tags'] = ItemTag::get();
        if ($product_type == 'physical') {
            return $this->physicalProductEditView($data);
        } elseif ($product_type == 'digital') {
            return $this->digitalProductEditView($data);
        } elseif ($product_type == 'license') {
            return $this->licenseProductEditView($data);
        } else {
            return $this->affiliateProductEditView($data);
        }
    }

    public function physicalProductEditView($data)
    {
        return view('admin.pages.product.edit.physical', $data);
    }

    public function digitalProductEditView($data)
    {
        return view('admin.pages.product.edit.digital', $data);
    }

    public function licenseProductEditView($data)
    {
        return view('admin.pages.product.edit.license', $data);
    }

    public function affiliateProductEditView($data)
    {
        return view('admin.pages.product.edit.affiliate', $data);
    }

    public function productUpdate(Request $request)
    {
        $id = $request->id;
        $product = Product::where('id', $id)->first();
        $data = $request->except([
            'primary_image',
            'image_two',
            'image_three',
            'image_four',
            'image_five',
            'status',
            'feature',
            'best_sale',
            'on_sale',
            'on_arrival',
            'digital_file',
            'digital_link',
            'license_name',
            'license_key',
            'affiliate_link',
        ]);

        if (!empty($request->primary_image)) {
            $data['primary_image'] = fileUpload($request['primary_image'], ProductImage());
        } else {
            $data['primary_image'] = $product->Primary_Image;
        }
        if (!empty($request->image_two)) {
            $data['img_two'] = fileUpload($request['image_two'], ProductImage());
        } else {
            $data['img_two'] = $product->Image2;
        }

        if (!empty($request->image_three)) {
            $data['img_three'] = fileUpload($request['image_three'], ProductImage());
        } else {
            $data['img_three'] = $product->Image3;
        }
        if (!empty($request->image_four)) {
            $data['img_four'] = fileUpload($request['image_four'], ProductImage());
        } else {
            $data['img_four'] = $product->Image4;
        }
        if (!empty($request->image_five)) {
            $data['img_five'] = fileUpload($request['image_five'], ProductImage());
        } else {
            $data['img_five'] = $product->Image5;
        }

        $data['status'] = checkBoxValue($request->status);
        $data['feature'] = checkBoxValue($request->feature);
        $data['best_sale'] = checkBoxValue($request->best_sale);
        $data['on_sale'] = checkBoxValue($request->on_sale);
        $data['on_arrival'] = checkBoxValue($request->on_arrival);
        $data['today_special'] = checkBoxValue($request->today_special);
        $data['is_package'] = $request->has('is_package') ? checkBoxValue($request->is_package) : 0;

        if ($product->type == PRODUCT_PHYSICAL) {
            $update = $this->physicalProductUpdate($data, $product);
        } elseif ($product->type == PRODUCT_DIGITAL) {
            if ($product->digital_type == 'file') {
                if (!empty($request->digital_file)) {
                    $data['digital_file'] = fileUpload($request['digital_file'], PRODUCT_DIGITAL_PRODUCT);
                    $data['digital_link'] = null;
                } else {
                    $data['digital_file'] = $product->digital_file;
                    $data['digital_link'] = null;
                }
            } else {
                $data['digital_file'] = null;
                $data['digital_link'] = is_null($request->digital_link) ? $product->digital_link : $request->digital_link;
            }
            $update = $this->digitalProductUpdate($data, $product);
        } elseif ($product->type == PRODUCT_LICENSE) {
            $data['license_name'] = is_null($request->license_name) ? $product->license_name : $request->license_name;
            $data['license_key'] = is_null($request->license_key) ? $product->license_link : $request->license_key;
            if ($product->digital_type == 'file') {
                if (!empty($request->digital_file)) {
                    $data['digital_file'] = fileUpload($request['digital_file'], PRODUCT_DIGITAL_PRODUCT);
                    $data['digital_link'] = null;
                } else {
                    $data['digital_file'] = $product->digital_file;
                    $data['digital_link'] = null;
                }
            } else {
                $data['digital_file'] = null;
                $data['digital_link'] = is_null($request->digital_link) ? $product->digital_link : $request->digital_link;
            }
            $update = $this->licenseProductUpdate($data, $product);
        } elseif ($product->type == PRODUCT_AFFILIATE) {
            $data['affiliate_link'] = is_null($request->affiliate_link) ? $product->affiliate_link : $request->affiliate_link;
            $update = $this->affiliateProductUpdate($data, $product);
        }

        if ($update['success'] == true) {
            return redirect()->route('admin.product')->with('success', __('Successfully Updated!'));
        }
        return redirect()->back()->with('error', __('Something went wrong!'));
    }

    public function physicalProductUpdate($data, $product)
    {

        // dd($data);

        $result = ['success' => false];
        if (Product::where('en_Product_Slug', $data['en_product_slug'])->where('id', '!=', $product->id)->count() > 0) {
            $enSlug = $data['en_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $enSlug = $data['en_product_slug'];
        }
        if (Product::where('fr_Product_Slug', $data['fr_product_slug'])->where('id', '!=', $product->id)->count() > 0) {
            $frSlug = $data['fr_product_slug'] . '-' . rand(100000, 999999);
        } else {
            $frSlug = $data['fr_product_slug'];
        }
        $update = $product->update([
            'en_Product_Name' => ($data['en_product_name'] ?? null) === null ? $product->en_Product_Name : $data['en_product_name'],
            'en_Product_Slug' => $enSlug,
            'Brand_Id' => null,
            'Category_Id' => $data['en_category_name'] ?? $product->Category_Id,
            'Price' => ($data['price'] ?? null) === null ? $product->Price : $data['price'],
            'Discount' => ($data['discount'] ?? null) === null ? $product->Discount : $data['discount'],
            'Discount_Price' => ($data['discount_price'] ?? null) === null ? $product->Discount_Price : $data['discount_price'],
            'en_About' => ($data['en_about'] ?? null) === null ? $product->en_About : $data['en_about'],
            'en_Description' => ($data['en_description'] ?? null) === null ? $product->en_Description : $data['en_description'],
            'en_ShippingReturn' => ($data['en_shippingreturn'] ?? null) === null ? $product->en_ShippingReturn : $data['en_shippingreturn'],
            'en_AdditionalInformation' => ($data['en_additionalinformation'] ?? null) === null ? $product->en_AdditionalInformation : $data['en_additionalinformation'],
            'fr_Product_Name' => ($data['fr_product_name'] ?? null) === null ? $product->fr_Product_Name : $data['fr_product_name'],
            'fr_Product_Slug' => $frSlug,
            'fr_About' => ($data['fr_about'] ?? null) === null ? $product->fr_About : $data['fr_about'],
            'fr_Description' => ($data['fr_description'] ?? null) === null ? $product->fr_Description : $data['fr_description'],
            'fr_ShippingReturn' => ($data['fr_shippingreturn'] ?? null) === null ? $product->fr_ShippingReturn : $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => ($data['fr_additionalinformation'] ?? null) === null ? $product->fr_AdditionalInformation : $data['fr_additionalinformation'],
            'Quantity' => ($product->synced_from_smartlife || !empty($product->smartlife_id)) ? $product->Quantity : $data['qty'],
            // 'ItemTag' => $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'],
            'Image3' => $data['img_three'],
            'Image4' => $data['img_four'],
            'Image5' => $data['img_five'],

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'points' => $data['points'] ?? 0,
            'subcategory_id' => $data['subcategory_id'] ?? null
        ]);
        if (!empty($update)) {
            if (isset($data['product_tag'])) {
                ProductTag::where('product_id', $product->id)->delete();
                foreach ($data['product_tag'] as $rpt) {
                    ProductTag::create([
                        'tag' => explode("_", $rpt)[0],
                        'tag_ar' => explode("_", $rpt)[1],
                        'product_id' => $product->id,
                    ]);
                }
            }

            $pr = Product::find($product->id);
            if (isset($data['color'])) {
                DB::table('color_product')->where('Product_Id', $product->id)->delete();
                $colorsid = $data['color'];
                $pr->colors()->syncWithoutDetaching($colorsid);
            }

            $sizes = request('size', []);
            $prices = request('size_price', []);
            $weights = request('size_weight', []);

            $newPrSizes = [];
            foreach ($sizes as $key => $size) {
                if ($size != null) {
                    $newPrSizes[$size] = [
                        'price' => isset($prices[$key]) ? $prices[$key] : 0,
                        'weight' => !empty($weights[$key]) ? $weights[$key] : 0
                    ];
                }
            }

            $pr->sizes()->sync($newPrSizes);

            // Handle weights
            $weights = request('weight_amount', []);
            $weightPrices = request('weight_price', []);

            // Collect all weights from the request
            $newWeights = [];
            foreach ($weights as $key => $weight) {
                if ($weight != null) {
                    $newWeights[$key] = [
                        'weight' => $weight,
                        'price' => isset($weightPrices[$key]) ? $weightPrices[$key] : 0
                    ];
                }
            }

            // Update existing weights or create new ones
            foreach ($product->weights as $existingWeight) {
                $weightId = $existingWeight->id;
                if (isset($newWeights[$weightId])) {
                    $existingWeight->update($newWeights[$weightId]);
                    unset($newWeights[$weightId]);
                } else {
                    $existingWeight->delete();
                }
            }

            // Create new weights
            foreach ($newWeights as $weight) {
                $product->weights()->create($weight);
            }


            // if (isset($data['size'])) {
            //     DB::table('size_product')->where('Product_Id', $product->id)->delete();
            //     $sizeid = $data['size'];
            //     $pr->sizes()->syncWithoutDetaching($sizeid);
            // }

            $result['success'] = true;
        }
        return $result;
    }

    public function digitalProductUpdate($data, $product)
    {
        $result = ['success' => false];
        $update = $product->update([
            'en_Product_Name' => is_null($data['en_product_name']) ? $product->en_Product_Name : $data['en_product_name'],
            'en_Product_Slug' => is_null($data['en_product_slug']) ? $product->en_Product_Slug : $data['en_product_slug'],
            'Brand_Id' => is_null($data['en_brand_name']) ? $product->Brand_Id : $data['en_brand_name'],
            'Category_Id' => is_null($data['en_category_name']) ? $product->Category_Id : $data['en_category_name'],
            'Price' => is_null($data['price']) ? $product->Price : $data['price'],
            'Discount' => is_null($data['discount']) ? $product->Discount : $data['discount'],
            'Discount_Price' => is_null($data['discount_price']) ? $product->Discount_Price : $data['discount_price'],
            'en_About' => is_null($data['en_about']) ? $product->en_About : $data['en_about'],
            'en_Description' => is_null($data['en_description']) ? $product->en_Description : $data['en_description'],
            'en_ShippingReturn' => is_null($data['en_shippingreturn']) ? $product->en_ShippingReturn : $data['en_shippingreturn'],
            'en_AdditionalInformation' => is_null($data['en_additionalinformation']) ? $product->en_AdditionalInformation : $data['en_additionalinformation'],
            'fr_Product_Name' => is_null($data['fr_product_name']) ? $product->fr_Product_Name : $data['fr_product_name'],
            'fr_Product_Slug' => is_null($data['fr_product_slug']) ? $product->fr_Product_Slug : $data['fr_product_slug'],
            'fr_About' => is_null($data['fr_about']) ? $product->fr_About : $data['fr_about'],
            'fr_Description' => is_null($data['fr_description']) ? $product->fr_Description : $data['fr_description'],
            'fr_ShippingReturn' => is_null($data['fr_shippingreturn']) ? $product->fr_ShippingReturn : $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => is_null($data['fr_additionalinformation']) ? $product->fr_AdditionalInformation : $data['fr_additionalinformation'],
            'Quantity' => ($product->synced_from_smartlife || !empty($product->smartlife_id)) ? $product->Quantity : (is_null($data['qty']) ? $product->Quantity : $data['qty']),
            'ItemTag' => is_null($data['item_teg']) ? $product->ItemTag : $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'],
            'Image3' => $data['img_three'],
            'Image4' => $data['img_four'],
            'Image5' => $data['img_five'],

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'digital_file' => $data['digital_file'],
            'digital_link' => $data['digital_link'],
        ]);
        if (!empty($update)) {
            if (isset($data['product_tag'])) {
                ProductTag::where('product_id', $product->id)->delete();
                foreach ($data['product_tag'] as $rpt) {
                    ProductTag::create([
                        'tag' => $rpt,
                        'product_id' => $product->id,
                    ]);
                }
            }

            $result['success'] = true;
        }
        return $result;
    }

    public function licenseProductUpdate($data, $product)
    {
        $result = ['success' => false];
        $update = $product->update([
            'en_Product_Name' => is_null($data['en_product_name']) ? $product->en_Product_Name : $data['en_product_name'],
            'en_Product_Slug' => is_null($data['en_product_slug']) ? $product->en_Product_Slug : $data['en_product_slug'],
            'Brand_Id' => is_null($data['en_brand_name']) ? $product->Brand_Id : $data['en_brand_name'],
            'Category_Id' => is_null($data['en_category_name']) ? $product->Category_Id : $data['en_category_name'],
            'Price' => is_null($data['price']) ? $product->Price : $data['price'],
            'Discount' => is_null($data['discount']) ? $product->Discount : $data['discount'],
            'Discount_Price' => is_null($data['discount_price']) ? $product->Discount_Price : $data['discount_price'],
            'en_About' => is_null($data['en_about']) ? $product->en_About : $data['en_about'],
            'en_Description' => is_null($data['en_description']) ? $product->en_Description : $data['en_description'],
            'en_ShippingReturn' => is_null($data['en_shippingreturn']) ? $product->en_ShippingReturn : $data['en_shippingreturn'],
            'en_AdditionalInformation' => is_null($data['en_additionalinformation']) ? $product->en_AdditionalInformation : $data['en_additionalinformation'],
            'fr_Product_Name' => is_null($data['fr_product_name']) ? $product->fr_Product_Name : $data['fr_product_name'],
            'fr_Product_Slug' => is_null($data['fr_product_slug']) ? $product->fr_Product_Slug : $data['fr_product_slug'],
            'fr_About' => is_null($data['fr_about']) ? $product->fr_About : $data['fr_about'],
            'fr_Description' => is_null($data['fr_description']) ? $product->fr_Description : $data['fr_description'],
            'fr_ShippingReturn' => is_null($data['fr_shippingreturn']) ? $product->fr_ShippingReturn : $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => is_null($data['fr_additionalinformation']) ? $product->fr_AdditionalInformation : $data['fr_additionalinformation'],
            'Quantity' => ($product->synced_from_smartlife || !empty($product->smartlife_id)) ? $product->Quantity : (is_null($data['qty']) ? $product->Quantity : $data['qty']),
            'ItemTag' => is_null($data['item_teg']) ? $product->ItemTag : $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'],
            'Image3' => $data['img_three'],
            'Image4' => $data['img_four'],
            'Image5' => $data['img_five'],

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'digital_file' => $data['digital_file'],
            'digital_link' => $data['digital_link'],
            'license_name' => $data['license_name'],
            'license_key' => $data['license_key'],
        ]);
        if (!empty($update)) {
            ProductTag::where('product_id', $product->id)->delete();
            foreach ($data['product_tag'] as $rpt) {
                ProductTag::create([
                    'tag' => $rpt,
                    'product_id' => $product->id,
                ]);
            }
            $result['success'] = true;
        }
        return $result;
    }

    public function affiliateProductUpdate($data, $product)
    {
        $result = ['success' => false];
        $update = $product->update([
            'en_Product_Name' => is_null($data['en_product_name']) ? $product->en_Product_Name : $data['en_product_name'],
            'en_Product_Slug' => is_null($data['en_product_slug']) ? $product->en_Product_Slug : $data['en_product_slug'],
            'Brand_Id' => is_null($data['en_brand_name']) ? $product->Brand_Id : $data['en_brand_name'],
            'Category_Id' => is_null($data['en_category_name']) ? $product->Category_Id : $data['en_category_name'],
            'Price' => is_null($data['price']) ? $product->Price : $data['price'],
            'Discount' => is_null($data['discount']) ? $product->Discount : $data['discount'],
            'Discount_Price' => is_null($data['discount_price']) ? $product->Discount_Price : $data['discount_price'],
            'en_About' => is_null($data['en_about']) ? $product->en_About : $data['en_about'],
            'en_Description' => is_null($data['en_description']) ? $product->en_Description : $data['en_description'],
            'en_ShippingReturn' => is_null($data['en_shippingreturn']) ? $product->en_ShippingReturn : $data['en_shippingreturn'],
            'en_AdditionalInformation' => is_null($data['en_additionalinformation']) ? $product->en_AdditionalInformation : $data['en_additionalinformation'],
            'fr_Product_Name' => is_null($data['fr_product_name']) ? $product->fr_Product_Name : $data['fr_product_name'],
            'fr_Product_Slug' => is_null($data['fr_product_slug']) ? $product->fr_Product_Slug : $data['fr_product_slug'],
            'fr_About' => is_null($data['fr_about']) ? $product->fr_About : $data['fr_about'],
            'fr_Description' => is_null($data['fr_description']) ? $product->fr_Description : $data['fr_description'],
            'fr_ShippingReturn' => is_null($data['fr_shippingreturn']) ? $product->fr_ShippingReturn : $data['fr_shippingreturn'],
            'fr_AdditionalInformation' => is_null($data['fr_additionalinformation']) ? $product->fr_AdditionalInformation : $data['fr_additionalinformation'],
            'Quantity' => is_null($data['qty']) ? $product->Quantity : $data['qty'],
            'ItemTag' => is_null($data['item_teg']) ? $product->ItemTag : $data['item_teg'],
            'Primary_Image' => $data['primary_image'],
            'Image2' => $data['img_two'],
            'Image3' => $data['img_three'],
            'Image4' => $data['img_four'],
            'Image5' => $data['img_five'],

            'Status' => $data['status'],
            'Featured_Product' => $data['feature'],
            'Best_Selling' => $data['best_sale'],
            'On_Sale' => $data['on_sale'],
            'New_Arrival' => $data['on_arrival'],
            'Today_Special' => $data['today_special'],
            'affiliate_link' => $data['affiliate_link'],
        ]);
        if (!empty($update)) {
            ProductTag::where('product_id', $product->id)->delete();
            foreach ($data['product_tag'] as $rpt) {
                ProductTag::create([
                    'tag' => $rpt,
                    'product_id' => $product->id,
                ]);
            }
            $result['success'] = true;
        }
        return $result;
    }

    public function slugify($text)
    {

        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        $text = preg_replace('~[^-\w]+~', '', $text);

        $text = trim($text, '-');

        $text = preg_replace('~-+~', '-', $text);

        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }

    public function generateRandomString($length = 20)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    public function syncSmartLife()
    {
        try {
            // Increase time limit and memory for large syncs
            set_time_limit(0);
            ini_set('memory_limit', '512M');

            \Illuminate\Support\Facades\Artisan::call('smartlife:sync-products');

            return redirect()->back()->with('success', __('Products synced successfully with SmartLife ERP!'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', __('Sync failed: ') . $e->getMessage());
        }
    }

    public function stockBreakdown($id)
    {
        return response()->json(['error' => 'Combos are no longer supported'], 400);
    }

    public function bulkActive(Request $request)
    {
        try {
            $ids = $request->ids;
            if (empty($ids) || !is_array($ids)) {
                return response()->json(['success' => false, 'message' => __('No products selected')]);
            }

            Product::whereIn('id', $ids)->update(['Status' => 1]);

            return response()->json(['success' => true, 'message' => __('Selected products activated successfully')]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
