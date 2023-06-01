<?php

use Illuminate\Database\Seeder;
use App\Role;
use App\User;

class UserTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $owner = Role::where('name', 'owner')->first();
		$users = User::create([
			'name' => 'owner',
			'email' => 'owner@gmail.com',
			'password' => \Hash::make('1234567890'),
			'remember_token' => Str::random(60),
		]);
		$users->assignRole($owner);
		$users->save();
    }
}
