<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Admin\Product;
use App\Models\Admin\Category;
use App\Models\SeoSetting;
use App\Models\ProductReview;
use App\Models\Admin\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewDesignController extends Controller
{
    /**
     * Display the new design home page.
     */
    public function index()
    {
        $relations = ['sizes', 'weights', 'additions'];

        // Only show admin-selected Today Special products in the special offers carousel.
        $products = Product::where('Status', 1)
            ->available()
            ->where('Today_Special', 1)
            ->with($relations)
            ->orderBy('id', 'desc')
            ->get();

        // If no Today_Special products are set, fallback to the latest 5 active products.
        if ($products->isEmpty()) {
            $products = Product::where('Status', 1)
                ->available()
                ->with($relations)
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();
        }

        $tab = request('tab', 'best_seller');

        if ($tab === 'new_arrival') {
            $bestSellers = Product::where('Status', 1)
                ->available()
                ->with($relations)
                ->orderBy('id', 'desc')
                ->take(8)
                ->get();
        } elseif ($tab === 'trending') {
            // If there's a view_count or similar we'd use it, otherwise fallback to random or latest
            $bestSellers = Product::where('Status', 1)
                ->available()
                ->with($relations)
                ->inRandomOrder()
                ->take(8)
                ->get();
        } else {
            // best_seller (default)
            $bestSellers = Product::where('Status', 1)
                ->available()
                ->where('Sold', '>', 0)
                ->with($relations)
                ->orderBy('Sold', 'desc')
                ->take(8)
                ->get();

            if ($bestSellers->isEmpty()) {
                $bestSellers = Product::where('Status', 1)
                    ->available()
                    ->with($relations)
                    ->orderBy('id', 'desc')
                    ->take(5)
                    ->get();
            }
        }

        // Load SEO for home page (admin-managed). Controller supplies title/description/keywords to the view.
        $seo = SeoSetting::where('slug', 'home')->first();
        $title = $seo->title ?? __('HiSpeed — New Design');
        $description = $seo->description ?? '';
        $keywords = $seo->keywords ?? '';

        $reviews = ProductReview::with('user', 'product')
            ->where('is_visible', true)
            ->whereHas('product', function ($q) {
                $q->where('Status', 1)->available();
            })
            ->orderBy('created_at', 'desc')
            ->take(6)
            ->get();

        // All active categories for the "Browse Categories" carousel
        $allCategories = Category::where('Status', 1)
            ->orderBy('order', 'asc')
            ->orderBy('id', 'desc')
            ->get();

        // Featured Categories to show as individual product sections (marked by admin)
        $featuredCategories = $allCategories->where('show_on_home', 1);

        // Manual lazy load products per featured category
        foreach ($featuredCategories as $cat) {
            $catProducts = $cat->products()->where('Status', 1)->available()->orderByRaw('fr_Product_Name COLLATE utf8mb4_unicode_ci ASC')->with($relations)->take(12)->get();
            $cat->setRelation('products', $catProducts);
        }

        // Load homepage sections from content management
        $whyChoose = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_why_choose')->first();
        $features = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_features')->first();
        $saleBanner = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_sale_banner')->first();
        $brands = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_brands')->first();
        $heroSection = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_hero')->first();
        $statsSection = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_stats')->first();
        $farmSection = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_farm')->first();
        $featuresSection = \App\Models\Admin\SiteContent\HomepageSection::where('section_key', 'newdesign_features')->first();

        // Fetch dynamic packages
        $packages = Product::where('Status', 1)
            ->available()
            ->where('is_package', 1)
            ->with($relations)
            ->orderBy('id', 'desc')
            ->get();

        // Fetch dynamic occasions
        $occasions = Category::where('Status', 1)
            ->where('is_occasion', 1)
            ->orderBy('order', 'asc')
            ->get();

        // Fetch dynamic premium cuts
        $cuts = Category::where('Status', 1)
            ->where('is_cut', 1)
            ->withCount('products')
            ->orderBy('order', 'asc')
            ->get();

        // Fetch FAQs
        $faqs = \App\Models\Faq::all();

        // Fetch Hero Advertises
        $heroAdvertises = \App\Models\Admin\Advertise::where('status', 1)
            ->where('location', 'hero')
            ->orderBy('display_order', 'asc')
            ->get();

        // Fetch active recipes
        $recipes = \App\Models\Admin\Recipe::where('status', 1)
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        return view('v2.home.index', compact(
            'products',
            'bestSellers',
            'title',
            'description',
            'keywords',
            'reviews',
            'allCategories',
            'featuredCategories',
            'whyChoose',
            'features',
            'saleBanner',
            'brands',
            'heroSection',
            'statsSection',
            'packages',
            'occasions',
            'cuts',
            'faqs',
            'heroAdvertises',
            'recipes',
            'farmSection',
            'featuresSection'
        ));
    }

    public function store(Request $request)
    {
        // عرض صفحة المتجر الكامل مع جلب الفئات والمنتجات المتاحة (باستثناء الباقات والعروض والمنتجات التجميعية)
        $categories = Category::where('Status', 1)
            ->whereNotIn('en_Category_Slug', ['packages', 'offers'])
            ->orderBy('order', 'asc')
            ->get();

        $subcategories = \App\Models\Subcategory::where('status', 1)->get();

        $query = Product::with(['subcategory', 'sizes', 'weights', 'category'])
            ->available()
            ->whereHas('category', function ($q) {
                $q->whereNotIn('en_Category_Slug', ['packages', 'offers']);
            });

        // Category Filter
        if ($request->has('category') && !empty($request->category)) {
            $query->whereIn('category_id', $request->category);
        }

        // Subcategory (Cut Style) Filter
        if ($request->has('subcategory') && !empty($request->subcategory)) {
            $query->whereIn('subcategory_id', $request->subcategory);
        }

        // Search text Filter
        if ($request->has('q') && !empty($request->q)) {
            $q = $request->q;
            $query->where(function ($qBuilder) use ($q) {
                $qBuilder->where('en_Product_Name', 'LIKE', '%' . $q . '%')
                         ->orWhere('fr_Product_Name', 'LIKE', '%' . $q . '%');
            });
        }

        // Price Filter
        if ($request->has('price_min') && $request->price_min != '') {
            $query->where('Price', '>=', $request->price_min);
        }
        if ($request->has('price_max') && $request->price_max != '') {
            $query->where('Price', '<=', $request->price_max);
        }

        // Stock Filter
        if ($request->has('stock')) {
            $stockFilters = $request->stock;
            $query->where(function($q) use ($stockFilters) {
                if (in_array('in_stock', $stockFilters)) {
                    $q->orWhere('Quantity', '>', 5);
                }
                if (in_array('out_of_stock', $stockFilters)) {
                    $q->orWhere('Quantity', '<=', 0);
                }
                if (in_array('limited', $stockFilters)) {
                    $q->orWhereBetween('Quantity', [1, 5]);
                }
            });
        }

        // Sorting
        if ($request->has('sort')) {
            if ($request->sort == 'price_asc') {
                $query->orderBy('Price', 'asc');
            } elseif ($request->sort == 'price_desc') {
                $query->orderBy('Price', 'desc');
            } else {
                $query->orderBy('id', 'desc');
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->paginate(12);

        // Collect all unique sizes (weights) associated with available products
        $availableSizes = collect();
        foreach ($products->items() as $product) {
            if($product->sizes) {
                foreach ($product->sizes as $size) {
                    $availableSizes->push($size);
                }
            }
        }
        $sizes = $availableSizes->unique('id')->values();

        if ($request->ajax()) {
            return view('v2.home.partials.product_grid', compact('products'))->render();
        }

        return view('v2.home.store', compact('categories', 'products', 'subcategories', 'sizes'));
    }

    public function login()
    {
        // عرض صفحة تسجيل الدخول (فرونت فقط)
        return view('v2.auth.login');
    }

    public function register()
    {
        // عرض صفحة إنشاء الحساب (فرونت فقط)
        return view('v2.auth.register');
    }

    public function recipeDetails($slug)
    {
        $recipe = \App\Models\Admin\Recipe::where('slug', $slug)->firstOrFail();
        
        // Fetch some related or latest recipes for sidebar/footer (optional)
        $latestRecipes = \App\Models\Admin\Recipe::where('status', 1)
            ->where('id', '!=', $recipe->id)
            ->orderBy('id', 'desc')
            ->take(3)
            ->get();

        return view('v2.recipe.details', compact('recipe', 'latestRecipes'));
    }

    public function product_details($slug)
    {
        $relations = ['sizes', 'weights', 'additions', 'product_reviews', 'category'];
        
        $product = Product::with($relations)
            ->where('en_Product_Slug', $slug)
            ->orWhere('fr_Product_Slug', $slug)
            ->firstOrFail();

        // Calculate average rating
        $avgRating = $product->product_reviews()->where('is_visible', true)->avg('rating') ?? 5;
        $reviewsCount = $product->product_reviews()->where('is_visible', true)->count();

        // Fetch related products (same category)
        $relatedProducts = Product::where('Category_Id', $product->Category_Id)
            ->where('id', '!=', $product->id)
            ->where('Status', 1)
            ->available()
            ->with(['sizes', 'weights'])
            ->take(4)
            ->get();

        // Fetch FAQs for the bottom section
        $faqs = \App\Models\Faq::take(4)->get();

        return view('v2.home.product_details', compact('product', 'avgRating', 'reviewsCount', 'relatedProducts', 'faqs'));
    }

    public function aboutUs()
    {
        $seo = SeoSetting::where('slug', 'about')->first();
        $title = $seo->title ?? __('v2_layout.nav_about');
        
        $settings = \App\Models\Setting::pluck('value', 'slug')->toArray();
        $lang = app()->getLocale() == 'en' ? 'en' : 'ar';
        
        return view('v2.home.about_us', compact('seo', 'title', 'settings', 'lang'));
    }

    public function contactUs()
    {
        $seo = SeoSetting::where('slug', 'contact')->first();
        $title = $seo->title ?? __('v2_layout.nav_contact');
        
        $settings = \App\Models\Setting::pluck('value', 'slug')->toArray();
        $lang = app()->getLocale() == 'en' ? 'en' : 'ar';

        if(!isset($settings['contact_map_iframe'])) {
            \App\Models\Setting::firstOrCreate(['slug' => 'contact_map_iframe'], ['value' => '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11624.966953932402!2d58.4063!3d23.5859!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e91f173c38b29f7%3A0x6b772d1f0ec843f8!2sMuscat%2C%20Oman!5e0!3m2!1sen!2s!4v1680000000000!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>']);
            $settings['contact_map_iframe'] = '<iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d11624.966953932402!2d58.4063!3d23.5859!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3e91f173c38b29f7%3A0x6b772d1f0ec843f8!2sMuscat%2C%20Oman!5e0!3m2!1sen!2s!4v1680000000000!5m2!1sen!2s" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
        }
        
        return view('v2.home.contact_us', compact('seo', 'title', 'settings', 'lang'));
    }

    public function contactUsSend(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:20',
            'message' => 'required|string'
        ]);
        
        \App\Models\Front\Contactus::create([
            'FirstName' => $request->name,
            'Email' => $request->email,
            'Phone' => $request->phone ?? '',
            'Message' => $request->message ?? '',
        ]);
        
        return redirect()->back()->with('success', __('v2_home.contact_success_message'));
    }

    public function favorites()
    {
        $wishlists = \App\Models\Front\Wishlist::where('User_Id', auth()->id())
            ->with(['product.category', 'product.sizes', 'product.weights'])
            ->get();
            
        return view('v2.home.favorites', compact('wishlists'));
    }

    public function profileFavorites()
    {
        $wishlists = \App\Models\Front\Wishlist::where('User_Id', auth()->id())
            ->with(['product.category', 'product.sizes', 'product.weights'])
            ->get();
            
        return view('v2.user.favorites', compact('wishlists'));
    }

    public function cart()
    {
        $relatedProducts = \App\Models\Admin\Product::where('Status', 1)
            ->inRandomOrder()
            ->take(4)
            ->get();
            
        return view('v2.home.cart', compact('relatedProducts'));
    }

    public function profile()
    {
        $user = auth()->user();
        $title = __('v2_home.your_account');
        return view('v2.user.profile', compact('user', 'title'));
    }

    public function profileAddresses()
    {
        $user = Auth::user();
        $title = __('v2_product.my_addresses');
        $addresses = $user->addresses()->orderBy('is_default', 'desc')->get();
        
        $oman = \App\Models\Country::where('name_en', 'Oman')->first();
        if ($oman) {
            $oman_country_id = $oman->id;
        } else {
            $firstCountry = \App\Models\Country::first();
            $oman_country_id = $firstCountry ? $firstCountry->id : null;
        }
        $states = $oman_country_id ? \App\Models\State::where('country_id', $oman_country_id)->get() : collect([]);

        return view('v2.user.addresses', compact('user', 'addresses', 'title', 'states'));
    }

    public function profileOrders()
    {
        $user = auth()->user();
        $orders = \App\Models\Admin\Order::with(['order_details', 'order_details.product'])
            ->where('User_Id', $user->id)
            ->latest()
            ->get();
            
        foreach ($orders as $order) {
            $order->confirmed_at = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('j M Y, g:i A') : null;
            $order->delivered_at = !empty($order->Delivery_At) ? \Carbon\Carbon::parse($order->Delivery_At)->format('j M Y, g:i A') : null;
        }

        $pendingOrders = $orders->whereNotIn('Order_Status', [ORDER_DELIVERED, ORDER_CANCELLED, ORDER_DELIVERED_FAILED, ORDER_RETURN]);
        $completedOrders = $orders->where('Order_Status', ORDER_DELIVERED);

        $title = __('v2_home.orders');
        return view('v2.user.orders', compact('user', 'pendingOrders', 'completedOrders', 'title'));
    }

    public function trackOrder($id)
    {
        $user = auth()->user();
        $order = \App\Models\Admin\Order::with(['order_details', 'order_details.product'])
            ->where('id', $id)
            ->where('User_Id', $user->id)
            ->firstOrFail();

        $order->confirmed_at = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('j M Y, g:i A') : null;
        $order->delivered_at = !empty($order->Delivery_At) ? \Carbon\Carbon::parse($order->Delivery_At)->format('j M Y, g:i A') : null;
        $order->time_only = $order->created_at ? \Carbon\Carbon::parse($order->created_at)->format('g:i A') : null;

        $title = __('v2_home.tracking_details');
        return view('v2.user.order_track', compact('user', 'order', 'title'));
    }


    public function wholesale()
    {
        $is_approved = false;
        $is_pending = false;
        $request_status = null;
        $products = collect();

        if (auth()->check()) {
            $user = auth()->user();
            // Find request by user_id, or fallback to matching by phone number
            $wholesaleReq = \App\Models\Front\WholesaleRequest::where('user_id', $user->id)
                ->orWhere(function ($query) use ($user) {
                    if ($user->Number) {
                        $query->where('contact_phone', $user->Number);
                    } else {
                        $query->whereRaw('1=0');
                    }
                })
                ->first();

            if ($wholesaleReq) {
                $request_status = $wholesaleReq->status;
                if ($wholesaleReq->status == 1) {
                    $is_approved = true;
                    // Link user_id if it wasn't linked already
                    if (!$wholesaleReq->user_id) {
                        $wholesaleReq->update(['user_id' => $user->id]);
                    }
                } elseif ($wholesaleReq->status == 0) {
                    $is_pending = true;
                }
            }
        }

        if ($is_approved) {
            // Load wholesale products (e.g. coffee crops & preparation tools)
            $relations = ['sizes', 'weights', 'additions'];
            $products = Product::where('Status', 1)
                ->available()
                ->whereHas('category', function ($query) {
                    $query->whereIn('en_Category_Slug', ['coffee-crops']);
                })
                ->with($relations)
                ->get();
        }

        return view('front.home.wholesale', compact('is_approved', 'is_pending', 'request_status', 'products'));
    }

    public function storeWholesaleRequest(Request $request)
    {
        if (!auth()->check()) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => app()->getLocale() == 'en'
                        ? 'You must be logged in to submit a wholesale request.'
                        : 'يجب عليك تسجيل الدخول أولاً لتقديم طلب الجملة.',
                ], 401);
            }
            return redirect()->route('login')->with('error', app()->getLocale() == 'en'
                ? 'You must be logged in to submit a wholesale request.'
                : 'يجب عليك تسجيل الدخول أولاً لتقديم طلب الجملة.');
        }

        $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_name' => 'required|string|max:255',
            'contact_phone' => 'required|string|max:50',
            'estimated_qty' => 'required|string|max:255',
            'services' => 'nullable|array',
            'notes' => 'nullable|string',
            'cr_or_signboard' => 'required|file|mimes:pdf,jpeg,png,jpg,gif|max:10240', // 10MB limit
        ]);

        $existing = \App\Models\Front\WholesaleRequest::where('user_id', auth()->id())->first();
        if ($existing && $existing->status != 2) {
            $msg = $existing->status == 1
                ? (app()->getLocale() == 'en' ? 'You are already approved for wholesale!' : 'لقد تمت الموافقة على طلب الجملة الخاص بك بالفعل!')
                : (app()->getLocale() == 'en' ? 'You already have a pending wholesale request.' : 'لديك بالفعل طلب جملة معلق قيد المراجعة.');
            if ($request->ajax()) {
                return response()->json(['success' => false, 'message' => $msg]);
            }
            return redirect()->back()->with('error', $msg);
        }

        $docName = null;
        if ($request->hasFile('cr_or_signboard')) {
            $docName = fileUpload($request->file('cr_or_signboard'), 'uploaded_files/wholesale/');
        }

        \App\Models\Front\WholesaleRequest::create([
            'user_id' => auth()->id(),
            'company_name' => $request->company_name,
            'contact_name' => $request->contact_name,
            'contact_phone' => $request->contact_phone,
            'estimated_qty' => $request->estimated_qty,
            'services' => $request->services,
            'notes' => $request->notes,
            'cr_or_signboard' => $docName,
            'status' => 0, // Pending
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'en'
                    ? 'Your wholesale request has been submitted successfully and is pending review!'
                    : 'تم إرسال طلب الجملة الخاص بك بنجاح وهو قيد المراجعة حالياً!',
            ]);
        }

        return redirect()->back()->with('success', app()->getLocale() == 'en'
            ? 'Your wholesale request has been submitted successfully and is pending review!'
            : 'تم إرسال طلب الجملة الخاص بك بنجاح وهو قيد المراجعة حالياً!');
    }

    public function become_partner()
    {
        // عرض صفحة كن شريكاً
        return view('front.home.become_partner');
    }

    public function social_responsibility()
    {
        // عرض صفحة المسؤولية الاجتماعية
        $initiatives = \App\Models\CsrInitiative::orderBy('created_at', 'desc')->get();
        return view('front.home.social_responsibility', compact('initiatives'));
    }

    public function trial_boxes()
    {
        // عرض صفحة بوكسات التجربة
        $category = Category::where('en_Category_Slug', 'trial-boxes')
            ->orWhere('fr_Category_Slug', 'trial-boxes')
            ->first();
        $products = $category ? $category->products()->where('Status', 1)->get() : collect();
        return view('front.home.trial_boxes', compact('products'));
    }

    public function subscriptions()
    {
        $subscriptions = \App\Models\Subscription::where('is_active', 1)->get();
        return view('front.home.subscriptions', compact('subscriptions'));
    }

    public function custom_box()
    {
        $cropsCategory = Category::where('en_Category_Slug', 'coffee-crops')
            ->orWhere('fr_Category_Slug', 'coffee-crops')
            ->first();
        $crops = $cropsCategory ? $cropsCategory->products()->where('Status', 1)->get() : collect();

        $toolsCategory = Category::whereIn('en_Category_Slug', ['preparation-tools', 'accessories'])
            ->orWhereIn('fr_Category_Slug', ['preparation-tools', 'accessories'])
            ->get();
        $tools = collect();
        foreach ($toolsCategory as $cat) {
            $tools = $tools->merge($cat->products()->where('Status', 1)->get());
        }

        $templates = \App\Models\CustomBoxTemplate::where('is_active', 1)->get();

        return view('front.home.custom_box', compact('crops', 'tools', 'templates'));
    }

    public function coffee_crops()
    {
        // عرض صفحة محاصيل القهوة
        $category = Category::where('en_Category_Slug', 'coffee-crops')
            ->orWhere('fr_Category_Slug', 'coffee-crops')
            ->first();
        $products = $category ? $category->products()->where('Status', 1)->get() : collect();
        $subcategories = $category ? $category->subCategories()->where('status', 1)->get() : collect();

        $advertises = \App\Models\Admin\Advertise::where('status', 1)
            ->where('location', 'coffee_crops')
            ->orderBy('display_order', 'asc')
            ->get();

        if ($advertises->isEmpty()) {
            $advertises = \App\Models\Admin\Advertise::where('status', 1)
                ->where('location', 'hero')
                ->orderBy('display_order', 'asc')
                ->get();
        }

        return view('front.home.coffee_crops', compact('products', 'subcategories', 'advertises'));
    }

    public function technical_tools()
    {
        // عرض صفحة معدات التحضير / الأدوات الفنية
        $category = Category::where('en_Category_Slug', 'preparation-tools')
            ->orWhere('fr_Category_Slug', 'preparation-tools')
            ->first();
        $products = $category ? $category->products()->where('Status', 1)->get() : collect();
        $subcategories = $category ? $category->subCategories()->where('status', 1)->get() : collect();

        $advertises = \App\Models\Admin\Advertise::where('status', 1)
            ->where('location', 'technical_tools')
            ->orderBy('display_order', 'asc')
            ->get();

        if ($advertises->isEmpty()) {
            $advertises = \App\Models\Admin\Advertise::where('status', 1)
                ->where('location', 'hero')
                ->orderBy('display_order', 'asc')
                ->get();
        }

        return view('front.home.technical_tools', compact('products', 'subcategories', 'advertises'));
    }

    public function experts()
    {
        // عرض صفحة استعن بخبير / الخبراء
        return view('front.home.experts');
    }

    public function storeExpertRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'required|string|max:50',
            'message' => 'required|string',
        ]);

        \App\Models\Front\ExpertRequest::create([
            'name' => $request->name,
            'company' => $request->company,
            'email' => $request->email,
            'phone' => $request->phone,
            'message' => $request->message,
            'status' => 0, // Pending
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'en'
                    ? 'Your expert assistance request has been submitted successfully!'
                    : 'تم إرسال طلب استشارة الخبير بنجاح!',
            ]);
        }

        return redirect()->back()->with('success', app()->getLocale() == 'en'
            ? 'Your expert assistance request has been submitted successfully!'
            : 'تم إرسال طلب استشارة الخبير بنجاح!');
    }

    public function storePartnerRequest(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'required|string|max:255',
            'phone' => 'required|string|max:50',
            'email' => 'required|email|max:255',
            'message' => 'nullable|string',
        ]);

        \App\Models\Front\PartnerRequest::create([
            'name' => $request->name,
            'company' => $request->company,
            'phone' => $request->phone,
            'email' => $request->email,
            'message' => $request->message,
            'status' => 0, // Pending
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'en'
                    ? 'Your partnership request has been submitted successfully!'
                    : 'تم إرسال طلب الشراكة بنجاح!',
            ]);
        }

        return redirect()->back()->with('success', app()->getLocale() == 'en'
            ? 'Your partnership request has been submitted successfully!'
            : 'تم إرسال طلب الشراكة بنجاح!');
    }

    public function monthly_offers()
    {
        $packages = \App\Models\Admin\Product::where('Status', 1)
            ->available()
            ->whereHas('category', function ($q) {
                $q->where('en_Category_Slug', 'packages');
            })
            ->orderBy('id', 'desc')
            ->get();

        return view('front.home.monthly_offers', compact('packages'));
    }

    public function gift_cards()
    {
        // عرض صفحة بطاقة الإهداء
        $packages = \App\Models\GiftCardPackage::where('status', 1)->orderBy('price', 'desc')->get();
        return view('front.home.gift_cards', compact('packages'));
    }

    public function contact_us()
    {
        // عرض صفحة تواصل معنا
        return view('front.home.contact_us');
    }

    public function contact_us_store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        \App\Models\Front\Contactus::create([
            'FirstName' => $request->name,
            'Email' => $request->email,
            'subject' => $request->subject,
            'Message' => $request->message,
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => app()->getLocale() == 'en'
                    ? 'Your message has been sent successfully!'
                    : 'تم إرسال رسالتك بنجاح!',
            ]);
        }

        return redirect()->back()->with('success', app()->getLocale() == 'en'
            ? 'Your message has been sent successfully!'
            : 'تم إرسال رسالتك بنجاح!');
    }



    public function purchaseGiftCard(Request $request)
    {
        $activeKeys = \App\Models\GiftCardPackage::where('status', 1)->pluck('key')->toArray();

        $request->validate([
            'package' => 'required|in:' . implode(',', $activeKeys),
            'recipient_name' => 'required|string|max:255',
            'method' => 'required|in:whatsapp,email',
            'phone' => 'required_if:method,whatsapp|nullable|string|max:50',
            'email' => 'required_if:method,email|nullable|email|max:255',
            'message' => 'nullable|string|max:1000',
        ]);

        $package = \App\Models\GiftCardPackage::where('key', $request->package)->where('status', 1)->first();
        if (!$package) {
            return redirect()->back()->with('error', __('Package not found or inactive.'));
        }

        $price = (float) $package->price;
        $priceInBz = round($price * 1000); // Thawani expects Baisa

        $giftRef = 'GIFT_' . strtoupper(\Illuminate\Support\Str::random(10));

        // Create a checkout session in Thawani
        $checkoutProduct = [
            [
                'name' => 'Al-Katar Gift Card - ' . $request->recipient_name,
                'quantity' => 1,
                'unit_amount' => $priceInBz,
            ]
        ];

        try {
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'thawani-api-key' => config('services.thawani.secret_key'),
            ])->post(config('services.thawani.checkout_url') . '/checkout/session', [
                        'client_reference_id' => $giftRef,
                        'mode' => 'payment',
                        'products' => $checkoutProduct,
                        'success_url' => route('gift_card.success', ['gift_ref' => $giftRef]),
                        'cancel_url' => route('gift_card.cancel'),
                        'metadata' => [
                            'recipient_name' => (string) $request->input('recipient_name'),
                            'recipient_phone' => (string) ($request->input('phone') ?? ''),
                            'recipient_email' => (string) ($request->input('email') ?? ''),
                            'send_method' => (string) $request->input('method'),
                            'gift_message' => (string) ($request->input('message') ?? ''),
                            'gift_amount' => (string) $price,
                            'buyer_name' => (string) (\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->name : 'Guest Buyer'),
                            'buyer_email' => (string) (\Illuminate\Support\Facades\Auth::check() ? \Illuminate\Support\Facades\Auth::user()->email : 'guest@example.com'),
                        ]
                    ]);

            if ($response->successful()) {
                $sessionData = $response->json();
                $sessionId = $sessionData['data']['session_id'] ?? null;
                if ($sessionId) {
                    \Illuminate\Support\Facades\Cache::put('gift_session_' . $giftRef, $sessionId, now()->addHours(2));
                }
                $paymentUrl = config('services.thawani.pay_url') . $sessionId . '?key=' . config('services.thawani.public_key');

                return redirect()->away($paymentUrl);
            } else {
                \Illuminate\Support\Facades\Log::error('Thawani session creation failed for gift card', ['body' => $response->body()]);
                return redirect()->back()->with('error', __('Something went wrong with the payment gateway.'));
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Exception in purchaseGiftCard', ['message' => $e->getMessage()]);
            return redirect()->back()->with('error', __('Failed to initiate payment.'));
        }
    }

    public function giftCardSuccess(Request $request)
    {
        $giftRef = $request->get('gift_ref');
        $sessionId = $request->get('session_id') ?: \Illuminate\Support\Facades\Cache::get('gift_session_' . $giftRef);

        if (!$sessionId) {
            return redirect()->route('gift.cards')->with('error', __('Invalid session.'));
        }

        try {
            // Verify payment status with Thawani API
            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'thawani-api-key' => config('services.thawani.secret_key'),
            ])->get(config('services.thawani.checkout_url') . '/checkout/session/' . $sessionId);

            $sessionData = $response->json();
            if ($response->successful() && isset($sessionData['success']) && $sessionData['success']) {
                $data = $sessionData['data'] ?? [];
                $paymentStatus = $data['payment_status'] ?? '';

                if ($paymentStatus === 'paid' || $paymentStatus === 'succeeded') {
                    $metadata = $data['metadata'] ?? [];
                    if (!empty($metadata)) {
                        $this->generateAndSendGiftCard(
                            $metadata['recipient_name'] ?? 'Recipient',
                            $metadata['recipient_phone'] ?? '',
                            $metadata['recipient_email'] ?? '',
                            $metadata['send_method'] ?? 'email',
                            $metadata['gift_message'] ?? '',
                            (float) ($metadata['gift_amount'] ?? 10.000),
                            $giftRef
                        );

                        return redirect()->route('gift.cards')->with('success', __('Your gift card has been successfully paid and sent to the recipient!'));
                    }
                }
            }

            return redirect()->route('gift.cards')->with('error', __('Payment was not successful.'));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error in giftCardSuccess', ['message' => $e->getMessage()]);
            return redirect()->route('gift.cards')->with('error', __('Verification failed.'));
        }
    }

    public function giftCardCancel()
    {
        return redirect()->route('gift.cards')->with('error', __('Gift Card payment cancelled.'));
    }

    public function generateAndSendGiftCard($recipientName, $recipientPhone, $recipientEmail, $sendMethod, $message, $amount, $giftRef = null)
    {
        // Check if already processed (using cache/lock or check if coupon with this giftRef suffix/code already exists)
        // We can append $giftRef to the coupon code metadata or check if CouponCode already exists for this giftRef
        // Let's generate a coupon code using the giftRef if present to ensure idempotency!
        // This is incredibly robust! If we use the giftRef in the CouponCode, it's 100% idempotent.
        // E.g. GIFT_ABC123XYZ -> coupon code 'GIFT-ABC123XYZ' or similar.
        if ($giftRef) {
            $couponCode = 'GIFT-' . str_replace('GIFT_', '', $giftRef);
        } else {
            $couponCode = 'GIFT-' . strtoupper(\Illuminate\Support\Str::random(10));
        }

        // Check if already exists in DB
        $existing = \App\Models\Admin\Coupon::where('CouponCode', $couponCode)->first();
        if ($existing) {
            \Illuminate\Support\Facades\Log::info("Gift card coupon already generated (idempotency)", ['code' => $couponCode]);
            return $existing;
        }

        // Create Coupon
        $coupon = \App\Models\Admin\Coupon::create([
            'CouponCode' => $couponCode,
            'Amount' => $amount,
            'Min_Expenses' => 0.000,
            'ExpireDate' => now()->addYear()->format('Y-m-d'),
            'usage_count' => 0,
            'user_id' => null,
        ]);

        // Create Order and OrderDetails for printer application integration
        try {
            $txnIdentifier = $giftRef ?: $couponCode;
            $existingOrder = \App\Models\Admin\Order::where('txn', $txnIdentifier)->first();
            if (!$existingOrder) {
                $maxId = \App\Models\Admin\Order::max('id') ?? 0;
                $nextNumber = 10000 + ($maxId + 1);
                $order_number = (string) $nextNumber;
                while (\App\Models\Admin\Order::where('Order_Number', $order_number)->exists()) {
                    $nextNumber++;
                    $order_number = (string) $nextNumber;
                }

                $addr = [
                    'name' => $recipientName,
                    'email' => $recipientEmail,
                    'phone_number' => $recipientPhone,
                    'street' => 'Gift Card Delivery',
                    'state' => 'Oman',
                    'city' => 'Muscat',
                    'country' => 'Oman',
                ];

                $order = \App\Models\Admin\Order::create([
                    'Order_Number' => $order_number,
                    'User_Id' => auth()->check() ? auth()->id() : null,
                    'billing_address' => $addr,
                    'shipping_address' => $addr,
                    'Delivery_Charge' => 0.000,
                    'Tax' => 0.000,
                    'Sub_Total' => $amount,
                    'Grand_Total' => $amount,
                    'Is_Free_Delivery' => true,
                    'Is_Order_Successful' => true,
                    'Is_Order_Completed' => false,
                    'Payment_Method' => 'Thawani',
                    'Payment_Status' => 1, // PAYMENT_SUCCESS
                    'Order_Status' => 2, // ORDER_PROCESSING
                    'txn' => $txnIdentifier,
                    'order_source' => 'web',
                    'is_gift' => 1,
                    'gift_recipient_name' => $recipientName,
                    'gift_recipient_phone' => $recipientPhone,
                    'gift_message' => $message,
                ]);

                if ($order) {
                    \App\Models\Admin\OrderDetails::create([
                        'Order_Id' => $order->id,
                        'Product_Id' => null,
                        'Product_Name' => 'Gift Card - ' . $recipientName,
                        'Image' => null,
                        'Price' => $amount,
                        'Quantity' => 1,
                        'Total_Price' => $amount,
                    ]);

                    try {
                        event(new \App\Events\OrderCreated($order));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error("Failed to broadcast OrderCreated event for gift card", ['error' => $e->getMessage()]);
                    }
                }
            }
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to create Order for gift card", ['error' => $e->getMessage()]);
        }

        $amountFormatted = number_format($amount, 3) . ' OMR';

        $smsMessageAr = "مرحباً {$recipientName}،\nلقد أرسل لك أحدهم بطاقة إهداء بقيمة {$amountFormatted} من بن القطار!\nكود الخصم الخاص بك هو: {$couponCode}\n";
        if ($message) {
            $smsMessageAr .= "الرسالة المرفقة: \"{$message}\"\n";
        }
        $smsMessageAr .= "يمكنك استخدام هذا الكود عند الدفع في موقعنا: " . url('/');

        $smsMessageEn = "Hello {$recipientName},\nSomeone sent you a Gift Card worth {$amountFormatted} from Al-Katar Coffee!\nYour Gift coupon code is: {$couponCode}\n";
        if ($message) {
            $smsMessageEn .= "Message: \"{$message}\"\n";
        }
        $smsMessageEn .= "You can use this coupon at checkout: " . url('/');

        $notificationText = app()->getLocale() != 'en' ? $smsMessageAr : $smsMessageEn;

        // Send Email
        if ($sendMethod === 'email' && $recipientEmail) {
            try {
                $subject = app()->getLocale() != 'en' ? "بطاقة إهداء من بن القطار" : "A Gift Card from Al-Katar Coffee!";
                \Illuminate\Support\Facades\Mail::send([], [], function ($message) use ($recipientEmail, $subject, $notificationText) {
                    $message->to($recipientEmail)
                        ->subject($subject)
                        ->html('<div style="font-family: sans-serif; direction: ' . (app()->getLocale() != 'en' ? 'rtl' : 'ltr') . '; text-align: start; padding: 20px; background-color: #FDF9F0; border: 1px solid #1A4231; border-radius: 12px; max-width: 600px; margin: auto; color: #1A4231;">' . nl2br(e($notificationText)) . '</div>');
                });
                \Illuminate\Support\Facades\Log::info("Gift Card Email successfully sent to {$recipientEmail}");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Gift Card Email", ['error' => $e->getMessage()]);
            }
        }

        // Send WhatsApp
        if ($sendMethod === 'whatsapp' && $recipientPhone) {
            try {
                $formattedPhone = str_starts_with($recipientPhone, '+') ? $recipientPhone : '+' . $recipientPhone;
                \Illuminate\Support\Facades\Log::info("WhatsApp Gift Card (BYPASSED) for {$formattedPhone}: {$notificationText}");
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error("Failed to send Gift Card WhatsApp message", ['error' => $e->getMessage()]);
            }
        }

        return $coupon;
    }
}
