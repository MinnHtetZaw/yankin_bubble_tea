<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\apiBaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Employee;
use App\User;
use Illuminate\Support\Str;

class EmployeeController extends apiBaseController
{
    public function all(Request $request){

        $employees = Employee::all();

        return $this->sendResponse('employees', $employees);
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            "name" => "required",
            "address" => "required",
            "phone" => "required",
            "email" => "required",
            'password' => "required",
            // "role" => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        if ($request->hasfile('photo')) {

			$image = $request->file('photo');
			$name = $image->getClientOriginalName();
			$image->move(public_path() . '/image/employee/', $name);
			$image = $name;
		}

        $employee = Employee::create([
            "name" => $request->name,
            "address" => $request->address,
            "phone" => $request->phone,
            "photo" => $image??null,
            "email" => $request->email,
            "nrc" => $request->nrc??null,
            "salary" => $request->salary??null,
        ]);

        $user = User::create([
			'name' => $request->name,
			'email' => $request->email,
			'password' => \Hash::make($request->password),
			'remember_token' => Str::random(60),
		]);

		$employee->user_id = $user->id;
		$employee->save();

        if ($request->has('role')) {
            $user->assignRole($request->role);
			$user->save();
        }

        return $this->sendResponse('employee', $employee);
    }

    public function update(Request $request){

        $validator = Validator::make($request->all(), [
            "employee_id" => "required",
            "name" => "required",
            "address" => "required",
            "phone" => "required",
            "email" => "required",
            // 'password' => "required",
        ]);
        if ($validator->fails()) {
            return $this->sendError('အချက်အလက် များ မှားယွင်း နေပါသည်။');
        }

        $employee = Employee::find($request->employee_id);

        if (empty($employee)) {
            return $this->sendError('Employee not found!');
        }

        if ($request->hasfile('photo')) {

            $image = $request->file('photo');
            $name = $image->getClientOriginalName();
            $image->move(public_path() . '/image/employee/', $name);
            $image = $name;
            $employee->photo = $image;
            $employee->save();
        }

        $employee->name = $request->name;
        $employee->address = $request->address;
        $employee->phone = $request->phone;
        $employee->email = $request->email;
        $employee->nrc = $request->nrc;
        $employee->salary = $request->salary;
        $employee->save();

        $user = User::find($employee->user_id);

        if (empty($user)) {
            return $this->sendError('User not found!');
        }

        // $user->removeRole($user->roles[0]); // minnhtetzaw

        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = \Hash::make($request->password);
        $user->save();

        if ($request->has('role')) {

            $user->assignRole($request->role);
            $user->save();
        }

        return $this->sendResponse('employee', $employee);

    }

    // public function testuser($id){
    //     $employee = Employee::find($id);

    //     $user = User::find($employee->user_id);

    //     dd($user->roles['name']);
    // }
}
