<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Factory;
class FactoriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $factories = Factory::with('client')->get();
            return response()->json($factories, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve factories'], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try{
            $validatedData = $request->validate([
                'factory_name' => 'required|string|max:255',
                'client_id' => 'required|exists:clients,id',
                'location' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'contact_number' => 'nullable|string|max:20',
                'loading_person' => 'nullable|string|max:255',
                'loading_contact' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
            ]);

            $factory = Factory::create($validatedData);
            return response()->json([
                'message' => 'Factory created successfully',
                'factory' => $factory
            ], 201);
        }catch(\Exception $e){
            return response()->json(['error' => 'Failed to create factory'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $factory = Factory::findOrFail($id);

            $validatedData = $request->validate([
                'factory_name' => 'sometimes|required|string|max:255',
                'client_id' => 'sometimes|required|exists:clients,id',
                'location' => 'nullable|string|max:255',
                'contact_person' => 'nullable|string|max:255',
                'contact_number' => 'nullable|string|max:20',
                'loading_person' => 'nullable|string|max:255',
                'loading_contact' => 'nullable|string|max:20',
                'notes' => 'nullable|string',
            ]);

            $factory->update($validatedData);

            return response()->json([
                'message' => 'Factory updated successfully',
                'factory' => $factory
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update factory'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $factory = Factory::findOrFail($id);
            $factory->delete();

            return response()->json(['message' => 'Factory deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete factory'], 500);
        }
    }
}
