<?php

namespace App\Http\Controllers\Api;

use Mail;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Traits\HttpResponses;
use Illuminate\Support\Carbon;
use App\Models\PasswordResetToken;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Mail\SendForgotPasswordEmail;
use App\Http\Requests\VerifyTokenRequest;
use App\Http\Requests\SubmitForgotPasswordRequest;
use App\Http\Requests\UpdatePasswordWithTokenRequest;

class ForgotPasswordController extends Controller
{
    use HttpResponses;

    /*
     * send email to change Password token
     */
    public function submitForgetPassword(SubmitForgotPasswordRequest $request)
    {
        // Delete all old code that user send before.
        PasswordResetToken::where('email', $request->email)->delete();

        $token = Str::random(64); // Generate random string token
        // $token = mt_rand(100000, 999999); // Generate random code 

        $passwordReset = new PasswordResetToken; 
        $passwordReset->timestamps = false;
        $passwordReset->email = $request->email; 
        $passwordReset->token = $token; 
        $passwordReset->created_at = Carbon::now(); 
        $passwordReset->save();

        $mailData = [
            'body' => 'This is for testing email using smtp.',
            'token' => $token,
            'reset_password_link' => config('app.url') . '/api/password/reset/?token=' . $token . '&email=' . urlencode($request->email),
        ];
        Mail::to($request->email)->send(new SendForgotPasswordEmail($mailData));
        return $this->success([], 'We have e-mailed your password reset link!');
    }

    /*
     * token Verification
    */
    public function verifyToken(VerifyTokenRequest $request)
    {
        // find the token
        $passwordReset = PasswordResetToken::firstWhere('token', $request->token);   
        
        if (empty($passwordReset)){  // token not found
            return $this->error('', 'No token found...!', 422);
        }
        
        if ($passwordReset->created_at < $passwordReset->created_at->subMinutes(5)) { // check token time expired after 5 mins
            $passwordReset->where('token', $request->token)->delete();
            return $this->error('', 'Reset Password Token expired...!', 422);
        }      
        return $this->success([], 'Token and Email are verified...! Kindly update your password now...!');
    }

    /*
    * updatePasswordByToken on basis of Token and Password
    */
    public function updatePasswordByToken(UpdatePasswordWithTokenRequest $request)
    {
        // find the code
        $passwordReset = PasswordResetToken::firstWhere('token', $request->token);        
        // find user's email 
        $user = User::firstWhere('email', $passwordReset->email);         
         // update user password
        $user->update($request->only('password')); 
         // delete current code 
        $passwordReset->where('token', $request->token)->delete();
        return $this->success([], 'Password updated successfully...!');
    } 
}