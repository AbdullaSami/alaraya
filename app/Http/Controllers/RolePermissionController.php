<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    /**
     * Get all roles
     */
    public function roles()
    {
        $roles = Role::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $roles
        ]);
    }

    /**
     * Get all permissions
     */
    public function permissions()
    {
        $permissions = Permission::select('id', 'name')->get();

        return response()->json([
            'status' => true,
            'data' => $permissions
        ]);
    }
}
