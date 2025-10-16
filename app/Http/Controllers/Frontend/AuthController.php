<?php
namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use App\Notifications\Customer\CustomerRegistrationNotification;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Helpers\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use App\Models\TermCondition;
use Exception;

class AuthController extends Controller
{
    public function registrationTermsConditionsPage()
    {
        return view('frontend.pages.terms-condition.customer-registration-terms-condition-page');
    }

    public function registrationTermsConditionsInfo(Request $request, $name)
    {
        try {
            $termsCondition = TermCondition::where('name', str_replace('_', ' ', $name))->first();

            if ($termsCondition) {
                ActivityLogger::beforeAuthLog(
                    'tc_access_success', 
                    'Terms & Conditions retrieved successfully.', 
                    $request, 
                    'term_conditions'
                );
                return response()->json([
                    'status' => 'success',
                    'data' => $termsCondition
                ], 200);
            } else {
                ActivityLogger::beforeAuthLog(
                    'tc_access_failed', 
                    'Failed to retrieve Terms & Conditions: T&C not found.', 
                    $request, 
                    'term_conditions'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'T&C not found.'
                ], 404);
            }
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog(
                'tc_access_failed', 
                'System error while retrieving Terms & Conditions: ' . $e->getMessage(), 
                $request, 
                'term_conditions'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Failed to retrieve Terms & Conditions.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function RegistrationPage()
    {
        return view('frontend.pages.auth.registration-page');
    }

    public function Registration(Request $request)
    {
        try {
            $request->validate([
                'firstName' => 'required|string|max:50',
                'email' => 'required|string|email|max:50',
                'password' => 'required|string|min:6',
                'accept_registration_tnc' => 'required|boolean',
            ]);

            $customer = User::where('email', $request->input('email'))->first();

            if (!$customer) {
                $customer = User::create([
                    'firstName' => $request->input('firstName'),
                    'email' => $request->input('email'),
                    'password' => Hash::make($request->input('password')),
                    'accept_registration_tnc' => $request->input('accept_registration_tnc'),
                    'role' => 'customer',
                ]);

                ActivityLogger::beforeAuthLog('registration_success', 'New customer registered successfully.', $request, 'users');
            } else {
                $customer = User::updateOrCreate(
                    ['id' => $user->id],
                    [
                        'firstName' => $request->input('firstName'),
                        'password' => Hash::make($request->input('password')),
                        'accept_registration_tnc' => $request->input('accept_registration_tnc'),
                    ]
                );

                ActivityLogger::beforeAuthLog('registration_success', 'Customer registration updated successfully.', $request, 'users');
            }

            if ($customer) {
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new CustomerRegistrationNotification($customer));

                Mail::to($customer->email)->send(new EmailVerificationMail($customer, 'customer'));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. We have sent you an activation link, please check your email.',
                'customer' => $customer,
            ], 201);

        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog('registration_failed', 'Validation failed: ' . json_encode($e->errors()), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog('registration_failed', 'System error: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function VerifyCustomer(Request $request)
    {
        $customer = User::where('email', $request->input('email'))->first();

        if ($customer) {
            if ($customer->role === 'customer' && $customer->is_email_verified == 0) {
                $customer->is_email_verified = 1;
                $customer->save();

                ActivityLogger::beforeAuthLog('email_verified_success', 'Email verification successful.', $request, 'users');
                return view('frontend.pages.auth.login-page')->with('message', 'Your account is activated. You can login now.');
            }
        } else {
            ActivityLogger::beforeAuthLog('email_verified_failed', 'Email already verified.', $request, 'users');
            return view('frontend.pages.auth.login-page')->with('message', 'User not found.');
        }
    }

    public function LoginPage()
    {
        return view('frontend.pages.auth.login-page');
    }

    public function Login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6',
            ]);

            $customer = User::where('role', 'customer')
            ->where('email', $request->input('email'))
            ->select('firstName', 'id', 'password', 'is_email_verified')
            ->first();

            if ($customer === null) {
                ActivityLogger::beforeAuthLog('login_failed', 'Login failed. Customer not found.', $request, 'users');
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Customer not found',
                ], 404);
            }

            if ($customer->is_email_verified == 0) {
                ActivityLogger::beforeAuthLog('login_failed', 'Customer login failed. Account not activated.', $request, 'users');

                return response()->json([
                    'status' => 'failed',
                    'message' => 'You have to activate your account. Please check your email.',
                ], 403);
            }

            if (!Hash::check($request->input('password'), $customer->password)) {
                ActivityLogger::beforeAuthLog('login_failed', 'Customer login failed. Invalid email or password.', $request, 'users');

                return response()->json([
                    'status' => 'failed',
                    'message' => 'Email or Password is Invalid',
                ], 401);
            }

            $token = JWTToken::CreateToken($request->input('email'), $customer->id, $customer->role);
            $intendedUrl = session('url.intended', '/user/dashboard');

            ActivityLogger::beforeAuthLog('login_success', 'Customer login successful.', $request, 'users');
            return response()->json([
                'status' => 'success',
                'message' => 'User Login Successful',
                'token' => $token,
                'redirect' => $intendedUrl,
            ], 200)->cookie('token', $token, 60, null, null, false, false);

        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog('login_failed', 'Customer login failed due to validation errors.', $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog('login_failed', 'Customer login failed due to a system error: ' . $e->getMessage(), $request, 'users');
            return response()->json([
                'status' => 'failed',
                'message' => 'Login failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function SendOtpPage()
    {
        return view('frontend.pages.auth.send-otp-page');
    }


    public function SendOTPCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = $request->input('email');
            $otp = rand(1000, 9999);

            $customer = User::where('email', '=', $email)->first();

            if ($customer) {
                Mail::to($email)->send(new OTPMail($otp));
                User::where('email', '=', $email)->update(['otp' => $otp]);
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
        return view('frontend.pages.auth.verify-otp-page');
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
            $customer = User::where('email', '=', $email)->where('otp', '=', $otp)->first();

            if ($customer !== null) {
                User::where('email', '=', $email)->update(['otp' => '0']);

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
        return view('frontend.pages.auth.reset-password-page');
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

            $customer = User::where('email', '=', $email)->first();

            if ($customer) {
                User::where('email', '=', $email)->update(['password' => $password]);
                ActivityLogger::beforeAuthLog('forgot_password_success', 'Password reset successful', $request, 'users');
                return response()->json([
                    'status' => 'success',
                    'message' => 'Password reset successful',
                ], 200);
            } else {
                ActivityLogger::beforeAuthLog('forgot_password_failed', 'Customer not found for password reset', $request, 'users');
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
