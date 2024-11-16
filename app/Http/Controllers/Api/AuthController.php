<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Http\Controllers\Api\Responses\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => [
                'required',
                'string',
                'unique:users',
                'regex:/^(010|011|012|015)[0-9]{8}$/',
            ],
            'password' => [
                'required',
                'string',
                'min:8', 
                'regex:/[a-z]/', 
                'regex:/[A-Z]/', 
                'regex:/[0-9]/', 
                'regex:/[@$!%*?&]/',
            ],
        ]);

        if ($validator->fails()) {
            return ApiResponse::error('Validation Error', 400, $validator->errors());
        }

        // Generate a random 6-digit verification code
        $verificationCode = rand(100000, 999999);

        $user = User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'verification_code' => $verificationCode,
            'is_verified' => false,
        ]);

        // Log the verification code (for testing purposes - replace with email/SMS in production)
        Log::info('Verification code for user ' . $user->phone . ': ' . $verificationCode);

        // Generate Sanctum token for the newly registered user
        $token = $user->createToken('API_Token')->plainTextToken;

        // Custom response data
        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ];

        return ApiResponse::success($data, 'User registered successfully', 201);
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ApiResponse::error('Invalid credentials', 401);
        }

        if (!$user->is_verified) {
            return ApiResponse::error('Account not verified. Please verify your account first.', 403);
        }

        // Generate a Sanctum token upon successful login
        $token = $user->createToken('API_Token')->plainTextToken;

        // Custom response data
        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ];

        return ApiResponse::success($data, 'Login successful', 200);
    }

    
  /**
 * Verify code sent to user and get user information
 */
public function verifyCode(Request $request)
{
   
    // Validate the incoming request
    $request->validate([
        'code' => 'required|integer',
        'email' => 'required|string|email|exists:users,email',
    ]);

    // Find the user with the given verification code and email
    $user = User::where('verification_code', $request->code)
                ->where('email', $request->email)
                ->first();
    Log::info($user);
    if ($user) {
       
        $user->is_verified = true;
        $user->verification_code = null; 
        $user->save();

        $data = [
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
        ];

        return ApiResponse::success($data, 'Account verified successfully', 200);
    }

    return ApiResponse::error('Invalid verification code or email', 400);
}

     /**
     * Logout user
     */
public function logout(Request $request)
{
    $request->user()->currentAccessToken()->delete();

    return ApiResponse::success(null, 'Logged out successfully', 200);
}


}
