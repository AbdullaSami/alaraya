<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function getUsers()
    {
        try {
            $users = User::with('roles')->get();
            return response()->json(['users' => $users], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve users', 'message' => $e->getMessage()], 500);
        }
    }

    public function createUser(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'full_name'     => 'required|string|max:255',
                'user_name'     => 'required|string|max:255|unique:users,user_name',
                'email'         => 'required|string|email|max:255|unique:users,email',
                'password'      => 'required|string|min:8',
                'phone_number'  => 'required|string|max:20',
                'role'          => 'required|string|in:admin,operations,data_entry',
            ]);
            $validatedData['password'] = bcrypt($validatedData['password']);
            $user = User::create($validatedData);
            $user->assignRole($validatedData['role']);
            return response()->json(['message' => 'User created successfully', 'user' => $user], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create user', 'message' => $e->getMessage()], 500);
        }
    }

    public function login(Request $request)
    {
        try {
            $credentials = $request->only('email', 'password');
            if (!auth()->attempt($credentials)) {
                return response()->json(['error' => 'Invalid credentials'], 401);
            }
            $user = auth()->user();
            $token = $user->createToken('auth_token')->plainTextToken;
            return response()->json(['message' => 'Login successful', 'access_token' => $token, 'token_type' => 'Bearer'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Login failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logout successful'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Logout failed', 'message' => $e->getMessage()], 500);
        }
    }

    public function editUser(Request $request, $id)
    {
        try {
            $user = User::findOrFail($id);
            $validatedData = $request->validate([
                'full_name'     => 'sometimes|string|max:255',
                'user_name'     => 'sometimes|string|max:255|unique:users,user_name,' . $user->id,
                'email'         => 'sometimes|string|email|max:255|unique:users,email,' . $user->id,
                'password'      => 'sometimes|string|min:8',
                'phone_number'  => 'sometimes|string|max:20',
                'role'          => 'sometimes|string|in:admin,operations,data_entry',
            ]);

            if(isset($validatedData['password'])){
                $validatedData['password'] = bcrypt($validatedData['password']);
            }else{
                unset($validatedData['password']);
            }
            $user->update($validatedData);
            if (isset($validatedData['role'])) {
                $user->syncRoles([$validatedData['role']]);
            }
            return response()->json(['message' => 'User updated successfully', 'user' => $user], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to update user', 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();
            return response()->json(['message' => 'User deleted successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to delete user', 'message' => $e->getMessage()], 500);
        }
    }

}
