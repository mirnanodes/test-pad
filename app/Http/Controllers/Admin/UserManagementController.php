<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\{User, Role};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserManagementController extends Controller
{
    /**
     * List all users (exclude admins)
     * GET /api/admin/users?role=Owner&status=active&search=john
     */
    public function index(Request $request)
    {
        $query = User::with('role')
            ->excludeAdmins(); // tidak tampilkan admin

        // Filter by role
        if ($request->has('role')) {
            $query->byRoleName($request->role);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search by name, username, or email
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('date_joined', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $users->map(function ($user) {
                return [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                    'phone_number' => $user->phone_number,
                    'status' => $user->status,
                    'date_joined' => $user->date_joined?->format('d M Y'),
                    'last_login' => $user->last_login?->format('d M Y H:i'),
                ];
            }),
        ]);
    }

    /**
     * Create new user (Owner or Peternak)
     * POST /api/admin/users
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|max:100|unique:users,email',
            'password' => 'required|string|min:6',
            'name' => 'required|string|max:100',
            'role_id' => 'required|exists:roles,id',
            'phone_number' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive',
            'farm_id' => 'nullable|exists:farms,farm_id', // untuk peternak
        ]);

        // Tidak boleh create admin
        if ($validated['role_id'] == Role::ROLE_ADMIN) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat membuat user dengan role Admin',
            ], 403);
        }

        $user = User::create([
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'name' => $validated['name'],
            'role_id' => $validated['role_id'],
            'phone_number' => $validated['phone_number'] ?? null,
            'status' => $validated['status'],
            'date_joined' => now(),
        ]);

        // Jika peternak, assign ke farm
        if ($validated['role_id'] == Role::ROLE_PETERNAK && isset($validated['farm_id'])) {
            $farm = \App\Models\Farm::find($validated['farm_id']);
            if ($farm) {
                $farm->update(['peternak_id' => $user->user_id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dibuat',
            'data' => $user->load('role'),
        ], 201);
    }

    /**
     * Get user detail
     * GET /api/admin/users/{id}
     */
    public function show($id)
    {
        $user = User::with(['role', 'ownedFarms', 'assignedFarm'])
            ->findOrFail($id);

        $data = [
            'user_id' => $user->user_id,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->name,
            'role_id' => $user->role_id,
            'phone_number' => $user->phone_number,
            'profile_pic' => $user->profile_pic,
            'status' => $user->status,
            'date_joined' => $user->date_joined?->format('d M Y'),
            'last_login' => $user->last_login?->format('d M Y H:i'),
        ];

        // Add farms info
        if ($user->isOwner()) {
            $data['farms'] = $user->ownedFarms->map(function ($farm) {
                return [
                    'farm_id' => $farm->farm_id,
                    'farm_name' => $farm->farm_name,
                    'location' => $farm->location,
                ];
            });
        } elseif ($user->isPeternak() && $user->assignedFarm) {
            $data['assigned_farm'] = [
                'farm_id' => $user->assignedFarm->farm_id,
                'farm_name' => $user->assignedFarm->farm_name,
                'location' => $user->assignedFarm->location,
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Update user
     * PUT /api/admin/users/{id}
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Tidak boleh edit admin
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat mengubah user Admin',
            ], 403);
        }

        $validated = $request->validate([
            'username' => ['sometimes', 'string', 'max:50', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'email' => ['sometimes', 'email', 'max:100', Rule::unique('users')->ignore($user->user_id, 'user_id')],
            'password' => 'sometimes|string|min:6',
            'name' => 'sometimes|string|max:100',
            'phone_number' => 'nullable|string|max:20',
            'status' => 'sometimes|in:active,inactive',
            'farm_id' => 'nullable|exists:farms,farm_id',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $user->update($validated);

        // Update farm assignment for peternak
        if ($user->isPeternak() && isset($validated['farm_id'])) {
            // Remove from old farm
            if ($user->assignedFarm) {
                $user->assignedFarm->update(['peternak_id' => null]);
            }
            // Assign to new farm
            $farm = \App\Models\Farm::find($validated['farm_id']);
            if ($farm) {
                $farm->update(['peternak_id' => $user->user_id]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'User berhasil diupdate',
            'data' => $user->load('role'),
        ]);
    }

    /**
     * Delete user
     * DELETE /api/admin/users/{id}
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Tidak boleh delete admin
        if ($user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak dapat menghapus user Admin',
            ], 403);
        }

        // Remove farm assignment if peternak
        if ($user->isPeternak() && $user->assignedFarm) {
            $user->assignedFarm->update(['peternak_id' => null]);
        }

        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'User berhasil dihapus',
        ]);
    }
}