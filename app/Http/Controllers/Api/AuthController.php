<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginUserRequest;
use App\Http\Requests\StoreUserRequest;

class AuthController extends Controller
{
    use HttpResponses;

    public function login(LoginUserRequest $request)
    {
        $request->validated($request->all());        
        if(!auth()->attempt($request->only(['email','password']))){         
            return $this->error('','Credentails are not matched...!',401);
        }             
        return $this->success(['user' => auth()->user(),'token' => auth()->user()->createToken('API Token of '.auth()->user()->name)->plainTextToken]);        
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
            'token' => $user->createToken('API Token of '.$user->name)->plainTextToken,
        ],"User created Successfully...!");
    }

    public function logout()
    {
        auth()->user()->currentAccessToken()->delete();
        return $this->success("","User logout successfully...!",200);
    }
}