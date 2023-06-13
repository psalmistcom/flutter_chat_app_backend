<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function register(RegisterRequest $request){
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $data['username'] = strstr($data['email'], '@', true);

        $user = User::create($data);
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success([
            'user' => $user,
            'token' => $token->plainTextToken,
        ], 'User has been register successfully.');
    }

    public function login(LoginRequest $request) : JsonResponse {
        $isValid = $this->isValidCredential($request);

        if (!$isValid['success']) {
            return $this->error($isValid['message'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $user = $isValid['user'];
        $token = $user->createToken(User::USER_TOKEN);

        return $this->success([
            'user'=>$user,
            'token' => $token->plainTextToken
        ], 'Login successfully');
    }

    private function isValidCredential(LoginRequest $request) : array {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();
        if($user === null) {
            return [
                'success'=>false,
                'message'=> 'invalid credential'
            ];
        }

        if(Hash::check($data['password'], $user->password)){
            return [
                'success'=>true,
                'user'=>$user
            ];
        }
    }

    public function loginWithToken() : JsonResponse{
        return $this->success(auth()->user(),'Login successfully!');
    }

    public function logout(Request $request) : JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return $this->success(null,'Logout successfully!');
    }
}
