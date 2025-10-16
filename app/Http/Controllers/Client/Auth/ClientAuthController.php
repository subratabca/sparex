<?php
namespace App\Http\Controllers\Client\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerificationMail;
use App\Notifications\Client\ClientRegistrationNotification;
use Illuminate\Validation\ValidationException;
use App\Helpers\ActivityLogger;
use App\Helpers\JWTToken;
use App\Mail\OTPMail;
use App\Models\User;
use App\Models\TermCondition;
use Exception;

class ClientAuthController extends Controller
{
    public function registrationTermsConditionsPage()
    {
        return view('frontend.pages.terms-condition.client-registration-terms-condition-page');
    }

    public function registrationTermsConditionsInfo(Request $request, $name)
    {
        try {
            $termsCondition = TermCondition::where('name', str_replace('_', ' ', $name))->first();

            if ($termsCondition) {
                ActivityLogger::log(
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
        return view('client.pages.auth.registration-page');
    }


    public function Registration(Request $request)
    {
        try {
            $request->validate([
                'firstName' => 'required|string|max:50',
                'email' => 'required|string|email|max:50|unique:users,email',
                'password' => 'required|string|min:6',
                'accept_registration_tnc' => 'required|boolean',
            ]);


            $client = User::create([
                'firstName' => $request->input('firstName'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
                'accept_registration_tnc' => $request->input('accept_registration_tnc'),
                'role' => 'client'
            ]);

            if ($client) {
                ActivityLogger::beforeAuthLog('registration_success', 'New client registered successfully.', $request, 'users');
                $admin = User::where('role', 'admin')->first();
                $admin->notify(new ClientRegistrationNotification($client));

                Mail::to($client->email)->send(new EmailVerificationMail($client, 'client'));
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Registration successful. We have sent you an activation link, please check your email.'
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


    public function VerifyClient(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if ($user) {
            if ($user->role === 'client' && $user->is_email_verified == 0) {
                $user->is_email_verified = 1;
                $user->save();
                ActivityLogger::beforeAuthLog('email_verified_success', 'Email verification successful.', $request, 'users');
                return view('client.pages.auth.login-page')->with('message', 'Your account is activated. You can login now.');
            }
        } else {
            ActivityLogger::beforeAuthLog('email_verified_failed', 'Email already verified.', $request, 'users');
            return view('client.pages.auth.login-page')->with('message', 'Client not found.');
        }
    }

    public function LoginPage()
    {
        return view('client.pages.auth.login-page');
    }


    public function Login(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'password' => 'required|string|min:6'
            ]);

            $user = User::where('role', 'client')
            ->where('email', $request->input('email'))
            ->select('firstName', 'id', 'password', 'role', 'is_email_verified')
            ->first();

            if ($user !== null) {
                if ($user->is_email_verified == 0) {
                    ActivityLogger::beforeAuthLog(
                        'login_failed',
                        'Account not activated. Client needs to verify email.',
                        $request,
                        'users'
                    );
                    return response()->json([
                        'status' => 'failed',
                        'message' => 'You have to activate your account. Please check your email.',
                    ], 403);
                }

                if (Hash::check($request->input('password'), $user->password)) {
                    $token = JWTToken::ClientCreateToken($request->input('email'), $user->id, $user->role);
                    ActivityLogger::beforeAuthLog(
                        'login_success',
                        'Client logged in successfully.',
                        $request,
                        'users'
                    );
                    return response()->json([
                        'status' => 'success',
                        'message' => 'User Login Successful',
                        'token' => $token
                    ], 200)->cookie('token', $token, 60 * 24 * 30, null, null, false, false);
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
            } else {
                ActivityLogger::beforeAuthLog(
                    'login_failed',
                    'Client not found.',
                    $request,
                    'users'
                );
                return response()->json([
                    'status' => 'failed',
                    'message' => 'User not found'
                ], 404);
            }
        } catch (ValidationException $e) {
            ActivityLogger::beforeAuthLog(
                'login_failed',
                'Validation failed: ' . json_encode($e->errors()),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Validation Failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            ActivityLogger::beforeAuthLog(
                'login_failed',
                'Unexpected error occurred: ' . $e->getMessage(),
                $request,
                'users'
            );
            return response()->json([
                'status' => 'failed',
                'message' => 'Login failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function SendOtpPage()
    {
        return view('client.pages.auth.send-otp-page');
    }


    public function SendOTPCode(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $email = $request->input('email');
            $otp = rand(1000, 9999);

            $user = User::where('email', '=', $email)->first();

            if ($user) {
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
        return view('client.pages.auth.verify-otp-page');
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
            $user = User::where('email', '=', $email)->where('otp', '=', $otp)->first();

            if ($user !== null) {
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
        return view('client.pages.auth.reset-password-page');
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

            $user = User::where('email', '=', $email)->first();

            if ($user) {
                User::where('email', '=', $email)->update(['password' => $password]);
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
