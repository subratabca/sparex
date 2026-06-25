<?php
namespace App\Http\Controllers\Backend\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use Exception;
use App\Models\ActivityLog;
use App\Helpers\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class AdminAuthController extends Controller
{

    public function RegistrationPage()
    {
        return view('backend.pages.auth.registration-page');
    }

    public function Registration(Request $request)
    {
        try {
            // Block public admin self-registration once an admin already exists.
            if (User::where('role', 'admin')->exists()) {
                ActivityLogger::beforeAuthLog('registration_failed', 'Admin registration blocked: an admin already exists.', $request, 'users');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Admin registration is disabled.',
                ], 403);
            }

            $request->validate([
                'firstName' => 'required|string|max:50',
                'email' => 'required|string|email|max:50|unique:users,email',
                'password' => 'required|string|min:6'
            ]);

            User::create([
                'firstName' => $request->input('firstName'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'role' => 'admin'
            ]);
            ActivityLogger::beforeAuthLog('registration_success', 'New admin registered successfully.', $request, 'users');
            return response()->json([
                'status' => 'success',
                'message' => 'Registration Successfully Done'
            ], 201);

        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog('registration_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog('registration_failed', 'System error: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function LoginPage()
    {
        return view('backend.pages.auth.login-page');
    }

    public function Login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6'
            ]);

            $user = User::where('role', 'admin')
            ->where('email', $request->input('email'))
            ->select('firstName', 'id', 'password', 'role')
            ->first();

            if ($user !== null && Hash::check($request->input('password'), $user->password)) {
                $remember      = $request->boolean('remember');
                $tokenMinutes  = $remember ? 60 * 24 * 30 : 60 * 12;   // 30 days vs 12 hours
                $cookieMinutes = $remember ? 60 * 24 * 30 : 0;          // 0 = session cookie

                $token = JWTToken::CreateToken($request->input('email'), $user->id, $user->role, 'admin', $tokenMinutes);
                ActivityLogger::beforeAuthLog('login_success', 'Admin login successful.', $request, 'users', $user->id);
                return response()->json([
                    'status' => 'success',
                    'message' => 'User Login Successful',
                    'token' => $token
                ], 200)->cookie('token', $token, $cookieMinutes, null, null, config('jwt.cookie_secure'), true, false, 'Lax');
            } else {
                ActivityLogger::beforeAuthLog(
                    'login_failed',
                    'Invalid email or password.',
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email or Password is Invalid'
                ], 401);
            }
        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog('login_failed', 'Admin login failed due to validation errors.', $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog('login_failed', 'Admin login failed due to a system error: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function SendOtpPage()
    {
        return view('backend.pages.auth.send-otp-page');
    }

    public function SendOTPCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = $request->input('email');
            $otp = rand(1000, 9999);

            $user = User::where('role', 'admin')->where('email', '=', $email)->first();

            if ($user) {
                Mail::to($email)->send(new OTPMail($otp));
                User::where('email', '=', $email)->update(['otp' => $otp]);
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                ActivityLogger::beforeAuthLog(
                    'send_otp_success',
                    "OTP sent to email $email",
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'success',
                    'message' => '4 Digit OTP Code has been sent to your email!'
                ], 200);
            } else {
                ActivityLogger::beforeAuthLog(
                    'send_otp_failed',
                    "Client not found for email $email",
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found',
                ], 401);
            }
        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog(
                'send_otp_failed',
                "Validation Failed: " . json_encode($e->errors()),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog(
                'send_otp_failed',
                "System Error: " . $e->getMessage(),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    public function VerifyOTPPage()
    {
        return view('backend.pages.auth.verify-otp-page');
    }

    public function VerifyOTP(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|string|email|max:50|exists:users,email',
                'otp' => 'required|string|size:4'
            ]);

            $email = $request->input('email');
            $otp = $request->input('otp');
            $user = User::where('role', 'admin')->where('email', '=', $email)->where('otp', '=', $otp)->first();

            if ($user !== null) {
                User::where('email', '=', $email)->update(['otp' => '0']);
                DB::table('password_reset_tokens')->updateOrInsert(
                    ['email' => $email],
                    ['token' => Str::random(64), 'created_at' => now()]
                );
                ActivityLogger::beforeAuthLog(
                    'otp_verified_success', 
                    'Success, OTP verified successfully.', 
                    $request, 
                    'users'
                );
                return response()->json([
                    'status' => 'success',
                    'message' => 'OTP Verification Successful',
                ], 200);
            } else {
                ActivityLogger::beforeAuthLog(
                    'otp_verified_failed', 
                    'Failed, Incorrect OTP entered.', 
                    $request, 
                    'users'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Unauthorized',
                ], 401);
            }
        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog(
                'otp_verified_failed', 
                'Failed, Validation error: ' . json_encode($e->errors()), 
                $request, 
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog(
                'otp_verified_failed', 
                'Failed, System error: ' . $e->getMessage(), 
                $request, 
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurred. Please try again later.',
            ], 500);
        }
    }

    public function ResetPasswordPage()
    {
        return view('backend.pages.auth.reset-password-page');
    }

    public function ResetPassword(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6'
            ]);

            $email = $request->input('email');
            $password = Hash::make($request->input('password'));

            $user = User::where('role', 'admin')->where('email', '=', $email)->first();

            if ($user) {
                $resetRow = DB::table('password_reset_tokens')->where('email', $email)->first();
                if (!$resetRow || \Carbon\Carbon::parse($resetRow->created_at)->addMinutes(15)->isPast()) {
                    return response()->json([
                        'status' => 'fail',
                        'message' => 'OTP verification required. Please verify the OTP before resetting your password.',
                    ], 403);
                }

                User::where('email', '=', $email)->update(['password' => $password]);
                DB::table('password_reset_tokens')->where('email', $email)->delete();
                ActivityLogger::beforeAuthLog('forgot_password_success', 'Password reset successful', $request, 'users');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password reset successful',
                ], 200);
            } else {
                ActivityLogger::beforeAuthLog('forgot_password_failed', 'User not found for password reset', $request, 'users');
                return response()->json([
                    'status' => 'fail',
                    'message' => 'User not found',
                ], 404); 
            }

        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog('forgot_password_failed', 'Validation error: ' . json_encode($e->errors()), $request, 'users');
            return response()->json([
                'status' => 'fail',
                'errors' => $e->errors(),
            ], 422);

        } catch (Exception $exception) {
            ActivityLogger::beforeAuthLog('forgot_password_failed', 'System error: ' . $exception->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'fail',
                'message' => 'Something went wrong. Please try again later.',
            ], 500);
        }
    }

}
