<?php

namespace App\Http\Services\Auth;
use App\Events\UserEvent;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Notifications\TwoFactorCode;
use App\Notifications\VerificationEmail;
use App\Traits\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginUserService{
    use ApiResponse;
    public function loginService(LoginRequest $request){
        try {
            if(!Auth::attempt($request->only(['email', 'password','phone']))){
                throw new AuthenticationException('Username or password is invalid.');
            }
            $user = User::where('email', $request->email)->first();
            $user->generateTwoFactorCode();
            $user->notify(new TwoFactorCode());
            return $user;
        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
    public function  Resend_2FA(Request $request){
        try {
            $user = $request->user();
            $user->generateTwoFactorCode();
            $user->notify(new TwoFactorCode());
            return $user;
        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
}
