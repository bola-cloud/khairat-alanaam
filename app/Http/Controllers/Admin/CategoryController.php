<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\CategoryRequest;
use App\Models\Admin\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function category(Request $request)
    {
        if ($request->ajax()) {
            $data = Category::latest()->get();
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function ($data) {
                    $btn = '<div class="action__buttons" style="display: flex; gap: 8px; justify-content: start;">';

                    // Logic to encourage completing translation if FR name is missing or same as EN
                    $editTitle = (!$data->fr_Category_Name || $data->fr_Category_Name == $data->en_Category_Name) ? __('Complete Data') : __('Edit');
                    $editIcon = (!$data->fr_Category_Name || $data->fr_Category_Name == $data->en_Category_Name) ? 'fa-file-signature' : 'fa-pen-to-square';
                    $btnStyle = 'font-size: 1.1rem; padding: 6px 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05);';

                    $btn = $btn . '<a href="' . route('admin.category.edit', $data->id) . '" class="btn-action" title="' . $editTitle . '" style="' . $btnStyle . '"><i class="fas ' . $editIcon . '"></i></a>';

                    if ($data->Status == 1) {
                        $btn = $btn . '<a href="' . route('admin.category.inactive', $data->id) . '" class="btn-action" style="' . $btnStyle . '"><i class="fas fa-toggle-on text-success"></i></a>';
                    } else {
                        $btn = $btn . '<a href="' . route('admin.category.active', $data->id) . '" class="btn-action" style="' . $btnStyle . '"><i class="fas fa-toggle-off text-secondary"></i></a>';
                    }

                    $btn = $btn . '<a href="' . route('admin.category.delete', $data->id) . '" class="btn-action delete" title="Delete" style="' . $btnStyle . '"><i class="fas fa-trash-alt"></i></a>';
                    $btn = $btn . '</div>';
                    return $btn;
                })
                ->editColumn('Category_Name', function ($data) {
                    return $data->fr_Category_Name;
                })
                // ->editColumn('Category_Slug', function ($data) {
                //     return $data->en_Category_Slug;
                // })
                ->editColumn('Status', function ($data) {
                    if ($data->Status == 1) {
                        return '<span class="badge badge-pill" style="background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-check-circle mr-1"></i>' . __('Active') . '</span>';
                    } else {
                        return '<span class="badge badge-pill" style="background: linear-gradient(135deg, #ee0979 0%, #ff6a00 100%); color: white; padding: 0.5rem 1rem; font-size: 0.85rem; font-weight: 600; white-space: nowrap; border-radius: 50rem;"><i class="fas fa-times-circle mr-1"></i>' . __('Inactive') . '</span>';
                    }
                })
                ->editColumn('Description', function ($data) {
                    return Str::limit($data->fr_Description, 10);
                })
                ->editColumn('Category_Icon', function ($data) {
                    return '<img src=' . asset(CategoryImage() . $data->Category_Icon) . ' width="50" height="50" alt="Category Icon" onerror="this.style.display=\'none\'" />';
                })
                ->editColumn('show_on_home', function ($data) {
                    if ($data->show_on_home == 1) {
                        return '<span class="badge badge-pill badge-primary">' . __('Yes') . '</span>';
                    } else {
                        return '<span class="badge badge-pill badge-secondary">' . __('No') . '</span>';
                    }
                })
                ->addColumn('order', function ($data) {
                    return $data->order;
                })
                ->rawColumns(['action', 'Category_Name', 'Category_Slug', 'Status', 'Description', 'Category_Icon', 'show_on_home', 'order'])
                ->make(true);
        }
        $data['title'] = __('Category List');
        return view('admin.pages.category.index', $data);
    }

    public function categoryCreate()
    {
        $data['title'] = __('Category Create');
        return view('admin.pages.category.create', $data);
    }

    public function categoryStore(CategoryRequest $request)
    {

        $icon_name = null;

        if ($request->icon) {
            // upload image
            $icon_name = fileUpload($request['icon'], CategoryImage());
        }


        $category = Category::create([
            'en_Category_Name' => $request->en_category_name,
            'en_Description' => $request->en_description,
            'en_Category_Slug' => $this->slugify($request->en_category_name),
            'fr_Category_Name' => $request->fr_category_name,
            'fr_Description' => $request->fr_description,
            'fr_Category_Slug' => $this->slugify($request->fr_category_name),
            'Category_Icon' => $icon_name,
            'order' => $request->order,
            'show_on_home' => $request->show_on_home ? 1 : 0,
            'is_occasion' => $request->is_occasion ? 1 : 0,
            'is_cut' => $request->is_cut ? 1 : 0
        ]);
        if ($category) {
            return redirect()->route('admin.category')->with('success', __('Successfully Stored !'));
        }
        return redirect()->route('admin.category')->with('error', __('Does not Stored !'));
    }
    public function categoryEdit($id)
    {
        $data['title'] = __('Category Create');
        $data['edit'] = Category::where('id', $id)->first();
        return view('admin.pages.category.edit', $data);
    }
    public function categoryUpdate(Request $request)
    {
        $id = $request->id;
        $cat = Category::whereid($id)->first();


        if ($request->icon) {
            // delete image
            if ($cat->Category_Icon) {
                $oldIcon = $cat->Category_Icon;
                if ($request->icon && $oldIcon) {
                    Storage::delete(CategoryImage() . $oldIcon);
                }

            }
            // upload image
            $icon_name = fileUpload($request['icon'], CategoryImage());
        } else {
            $icon_name = $cat->Category_Icon;
        }


        $update = $cat->update([
            'en_Category_Name' => is_null($request->en_category_name) ? $cat->en_Category_Name : $request->en_category_name,
            'en_Description' => is_null($request->en_description) ? $cat->en_Description : $request->en_description,
            'en_Category_Slug' => is_null($request->en_category_name) ? $cat->en_Category_Slug : $this->slugify($request->en_category_name),
            'fr_Category_Name' => is_null($request->fr_category_name) ? $cat->fr_Category_Name : $request->fr_category_name,
            'fr_Description' => is_null($request->fr_description) ? $cat->fr_Description : $request->fr_description,
            'fr_Category_Slug' => is_null($request->fr_category_name) ? $cat->fr_Category_Slug : $this->slugify($request->fr_category_name),
            'Category_Icon' => $icon_name,
            'order' => $request->order,
            'show_on_home' => $request->show_on_home ? 1 : 0,
            'is_occasion' => $request->is_occasion ? 1 : 0,
            'is_cut' => $request->is_cut ? 1 : 0
        ]);
        if ($update) {
            return redirect()->route('admin.category')->with('success', __('Successfully Updated!'));
        }
        return redirect()->back()->with('error', __('Does not Update  !'));
    }
    public function categoryActive($id)
    {
        $inactive = Category::find($id)->update(['Status' => 1]);
        if ($inactive) {
            return redirect()->route('admin.category')->with('success', __('Successfully Active !'));
        }
        return redirect()->route('admin.category')->with('success', __('Does not Updated!'));
    }
    public function categoryInactive($id)
    {
        $inactive = Category::find($id)->update(['Status' => 0]);
        if ($inactive) {
            return redirect()->route('admin.category')->with('success', __('Successfully Inactive !'));
        }
        return redirect()->route('admin.category')->with('success', __('Does not Updated !'));
    }

    public function categoryDelete($id)
    {
        $delete = Category::Where('id', $id)->delete();
        if ($delete) {
            return redirect()->route('admin.category')->with('success', __('Successfully Deleted !'));
        }
        return redirect()->route('admin.category')->with('error', __('Does Not Delete!'));
    }

    public function slugify($text)
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, '-');

        // remove duplicate divider
        $text = preg_replace('~-+~', '-', $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
            return 'n-a';
        }
        return $text;
    }
}
