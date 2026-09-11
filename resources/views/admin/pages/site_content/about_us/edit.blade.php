@extends("admin.master", ["menu" => "site_content", "submenu" => "content_about"])
@section("title", isset($title) ? $title : "إعدادات صفحة من نحن")
@section("content")
    <div class="row">
        <div class="col-md-12">
            <div class="breadcrumb__content">
                <div class="breadcrumb__content__left">
                    <div class="breadcrumb__title">
                        <h2>{{ __("إعدادات صفحة من نحن") }}</h2>
                    </div>
                </div>
                <div class="breadcrumb__content__right">
                    <nav aria-label="breadcrumb">
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route("admin.dashboard") }}">{{ __("الرئيسية") }}</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ __("إعدادات من نحن") }}</li>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="gallery__area bg-style p-4">
                <form enctype="multipart/form-data" method="POST" action="{{ route("admin.about.page.site.content.update") }}">
                    @csrf

                    <!-- 1. Hero Section -->
                    <div class="card mb-4" style="border: 1px solid #e3e6f0; border-radius: 8px;">
                        <div class="card-header py-3" style="background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0;">
                            <h5 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-heading mr-2"></i> {{ __("1. قسم البانر الرئيسي") }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- English -->
                                <div class="col-md-6 border-right">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("المحتوى بالإنجليزية") }}</h6>
                                    <div class="input__group mb-3">
                                        <label>{{ __("العنوان الرئيسي") }}</label>
                                        <input type="text" class="form-control" name="about_hero_title_en" value="{{ $settings["about_hero_title_en"] ?? "Rooted in Quality, Delivered with Passion" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("العنوان الفرعي") }}</label>
                                        <textarea class="form-control" name="about_hero_desc_en" rows="3">{{ $settings["about_hero_desc_en"] ?? "Established in 2008, Khairat Al An`aam is one of Oman`s leading companies in sourcing, processing, and marketing premium quality red meat." }}</textarea>
                                    </div>
                                </div>
                                <!-- Arabic -->
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("المحتوى بالعربية") }}</h6>
                                    <div class="input__group mb-3">
                                        <label>{{ __("العنوان الرئيسي") }}</label>
                                        <input type="text" class="form-control" name="about_hero_title_ar" value="{{ $settings["about_hero_title_ar"] ?? "متأصلة في الجودة، مسلمة بالشغف" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("العنوان الفرعي") }}</label>
                                        <textarea class="form-control" name="about_hero_desc_ar" rows="3">{{ $settings["about_hero_desc_ar"] ?? "تأسست خيرات الأنعام في عام 2008، وتعد واحدة من أبرز الشركات في عمان في توريد ومعالجة وتسويق اللحوم الحمراء عالية الجودة." }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3 pt-3 border-top">
                                    <div class="input__group">
                                        <label class="font-weight-bold text-dark">{{ __("صورة البانر الرئيسي") }} <small class="text-danger" style="font-weight:normal;">(يفضل أن تكون صورة عرضية، لأن الأطراف سيتم قصها تلقائياً لتناسب الشاشات)</small></label>
                                        <input type="file" class="form-control" name="about_hero_image" accept="image/*">
                                        @if(isset($settings["about_hero_image"]) && $settings["about_hero_image"])
                                            <div class="mt-2">
                                                <img src="{{ asset(aboutUsPage() . $settings["about_hero_image"]) }}" alt="Banner" style="max-height: 100px; border-radius: 5px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Features Grid Section -->
                    <div class="card mb-4" style="border: 1px solid #e3e6f0; border-radius: 8px;">
                        <div class="card-header py-3" style="background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0;">
                            <h5 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-list mr-2"></i> {{ __("2. قسم المميزات الأربعة") }}</h5>
                        </div>
                        <div class="card-body">
                            <!-- Feature 1 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("الميزة الأولى") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature1_title_en" value="{{ $settings["about_feature1_title_en"] ?? "2-Hour Delivery" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature1_desc_en" value="{{ $settings["about_feature1_desc_en"] ?? "Fresh delivery in 2 hours" }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature1_title_ar" value="{{ $settings["about_feature1_title_ar"] ?? "توصيل خلال ساعتين" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature1_desc_ar" value="{{ $settings["about_feature1_desc_ar"] ?? "توصيل طازج خلال ساعتين" }}">
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">
                            
                            <!-- Feature 2 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("الميزة الثانية") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature2_title_en" value="{{ $settings["about_feature2_title_en"] ?? "100% Halal Certified" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature2_desc_en" value="{{ $settings["about_feature2_desc_en"] ?? "Certified and traceable" }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature2_title_ar" value="{{ $settings["about_feature2_title_ar"] ?? "حلال 100%" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature2_desc_ar" value="{{ $settings["about_feature2_desc_ar"] ?? "معتمد وقابل للتتبع" }}">
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">

                            <!-- Feature 3 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("الميزة الثالثة") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature3_title_en" value="{{ $settings["about_feature3_title_en"] ?? "Quality Guaranteed" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature3_desc_en" value="{{ $settings["about_feature3_desc_en"] ?? "Free returns and exchanges" }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature3_title_ar" value="{{ $settings["about_feature3_title_ar"] ?? "جودة مضمونة" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature3_desc_ar" value="{{ $settings["about_feature3_desc_ar"] ?? "إرجاع واستبدال مجاني" }}">
                                    </div>
                                </div>
                            </div>
                            <hr class="my-3">

                            <!-- Feature 4 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("الميزة الرابعة") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature4_title_en" value="{{ $settings["about_feature4_title_en"] ?? "Fresh Delivery" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالإنجليزية") }}</label>
                                        <input type="text" class="form-control" name="about_feature4_desc_en" value="{{ $settings["about_feature4_desc_en"] ?? "Temperature controlled" }}">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature4_title_ar" value="{{ $settings["about_feature4_title_ar"] ?? "توصيل طازج" }}">
                                    </div>
                                    <div class="input__group mb-2">
                                        <label>{{ __("العنوان الفرعي بالعربية") }}</label>
                                        <input type="text" class="form-control" name="about_feature4_desc_ar" value="{{ $settings["about_feature4_desc_ar"] ?? "نتحكم بدرجة حرارته" }}">
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 3. Middle Section (About details & lists) -->
                    <div class="card mb-4" style="border: 1px solid #e3e6f0; border-radius: 8px;">
                        <div class="card-header py-3" style="background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0;">
                            <h5 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-file-lines mr-2"></i> {{ __("3. قسم التفاصيل الأوسط") }}</h5>
                        </div>
                        <div class="card-body">
                            
                            <!-- Main Text -->
                            <div class="row mb-4">
                                <div class="col-md-6 border-right">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("المحتوى بالإنجليزية") }}</h6>
                                    <div class="input__group mb-3">
                                        <label>{{ __("عنوان القسم") }}</label>
                                        <input type="text" class="form-control" name="about_section_title_en" value="{{ $settings["about_section_title_en"] ?? "From the goodness of the earth and sea to your table" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("وصف القسم") }}</label>
                                        <textarea class="form-control" name="about_section_desc_en" rows="3">{{ $settings["about_section_desc_en"] ?? "We are committed to the highest international quality standards in selecting products. Our meat is sourced from the best farms that follow natural and sustainable breeding methods to ensure rich flavor and unparalleled quality." }}</textarea>
                                    </div>
                                </div>
                                <!-- Arabic -->
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("المحتوى بالعربية") }}</h6>
                                    <div class="input__group mb-3">
                                        <label>{{ __("عنوان القسم") }}</label>
                                        <input type="text" class="form-control" name="about_section_title_ar" value="{{ $settings["about_section_title_ar"] ?? "من خيرات الأرض والبحر لمائدتك" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("وصف القسم") }}</label>
                                        <textarea class="form-control" name="about_section_desc_ar" rows="3">{{ $settings["about_section_desc_ar"] ?? "نلتزم بأعلى معايير الجودة العالمية في اختيار المنتجات. يتم توريد لحومنا من أفضل المزارع التي تتبع أساليب التربية الطبيعية والمستدامة لضمان نكهة غنية وجودة لا تضاهى." }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-12 mt-3 pt-3 border-top">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("صور القسم الأوسط") }}</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="input__group">
                                                <label class="font-weight-bold text-dark">{{ __("الصورة الرئيسية الكبيرة") }}</label>
                                                <input type="file" class="form-control" name="about_middle_image_1" accept="image/*">
                                                @if(isset($settings["about_middle_image_1"]) && $settings["about_middle_image_1"])
                                                    <div class="mt-2">
                                                        <img src="{{ asset(aboutUsPage() . $settings["about_middle_image_1"]) }}" alt="Image 1" style="max-height: 100px; border-radius: 5px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="input__group">
                                                <label class="font-weight-bold text-dark">{{ __("الصورة الفرعية (الصغيرة)") }}</label>
                                                <input type="file" class="form-control" name="about_middle_image_2" accept="image/*">
                                                @if(isset($settings["about_middle_image_2"]) && $settings["about_middle_image_2"])
                                                    <div class="mt-2">
                                                        <img src="{{ asset(aboutUsPage() . $settings["about_middle_image_2"]) }}" alt="Image 2" style="max-height: 100px; border-radius: 5px;">
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <hr class="my-3">
                            <h6 class="font-weight-bold text-dark mb-3">{{ __("نقاط القائمة") }}</h6>

                            <!-- List Item 1 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("النقطة الأولى") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <input type="text" class="form-control mb-1" placeholder="العنوان (EN)" name="about_list1_title_en" value="{{ $settings["about_list1_title_en"] ?? "Reliable Halal Certification" }}">
                                        <textarea class="form-control" placeholder="الوصف (EN)" name="about_list1_desc_en" rows="2">{{ $settings["about_list1_desc_en"] ?? "All our products are slaughtered and prepared according to Islamic law and certified by reliable authorities." }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <input type="text" class="form-control mb-1" placeholder="العنوان (AR)" name="about_list1_title_ar" value="{{ $settings["about_list1_title_ar"] ?? "شهادة حلال موثوقة" }}">
                                        <textarea class="form-control" placeholder="الوصف (AR)" name="about_list1_desc_ar" rows="2">{{ $settings["about_list1_desc_ar"] ?? "جميع منتجاتنا مذبوحة ومجهزة وفقاً للشريعة الإسلامية ومعتمدة من جهات موثوقة." }}</textarea>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- List Item 2 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("النقطة الثانية") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <input type="text" class="form-control mb-1" placeholder="العنوان (EN)" name="about_list2_title_en" value="{{ $settings["about_list2_title_en"] ?? "Carefully Selected Farms" }}">
                                        <textarea class="form-control" placeholder="الوصف (EN)" name="about_list2_desc_en" rows="2">{{ $settings["about_list2_desc_en"] ?? "We ensure that our sources are from selected and reliable farms that follow natural farming and breeding methods free of hormones and pesticides." }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <input type="text" class="form-control mb-1" placeholder="العنوان (AR)" name="about_list2_title_ar" value="{{ $settings["about_list2_title_ar"] ?? "مزارع مختارة بعناية" }}">
                                        <textarea class="form-control" placeholder="الوصف (AR)" name="about_list2_desc_ar" rows="2">{{ $settings["about_list2_desc_ar"] ?? "نضمن أن مصادرنا من مزارع مختارة وموثوقة تتبع أساليب الزراعة والتربية الطبيعية الخالية من الهرمونات والمبيدات." }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- List Item 3 -->
                            <div class="row mb-3">
                                <div class="col-md-12"><h6 class="font-weight-bold text-secondary mb-2">{{ __("النقطة الثالثة") }}</h6></div>
                                <div class="col-md-6 border-right">
                                    <div class="input__group mb-2">
                                        <input type="text" class="form-control mb-1" placeholder="العنوان (EN)" name="about_list3_title_en" value="{{ $settings["about_list3_title_en"] ?? "Integrated Cold Chain" }}">
                                        <textarea class="form-control" placeholder="الوصف (EN)" name="about_list3_desc_en" rows="2">{{ $settings["about_list3_desc_en"] ?? "An advanced logistics system that maintains an ideal temperature from the moment of harvest and processing until it reaches your door." }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="input__group mb-2">
                                        <input type="text" class="form-control mb-1" placeholder="العنوان (AR)" name="about_list3_title_ar" value="{{ $settings["about_list3_title_ar"] ?? "سلسلة تبريد متكاملة" }}">
                                        <textarea class="form-control" placeholder="الوصف (AR)" name="about_list3_desc_ar" rows="2">{{ $settings["about_list3_desc_ar"] ?? "نظام لوجستي متطور يحافظ على درجة حرارة مثالية من لحظة الحصاد والتجهيز حتى وصولها لباب منزلك." }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- 4. CTA Section -->
                    <div class="card mb-4" style="border: 1px solid #e3e6f0; border-radius: 8px;">
                        <div class="card-header py-3" style="background-color: #f8f9fc; border-bottom: 1px solid #e3e6f0;">
                            <h5 class="m-0 font-weight-bold text-primary"><i class="fa-solid fa-bullhorn mr-2"></i> {{ __("4. قسم الدعوة لاتخاذ إجراء") }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- English -->
                                <div class="col-md-6 border-right">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("المحتوى بالإنجليزية") }}</h6>
                                    <div class="input__group mb-3">
                                        <label>{{ __("العنوان الصغير (الشارة)") }}</label>
                                        <input type="text" class="form-control" name="about_cta_subtitle_en" value="{{ $settings["about_cta_subtitle_en"] ?? "Why Choose Us" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("عنوان الدعوة") }}</label>
                                        <input type="text" class="form-control" name="about_cta_title_en" value="{{ $settings["about_cta_title_en"] ?? "Premium Meat Delivery" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("وصف الدعوة") }}</label>
                                        <textarea class="form-control" name="about_cta_desc_en" rows="3">{{ $settings["about_cta_desc_en"] ?? "We provide you with the finest types of meat carefully selected to meet your taste. Choose your favorite cuts and leave the rest to us." }}</textarea>
                                    </div>
                                </div>
                                <!-- Arabic -->
                                <div class="col-md-6">
                                    <h6 class="font-weight-bold text-secondary mb-3">{{ __("المحتوى بالعربية") }}</h6>
                                    <div class="input__group mb-3">
                                        <label>{{ __("العنوان الصغير (الشارة)") }}</label>
                                        <input type="text" class="form-control" name="about_cta_subtitle_ar" value="{{ $settings["about_cta_subtitle_ar"] ?? "لماذا نحن" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("عنوان الدعوة") }}</label>
                                        <input type="text" class="form-control" name="about_cta_title_ar" value="{{ $settings["about_cta_title_ar"] ?? "توصيل لحوم فاخرة" }}">
                                    </div>
                                    <div class="input__group mb-3">
                                        <label>{{ __("وصف الدعوة") }}</label>
                                        <textarea class="form-control" name="about_cta_desc_ar" rows="3">{{ $settings["about_cta_desc_ar"] ?? "نوفر لك أجود أنواع اللحوم المختارة بعناية لتلبي ذوقك. اختر ما يناسبك ودع الباقي علينا." }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="input__button text-center mt-4">
                        <button type="submit" class="btn btn-blue btn-lg px-5" style="border-radius: 8px; font-weight: bold;"><i class="fa-solid fa-save mr-2"></i>{{ __("حفظ التغييرات") }}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
