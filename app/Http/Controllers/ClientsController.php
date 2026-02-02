<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Client;

class ClientsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $clients = Client::all();
            return response()->json($clients, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve clients'], 500);
        }
    }


    public function show(string $id)
    {
        try {
            $client = Client::with('factories')->findOrFail($id);
            return response()->json($client, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Client not found'], 404);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'client_name' => 'required|string|max:255',
                'contact_number' => 'required|string|max:20',
                'notes' => 'nullable|string',
            ]);
            $client = Client::create($validatedData);
            return response()->json(
                [
                    'message' => 'Client created successfully',
                    'client' => $client
                ],
                201
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create client'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $client = Client::findOrFail($id);
            $validatedData = $request->validate([
                'client_name' => 'sometimes|required|string|max:255',
                'contact_number' => 'sometimes|required|string|max:20',
                'notes' => 'nullable|string',
            ]);
            $client->update($validatedData);
            return response()->json(
                [
                    'message' => 'Client updated successfully',
                    'client' => $client
                ],
                200
            );
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update client', 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $client = Client::findOrFail($id);
            $client->delete();
            return response()->json(['message' => 'Client deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete client'], 500);
        }
    }
}
