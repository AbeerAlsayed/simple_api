<?php

namespace App\Http\Controllers\Api\v1\auth;

use App\Enums\TokenAbility;
use App\Events\UserEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\UpdateRequest;
use App\Http\Requests\TwoFactorRequest;
use App\Http\Services\Auth\CreateUserService;
use App\Http\Services\Auth\LoginUserService;
use App\Http\Services\Auth\UpdateUserService;
use App\Mail\emailMailable;
use App\Notifications\TwoFactorCode;
use App\Notifications\VerificationEmail;
use App\Notifications\VerificationEmailCode;
use App\Traits\ApiResponse;
use App\Traits\UploadedFile;
use App\Traits\UploadedFileStorage;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    use ApiResponse;
    public CreateUserService $createUser;
    public UpdateUserService $updateService;
    public LoginUserService $loginUserService;

    public function __construct(CreateUserService $createUser,UpdateUserService $updateService,LoginUserService $loginUserService)
    {
        $this->createUser=$createUser;
        $this->updateService=$updateService;
        $this->loginUserService=$loginUserService;
    }

    public function signup(RegisterRequest $request)
    {
        $user=$this->createUser->storeUser($request);
        $accessToken = $user->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        $refreshToken = $user->createToken('refresh_token', [TokenAbility::ISSUE_ACCESS_TOKEN->value], Carbon::now()->addMinutes(config('sanctum.rt_expiration')));
        return $this->success(['token' => $accessToken->plainTextToken,'refresh_token' => $refreshToken->plainTextToken,],'User Created Successfully.', 200);
    }
    public function login(LoginRequest $request){
        $user=$this->loginUserService->loginService($request);
        $token=$user->createToken($request->device_name)->plainTextToken;
        $user->token=$token;
        return $this->success($user ,"User Logged In Successfully", 200);

    }
    public function getProfile(Request $request){
        try {
            $user_id=$request->user()->id;
            $user=User::find($user_id);
            return $this->success($user,'User Profile',200);

        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
    public function updateProfile(UpdateRequest $request){
        try {
            $user=$this->updateService->updateUser($request);
            return $this->success($user,'update user',200);
        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
    public function logout(Request $request){
        try {
            $request->user()->currentAccessToken()->delete();
            return $this->success([],'Logout Successful',200);
        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
    public function refreshToken(Request $request)
    {
        $accessToken = $request->user()->createToken('access_token', [TokenAbility::ACCESS_API->value], Carbon::now()->addMinutes(config('sanctum.ac_expiration')));
        return $this->success($accessToken->plainTextToken,"Token generate",200);
    }

    public function confirmTwoFactorCode(Request $request){
        $user=auth()->user();
        $inputCode = $request->input('two_factor_code'); // Get the input code
        if ($inputCode === $user->two_factor_code) {
            $user->resetTwoFactorCode();
            return $this->success([],'Login Successful',200);
        }
        return $this->error('the two factor is error',401);
    }
    public function confirmEmailCode(Request $request){
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        $inputCode = $request->input('code_email'); // Get the input code
        if ($inputCode === $user->code_email) {
            $user->resetEmailCode();
            return response()->json(['message' => 'Verification Code Email Successful'], 200);
        }

        return response()->json(['error' => 'The code is incorrect'], 401);
    }
    public function resendEmailVerification(Request $request){
        $user=$this->createUser->Resend_email_vf($request);
        return $this->success($user, 'Verification Code Email Successful', 200);
    }
    public function resendEmailTwoFactor(Request $request){
        $user=$this->loginUserService->loginService($request);
        return $this->success($user, 'Verification Code Email Successful', 200);
    }

}
