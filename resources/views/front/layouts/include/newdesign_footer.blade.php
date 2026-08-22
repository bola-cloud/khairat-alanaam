@php
    $isRtl = app()->getLocale() != 'en';
    $dir = $isRtl ? 'rtl' : 'ltr';
    $socialLinks = getSocialLink();
@endphp

<footer class="relative overflow-hidden text-white py-16 mt-0" dir="{{ $dir }}" style="background-image: url('{{ asset('assets/elketar/fotter_image.png') }}'); background-size: cover; background-position: center; background-color: #1A4231; background-blend-mode: multiply;">
    <!-- Absolute dark green semi-transparent overlay to ensure stunning Figma contrast -->
    <div class="absolute inset-0 z-0"></div>

    <div class="container mx-auto px-4 relative z-10">
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12 lg:gap-16 items-start text-start">
            
            <!-- Column 1: Logo -->
            <div class="flex flex-col items-center md:items-start justify-center md:justify-start">
                <a href="{{ route('front') }}" class="block mb-6">
                    <img src="{{ isset($allsettings['footer_logo']) ? asset(IMG_LOGO_PATH . $allsettings['footer_logo']) : asset('assets/elketar/logo-footer.png') }}" class="h-24 lg:h-28 w-auto object-contain" alt="El Katar Logo">
                </a>
            </div>

            <!-- Column 2: Explore -->
            <div>
                <h4 class="text-lg lg:text-xl font-bold text-white mb-6">{{ __('new_design.footer.explore') }}</h4>
                <ul class="space-y-4 font-semibold text-white/80">
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.about') }}</a></li>
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.farms') }}</a></li>
                    <li><a href="{{ route('about.us') }}" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.about_us') }}</a></li>
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.methods') }}</a></li>
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.tools') }}</a></li>
                </ul>
            </div>

            <!-- Column 3: Support -->
            <div>
                <h4 class="text-lg lg:text-xl font-bold text-white mb-6">{{ __('new_design.footer.support') }}</h4>
                <ul class="space-y-4 font-semibold text-white/80">
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.faq') }}</a></li>
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.shipping') }}</a></li>
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.track') }}</a></li>
                    <li><a href="#" class="hover:text-white transition-colors text-sm lg:text-base">{{ __('new_design.footer.contact') }}</a></li>
                </ul>
            </div>

            <!-- Column 4: Follow Us -->
            <div>
                <h4 class="text-lg lg:text-xl font-bold text-white mb-6">{{ __('new_design.footer.follow') }}</h4>
                <div class="flex gap-6 items-center">
                    @if($socialLinks)
                        @if($socialLinks->Youtube)
                            <!-- Youtube -->
                            <a href="{{ format_social_url($socialLinks->Youtube) }}" target="_blank" class="text-white hover:text-[#FBF0D8] transition-colors">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M21.582 6.186a2.63 2.63 0 0 0-1.85-1.864C18.102 3.88 12 3.88 12 3.88s-6.102 0-7.732.442a2.63 2.63 0 0 0-1.85 1.864C2 7.828 2 12 2 12s0 4.172.418 5.814a2.63 2.63 0 0 0 1.85 1.864C5.898 20.12 12 20.12 12 20.12s6.102 0 7.732-.442a2.63 2.63 0 0 0 1.85-1.864C22 16.172 22 12 22 12s0-4.172-.418-5.814zM9.75 15.02v-6.04l5.58 3.02-5.58 3.02z"/>
                                </svg>
                            </a>
                        @endif
                        @if($socialLinks->Twitter)
                            <!-- Twitter -->
                            <a href="{{ format_social_url($socialLinks->Twitter) }}" target="_blank" class="text-white hover:text-[#FBF0D8] transition-colors">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
                                </svg>
                            </a>
                        @endif
                        @if($socialLinks->Instagram)
                            <!-- Instagram -->
                            <a href="{{ format_social_url($socialLinks->Instagram) }}" target="_blank" class="text-white hover:text-[#FBF0D8] transition-colors">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>
                                </svg>
                            </a>
                        @endif
                        @if($socialLinks->Facebook)
                            <!-- Facebook (with white circular background) -->
                            <a href="{{ format_social_url($socialLinks->Facebook) }}" target="_blank" class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-[#1A4231] hover:bg-[#FBF0D8] transition-colors shadow-md">
                                <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24">
                                    <path d="M9 8h-3v4h3v12h5v-12h3.642l.358-4h-4v-1.667c0-.955.192-1.333 1.115-1.333h2.885v-5h-3.808c-3.596 0-5.192 1.583-5.192 4.615v3.385z"/>
                                </svg>
                            </a>
                        @endif
                    @endif
                </div>
            </div>

        </div>

        <!-- Divider Line and Copyright -->
        <div class="border-t border-white/20 mt-16 pt-8 text-center relative z-10">
            <p class="text-white/60 text-xs lg:text-sm font-semibold">
                {{ $allsettings['footer_title'] ?? __('new_design.footer.copyright') }}
            </p>
        </div>

    </div>
</footer>
