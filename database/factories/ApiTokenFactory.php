<?php
use Faker\Generator as Faker;
use Illuminate\Support\Str;
use Livijn\MultipleTokensAuth\Models\ApiToken;
use Livijn\MultipleTokensAuth\Test\User;

$factory->define(ApiToken::class, function (Faker $faker) {
    return [
        'user_id' => factory(User::class),
        'token' => Str::random(64),
    ];
});
