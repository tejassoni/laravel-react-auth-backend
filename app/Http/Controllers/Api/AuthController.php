<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdatePasswordRequest;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());
        if (!auth()->attempt($request->only(['email', 'password']))) {
            return $this->error('', 'Credentails are not matched...!', 401);
        }
        return $this->success(['user' => auth()->user(), 'token' => auth()->user()->createToken('API Token of ' . auth()->user()->name)->plainTextToken]);
    }

    public function register(StoreUserRequest $request)
    {
        $request->validated($request->all());
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
        ]);
        return $this->success([
            'user' => $user,
            'token' => $user->createToken('API Token of ' . $user->name)->plainTextToken,
        ], "User created Successfully...!");
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return $this->success("", "User logout successfully...!", 200);
    }

    public function profile(Request $request)
    {
        return $this->success(['user' => $request->user()], 'User Detail get successfully...!');
    }

    public function refresh(Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return $this->success(['token' => $user->createToken('API Token of ' . auth()->user()->name)->plainTextToken], 'Token refresh successfully...!');
    }

    /*
    * UpdatePassword on basis of Old password and new Password
    */
    public function updatePassword(UpdatePasswordRequest $request)
    {
        #Match The Old Password
        if (!Hash::check($request->old_password, auth()->user()->password)) {
            return $this->error('', "Old Password Doesn't match...!", 401);
        }
        #Update the new Password  
        $request->user()->fill([
            'password' => Hash::make($request->new_password)
        ])->save();            
        return $this->success([], 'Password changed successfully...!');
    }
}