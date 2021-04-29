<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginUserRequest;
use App\Rules\MatchOldPassword;
use App\Rules\MatchNewPassword;
use App\Mail\PasswordUpdated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Foundation\Auth\ResetsPasswords;
use Hash;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    use SendsPasswordResetEmails, ResetsPasswords {
        SendsPasswordResetEmails::broker insteadof ResetsPasswords;
        ResetsPasswords::credentials insteadof SendsPasswordResetEmails;
    }

    /**
     * Login an existing User.
     *
     * @param LoginUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function login(LoginUserRequest $request)
    {
        if (Auth::attempt($request->only('email', 'password'))) {
            if (Auth::user()->suspended) {
                return response()->json([
                    'error' => [
                        'message' => trans('auth.suspended')
                    ]
                ], Response::HTTP_UNAUTHORIZED);
            }
            return response()->json([], Response::HTTP_NO_CONTENT);
        }

        return response()->json([
            'error' => [
                'message' => trans('auth.failed')
            ]
        ], Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Logout a currently authenticated User.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout()
    {
        Auth::logout();

        return response()->json([], Response::HTTP_NO_CONTENT);
    }

    /**
     * Send password reset link. 
     */
    public function sendPasswordResetLink(Request $request)
    {
        return $this->sendResetLinkEmail($request);
    }

    /**
     * Get the response for a successful password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkResponse(Request $request, $response)
    {
        return response()->json([
            'message' => 'Password reset email sent.',
            'data' => $response
        ]);
    }
    /**
     * Get the response for a failed password reset link.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetLinkFailedResponse(Request $request, $response)
    {
        return response()->json(['message' => 'Email could not be sent to this email address.']);
    }

    /**
     * Handle reset password 
     */
    public function callResetPassword(Request $request)
    {
        return $this->reset($request);
    }

    /**
     * Reset the given user's password.
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @param  string  $password
     * @return void
     */
    protected function resetPassword($user, $password)
    {
        $user->password = Hash::make($password);
        $user->save();
        event(new PasswordReset($user));
    }

    /**
     * Get the response for a successful password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetResponse(Request $request, $response)
    {
        return response()->json(['message' => 'Password reset successfully.']);
    }
    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendResetFailedResponse(Request $request, $response)
    {
        return response()->json(['message' => 'Failed, Invalid Token.']);
    }

    /**
     * Update the authenticated user's password.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', new MatchOldPassword],
            'password' => ['required', 'min:8', 'confirmed', new MatchNewPassword],
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->input('password'));
        $user->save();

        Mail::to($user->email)->send(new PasswordUpdated());

        return response()->json([], Response::HTTP_NO_CONTENT);
    }
}
