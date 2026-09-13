<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileAddressController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Store a newly created address in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building_no' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'state_id' => 'required',
            'city_id' => 'required',
            'area_id' => 'required',
            'phone' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'type' => 'nullable|string|in:home,work',
        ]);

        $user = Auth::user();

        // Set default if this is the first address
        $hasDefault = $user->addresses()->where('is_default', true)->exists();

        $address = new Address();
        $address->user_id = $user->id;
        $address->label = $request->label;
        $address->recipient_name = $user->name;
        $address->phone = ($request->country_code ?? '') . $request->phone;
        $address->address_line1 = $request->street;
        $address->address_line2 = json_encode([
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'area_id' => $request->area_id,
            'building' => $request->building_no ?? '',
            'apartment' => $request->apartment ?? '',
            'notes' => $request->notes ?? '',
            'type' => $request->type ?? 'home',
        ]);
        $address->city = $request->city_id; // Just for fallback/compatibility
        $address->country = 'Oman';
        $address->address_type = 'both';
        $address->is_default = !$hasDefault;
        $address->save();

        return response()->json([
            'success' => true,
            'message' => __('Address created successfully'),
            'address' => $address
        ]);
    }

    /**
     * Update the specified address in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'label' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'building_no' => 'nullable|string|max:255',
            'apartment' => 'nullable|string|max:255',
            'state_id' => 'required',
            'city_id' => 'required',
            'area_id' => 'required',
            'phone' => 'required|string|max:255',
            'notes' => 'nullable|string',
            'type' => 'nullable|string|in:home,work',
        ]);

        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);

        $address->label = $request->label;
        $address->phone = ($request->country_code ?? '') . $request->phone;
        $address->address_line1 = $request->street;
        $address->address_line2 = json_encode([
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
            'area_id' => $request->area_id,
            'building' => $request->building_no ?? '',
            'apartment' => $request->apartment ?? '',
            'notes' => $request->notes ?? '',
            'type' => $request->type ?? 'home',
        ]);
        $address->city = $request->city_id;
        $address->save();

        return response()->json([
            'success' => true,
            'message' => __('Address updated successfully'),
            'address' => $address
        ]);
    }

    /**
     * Remove the specified address from storage.
     */
    public function destroy($id)
    {
        $user = Auth::user();
        $address = $user->addresses()->findOrFail($id);
        $address->delete();

        return response()->json([
            'success' => true,
            'message' => __('Address deleted successfully')
        ]);
    }
}
