<?php

namespace App\Http\Controllers\Api\V1;

use App\Events\RegistationEvent;
use App\Http\Controllers\Controller;
use App\Http\Repositories\UserRepository;
use App\Http\Requests\Users\ResetPasswordRequest;
use App\Http\Requests\Users\UserCreateRequest;
use App\Http\Requests\Users\VerifyEmailRequest;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthApiController extends Controller
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function register(UserCreateRequest $request)
    {
        $validatedData = $request->validated();

        $user = new User([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'department_id' => $validatedData['department_id'],
            'role_id' => $validatedData['role_id'],
        ]);

        try {
            DB::beginTransaction();
            $user =  $this->userRepo->save($user);
            DB::commit();
            if(!$user){
                return apiResponse(__('Registation error'), null, ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
            }
            event(new RegistationEvent($user));
            return apiResponse(__('Registation successfully!'), $user, ResponseAlias::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Registration failed: '.$e->getMessage());
            return apiResponse(__($e->getMessage()), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        try {
            $credentials = $request->only('email', 'password');
            if (!$token = JWTAuth::attempt($credentials)) {
                return apiResponse(__('Email or password invalid! Please try again.'), null, ResponseAlias::HTTP_UNAUTHORIZED);
            }

            $user = JWTAuth::user();
            if(!$user->hasVerifiedEmail()){
                event(new RegistationEvent($user));
                return apiResponse(__('Email is not verified! Please verify your email before logging in.'), null, ResponseAlias::HTTP_FORBIDDEN);
            }

            $refreshToken = JWTAuth::fromUser($user, ['type' => 'refresh']);
            $data = [
                'user' => $user,
                'access_token' => $token,
                'refresh_token' => $refreshToken,
            ];
            return apiResponse(__('Login successfully!'), $data);

        } catch (\Exception $e) {
            return apiResponse(__($e->getMessage()), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function logout(Request $request){

    }

    public function refreshToken(Request $request)
    {
        try {

            $refreshToken = $request->input('refresh_token');
            $token = JWTAuth::refresh($refreshToken);

            if (!$token) {
                return apiResponse('Unauthorized',null,ResponseAlias::HTTP_UNAUTHORIZED);
            }

            $user = JWTAuth::toUser($token);

            $accessToken = JWTAuth::fromUser($user);

            return apiResponse(__('Token refresh successfully!'), ['access_token' => $accessToken]);

        } catch (TokenExpiredException $e) {
            return apiResponse(__('Refresh token expired'), null, ResponseAlias::HTTP_UNAUTHORIZED);
        } catch (\Exception $e) {
            return apiResponse(__($e->getMessage()), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function verifyEmail(VerifyEmailRequest $request){
        $request->validated();
        try {
            $request->fulfill();
            return apiResponse(__('Email verified successfully!'));
        }
        catch (\Exception $e) {
            return apiResponse(__($e->getMessage()), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );

            if($status !== Password::RESET_LINK_SENT){
                return apiResponse(__('Unable to send password reset link.'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }
            return apiResponse(__('Password reset link sent to your email address.'));
        }catch(\Exception $e) {
            return apiResponse(__($e->getMessage()),null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function resetPassword(ResetPasswordRequest $request)
    {
        $request->validated();

        try {
            $status = Password::reset(
                $request->only('email', 'password', 'password_confirmation', 'token'),
                function ($user, $password) {
                    $user->password = Hash::make($password);
                    $user->save();
                    event(new PasswordReset($user));
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                return apiResponse(__('Unable to reset password.'), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }
            return apiResponse(__('Password has been reset successfully.'));
        } catch (\Exception $e) {
            return apiResponse(__($e->getMessage()), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
