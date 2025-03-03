<?php

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
