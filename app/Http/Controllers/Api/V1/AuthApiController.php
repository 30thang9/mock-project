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
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthApiController extends Controller
{
    private UserRepository $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
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

            $data = [
                'user' => $user,
                'access_token' => $token
            ];
            return apiResponse(__('Login successfully!'), $data);

        } catch (\Exception $e) {
            Log::error('Login failed: '. $e->getMessage());
            return apiResponse(__("Something went wrong on our end"), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    public function logout(Request $request){
        try {
            JWTAuth::parseToken()->invalidate();

            return apiResponse(__('Logout successfully!'));

        } catch (\Exception $e) {
            Log::error('Logout failed: '. $e->getMessage());
            return apiResponse(__("Something went wrong on our end"), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function refreshToken()
    {
        try {
            $newToken = JWTAuth::parseToken()->refresh();
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_refresh_token'], 500);
        }

        return response()->json(['token' => $newToken]);
    }

    public function verifyEmail(VerifyEmailRequest $request){
        $request->validated();
        try {
            $request->fulfill();
            return apiResponse(__('Email verified successfully!'));
        }
        catch (\Exception $e) {
            Log::error('Failed: '. $e->getMessage());
            return apiResponse(__("Something went wrong on our end"), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
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
            Log::error('Failed: '. $e->getMessage());
            return apiResponse(__("Something went wrong on our end"),null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
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
            Log::error('Failed: '. $e->getMessage());
            return apiResponse(__("Something went wrong on our end"), null, ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

}
