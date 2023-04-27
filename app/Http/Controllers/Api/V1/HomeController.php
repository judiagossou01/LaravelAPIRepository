<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function __invoke()
    {
        return config('digit');
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'mdp' => 'required',
        ], [
            'email.required' => '__("Please enter your email")',
            'mdp.required' => '__("Please enter your password")',
        ]);

        if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 500);
        }

        $email = $request['email'];
        $mdp = $request['mdp'];
        $response = [];

        if (!empty($request['email'] && $request['mdp'])) {

            $checkuser = DB::table('users')->where('email', $email)->first();

            if (isset($checkuser)) {

                $checkaccount = DB::table('users')->where('email', $email)->where('status', 1)->first();

                if (isset($checkaccount)) {

                    $user = DB::table('users')->where('email', $email)->where('status', 1)->first();

                    if ($user->user_type == 'staff') {

                        $company = DB::table('users')->where('id', $user->company_id)->first();

                        if ($company->status != 1) {
                            $response['status'] = false;
                            $response['message'] = __('Your company account is not active !');
                        }
                    }

                    if (Hash::check($mdp, $user->password)) {

                        $response['status'] = true;
                        $response['message'] = __('Login Sucessfully');

                        $row = (array) $user;
                        unset($row['password']);
                        unset($row['remember_token']);
                        $token = $this->generateAccess($user->id, $user->user_type);
                        $row['token'] = $token;
                        $response['data'] = $row;
                    } else {
                        $response['status'] = false;
                        $response['message'] = __('Incorrect password !');
                    }
                } else {
                    $response['status'] = false;
                    $response['message'] = __('Your account is not active !');
                }
            } else {
                $response['status'] = false;
                $response['message'] = __('User not found');
            }
        }

        return response()->json($response);
    }

    public function generateAccess($user_id, $user_type)
    {
        $token = $this->getUniqAccessToken();
        $user = DB::table('users_access')->where('user_id', $user_id)->where('user_type', $user_type)->first();
        if (isset($user)) {
            DB::table('users_access')
                ->where('id', $user->id)
                ->update(['token' => $token]);
        } else {
            DB::table('users_access')->insert(['user_id' => $user_id, 'token' => $token, 'user_type' => $user_type]);
        }
        return $token;
    }

    public function getUniqAccessToken()
    {
        $accessget = 0;
        $accessToken = '';
        while ($accessget == 0) {
            $accessToken = md5(uniqid(mt_rand(), true));
            $isToken = DB::table('users_access')->where('token', $accessToken)->first();
            if (!isset($isToken)) {
                $accessget = 1;
            }
        }
        return $accessToken;
    }
}
