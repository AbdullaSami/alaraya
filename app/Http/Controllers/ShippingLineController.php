<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ShippingLine;
class ShippingLineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $shippingLines = ShippingLine::all();
            return response()->json($shippingLines, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve shipping lines'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'shipping_line_name' => 'required|string|max:255',
                'contact_info'       => 'nullable|string',
                'notes'              => 'nullable|string',
            ]);
            $shippingLine = ShippingLine::create($validatedData);
            return response()->json([
                'message' => 'Shipping line created successfully',
                'data' => $shippingLine
            ], 201);
        }catch(\Exception $e){
            return response()->json(['error' => 'Failed to create shipping line'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $shippingLine = ShippingLine::findOrFail($id);
            $validatedData = $request->validate([
                'shipping_line_name' => 'sometimes|string|max:255',
                'contact_info'       => 'sometimes|string',
                'notes'              => 'sometimes|string',
            ]);
            $shippingLine->update($validatedData);
            return response()->json([
                'message' => 'Shipping line updated successfully',
                'data' => $shippingLine
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update shipping line'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $shippingLine = ShippingLine::findOrFail($id);
            $shippingLine->delete();
            return response()->json(['message' => 'Shipping line deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete shipping line'], 500);
        }
    }
}
