<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthenticationController extends BaseController
{
    /**
     * Register a new user.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,admin'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => bcrypt($request->password),
                'role' => $request->role,
            ]);
            $token = $user->createToken('AppName')->accessToken;

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'User created successfully'
            ], 200);
    }


    /**
     * Authenticate a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {$credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = auth()->user();
            $token = $user->createToken('AppName')->accessToken;

            return response()->json([
                'user' => new UserResource($user),
                'token' => $token,
                'message' => 'User logged in successfully'
            ], 200);
        } else {
            return $this->sendError('Unauthorised.', ['error'=>'Unauthorised']);
        }
    }



    /**
     * Logout the authenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(Request $request)
    { $user = Auth::user();

        if ($user) {
            $user->token()->revoke();

            return response()->json(['message' => 'Successfully logged out'], 200);
        } else {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
    }
}
