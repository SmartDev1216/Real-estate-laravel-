<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Activation;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class VerificationController extends Controller
{
    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
    }

    /**
     * verify user token.
     */
    public function verify_user(Request $request)
    {
        $token = null;
        $activation = null;
        $user_id = null;
        $user = null;
        $data = $request->all();
        $this->validator($data)->validate();
        $token = $request->get('token');
        $activation = Activation::where('token', $token)->first();
        if ($activation === null) {
            return response()->json(
                [
                    'error' => [
                        'code' => 300,
                        'message' => 'Send activation code again.',
                    ],
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $user_id = $activation->user_id;
        $user = User::find($user_id);
        if ($user === null) {
            return response()->json(
                [
                    'error' => [
                        'code' => 301,
                        'message' => 'There is not such user.',
                    ],
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }
        $user->is_active = 1;
        $user->email_verified_at = date('Y-m-d H:i:s');
        $user->save();
        Activation::where('user_id', $user_id)->delete();

        return response()->json([
            'csrfToken' => csrf_token(),
        ]);
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'token' => ['required', 'string', 'max:255'],
        ]);
    }
}
