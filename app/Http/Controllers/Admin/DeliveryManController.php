<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\DeliveryMan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Yajra\DataTables\Facades\DataTables;

class DeliveryManController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = DeliveryMan::query()->latest();
            return DataTables::of($data)
                ->addColumn('action', function ($row) {
                    $btn = '<div class="action__buttons">';
                    $btn .= '<a href="' . route('admin.delivery_man.edit', $row->id) . '" class="btn-action" title="Edit"><i
            class="fa-solid fa-pen-to-square"></i></a>';
                    $btn .= '<a href="' . route('admin.delivery_man.delete', $row->id) . '" class="btn-action delete" title="Delete"><i
            class="fas fa-trash-alt"></i></a>';
                    $btn .= '</div>';
                    return $btn;
                })
                ->editColumn('status', function ($row) {
                    return $row->status ? '<span class="badge bg-success">' . __('Active') . '</span>' : '<span class="badge bg-danger">' . __('Inactive') . '</span>';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
        return view('admin.pages.delivery_man.index', ['menu' => 'delivery_man', 'submenu' => 'delivery_man_list']);
    }

    public function create()
    {
        return view('admin.pages.delivery_man.create', ['menu' => 'delivery_man', 'submenu' => 'delivery_man_create']);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'password' => 'required|string|min:6',
            'phone' => 'required|string|unique:delivery_men,phone',
        ]);

        DeliveryMan::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'status' => $request->has('status'),
        ]);

        return redirect()->route('admin.delivery_man')->with('toast_success', __('Delivery Man Created Successfully'));
    }

    public function edit($id)
    {
        $deliveryMan = DeliveryMan::findOrFail($id);
        return view('admin.pages.delivery_man.edit', [
            'deliveryMan' => $deliveryMan,
            'menu' => 'delivery_man',
            'submenu' => 'delivery_man_list'
        ]);
    }

    public function update(Request $request, $id)
    {
        $deliveryMan = DeliveryMan::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|unique:delivery_men,phone,' . $id,
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'status' => $request->has('status'),
        ];

        if ($request->password) {
            $data['password'] = Hash::make($request->password);
        }

        $deliveryMan->update($data);

        return redirect()->route('admin.delivery_man')->with('toast_success', __('Delivery Man Updated Successfully'));
    }

    public function delete($id)
    {
        $deliveryMan = DeliveryMan::findOrFail($id);
        $deliveryMan->delete();
        return redirect()->back()->with('toast_success', __('Delivery Man Deleted Successfully'));
    }
}