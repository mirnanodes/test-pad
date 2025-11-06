<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->with('role')->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        // Check if user is active
        if ($user->status !== 'active') {
            throw ValidationException::withMessages([
                'email' => ['Akun Anda tidak aktif. Silakan hubungi admin.'],
            ]);
        }

        // Update last login
        $user->updateLastLogin();

        // Create token
        $token = $user->createToken('auth-token')->plainTextToken;

        // Get farm data based on role
        $farmData = $this->getFarmData($user);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                    'role_id' => $user->role_id,
                    'phone_number' => $user->phone_number,
                    'profile_pic' => $user->profile_pic,
                ],
                'farms' => $farmData,
                'token' => $token,
            ],
        ]);
    }

    /**
     * Handle logout request
     */
    public function logout(Request $request)
    {
        // Revoke all tokens
        $request->user()->tokens()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Get current authenticated user
     */
    public function me(Request $request)
    {
        $user = $request->user()->load('role');
        $farmData = $this->getFarmData($user);

        return response()->json([
            'success' => true,
            'data' => [
                'user' => [
                    'user_id' => $user->user_id,
                    'username' => $user->username,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role->name,
                    'role_id' => $user->role_id,
                    'phone_number' => $user->phone_number,
                    'profile_pic' => $user->profile_pic,
                    'last_login' => $user->last_login,
                ],
                'farms' => $farmData,
            ],
        ]);
    }

    /**
     * Get farm data based on user role
     */
    private function getFarmData(User $user)
    {
        if ($user->isOwner()) {
            // Owner bisa punya banyak kandang
            return $user->ownedFarms()
                ->with('latestSensorData')
                ->get()
                ->map(function ($farm) {
                    return [
                        'farm_id' => $farm->farm_id,
                        'farm_name' => $farm->farm_name,
                        'location' => $farm->location,
                        'status' => $farm->calculateStatus(),
                        'latest_sensor' => $farm->latestSensorData ? [
                            'temperature' => $farm->latestSensorData->temperature,
                            'humidity' => $farm->latestSensorData->humidity,
                            'ammonia' => $farm->latestSensorData->ammonia,
                            'timestamp' => $farm->latestSensorData->timestamp,
                        ] : null,
                    ];
                });
        } elseif ($user->isPeternak()) {
            // Peternak hanya punya 1 kandang
            $farm = $user->assignedFarm;
            
            if ($farm) {
                $farm->load('latestSensorData');
                return [
                    'farm_id' => $farm->farm_id,
                    'farm_name' => $farm->farm_name,
                    'location' => $farm->location,
                    'status' => $farm->calculateStatus(),
                    'latest_sensor' => $farm->latestSensorData ? [
                        'temperature' => $farm->latestSensorData->temperature,
                        'humidity' => $farm->latestSensorData->humidity,
                        'ammonia' => $farm->latestSensorData->ammonia,
                        'timestamp' => $farm->latestSensorData->timestamp,
                    ] : null,
                ];
            }
        }

        return null;
    }
}
