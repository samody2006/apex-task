<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UserController extends BaseController
{
    public function __construct()
{
    $this->middleware('auth:api');
}
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $users = User::latest()->paginate(10);

        if ($users->isEmpty()) {
            return $this->sendError('Error', 'No users found.', 404);
        }

        return $this->sendResponse([
            'users' => UserResource::collection($users),
            'pagination' => [
                'total' => $users->total(),
                'per_page' => $users->perPage(),
                'current_page' => $users->currentPage(),
                'last_page' => $users->lastPage(),
                'from' => $users->firstItem(),
                'to' => $users->lastItem()
            ]
        ], 'Users retrieved successfully.');
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
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
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = $request->role;
        $user->save();
        $token = $user->createToken('AppName')->accessToken;

        return $this->sendResponse(['user' => new UserResource($user),
            'token' => $token,
            'message' => 'User created successfully' ], 200);
    }


    /**
     * Display the specified resource.
     *
     * @param  User $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $user = User::find($id);

        if (!$user) {
            return $this->sendError('User not found', 404);
        }
        return $this->sendResponse(['user' => new UserResource($user),
            'message' => 'User retrieved successfully'], 200);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:6',
            'role' => 'required|in:user,admin'
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation Error.', $validator->errors());
        }
        $userData = $request->all();
        if(isset($userData['password'])) {
            $userData['password'] = bcrypt($userData['password']);
        $user->update($userData);

        return $this->sendResponse([
            'user' => new UserResource($user),
            'message' => 'User updated successfully'
        ], 200);
    }
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
             return $this->sendError('User not found', 404);
        }
        if (auth()->user()->role !== 'admin') {
            return $this->sendError( 'Permission denied. Only admins can delete users', 403);
        }
        $user->delete();
            return $this->sendResponse('User deleted successfully', 200);
    }
}
