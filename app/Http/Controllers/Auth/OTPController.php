<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;

use Illuminate\Support\Facades\Hash;

use App\Models\User;
use App\Models\Subscription;


class OTPController extends Controller
{

public function setPassword(Request $request)
{
    
    $request->validate([
        'input_type' => 'required|string',
        'password' => 'required|string|min:6',
        'exist' => 'nullable|boolean',
    ]);

    $input = $request->input('input_type');
    $password = $request->input('password');
    $exist = $request->boolean('exist');

    // =============================
    // 1️⃣ LOGIN FLOW
    // =============================
    if ($exist === true) {
        $user = User::where('email', $input)
            ->orWhere('phone', $input)
            ->first();

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'message' => 'User not found.',
            ], 404);
        }

        if (!Hash::check($password, $user->password)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid password. Please try again.',
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        $user_id = $user->id;
        // return $user_id;
        return response()->json([
            'status' => 'success',
            'message' => 'Login successful.',
            'token' => $token,
            'user' => $user,
        ]);
    }

    // =============================
    // 2️⃣ REGISTER / FORGOT FLOW
    // =============================

    // Check registration OTP
    $otpVerified = Cache::get('otp_verified_'.$input);
    $otpVerifiedTime = Cache::get('otp_verified_time_'.$input);

    // Check forgot password OTP
    $forgotVerified = Cache::get('forgot_otp_verified_'.$input);
    $forgotVerifiedTime = Cache::get('forgot_otp_verified_time_'.$input);

    // ❌ No OTP verified found
    if (!$otpVerified && !$forgotVerified) {
        return response()->json([
            'status' => 'error',
            'message' => 'OTP not verified or expired. Please verify OTP first.',
        ], 400);
    }


    // Determine which flow verified
    $verifiedType = $otpVerified ? 'registration' : 'forget_password';
    $verifiedAt = $otpVerified ? $otpVerifiedTime : $forgotVerifiedTime;


    // Find user
    $user = User::where('email', $input)
        ->orWhere('phone', $input)
        ->first();

    if ($user && $user->password && $verifiedType == 'registration') {
        return response()->json([
            'status' => 'error',
            'message' => 'User already has a password. Please login instead.',
        ], 409);
    }

    if ($user) {
        $user->update(['password' => Hash::make($password)]);
    } else {
        $user = User::create([
            'email' => filter_var($input, FILTER_VALIDATE_EMAIL) ? $input : null,
            'phone' => is_numeric($input) ? $input : null,
            'password' => Hash::make($password),
        ]);
    }

    // Clear cache
    Cache::forget('otp_verified_'.$input);
    Cache::forget('otp_verified_time_'.$input);

    Cache::forget('forgot_otp_verified_'.$input);
    Cache::forget('forgot_otp_verified_time_'.$input);

    // Token generate
    $token = $user->createToken('auth_token')->plainTextToken;

    return response()->json([
        'status' => 'success',
        'message' => 'Password set successfully and user logged in.',
        'verified_type' => $verifiedType,
        'verified_at' => $verifiedAt,
        'token' => $token,
        'user' => $user,
    ]);
}



    public function sendUserOtp(Request $request)
    {
        
        $input = $request->input('input_type');

        
        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
            $phone = null;
        } elseif (preg_match('/^[0-9]{6,15}$/', $input)) {
            $phone = $input;
            $email = null;
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Please enter a valid email or phone number.',
            ], 422);
        }

    
        if($email){
            $existingUser = User::where('email', $email)
            ->first();
            
        }
        
        
    
        
        else if($phone){
            $existingUser = User::where('phone', $phone)
            ->first();
            
        }
    
        
        
        $otp = rand(100000, 999999);
        $key = $email ?? $phone;
        Cache::put('otp_'.$key, $otp, now()->addMinutes(5)); 

        

    if ($existingUser) {
        // 🔍 Check if the existing account is linked with Google
        if ($existingUser->password == NULL) {
            return response()->json([
                'status' => 'already_exist_google',
                'otp'=>$otp,
                'message' => 'This email is registered using Google. Please login with Google instead.',
            ], 200);
        }

        
        return response()->json([
            'status' => 'already_exist',
            'message' => 'User already registered.',
        ]);
    }

    


        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully.',
            'data' => [
                'input_type' => $key,
                'otp' => $otp,
            ],
        ]);
    }

