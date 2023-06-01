<?php

namespace Database\Factories;
use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;




$factory->define(User::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$YAJi02uPBSCur5Ggg.iqquvyKjNmstFJm711ptppJyMWSHXo5061i', // 1234567890  --  password
        'remember_token' => Str::random(10),
    ];
});

