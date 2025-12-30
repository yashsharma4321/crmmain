<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Hash;

use Illuminate\Support\Facades\Cache;
use App\Models\User;

    use Illuminate\Support\Facades\Validator;



use App\Models\Subscription;

class UserController extends Controller
{
 
public function signup(Request $request)
{
    // ✅ Validation
    
    $request->validate([
        'name'          => 'required|string|max:255',
        'email'         => 'required|email',
        'phone'         => 'required|string',
        'password'      => 'required|string|min:6',
        'email_verify'  => 'required|accepted',
        'phone_verify'  => 'required|accepted',
    ]);

    // ✅ Extra safety check (optional)
    $exists = User::where('email', $request->email)
        ->orWhere('phone', $request->phone)
        ->exists();

    if ($exists) {
        return response()->json([
            'status'  => 'error',
            'message' => 'User already exists.',
        ], 409);
    }

    // ✅ Create User
    $user = User::create([
        'name'     => $request->name,
        'email'    => $request->email,
        'phone'    => $request->phone,
        'password' => Hash::make($request->password),
    ]);

    // ✅ Generate Token (Sanctum)
    $token = $user->createToken('auth_token')->plainTextToken;

    // ✅ Response
    return response()->json([
        'status'  => 'success',
        'message' => 'Signup successful.',
        'token'   => $token,
        'user'    => $user,
    ], 201);
}

   


public function forgetPassword(Request $request)
{
    // ✅ Validator
    $validator = Validator::make($request->all(), [
        'input_type' => 'required|string',
        'forget'     => 'nullable|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    $input = $request->input('input_type');

    /* 🔍 Detect email or phone */
    if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
        $email = $input;
        $phone = null;
    } elseif (preg_match('/^[0-9]{6,15}$/', $input)) {
        $phone = $input;
        $email = null;
    } else {
        return response()->json([
            'status'  => 'error',
            'message' => 'Please enter a valid email or phone number.',
        ], 422);
    }

    /* 🔎 Check user */
    $user = User::where('email', $email)
        ->orWhere('phone', $phone)
        ->first();

    if (!$user) {
        return response()->json([
            'status'  => 'error',
            'message' => 'No account found with this email or phone number.',
        ], 404);
    }

    /* 🚫 Google account restriction */
    if ($user->provider === 'google') {
        return response()->json([
            'status'  => 'error',
            'message' => 'This account is linked with Google. Please login using Google.',
        ], 400);
    }

    /* 🔐 Generate OTP */
    $otp = rand(100000, 999999);

    Cache::put('forgot_otp_' . $input, $otp, now()->addMinutes(5));

    /* 📩 Send OTP */
    if ($email) {
        // Mail::to($email)->send(new OtpMail($otp));
    } else {
        // SmsHelper::sendOtp($phone, $otp);
    }

    return response()->json([
        'status'  => 'success',
        'message' => 'OTP sent successfully.',
        'data'    => [
            'input_type' => $input,
            // ⚠️ DEV only
            'otp' => $otp,
        ],
    ], 200);
}




public function verifyForgetOtp(Request $request)
{
    $request->validate([
        'input_type' => 'required|string',
        'otp' => 'required|digits:6',
    ]);

    $input = $request->input('input_type');
    $otp = $request->input('otp');

    
    $cachedOtp = Cache::get('forgot_otp_' . $input);

    if (!$cachedOtp || $cachedOtp != $otp) {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired OTP.',
        ], 400);
    }


    
    Cache::forget('forgot_otp_' . $input);
    Cache::put('forgot_otp_verified_' . $input, true, now()->addMinutes(5));

    return response()->json([
        'status' => 'success',
        'message' => 'OTP verified successfully.',
    ]);
}




public function googleLogin(Request $request)
{
    
      $request->validate([
        'email' => 'required|email',
        'google_id' => 'required|string',
        'name' => 'nullable|string',
        'avatar' => 'nullable|string',
    ]);

    $email = $request->email;
    $googleId = $request->google_id;
    $name = $request->name;
    $avatar = $request->avatar;

    // Step 1️⃣: Check if user exists by google_id
    $user = User::where('provider', 'google')
        ->where('provider_id', $googleId)
        ->first();

    if (!$user) {
        // Step 2️⃣: If not linked, check by email
        $user = User::where('email', $email)->first();
     
        if ($user) {
            // Existing user (registered via email/OTP/password)
            // ✅ Link Google now
            $user->update([
                'provider' => 'google',
                'provider_id' => $googleId,
            ]);
        } else {
            // Step 3️⃣: New Google-only user
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'provider' => 'google',
                'provider_id' => $googleId,
                'password' => null, // no password yet
                'profile_photo' => $avatar ?? null, // optional
            ]);
        }
    }

    // Step 4️⃣: Generate Sanctum token
    $token = $user->createToken('auth_token')->plainTextToken;

   $user_id = $user->id;

        // return $user_id;
        $is_subescribe = Subscription::where('user_id',$user_id)->first();
        $yes_subscribe = false;
        if($is_subescribe){
            $yes_subscribe = true;
        }

    return response()->json([
        'status' => 'success',
        'message' => 'Google login successful.',
        'token' => $token,
        'user' => $user,
         'subscribe' => $yes_subscribe
    ]);
}



   

   public function resetPassword(Request $request)
{
    $validator = Validator::make($request->all(), [
        'input_type' => 'required',
        'password'   => 'required|min:6',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors(),
        ], 422);
    }

    $input = $request->input('input_type');

    // 🔐 Check OTP verification
    if (!Cache::get("forgot_otp_verified_{$input}")) {
        return response()->json([
            'status'  => 'error',
            'message' => 'OTP not verified'
        ], 400);
    }

    // 👤 Find user by email or number
    $user = User::where('email', $input)
        ->orWhere('phone', $input)
        ->first();

    if (!$user) {
        return response()->json([
            'status'  => 'error',
            'message' => 'User not found'
        ], 404);
    }

    // 🔑 Update password
    $user->update([
        'password' => Hash::make($request->password),
    ]);

    // 🧹 Clear OTP cache
    Cache::forget("forgot_otp_verified_{$input}");

    return response()->json([
        'status'  => 'success',
        'message' => 'Password reset successfully',
    ]);
}


   
    public function login(Request $request)
    {

        // dd('sd');
        $request->validate([
            'input_type' => 'required',
            'password' => 'required'
        ]);

        $input = $request->input_type;

        $user = User::where('email', $input)->orWhere('phone', $input)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'User not found'], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['status' => 'error', 'message' => 'Invalid password'], 401);
        }

        
        // $userrole = $user->role_type;
        
        // if($userrole == 'company'){
        //       return response()->json(['status' => 'error', 'message' => 'You Can Not Login From Here Please Select Company Then Login'], 401);
        // }
        $token = $user->createToken('auth')->plainTextToken;

        $user_id = $user->id;

        // return $user_id;
        $is_subescribe = Subscription::where('user_id',$user_id)->first();
        $yes_subscribe = false;
        if($is_subescribe){
            $yes_subscribe = true;
        }
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful',
            'token' => $token,
            'user' => $user,
            'subscribe' => $yes_subscribe
        ]);
    }
}
