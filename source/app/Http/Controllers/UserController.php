<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\AccountCreated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\CreateUserRequest;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    /**
     * Create a new User and then authenticate them.
     *
     * @param CreateUserRequest $request
     * @return \Illuminate\Http\Response
     */
    public function create(CreateUserRequest $request)
    {
        $type = $request->driver;

        if (!empty($type) && !is_bool($type)) {
            return response()->json([
                'error' => [
                    'message' => trans('errors.something_went_wrong')
                ]
            ], Response::HTTP_BAD_REQUEST);
        }

        $role = $type ? User::$ROLE_DRIVER : User::$ROLE_MECHANIC;
        $newUser = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'password' => Hash::make($request->input('password')),
            'address' => $request->input('address'),
            'latitude' => $request->input('lat'),
            'longitude' => $request->input('lng'),
            'role' => $role
        ]);

        if (!empty($newUser)) {

            Auth::login($newUser);

            Mail::to($request->input('email'))->send(new AccountCreated());

            return response()->json($newUser, Response::HTTP_NO_CONTENT);
        }
    }

    /**
     * Test email
     *
     */
    public function test()
    {
        return (new AccountCreated())->render();
        // Mail::to('vladshedrin3232@gmail.com')->send(new AccountCreated());
    }
}