public function registerOtpVerify(Request $request)
{
    $request->validate([
        'input_type' => 'required|string',
        'otp' => 'required|digits:6',
    ]);

    $input = $request->input('input_type');
    $otp = $request->input('otp');

    // Current time
    $verifiedTime = now();

    // 🔍 Try both cache keys automatically
    $cachedOtp = Cache::get('otp_' . $input);
    $cachedForgotOtp = Cache::get('forgot_otp_' . $input);

    if ($cachedOtp && $cachedOtp == $otp) {

        Cache::forget('otp_' . $input);

        // 🔥 Save verified flag + time
        Cache::put('otp_verified_' . $input, true, now()->addMinutes(5));
        Cache::put('otp_verified_time_' . $input, $verifiedTime, now()->addMinutes(5));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully.',
            'type' => 'registration',
            'verified_at' => $verifiedTime,
        ]);
    } 
    elseif ($cachedForgotOtp && $cachedForgotOtp == $otp) {

        Cache::forget('forgot_otp_' . $input);

        // 🔥 Save verified flag + time
        Cache::put('forgot_otp_verified_' . $input, true, now()->addMinutes(5));
        Cache::put('forgot_otp_verified_time_' . $input, $verifiedTime, now()->addMinutes(5));

        return response()->json([
            'status' => 'success',
            'message' => 'OTP verified successfully.',
            'type' => 'forget_password',
            'verified_at' => $verifiedTime,
        ]);
    } 
    else {
        return response()->json([
            'status' => 'error',
            'message' => 'Invalid or expired OTP.',
        ], 400);
    }
}


    public function sendForgetOtp(Request $request)
    {
        $input = $request->input_type;

        if (filter_var($input, FILTER_VALIDATE_EMAIL)) {
            $email = $input;
            $phone = null;
        } elseif (preg_match('/^[0-9]{6,15}$/', $input)) {
            $phone = $input;
            $email = null;
        } else {
            return response()->json(['status' => 'error', 'message' => 'Invalid email or phone'], 422);
        }

        $user = User::where('email', $email)->orWhere('number', $phone)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Account not found'], 404);
        }

        if ($user->password == null) {
            return response()->json([
                'status' => 'error',
                'message' => 'Account linked with Google. Use Google Login.',
            ]);
        }

        $otp = rand(100000, 999999);
        Cache::put("forgot_otp_{$input}", $otp, now()->addMinutes(5));

        if ($email) {
            // Mail::to($email)->send(new OtpMail($otp));
        }

        return response()->json([
            'status' => 'success',
            'message' => 'OTP sent successfully',
            'otp' => $otp // remove in production
        ]);
    }


    // 🚀 Verify Forget Password OTP
    public function verifyForgetOtp(Request $request)
    {
        $request->validate([
            'input_type' => 'required',
            'otp' => 'required|digits:6',
        ]);

        $input = $request->input_type;
        $cachedOtp = Cache::get("forgot_otp_{$input}");

        if (!$cachedOtp || $cachedOtp != $request->otp) {
            return response()->json(['status' => 'error', 'message' => 'Invalid OTP'], 400);
        }

        Cache::forget("forgot_otp_{$input}");
        Cache::put("forgot_otp_verified_{$input}", true, now()->addMinutes(5));

        return response()->json(['status' => 'success', 'message' => 'OTP verified']);
    }





public function verifyphone(Request $request)
{
    try {
        // ✅ Validate phone number
        $request->validate([
            'phone_number' => 'required|string|max:20',
        ]);

        // ✅ Generate a 6-digit OTP
        $otp = rand(100000, 999999);

        // ✅ Store OTP in cache for 5 minutes
        Cache::put('otp_phone_' . $request->phone_number, $otp, now()->addMinutes(5));

        // (Optional) You can log it for testing
        \Log::info("OTP generated for phone {$request->phone_number}: {$otp}");

        // ✅ Return success response
        return response()->json([
            'status'  => true,
            'message' => 'OTP generated and stored successfully.',
            'otp'     => $otp, // ⚠️ remove this in production
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong while generating OTP.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function verifyphoneotp(Request $request)
{
    try {
        // ✅ Validate request data
        $request->validate([
            'phone_number' => 'required|string|max:20',
            'otp' => 'required|numeric',
        ]);

        // ✅ Retrieve stored OTP from cache
        $cachedOtp = Cache::get('otp_phone_' . $request->phone_number);

        // ✅ Check OTP validity
        if ($cachedOtp && $cachedOtp == $request->otp) {
            // ✅ OTP matched — remove from cache
            Cache::forget('otp_phone_' . $request->phone_number);

            return response()->json([
                'status'  => true,
                'message' => 'Phone number verified successfully.',
            ], 200);
        }

        // ❌ Invalid or expired OTP
        return response()->json([
            'status'  => false,
            'message' => 'Invalid or expired OTP.',
        ], 400);

    } catch (\Illuminate\Validation\ValidationException $e) {
        // ❌ Validation error
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed.',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        // ❌ General error
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong during verification.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


public function verifyEmailOtp(Request $request)
{
    try {
        $request->validate([
            'email' => 'required|email',
            'otp'   => 'required|digits:6',
        ]);

        $email = $request->email;
        $enteredOtp = $request->otp;

        // ✅ Retrieve OTP from cache
        $cachedOtp = Cache::get('otp_email_' . $email);

        if (!$cachedOtp) {
            return response()->json([
                'status' => false,
                'message' => 'OTP expired or not found',
            ], 400);
        }

        if ($cachedOtp != $enteredOtp) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid OTP entered',
            ], 400);
        }

        // ✅ OTP verified successfully
        Cache::forget('otp_email_' . $email);

        return response()->json([
            'status'  => true,
            'message' => 'Email verified successfully!',
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong while verifying OTP.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}




public function emailverify(Request $request)


{

    // dd('sd');
    try {
        // ✅ Step 1: Validate email
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->email;

        $otp = rand(100000, 999999);

     
        Cache::put('otp_email_' . $email, $otp, now()->addMinutes(5));

      
        return response()->json([
            'status'  => true,
            'otp'=>  $otp ,
            'message' => 'OTP sent successfully to your email.',
        ], 200);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation failed',
            'errors'  => $e->errors(),
        ], 422);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => false,
            'message' => 'Something went wrong while sending OTP.',
            'error'   => $e->getMessage(),
        ], 500);
    }
}


}
