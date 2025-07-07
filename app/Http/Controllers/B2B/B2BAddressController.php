<?php

namespace App\Http\Controllers\B2B;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Carbon;

use App\Models\B2BAddress;

class B2BAddressController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $addresses = B2BAddress::where('user_id', Auth::id())->latest();

            return DataTables::of($addresses)
                ->addColumn('select', function ($address) {
                    $checked = $address->status === 'active' ? 'checked' : '';
                    return '<input type="radio" name="default_address" class="select-address" data-id="' . $address->id . '" ' . $checked . '>';
                })
                ->addColumn('full_address', function ($address) {
                    return $address->street . ', ' . $address->barangay . ', ' . $address->city . ', ' . $address->province . ', ' . $address->zip_code;
                })
                ->editColumn('created_at', function ($address) {
                    return Carbon::parse($address->created_at)->format('Y-m-d H:i:s');
                })
                ->rawColumns(['select', 'full_address'])
                ->make(true);
        }


        return view('pages.b2b.v_address', [
            'page' => 'My Addresses',
        ]);
    }

    public function geoCode(Request $request)
    {
        $address = $request->input('q');

        if (!$address) {
            return response()->json(['error' => 'Address query is empty.'], 400);
        }

        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Laravel App - yourdomain.com', // REQUIRED
                    'Accept-Language' => 'en-US'
                ])
                ->get('https://nominatim.openstreetmap.org/search', [
                    'q' => $address,
                    'format' => 'json',
                    'addressdetails' => 1,
                    'limit' => 1
                ]);

            if ($response->successful()) {
                $data = $response->json();
                return response()->json($data);
            }

            return response()->json(['error' => 'Failed to retrieve geocode.'], $response->status());
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Exception occurred',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function setDefault(Request $request)
    {
        $userId = Auth::id();
   
        B2BAddress::where('user_id', $userId)->update(['status' => 'inactive']);
        B2BAddress::where('id', $request->input('id'))
            ->where('user_id', $userId)
            ->update(['status' => 'active']);

        return response()->json(['success' => true]);
    }

    public function create()
    {
        return view('b2b.address.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'barangay' => 'required|string|max:255',
            'street' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'province' => 'nullable|string|max:255',
            'zip_code' => 'nullable|string|max:20',
            'delivery_address_lat' => 'required|numeric',
            'delivery_address_lng' => 'required|numeric',
        ]);

        $fullAddress = collect([
            $request->street,
            $request->barangay,
            $request->city,
            $request->province,
            $request->zip_code
        ])->filter()->implode(', ');

        B2BAddress::create([
            'user_id' => Auth::id(),
            'street' => $request->street,
            'barangay' => $request->barangay,
            'city' => $request->city,
            'province' => $request->province,
            'zip_code' => $request->zip_code,
            'delivery_address_lat' => $request->delivery_address_lat,
            'delivery_address_lng' => $request->delivery_address_lng,
            'full_address' => $fullAddress,
            'status' => 'active'
        ]);

        return response()->json(['message' => 'Address saved successfully.']);
    }


    public function show($id)
    {
        $address = B2BAddress::where('user_id', Auth::id())->findOrFail($id);
        return view('b2b.address.show', compact('address'));
    }

    public function edit($id)
    {
        $address = B2BAddress::where('user_id', Auth::id())->findOrFail($id);
        return view('b2b.address.edit', compact('address'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'barangay' => 'required',
            'street' => 'required',
            'city' => 'required',
            'delivery_address' => 'required',
            'delivery_address_lat' => 'required|numeric',
            'delivery_address_lng' => 'required|numeric',
        ]);

        $address = B2BAddress::where('user_id', Auth::id())->findOrFail($id);
        $address->update($request->only(['barangay', 'street', 'city', 'delivery_address', 'delivery_address_lat', 'delivery_address_lng']));

        return redirect()->route('address.index')->with('success', 'Address updated successfully.');
    }

    public function destroy($id)
    {
        $address = B2BAddress::where('user_id', Auth::id())->findOrFail($id);
        $address->delete();

        return redirect()->route('address.index')->with('success', 'Address deleted successfully.');
    }
}
