<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\Recipe;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class RecipeController extends Controller
{
    public function index()
    {
        $data['title'] = __('Recipes');
        $data['recipes'] = Recipe::orderBy('id', 'desc')->get();
        return view('admin.pages.recipe.index', $data);
    }

    public function create()
    {
        $data['title'] = __('Create Recipe');
        return view('admin.pages.recipe.create', $data);
    }

    public function store(Request $request)
    {
        $request->validate([
            'en_title' => 'required|string|max:255',
            'fr_title' => 'required|string|max:255',
            'en_description' => 'required|string',
            'fr_description' => 'required|string',
            'time_to_cook' => 'required|integer|min:1',
            'difficulty' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = fileUpload($request->file('image'), 'recipes');
        }

        Recipe::create([
            'en_title' => $request->en_title,
            'fr_title' => $request->fr_title,
            'slug' => Str::slug($request->en_title) . '-' . time(),
            'en_description' => $request->en_description,
            'fr_description' => $request->fr_description,
            'time_to_cook' => $request->time_to_cook,
            'difficulty' => $request->difficulty,
            'image' => $imageName,
            'status' => $request->status ? 1 : 0,
        ]);

        return redirect()->route('admin.recipe.index')->with('success', __('Successfully Created!'));
    }

    public function edit($id)
    {
        $data['title'] = __('Edit Recipe');
        $data['recipe'] = Recipe::findOrFail($id);
        return view('admin.pages.recipe.edit', $data);
    }

    public function update(Request $request, $id)
    {
        $recipe = Recipe::findOrFail($id);

        $request->validate([
            'en_title' => 'required|string|max:255',
            'fr_title' => 'required|string|max:255',
            'en_description' => 'required|string',
            'fr_description' => 'required|string',
            'time_to_cook' => 'required|integer|min:1',
            'difficulty' => 'required|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = $recipe->image;
        if ($request->hasFile('image')) {
            if ($imageName) {
                Storage::delete('recipes/' . $imageName);
            }
            $imageName = fileUpload($request->file('image'), 'recipes');
        }

        $recipe->update([
            'en_title' => $request->en_title,
            'fr_title' => $request->fr_title,
            'slug' => Str::slug($request->en_title) . '-' . time(),
            'en_description' => $request->en_description,
            'fr_description' => $request->fr_description,
            'time_to_cook' => $request->time_to_cook,
            'difficulty' => $request->difficulty,
            'image' => $imageName,
            'status' => $request->has('status') ? 1 : 0,
        ]);

        return redirect()->route('admin.recipe.index')->with('success', __('Successfully Updated!'));
    }

    public function delete($id)
    {
        $recipe = Recipe::findOrFail($id);
        if ($recipe->image) {
            Storage::delete('recipes/' . $recipe->image);
        }
        $recipe->delete();
        return redirect()->back()->with('success', __('Successfully Deleted!'));
    }

    public function active($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->update(['status' => 1]);
        return redirect()->back()->with('success', __('Successfully Activated!'));
    }

    public function inactive($id)
    {
        $recipe = Recipe::findOrFail($id);
        $recipe->update(['status' => 0]);
        return redirect()->back()->with('success', __('Successfully Inactivated!'));
    }
}
