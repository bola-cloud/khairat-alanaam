<?php

namespace App\Http\Controllers\Admin\SiteContent;

use App\Http\Controllers\Controller;
use App\Models\Admin\SiteContent\SocialLink;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SocialLinkController extends Controller
{
    public function socialLink(Request $request)
    {
        $social = SocialLink::first();
        if (!$social) {
            $social = SocialLink::create([
                'Facebook' => '#',
                'Twitter' => '#',
                'Youtube' => '#',
                'Instagram' => '#',
            ]);
        }
        $data['title'] = __('Edit Social Link');
        $data['edit'] = $social;
        return view('admin.pages.site_content.social_link.edit', $data);
    }

    public function socialLinkEdit($id)
    {
        return redirect()->route('admin.social.link');
    }

    public function socialLinkUpdate(Request $request)
    {
        $id = $request->id;
        $update = SocialLink::where('id', $id)->update([
            'Facebook' => $request->facebook,
            'Twitter' => $request->twitter,
            'Youtube' => $request->youtube,
            'Instagram' => $request->instagram,
        ]);
        if ($update) {
            return redirect()->route('admin.social.link')->with('success', __('Successfully Update !'));
        }
        return redirect()->back()->with('error', __('Does not Update  !'));
    }
}
