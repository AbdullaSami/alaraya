<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTreasuryRequest;
use App\Http\Requests\UpdateTreasuryRequest;
use App\Models\Treasury;

class TreasuryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try{
            $treasuries = Treasury::all();
            return response()->json($treasuries);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTreasuryRequest $request)
    {
        try {
            $validatedData = $request->validate([
                'is_main' => 'required|boolean',
                'name' => 'required|string|max:255',
                'balance' => 'nullable|decimal:0,2',
            ]);
            $treasury = Treasury::create($validatedData);
            return response()->json($treasury, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Treasury $treasury)
    {
        try {
            return response()->json($treasury);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTreasuryRequest $request, Treasury $treasury)
    {
        try {
            $validatedData = $request->validate([
                'is_main' => 'required|boolean',
                'name' => 'required|string|max:255',
                'balance' => 'nullable|decimal:0,2',
            ]);
            $treasury->update($validatedData);
            return response()->json($treasury);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Treasury $treasury)
    {
        try {
            $treasury->delete();
            return response()->json(['message' => 'Treasury deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
