<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Car;
use App\Models\CarType;
use Illuminate\Http\Request;

class CarController extends Controller
{
    public function index(Request $request){
        $user = $request->user();
        $companyId = $user->companies()->first()?->id;

        if (! $companyId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No company linked to this account.'
            ], 403);
        }

        $cars = Car::with(['partner'])
            ->where('company_id', $companyId)
            ->where('deleted_at', null)
            ->get();
        
        return response()->json([
            'status' => 'success',
            'data' => $cars,
        ]);
    }

    public function show($id){
        $car = Car::with(['partner'])->find($id);
        return response()->json([
            'status' => 'success',
            'data' => $car,
        ]);
    }
    
    public function getCarTypes()
    {
        $carTypes = CarType::all();

        return response()->json([
            'status' => 'success',
            'data' => $carTypes,
        ]);
    }
    
    public function store(Request $request)
    {
        $user = $request->user();
        $companyId = $user->companies()->first()?->id;
    
        if (! $companyId) {
            return response()->json([
                'status' => 'error',
                'message' => 'No company linked to this account.',
            ], 403);
        }
    
        // Validate request
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|max:255',
            'model' => 'required|string|max:255',
            'year' => 'required|integer',
            'color' => 'nullable|string|max:255',
            'plate_number' => 'required|string|max:255|unique:cars,plate_number',
            'image' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'seat_count' => 'nullable|integer',
            'fuel_type' => 'nullable|string|max:100',
            'coding' => 'nullable|string|max:20',
            'price_starts_at' => 'nullable|numeric',
            'transmission' => 'nullable|string|max:50',
            'car_type_id' => 'required|exists:car_types,id',
        ]);
    
        // Handle file upload
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/cars', $filename);
            $validated['image'] = 'cars/' . $filename;
        }
    
        // Assign company_id
        $validated['company_id'] = $companyId;
    
        // Save car record
        $car = \App\Models\Car::create($validated);
    
        return response()->json([
            'status' => 'success',
            'message' => 'Car added successfully!',
            'data' => $car,
        ]);
    }

}
