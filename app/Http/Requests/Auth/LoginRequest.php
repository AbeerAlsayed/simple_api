<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

class LoginRequest extends FormRequest
{

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email',
            'password' => 'required',
            'phone'=>'required',
            'device_name' => 'required',
        ];
    }
    protected function failedValidation(Validator $validator)
    {

        throw new HttpResponseException(response([
            'status' => 'error',
            'message' => null,
            'data' => $validator->errors()
        ], Response::HTTP_BAD_REQUEST));

    }
}
