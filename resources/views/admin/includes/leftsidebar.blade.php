<!-- Sidebar area start -->
<div class="sidebar__area">
    <div class="sidebar__close">
        <button class="close-btn">
            <i class="fa fa-arrow-left"></i>
        </button>
    </div>

    <div class="sidebar__open">
        <button class="open-btn">
            <i class="fa fa-arrow-right"></i>
        </button>
    </div>
    <div class="sidebar__brand">
        <a href="{{ route('admin.dashboard') }}">
            <img src="{{ asset(IMG_LOGO_PATH . $allsettings['footer_logo']) }}" alt="icon" width="150">
        </a>
    </div>
    <ul id="sidebar-menu" class="sidebar__menu">
        <li class="{{ isset($menu) && $menu == 'dashboard' ? 'mm-active' : '' }}">
            <a href="{{ route('admin.dashboard') }}">
                <img src="{{ asset('admin/images/icons/sidebar/dashboard.svg') }}" alt="icon">
                <span>{{ __('Dashboard') }}</span>
            </a>
        </li>
        @canany(['user-list'])
            <li class="{{ isset($menu) && $menu == 'admins' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-user"></i>
                    <span>{{ __('Admin Manage') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'admin_list' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.admin_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Admin List') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'add_admin' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.create_admin') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Add Admin') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'roles' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.role_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Roles') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany
        @can('category-list')
            <li class="{{ isset($submenu) && $submenu == 'category' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.category') }}">
                    <i class="fa fa-circle"></i>
                    <span>{{ __('Category') }}</span>
                </a>
            </li>
        @endcan
        @can('category-list')
        <li class="{{ isset($submenu) && $submenu == 'subcategory' ? 'mm-active' : '' }}">
            <a href="{{ route('admin.subcategory') }}">
                <i class="fa fa-circle"></i>
                <span>{{ __('Subcategory') }}</span>
            </a>
        </li>
        @endcan
        {{-- @canany(['category-list'])
        <li class="{{ isset($menu) && $menu == 'catbad' ? 'mm-active' : '' }}">


            <a class="has-arrow" href="#">
                <i class="fas fa-list"></i>
                <span>{{ __('Category and Brand') }}</span>
            </a>
            <ul>
                @can('category-list')
                <li class="{{ isset($submenu) && $submenu == 'category' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.category') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Category') }}</span>
                    </a>
                </li>
                @endcan
                @can('brand-list')
                <li class="{{ isset($submenu) && $submenu == 'brand' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.brand') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Brand') }}</span>
                    </a>
                </li>
                @endcan

            </ul>
        </li>
        @endcanany --}}
        @canany(['product-list'])
            <li class="{{ isset($menu) && $menu == 'products' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fab fa-product-hunt"></i>
                    <span>{{ __('Products') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'product' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.physical.product.create') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Add Product') }}</span>
                        </a>
                    </li>
                    <!-- <li class="{{ isset($submenu) && $submenu == 'add_monthly_offer' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.physical.product.create', ['category_slug' => 'packages']) }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Add Monthly Offer') }}</span>
                        </a>
                    </li> -->
                    <li class="{{ isset($submenu) && $submenu == 'product_list' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.product') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Product List') }}</span>
                        </a>
                    </li>
                    <!-- @can('offers-packages-list')
                    <li class="{{ isset($submenu) && $submenu == 'offers_packages' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.offers-packages.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Offers Packages') }}</span>
                        </a>
                    </li>
                    @endcan -->

                    <li class="{{ isset($submenu) && $submenu == 'product_reviews' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.product.reviews') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Product Reviews') }}</span>
                        </a>
                    </li>

                    {{-- <li class="{{ isset($submenu) && $submenu == 'additions_list' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.physical.product.addition') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Additions List') }}</span>
                        </a>
                    </li> --}}

                    {{-- <li class="{{ isset($submenu) && $submenu == 'color' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.product.color') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Product Color') }}</span>
                        </a>
                    </li> --}}
                    <li class="{{ isset($submenu) && $submenu == 'size' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.product.size') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Product Options') }} (خيارات المنتجات)</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany

        {{-- @canany(['product-list'])
        <li class="{{ isset($menu) && $menu == 'products' ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#">
                <i class="fab fa-product-hunt"></i>
                <span>{{ __('Additions') }}</span>
            </a>
            <ul>
                <li class="{{ isset($submenu) && $submenu == 'add_addition' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.physical.product.addition.create') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Add Additions') }}</span>
                    </a>
                </li>

                <li class="{{ isset($submenu) && $submenu == 'addition_list' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.physical.product.addition.index') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Additions List') }}</span>
                    </a>
                </li>
            </ul>
        </li>
        @endcanany --}}

        @canany(['order-list'])
            <li class="{{ isset($menu) && $menu == 'shipment' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-shopping-cart"></i>
                    <span>{{ __('Order Management') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'orders_all' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'all') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('All Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount() }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_pending' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'pending') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Pending Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount(ORDER_PENDING) }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_processing' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'processing') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Processing Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount(ORDER_PROCESSING) }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_shipped' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'shipped') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Shipped Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount(ORDER_SHIPPED) }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_delivered' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'delivered') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Delivered Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount(ORDER_DELIVERED) }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_cancelled' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'cancelled') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Cancelled Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount(ORDER_CANCELLED) }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_returned' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.orders', 'returned') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Returned Orders') }}</span>
                            <span class="badge bg-info text-white">{{ orderCount(ORDER_RETURN) }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'orders_report' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.reports.orders.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Orders Report') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'delivery_men_report' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.reports.delivery_men.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Delivery Men Report') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($menu) && $menu == 'custom_box_orders' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.custom_box_orders.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Custom Box Orders') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany
        @canany(['transaction-list'])
            <li class="{{ isset($menu) && $menu == 'transactions' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.transactions') }}">
                    <i class="fas fa-random"></i>
                    <span>{{ __('Transactions') }}</span>
                </a>
            </li>
        @endcanany
        @canany(['tax-list'])
            <li class="{{ isset($menu) && $menu == 'tax' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.country_taxation_list') }}">
                    <i class="fas fa-percent"></i>
                    <span>{{ __('Tax Settings') }}</span>
                </a>
            </li>
        @endcanany
        @canany(['delivery-charge-list'])
            <li class="{{ isset($menu) && $menu == 'delivery_charge' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-shipping-fast"></i>
                    <span>{{ __('Delivery Charge') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'delivery_charge_list' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.country_dc_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Delivery Charge List') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'governorates' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.location.state.list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Governorates') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'wilayats' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.location.city.list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Wilayats') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'areas' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.location.area.list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Areas') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany
        {{-- @canany(['currency-list'])--}}
        {{-- <li class="{{ isset($menu) && $menu == 'currency' ? 'mm-active' : '' }}">--}}
            {{-- <a href="{{ route('admin.currency_list') }}">--}}
                {{-- <i class="fa fa-dollar-sign"></i>--}}
                {{-- <span>{{ __('Currency') }}</span>--}}
                {{-- </a>--}}
            {{-- </li>--}}
        {{-- @endcanany--}}



        @canany(['developer'])
            <li class="{{ isset($menu) && $menu == 'offers' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.offers') }}">
                    <i class="fas fa-code"></i>
                    <span>{{ __('offers') }}</span>
                </a>
            </li>
        @endcanany

        @canany(['developer'])
            <li class="{{ isset($menu) && $menu == 'AI' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.ai.chat.show') }}">
                    <i class="fas fa-comment-alt"></i>
                    <span>{{ __('AI') }}</span>
                </a>
            </li>
        @endcanany

        @canany(['currency-list'])
            <li class="{{ isset($menu) && $menu == 'coupon' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.coupon') }}">
                    <i class="fas fa-code"></i>
                    <span>{{ __('Coupon Code') }}</span>
                </a>
            </li>
        @endcanany

        @canany(['subscription-list'])
            <li class="{{ isset($menu) && $menu == 'subscriptions' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.subscriptions') }}">
                    <i class="fas fa-box-open"></i>
                    <span>{{ __('Subscriptions') }}</span>
                </a>
            </li>
        @endcanany

        @canany(['advertise-list'])
            <li class="{{ isset($menu) && $menu == 'advertise' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.advertise') }}">
                    <i class="fas fa-ad"></i>
                    <span>{{ __('Advertise') }}</span>
                </a>
            </li>
        @endcanany
        @canany(['advertise-list'])
            <li class="{{ isset($menu) && $menu == 'recipe' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.recipe.index') }}">
                    <i class="fas fa-utensils"></i>
                    <span>{{ __('Recipes') }}</span>
                </a>
            </li>
        @endcanany
        {{-- @canany(['blog-list'])
        <li class="{{ isset($menu) && $menu == 'blog' ? 'mm-active' : '' }}">
            <a href="{{ route('admin.blog') }}">
                <i class="fab fa-blogger-b"></i>
                <span>{{ __('Blog') }}</span>
            </a>
        </li>
        @endcanany --}}
        @canany(['product-create', 'product-edit'])
            <li class="{{ isset($menu) && $menu == 'tags' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-tags"></i>
                    <span>{{ __('Tags') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'product_tag' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.product.tag') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Product Tag') }}</span>
                        </a>
                    </li>
                    {{-- <li class="{{ isset($submenu) && $submenu == 'item_tag' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.item.tag') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Item Tag') }}</span>
                        </a>
                    </li> --}}
                </ul>
            </li>
        @endcanany
        @canany(['crm-list', 'wholesale-list', 'expert-request-list'])
            <li class="{{ isset($menu) && $menu == 'crm' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-blog"></i>
                    <span>{{ __('CRM') }}</span>
                </a>
                <ul>
                    @can('crm-list')
                    <li class="{{ isset($submenu) && $submenu == 'contact_us' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.contact.us.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Contact Us') }}</span>
                        </a>
                    </li>
                    @endcan
                    @can('wholesale-list')
                    <li class="{{ isset($submenu) && $submenu == 'wholesale_requests' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.wholesale.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Wholesale Requests') }}</span>
                        </a>
                    </li>
                    @endcan
                    @can('expert-request-list')
                    <li class="{{ isset($submenu) && $submenu == 'expert_requests' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.expert-requests.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Expert Requests') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'partner_requests' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.partner-requests.index') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Partner Requests') }}</span>
                        </a>
                    </li>
                    @endcan
                </ul>
            </li>
            @can('crm-list')
            <li class="{{ isset($menu) && $menu == 'subscribers' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.subscribe.index') }}">
                    <i class="fas fa-envelope"></i>
                    <span>{{ __('Subscribers') }}</span>
                </a>
            </li>
            @endcan
        @endcanany
        @canany(['user-list'])
            <li class="{{ isset($menu) && $menu == 'users' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-users"></i>
                    <span>{{ __('Users') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'customer_list' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.customer_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Customer List') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany
        @canany(['user-list'])
            <li class="{{ isset($menu) && $menu == 'delivery_man' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-truck"></i>
                    <span>{{ __('Delivery Men') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'delivery_man_list' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.delivery_man') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Delivery Men List') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'delivery_man_create' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.delivery_man.create') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Add Delivery Man') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany
        @canany(['cms-list'])
            <li class="{{ isset($menu) && $menu == 'site_content' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-cube"></i>
                    <span>{{ __('CMS') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'general_settings' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.general.settings') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('General Settings') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'content_home' ? 'mm-active' : '' }}">
                        @php
                            // Prefer linking directly to the edit blade for the first homepage section (if present),
                            // otherwise fall back to the index listing route.
                            try {
                                $firstSection = \App\Models\Admin\SiteContent\HomepageSection::orderBy('display_order')->first();
                            } catch (\Exception $e) {
                                $firstSection = null;
                            }
                            $homeLink = $firstSection ? route('admin.home.page.site.content.edit', $firstSection->id) : route('admin.home.page.site.content');
                        @endphp
                        <a href="{{ $homeLink }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Home Page') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'content_about' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.about.page.site.content') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('About Page') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'content_social_link' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.social.link') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Social Link') }}</span>
                        </a>
                    </li>
                    {{-- <li class="{{ isset($submenu) && $submenu == 'image_gallery' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.image.gallery') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Image Gallery') }}</span>
                        </a>
                    </li> --}}
                    <!-- <li class="{{ isset($submenu) && $submenu == 'testimonial' ? 'mm-active' : '' }}">
                                            <a href="{{ route('admin.testimonial') }}">
                                                <i class="fa fa-circle"></i>
                                                <span>{{ __('Testimonial') }}</span>
                                            </a>
                                        </li> -->
                    <!-- <li class="{{ isset($submenu) && $submenu == 'languages' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.language_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Languages') }}</span>
                        </a>
                    </li> -->
                </ul>
            </li>
        @endcanany
        {{-- <li class="{{ isset($menu) && $menu == 'menus' ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#">
                <i class="fa fa-bars"></i>
                <span>{{ __('Manage Menus') }}</span>
            </a>
            <ul>
                <li class="{{ isset($submenu) && $submenu == 'static_menus' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.static_menus') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Static Menus') }}</span>
                    </a>
                </li>
                <li class="{{ isset($submenu) && $submenu == 'dynamic_menus' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.dynamic_menus') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Dynamic Menus') }}</span>
                    </a>
                </li>
                <li class="{{ isset($submenu) && $submenu == 'submenus' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.submenus') }}">
                        <i class="fa fa-circle"></i>
                        <span>{{ __('Submenus') }}</span>
                    </a>
                </li>
            </ul>
        </li> --}}
        {{-- <li class="{{ isset($menu) && $menu == 'theme_management' ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#">
                <i class="fas fa-cog"></i>
                <span>{{ __('Theme Management') }}</span>
            </a>
            <ul>
                <li class="{{ isset($submenu) && $submenu == 'theme' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.theme') }}">{{ __('Select Theme') }}</a>
                </li>
            </ul>
        </li> --}}
        <!--
        <li class="{{ isset($menu) && $menu == 'slider_banner' ? 'mm-active' : '' }}">
            <a class="has-arrow" href="#">
                <i class="fas fa-list-ol"></i>
                <span>{{ __('Slider') }}</span>
            </a>
            <ul>
                {{-- @canany(['banner-list'])
                <li class="{{ isset($submenu) && $submenu == 'banner' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.banner') }}">{{ __('Banner') }} ({{ __('Home two') }})</a>
                </li>
                @endcanany --}}
                @canany(['slider-list'])
                    <li class="{{ isset($submenu) && $submenu == 'slider' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.slider') }}">{{ __('Slider') }}</a>
                    </li>
                @endcanany
                {{-- <li class="{{ isset($submenu) && $submenu == 'special_offer' ? 'mm-active' : '' }}">
                    <a href="{{ route('admin.theme.special.offer') }}">{{ __('Offer') }}
                        ({{ __('Home two') }})</a>
                </li> --}}
            </ul>
        </li> -->
        <!-- @canany(['cms-create', 'cms-edit'])
            <li class="{{ isset($menu) && $menu == 'seo' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-cube"></i>
                    <span>{{ __('SEO Management') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'home' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'home') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Home') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'about-us' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'about-us') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('About Us') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'contact' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'contact') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Contact') }}</span>
                        </a>
                    </li>
                    {{-- <li class="{{ isset($submenu) && $submenu == 'blog' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'blog') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Blog') }}</span>
                        </a>
                    </li> --}}
                    <li class="{{ isset($submenu) && $submenu == 'all-products' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'all-products') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Products') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'cart' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'cart') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Cart') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'checkout' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'checkout') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Checkout') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'wishlist' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'wishlist') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Wishlist') }}</span>
                        </a>
                    </li>
                    {{-- <li class="{{ isset($submenu) && $submenu == 'compare' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'compare') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Compare') }}</span>
                        </a>
                    </li> --}}
                    <li class="{{ isset($submenu) && $submenu == 'sign-in' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'sign-in') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Sign In') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'sign-up' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'sign-up') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Sign Up') }}</span>
                        </a>
                    </li>
                    {{-- <li class="{{ isset($submenu) && $submenu == 'forget-password' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'forget-password') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Forget Password') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'reset-password' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.manage_seo', 'reset-password') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Reset Password') }}</span>
                        </a>
                    </li> --}}
                </ul>
            </li>
        @endcanany -->

        @canany(['cms-list'])
            <li class="{{ isset($menu) && $menu == 'company' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-address-book"></i>
                    <span>{{ __('Company') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'faq' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.faq_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('FAQ') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'privacy_policy' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.privacy_policy') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Privacy Policy') }}</span>
                        </a>
                    </li>
                    <!-- <li class="{{ isset($submenu) && $submenu == 'return_policy' ? 'mm-active' : '' }}">
                                            <a href="{{ route('admin.refund_policy') }}">
                                                <i class="fa fa-circle"></i>
                                                <span>{{ __('Refund Policy') }}</span>
                                            </a>
                                        </li> -->
                    <li class="{{ isset($submenu) && $submenu == 'shipping_return' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.shipping_return') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Shipment & Return') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'terms_conditions' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.terms_conditions') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Terms & Condition') }}</span>
                        </a>
                    </li>
                    <!-- <li class="{{ isset($submenu) && $submenu == 'company_story' ? 'mm-active' : '' }}">
                                            <a href="{{ route('admin.company.story') }}">
                                                <i class="fa fa-circle"></i>
                                                <span>{{ __('Company Story') }}</span>
                                            </a>
                                        </li> -->
                </ul>
            </li>
        @endcanany

        <!-- @canany(['menu-list'])
            <li class="{{ isset($menu) && $menu == 'pages' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.pages') }}">
                    <i class="fas fa-book"></i>
                    <span>{{ __('Manage Pages') }}</span>
                </a>
            </li>
            <li class="{{ isset($menu) && $menu == 'menus' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fa fa-bars"></i>
                    <span>{{ __('Manage Menus') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'static_menus' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.static_menus') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Static Menus') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'dynamic_menus' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.dynamic_menus') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Dynamic Menus') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'submenus' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.submenus') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Submenus') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany -->
        <li class="{{ isset($menu) && $menu == 'delivery_charge_report' ? 'mm-active' : '' }}">
            <a href="{{ route('admin.delivery.charge.report') }}">
                <i class="fas fa-chart-line"></i>
                <span>{{ __('Delivery Charge Report') }}</span>
            </a>
        </li>
        {{-- @canany(['cms-list'])
        <li class="{{ isset($menu) && $menu == 'sitemap' ? 'mm-active' : '' }}">
            <a href="{{ route('admin.sitemap_list') }}">
                <i class="fa fa-sitemap"></i>
                <span>{{ __('Sitemaps') }}</span>
            </a>
        </li>
        @endcanany --}}
        @canany(['payment-gateway-list'])
            <li class="{{ isset($menu) && $menu == 'payment' ? 'mm-active' : '' }}">
                <a href="{{ route('admin.payment_gateway_list') }}">
                    <i class="fa fa-money-bill"></i>
                    <span>{{ __('Payment Gateway') }}</span>
                </a>
            </li>
        @endcanany
        @canany(['cms-list'])
            <li class="{{ isset($menu) && $menu == 'company' ? 'mm-active' : '' }}">
                <a class="has-arrow" href="#">
                    <i class="fas fa-address-book"></i>
                    <span>{{ __('Company') }}</span>
                </a>
                <ul>
                    <li class="{{ isset($submenu) && $submenu == 'faq' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.faq_list') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('FAQ') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'privacy_policy' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.privacy_policy') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Privacy Policy') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'return_policy' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.refund_policy') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Refund Policy') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'shipping_return' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.shipping_return') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Shipment & Return') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'terms_conditions' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.terms_conditions') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Terms & Condition') }}</span>
                        </a>
                    </li>
                    <li class="{{ isset($submenu) && $submenu == 'company_story' ? 'mm-active' : '' }}">
                        <a href="{{ route('admin.company.story') }}">
                            <i class="fa fa-circle"></i>
                            <span>{{ __('Company Story') }}</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endcanany
    </ul>
</div>
<!-- Sidebar area end -->