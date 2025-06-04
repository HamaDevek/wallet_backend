<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * User login
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8',
        ]);

        if ($validator->fails()) {
            return ApiResponseHelper::validationError($validator->errors());
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !password_verify($request->password, $user->password)) {
            return ApiResponseHelper::unauthorized('Invalid credentials');
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponseHelper::success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'Login successful');
    }

    /**
     * User logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return ApiResponseHelper::success(null, 'Logged out successfully');
    }

    /**
     * Get authenticated user
     */
    public function me(Request $request)
    {
        $token = $request->header('Authorization');
        // split the token to get the actual token value
        $token = explode(' ', $token);
        return ApiResponseHelper::success(
            [
                'user' => new UserResource($request->user()),
                'token' => $token[1] ?? null,
            ],
            'Authenticated user retrieved successfully'
        );
    }

    /**
     * User registration
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return ApiResponseHelper::validationError($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'wallet_balance' => '0.00',
            'email_verified_at' => now(),
            'password' => bcrypt($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return ApiResponseHelper::success([
            'user' => new UserResource($user),
            'token' => $token,
        ], 'User registered successfully', 201);
    }
}
