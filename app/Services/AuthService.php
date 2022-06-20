<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public static function register(RegisterRequest $r) : bool
    {
        $validate = $r->validated();
        $create = User::create($validate);
        $token = Hash::make($create->login);
        $edited = str_replace(['/', ',', '.', '?', '$'], '', $token);
        $create->token = $edited;
        $create->save();
        if($create) {
            return Auth::attempt($validate);
        }
        return false;
    }

    public static function login(Request $r) : bool
    {
        return Auth::attempt(['login' => $r->login, 'password' => $r->password]);
    }
}
