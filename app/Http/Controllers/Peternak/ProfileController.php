<?php

namespace App\Http\Controllers\Peternak;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load(['role', 'assignedFarm']);

        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'name' => $user->name,
                'email' => $user->email,
                'phone_number' => $user->phone_number,
                'profile_pic' => $user->profile_pic,
                'role' => $user->role->name,
                'assigned_farm' => $user->assignedFarm ? [
                    'farm_id' => $user->assignedFarm->farm_id,
                    'farm_name' => $user->assignedFarm->farm_name,
                    'location' => $user->assignedFarm->location,
                ] : null,
            ]
        ]);
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'phone_number' => 'nullable|string|max:20',
            'email' => 'sometimes|email|max:100|unique:users,email,' . $user->user_id . ',user_id',
        ]);

        $user->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Profile berhasil diupdate',
            'data' => [
                'phone_number' => $user->phone_number,
                'email' => $user->email,
            ]
        ]);
    }

    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $user = $request->user();

        // Delete old photo
        if ($user->profile_pic) {
            Storage::disk('public')->delete($user->profile_pic);
        }

        // Store new photo
        $path = $request->file('photo')->store('profile-photos', 'public');

        $user->update(['profile_pic' => $path]);

        return response()->json([
            'success' => true,
            'message' => 'Foto profile berhasil diupdate',
            'data' => [
                'profile_pic' => $path,
                'url' => Storage::url($path),
            ]
        ]);
    }
}
