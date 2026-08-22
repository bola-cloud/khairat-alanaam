<?php

namespace App\Http\Controllers\Admin\SiteContent;

use App\Http\Controllers\Controller;
use App\Models\Admin\SiteContent\Homepage;
use App\Models\Admin\SiteContent\HomepageSection;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PhpParser\Node\Stmt\TryCatch;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\App as AppFacade;

class HomePageController extends Controller
{
    public function homePage(Request $request)
    {
        if ($request->ajax()) {
            // prefer homepage_sections if available
            $data = HomepageSection::whereNotIn('section_key', ['newdesign_brands', 'newdesign_sale_banner'])
                ->orderBy('display_order')
                ->get();
            if ($data->isEmpty()) {
                $data = Homepage::latest()->orderBy('id', 'DESC')->get();
            }
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = '<div class="action__buttons">';
                    $btn = $btn . '<a href="' . route('admin.home.page.site.content.edit', $data->id) . '" class="btn-action"><i class="fa-solid fa-pen-to-square"></i></a>';
                    $btn = $btn . '</div>';
                    return $btn;
                })
                ->editColumn('Location', function ($data) {
                    $loc = $data->Location ?? $data->section_key ?? '';
                    return  '<span class="status active">' . $loc . '</span>';
                })
                ->editColumn('Title', function ($data) {
                    $title = $data->en_Title ?? data_get($data, 'content_en.title', '');
                    return Str::limit($title, 15);
                })
                ->editColumn('Description_One', function ($data) {
                    $desc = $data->en_Description_One ?? data_get($data, 'content_en.lead', '');
                    return Str::limit($desc, 15);
                })
                ->editColumn('Description_Two', function ($data) {
                    $dtwo = $data->en_Description_Two ?? (is_array(data_get($data,'content_en.points')) ? implode('\n', data_get($data,'content_en.points')) : data_get($data,'content_en.points',''));
                    return Str::limit($dtwo, 15);
                })
                ->rawColumns(['action', 'Description_One', 'Description_Two', 'Title', 'Location'])
                ->make(true);
        }
        $data['title'] = __('Home Page Content');
        return view('admin.pages.site_content.home_page.index', $data);
    }
    public function homePageEdit($id)
    {
        $data['title'] = __('Edit Home Page Content');
        $section = HomepageSection::find($id);
        // pass all sections so the edit page can render each section as a separate card
        try {
            $data['sections'] = HomepageSection::whereIn('section_key', ['newdesign_features', 'newdesign_farm'])
                ->orderBy('display_order')
                ->get();
        } catch (\Exception $e) {
            $data['sections'] = collect();
        }
        if ($section) {
            $data['edit'] = $section;
            $data['is_section'] = true;
        } else {
            $data['edit'] = Homepage::where('id', $id)->first();
            $data['is_section'] = false;
        }
        // Legacy mapping: when the UI display locale is Arabic we keep translations in the
        // `fr` files for backward compatibility. Set the translator locale to `fr`
        // so Blade `__()` calls use `resources/lang/fr.json` for this view.
        $displayLocale = session('HTML_LANG', app()->getLocale() ?? 'en');
        if ($displayLocale === 'ar') {
            AppFacade::setLocale('fr');
        }
        return view('admin.pages.site_content.home_page.edit', $data);
    }
    public function homePageUpdate(Request $request)
    {
        $id = $request->id;
        $section = HomepageSection::find($id);
        if ($section) {
            // update section
            if ($request->hasFile('image')) {
                $image = fileUpload($request['image'], PromotionImage());
                $section->image = $image;
            }

            // Special handling for features section (edit four features only)
            if ($section->section_key === 'newdesign_features') {
                $items_en = [];
                $items_fr = [];
                for ($i = 1; $i <= 4; $i++) {
                    $items_en[] = [
                        'title' => $request->input('en_feature_' . $i . '_title', ''),
                        'desc' => $request->input('en_feature_' . $i . '_desc', ''),
                        'icon' => $request->input('en_feature_' . $i . '_icon', ''),
                    ];
                    $items_fr[] = [
                        'title' => $request->input('fr_feature_' . $i . '_title', ''),
                        'desc' => $request->input('fr_feature_' . $i . '_desc', ''),
                        'icon' => $request->input('fr_feature_' . $i . '_icon', ''),
                    ];
                }
                $section->content_en = ['items' => $items_en];
                $section->content_fr = ['items' => $items_fr];
                $ok = $section->save();
                if ($ok) {
                    return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('success', __('Successfully Updated !'));
                }
                return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('error', __('Does not Updated !'));
            }

            // Special handling for farm section (title, lead, 3 features)
            if ($section->section_key === 'newdesign_farm') {
                $items_en = [];
                $items_fr = [];
                for ($i = 1; $i <= 3; $i++) {
                    $items_en[] = [
                        'title' => $request->input('en_farm_feat_' . $i . '_title', ''),
                        'desc' => $request->input('en_farm_feat_' . $i . '_desc', ''),
                        'icon' => $request->input('en_farm_feat_' . $i . '_icon', ''),
                    ];
                    $items_fr[] = [
                        'title' => $request->input('fr_farm_feat_' . $i . '_title', ''),
                        'desc' => $request->input('fr_farm_feat_' . $i . '_desc', ''),
                        'icon' => $request->input('fr_farm_feat_' . $i . '_icon', ''),
                    ];
                }
                
                $content_en_data = [
                    'title' => $request->en_title,
                    'lead' => $request->en_description_one,
                    'items' => $items_en
                ];
                $content_fr_data = [
                    'title' => $request->fr_title,
                    'lead' => $request->fr_description_one,
                    'items' => $items_fr
                ];

                if ($request->hasFile('image2')) {
                    $image2 = fileUpload($request['image2'], PromotionImage());
                    $content_en_data['image2'] = $image2;
                    $content_fr_data['image2'] = $image2;
                } else {
                    $old_en = is_array($section->content_en) ? $section->content_en : json_decode($section->content_en, true);
                    if (isset($old_en['image2'])) {
                        $content_en_data['image2'] = $old_en['image2'];
                        $content_fr_data['image2'] = $old_en['image2'];
                    }
                }

                $section->content_en = $content_en_data;
                $section->content_fr = $content_fr_data;
                
                $ok = $section->save();
                if ($ok) {
                    return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('success', __('Successfully Updated !'));
                }
                return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('error', __('Does not Updated !'));
            }

            // Special handling for stats section (edit four stats only)
            if ($section->section_key === 'newdesign_stats') {
                $stats_en = [];
                $stats_fr = [];
                for ($i = 1; $i <= 4; $i++) {
                    $stats_en[] = [
                        'val' => $request->input('en_stat_' . $i . '_val', ''),
                        'lbl' => $request->input('en_stat_' . $i . '_lbl', ''),
                    ];
                    $stats_fr[] = [
                        'val' => $request->input('fr_stat_' . $i . '_val', ''),
                        'lbl' => $request->input('fr_stat_' . $i . '_lbl', ''),
                    ];
                }
                $section->content_en = [
                    'title' => $request->en_title,
                    'lead' => $request->en_description_one,
                    'stats' => $stats_en
                ];
                $section->content_fr = [
                    'title' => $request->fr_title,
                    'lead' => $request->fr_description_one,
                    'stats' => $stats_fr
                ];
                $ok = $section->save();
                if ($ok) {
                    return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('success', __('Successfully Updated !'));
                }
                return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('error', __('Does not Updated !'));
            }

            // General section handling (title/lead/points/button)
            $content_en = [
                'title' => $request->en_title,
                'lead' => $request->en_description_one,
                'points' => preg_split('/\r?\n/', trim($request->en_description_two ?? '')),
                'button' => ['text' => $request->en_button_text, 'url' => $request->en_button_url]
            ];
            $content_fr = [
                'title' => $request->fr_title,
                'lead' => $request->fr_description_one,
                'points' => preg_split('/\r?\n/', trim($request->fr_description_two ?? '')),
                'button' => ['text' => $request->fr_button_text, 'url' => $request->fr_button_url]
            ];
            $section->content_en = $content_en;
            $section->content_fr = $content_fr;
            $ok = $section->save();
            if ($ok) {
                return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('success', __('Successfully Updated !'));
            }
            return redirect()->route('admin.home.page.site.content.edit', $section->id)->with('error', __('Does not Updated !'));
        }
        $homepage = Homepage::where('id', $id)->first();
        if ($request->hasFile('image')) {
            // for about_us we use aboutUsPage(), for new-design hero prefer PromotionImage()
            if ($request->location == 'about_us') {
                $image = fileUpload($request['image'], aboutUsPage());
            } else {
                $image = fileUpload($request['image'], PromotionImage());
            }
            $homepage->image = $image;
            $homepage->save();
        }
        $general = $homepage->update([
            'en_Title' => $request->en_title,
            'en_Description_One' => $request->en_description_one,
            'en_Description_Two' => $request->en_description_two,
            'fr_Title' => $request->fr_title,
            'fr_Description_One' => $request->fr_description_one,
            'fr_Description_Two' => $request->fr_description_two,
            // optional CTA/button fields
            'en_button_text' => $request->en_button_text,
            'en_button_url' => $request->en_button_url,
            'fr_button_text' => $request->fr_button_text,
            'fr_button_url' => $request->fr_button_url,
            // features fields (if present)
            'en_feature_1_title' => $request->en_feature_1_title,
            'en_feature_1_desc' => $request->en_feature_1_desc,
            'en_feature_2_title' => $request->en_feature_2_title,
            'en_feature_2_desc' => $request->en_feature_2_desc,
            'en_feature_3_title' => $request->en_feature_3_title,
            'en_feature_3_desc' => $request->en_feature_3_desc,
            'en_feature_4_title' => $request->en_feature_4_title,
            'en_feature_4_desc' => $request->en_feature_4_desc,
            'fr_feature_1_title' => $request->fr_feature_1_title,
            'fr_feature_1_desc' => $request->fr_feature_1_desc,
            'fr_feature_2_title' => $request->fr_feature_2_title,
            'fr_feature_2_desc' => $request->fr_feature_2_desc,
            'fr_feature_3_title' => $request->fr_feature_3_title,
            'fr_feature_3_desc' => $request->fr_feature_3_desc,
            'fr_feature_4_title' => $request->fr_feature_4_title,
            'fr_feature_4_desc' => $request->fr_feature_4_desc,
        ]);
        if ($general) {
            // (['image'=>'ddddd']);



            if ($request->location == 'products') {
                Setting::where('slug', 'home_products_page')->update(['value' => $request->home_products_page]);
            }
            if ($request->location == 'popular_products') {
                $new_arrival = checkBoxValue($request->new_arrival);
                $best_selling = checkBoxValue($request->best_selling);
                $on_sale = checkBoxValue($request->on_sale);
                $featured_items = checkBoxValue($request->featured_items);
                Setting::where('slug', 'home_trending_page')->update(['value' => $request->home_trending_page]);
                Setting::where('slug', 'new_arrival')->update(['value' => $new_arrival]);
                Setting::where('slug', 'best_selling')->update(['value' => $best_selling]);
                Setting::where('slug', 'on_sale')->update(['value' => $on_sale]);
                Setting::where('slug', 'featured_items')->update(['value' => $featured_items]);
            }
            return redirect()->route('admin.home.page.site.content')->with('success', __('Successfully Updated !'));
        }
        return redirect()->route('admin.home.page.site.content')->with('success', __('Does not Updated !'));
    }
}
