<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;

class UserRoleController extends Controller
{
    /**
     * Display roles for a specific user.
     */
    public function show(User $user)
    {
        $user->load('roles', 'permissions');
        $roles = Role::all();
        return view('admin.users.roles', compact('user', 'roles'));
    }

    /**
     * Assign a role to a user.
     */
    public function assign(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->assignRole($validated['role']);

        return redirect()->back()
            ->with('success', "Rôle '{$validated['role']}' assigné à l'utilisateur avec succès!");
    }

    /**
     * Remove a role from a user.
     */
    public function remove(Request $request, User $user)
    {
        $validated = $request->validate([
            'role' => 'required|exists:roles,name',
        ]);

        $user->removeRole($validated['role']);

        return redirect()->back()
            ->with('success', "Rôle '{$validated['role']}' supprimé de l'utilisateur avec succès!");
    }

    /**
     * Sync roles for a user (replace all roles).
     */
    public function sync(Request $request, User $user)
    {
        $validated = $request->validate([
            'roles' => 'required|array',
            'roles.*' => 'exists:roles,name',
        ]);

        $user->syncRoles($validated['roles']);

        return redirect()->back()
            ->with('success', 'Rôles de l\'utilisateur mis à jour avec succès!');
    }

    /**
     * Grant a direct permission to a user.
     */
    public function grantPermission(Request $request, User $user)
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user->givePermissionTo($validated['permission']);

        return redirect()->back()
            ->with('success', "Permission '{$validated['permission']}' accordée avec succès!");
    }

    /**
     * Revoke a direct permission from a user.
     */
    public function revokePermission(Request $request, User $user)
    {
        $validated = $request->validate([
            'permission' => 'required|exists:permissions,name',
        ]);

        $user->revokePermissionTo($validated['permission']);

        return redirect()->back()
            ->with('success', "Permission '{$validated['permission']}' révoquée avec succès!");
    }
}
