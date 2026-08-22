<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdvertiseRequest;
use App\Models\Admin\Advertise;
use App\Models\Admin\SiteContent\HomepageSection;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class AdvertiseController extends Controller
{
    public function advertise(Request $request)
    {

        if ($request->ajax()) {
            $data = Advertise::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = '<div class="action__buttons">';
                    $btn .= '<a href="' . route('admin.advertise.edit', $data->id) . '" class="btn-action" title="' . __('Edit') . '"><i class="fa-solid fa-pen-to-square"></i></a>';
                    $btn .= '<a href="' . route('admin.advertise.delete', $data->id) . '" class="btn-action delete" title="' . __('Delete') . '"><i class="fas fa-trash-alt"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('image', function ($data) {
                    if (empty($data->image)) return 'N/A';
                    $publicPath = public_path($data->image);
                    if (file_exists($publicPath)) {
                        $url = asset($data->image);
                    } else {
                        $url = asset(PromotionImage() . $data->image);
                    }
                    return '<img src=' . $url . ' border="0" width="80" class="img-rounded" align="center" />';
                })
                ->addColumn('title', function ($data) {
                    $htmlLocale = session('HTML_LANG', session('APP_LOCALE', app()->getLocale() ?? 'en'));
                    if ($htmlLocale === 'ar') {
                        return $data->ar_title ?? $data->fr_title ?? $data->en_title ?? 'N/A';
                    }
                    return $data->en_title ?? $data->ar_title ?? $data->fr_title ?? 'N/A';
                })
                ->addColumn('subtitle', function ($data) {
                    $htmlLocale = session('HTML_LANG', session('APP_LOCALE', app()->getLocale() ?? 'en'));
                    if ($htmlLocale === 'ar') {
                        return $data->ar_subtitle ?? $data->fr_subtitle ?? $data->en_subtitle ?? 'N/A';
                    }
                    return $data->en_subtitle ?? $data->ar_subtitle ?? $data->fr_subtitle ?? 'N/A';
                })
                ->rawColumns(['action', 'image'])
                ->make(true);
        }
        $data['title'] = __('Advertise List');
        return view('admin.pages.advertise.index', $data);
    }

    public function advertiseCreate()
    {
        $data['title'] = __('Advertise Create');
        return view('admin.pages.advertise.create', $data);
    }

    public function advertiseStore(AdvertiseRequest $request)
    {
        // handle legacy Image_One/Image_Two
        $image_one = null; $image_two = null; $image = null;
        if (!empty($request->image_one)) {
            $image_one = fileUpload($request['image_one'], PromotionImage());
        }
        if (!empty($request->image_two)) {
            $image_two = fileUpload($request['image_two'], PromotionImage());
        }
        // handle hero/general single image
        if (!empty($request->image)) {
            $image = fileUpload($request['image'], PromotionImage());
        }

        // ensure display_order uniqueness: shift existing records at or after this order
        $location = $request->location ?? 'hero';
        $newOrder = intval($request->display_order ?? 0);
        Advertise::where('location', $location)->where('display_order', '>=', $newOrder)->increment('display_order');

        $payload = [
            'Image_One' => $image_one,
            'Link_One' => $request->link_one ?? null,
            'Image_Two' => $image_two,
            'image' => $image, // do not accept manual image_path; store uploaded image only
            'en_title' => $request->en_title ?? null,
            'en_subtitle' => $request->en_subtitle ?? null,
            'en_badge' => $request->en_badge ?? null,
            'en_small_description' => $request->en_small_description ?? null,
            'ar_title' => $request->ar_title ?? null,
            'ar_subtitle' => $request->ar_subtitle ?? null,
            'ar_badge' => $request->ar_badge ?? null,
            'ar_small_description' => $request->ar_small_description ?? null,
            'link' => $request->link ?? $request->link_one ?? null,
            'display_order' => $request->display_order ?? 0,
            'status' => $request->has('status') ? 1 : 0,
            'location' => $request->location ?? 'hero'
        ];

        $slider = Advertise::create($payload);
        if ($slider) {
            return redirect()->route('admin.advertise')->with('success', __('Successfully Stored !'));
        }
        return redirect()->back()->with('error', __('Does not insert  !'));
    }

    /**
    /**
     * AJAX bulk upload endpoint for multiple brand images.
     * Accepts files in `images[]` and creates Advertise rows with location=company_logo.
     * Returns JSON { success: true, created: [...] } or JSON error.
     */
    public function advertiseBulkStore(Request $request)
    {
        try {
            \Log::info('advertiseBulkStore called', ['headers' => $request->headers->all(), 'content_length' => $request->server('CONTENT_LENGTH')]);
            $files = $request->file('images');
            if (empty($files) || !is_array($files)) {
                return response()->json(['success' => false, 'message' => 'No files provided'], 422);
            }

            // validate files explicitly and return readable errors
            $rules = [];
            foreach ($files as $i => $f) {
                $rules["images.$i"] = 'required|image|max:5120'; // max 5MB per file
            }
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                \Log::warning('advertiseBulkStore validation failed', ['errors' => $validator->errors()->toArray()]);
                return response()->json(['success' => false, 'errors' => $validator->errors()->all()], 422);
            }

            $created = [];
            $sectionKey = $request->input('section_key', 'newdesign_brands');
            // use HomepageSection to store brand images as part of the home page CMS
            $section = HomepageSection::firstOrCreate(
                ['section_key' => $sectionKey],
                ['content_en' => [], 'content_fr' => [], 'display_order' => 0, 'status' => 1]
            );
            foreach ($files as $file) {
                if (!$file->isValid()) continue;
                $imgName = fileUpload($file, PromotionImage());
                if (!$imgName) continue;

                // Append to content_en.images array (create if missing)
                $content = $section->content_en ?? [];
                if (!isset($content['images']) || !is_array($content['images'])) {
                    $content['images'] = [];
                }
                $content['images'][] = $imgName;
                $section->content_en = $content;
                $section->save();

                $created[] = [
                    'image' => $imgName,
                    'url' => file_exists(public_path($imgName)) ? asset($imgName) : asset(PromotionImage() . $imgName),
                ];
            }
            return response()->json(['success' => true, 'created' => $created]);
        } catch (\Exception $e) {
            \Log::error('Advertise bulk upload failed: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['success' => false, 'message' => 'Server error', 'detail' => $e->getMessage()], 500);
        }
    }

    public function deleteSectionImage(Request $request)
    {
        try {
            $sectionKey = $request->input('section_key');
            $imagePath = $request->input('image');
            if (empty($sectionKey) || empty($imagePath)) {
                return response()->json(['success' => false, 'message' => 'Invalid parameters'], 422);
            }
            $section = HomepageSection::where('section_key', $sectionKey)->first();
            if ($section) {
                $content = $section->content_en ?? [];
                if (isset($content['images']) && is_array($content['images'])) {
                    // Remove the image from the array
                    $content['images'] = array_values(array_filter($content['images'], function($img) use ($imagePath) {
                        return $img !== $imagePath;
                    }));
                    $section->content_en = $content;
                    $section->save();
                    
                    // Also delete the file from disk if it exists
                    $fullPath = public_path($imagePath);
                    if (file_exists($fullPath)) {
                        @unlink($fullPath);
                    }
                    return response()->json(['success' => true]);
                }
            }
            return response()->json(['success' => false, 'message' => 'Section or image not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function advertiseEdit($id)
    {
        $data['title'] = __('Advertise Edit');
        $data['edit'] = Advertise::where('id', $id)->first();
        return view('admin.pages.advertise.edit', $data);
    }

    public function advertiseUpdate(Request $request)
    {
        $id = $request->id;
        $record = Advertise::where('id', $id)->first();
        if (!$record) return redirect()->back()->with('error', __('Advertise not found'));

        if (!empty($request->image_one)) {
            $image_one = fileUpload($request['image_one'], PromotionImage());
        } else {
            $image_one = $record->Image_One;
        }
        if (!empty($request->image_two)) {
            $image_two = fileUpload($request['image_two'], PromotionImage());
        } else {
            $image_two = $record->Image_Two;
        }
        if (!empty($request->image)) {
            $image = fileUpload($request['image'], PromotionImage());
        } else {
            $image = $record->image;
        }

        // handle display_order reordering to avoid duplicates
        $location = $request->location ?? $record->location ?? 'hero';
        $oldOrder = intval($record->display_order ?? 0);
        $newOrder = intval($request->display_order ?? $oldOrder);
        if ($newOrder !== $oldOrder) {
            if ($newOrder < $oldOrder) {
                // shift up: increment orders in [newOrder, oldOrder-1]
                Advertise::where('location', $location)
                    ->where('display_order', '>=', $newOrder)
                    ->where('display_order', '<', $oldOrder)
                    ->increment('display_order');
            } else {
                // shift down: decrement orders in [oldOrder+1, newOrder]
                Advertise::where('location', $location)
                    ->where('display_order', '<=', $newOrder)
                    ->where('display_order', '>', $oldOrder)
                    ->decrement('display_order');
            }
        }

        $update = Advertise::find($id)->update([
            'Image_One' => $image_one,
            'Link_One' => $request->link_one ?? $record->Link_One,
            'Image_Two' => $image_two,
            'image' => $image,
            'en_title' => $request->en_title ?? $record->en_title,
            'en_subtitle' => $request->en_subtitle ?? $record->en_subtitle,
            'en_badge' => $request->en_badge ?? $record->en_badge,
            'en_small_description' => $request->en_small_description ?? $record->en_small_description,
            'ar_title' => $request->ar_title ?? $record->ar_title,
            'ar_subtitle' => $request->ar_subtitle ?? $record->ar_subtitle,
            'ar_badge' => $request->ar_badge ?? $record->ar_badge,
            'ar_small_description' => $request->ar_small_description ?? $record->ar_small_description,
            'link' => $request->link ?? $record->link,
            'display_order' => $request->display_order ?? $record->display_order,
            'status' => $request->has('status') ? 1 : ($record->status ?? 0),
            'location' => $request->location ?? $record->location ?? 'hero'
        ]);
        if ($update) {
            return redirect()->route('admin.advertise')->with('success', __('Successfully Updated !'));
        }
        return redirect()->back()->with('error', __('Does not Update  !'));
    }

    public function advertiseDelete($id)
    {
        $delete = Advertise::Where('id', $id);
        if ($delete) {
            $delete->delete();
            return redirect()->route('admin.advertise')->with('success', __('Successfully Deleted !'));
        }
        return redirect()->route('admin.advertise')->with('error', __('Does Not Delete!'));
    }
}
