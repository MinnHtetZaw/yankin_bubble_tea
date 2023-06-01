<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use App\SeparateUser;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends apiBaseController {
	public function login(Request $request) {

		$validator = Validator::make($request->all(), [
			'email' => 'required',
			'password' => 'required',
		]);

		if ($validator->fails()) {
			return response()->json(['error', $validator->fails()]);
		}

		$email = $request->email;

		$password = $request->password;

		$user = User::where('email', $email)->first();

		if (empty($user)) {

			return $this->sendError('User dose not exist!');
		} elseif (!\Hash::check($password, $user->password)) {

			return $this->sendError('Username or password is wrong!');

		} else {

			$user = User::where('email', $email)->first();

			$id = $user->id;
			$email = $user->email;
			$name = $user->name;
			$role = $user->roles[0]->name;

			if ($user->hasRole('Owner')) {

				return response()->json([
					'user' => [
						'id' => $user->id,
						'email' => $user->email,
						'name' => $user->name,
						'role' => $role,
					],
					'message' => "successful",
					'success' => true,
				]);

			}elseif ($user->hasRole('Manager')) {

				return response()->json([
					'user' => [
						'id' => $user->id,
						'email' => $user->email,
						'name' => $user->name,
						'role' => $role,
					],
					'message' => "successful",
					'success' => true,
				]);

			}elseif ($user->hasRole('Waiter')) {

				return response()->json([
					'user' => [
						'id' => $user->id,
						'email' => $user->email,
						'name' => $user->name,
						'role' => $role,
					],
					'message' => "successful",
					'success' => true,
				]);

			}
		}
	}

    public function mainLogin(Request $request){


        $validator = Validator::make($request->all(), [
			'email' => 'required',
			'password' => 'required',
		]);

		if ($validator->fails()) {
			return response()->json(['error', $validator->fails()]);
		}

		$email = $request->email;

		$password = $request->password;

		$user = SeparateUser::where('email', $email)->first();

		if (empty($user)) {

			return $this->sendError('User dose not exist!');
		} elseif (!\Hash::check($password, $user->password)) {

			return $this->sendError('Username or password is wrong!');

		}

        return response()->json([
            "userData"=>$user
        ]);
    }



	protected function updatePassword(Request $request){

    	$validator = Validator::make($request->all(), [
    	    'email' => 'required',
            'current_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {

        	return $this->sendFailResponse("Something Wrong! Validation Error.", "400");
        }

        $user = User::where('email', $request->email)->first();

        $current_pw = $request->current_password;

        if(!\Hash::check($current_pw, $user->password)){

            return $this->sendFailResponse("Something Wrong! Password doesn't match.", "400");
        }

        $has_new_pw = \Hash::make($request->new_password);

        $user->password = $has_new_pw;

        $user->save();

        return $this->sendSuccessResponse("user", $user);
    }
}
