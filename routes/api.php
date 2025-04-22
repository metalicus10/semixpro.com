<?php

use App\Models\Nomenclature;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('api')->group(function () {
    Route::get('/warehouses', function (Request $request) {
        if (!$request->has('technician_id')) {
            return response()->json(['error' => 'Missing technician_id'], 400);
        }

        $technician = User::find($request->technician_id);

        if (!$technician) {
            return response()->json(['error' => 'Technician not found'], 404);
        }

        return response()->json($technician->warehouses);
    });
});

Route::get('/api/nomenclatures', function (Request $request) {
    $query = Nomenclature::query()->where('manager_id', Auth::id());

    if ($search = $request->search) {
        $query->where('name', 'like', "%$search%");
    }
    if ($category = $request->category) {
        $query->where('category_id', $category);
    }
    if ($brand = $request->brand) {
        $query->whereHas('brands', function($q) use ($brand) {
            $q->where('id', $brand);
        });
    }

    return $query->with('category', 'brands')->limit(50)->get();
});

