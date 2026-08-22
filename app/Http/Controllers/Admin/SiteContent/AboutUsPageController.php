<?php

namespace App\Http\Controllers\Admin\SiteContent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AboutUsPageController extends Controller
{
    public function aboutPage(Request $request)
    {
        $data["title"] = __("Edit About Page Content");
        $data["settings"] = \App\Models\Setting::pluck("value", "slug")->toArray();
        return view("admin.pages.site_content.about_us.edit", $data);
    }
    
    public function aboutPageEdit($id)
    {
        return redirect()->route("admin.about.page.site.content");
    }
    
    public function aboutPageUpdate(Request $request)
    {
        $data = $request->except(["_token", "id"]);
        
        if ($request->hasFile('about_hero_image')) {
            $data['about_hero_image'] = fileUpload($request['about_hero_image'], aboutUsPage());
        }
        if ($request->hasFile('about_middle_image_1')) {
            $data['about_middle_image_1'] = fileUpload($request['about_middle_image_1'], aboutUsPage());
        }
        if ($request->hasFile('about_middle_image_2')) {
            $data['about_middle_image_2'] = fileUpload($request['about_middle_image_2'], aboutUsPage());
        }
        
        foreach ($data as $key => $value) {
            \App\Models\Setting::updateOrCreate(
                ["slug" => $key],
                ["value" => $value ?? ""]
            );
        }

        return redirect()->route("admin.about.page.site.content")->with("success", __("Successfully Updated !"));
    }
}

