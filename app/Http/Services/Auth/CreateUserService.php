<?php

namespace App\Http\Services\Auth;
use App\Events\UserEvent;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Notifications\VerificationEmail;
use App\Traits\UploadedFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CreateUserService{
    use UploadedFile;
    public function storeUser(RegisterRequest $request){
        try {
        $profile_picture=$this->uploadFile($request,'images','profile_picture');
        $certificate=$this->uploadFile($request,'files','certificate');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'profile_picture' =>$profile_picture,
            'certificate' => $certificate,
        ]);
        $user->generateEmailCode();
        UserEvent::dispatch($user);
        $user->notify(new VerificationEmail());
        return $user;
        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
    public function  Resend_email_vf(Request $request){
        try {
        $user = $request->user();
        $user->generateEmailCode();
        $user->notify(new VerificationEmail());
        return $user;
        }catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);}
    }
}
