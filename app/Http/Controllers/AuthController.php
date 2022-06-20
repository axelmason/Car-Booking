<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;

class AuthController extends Controller
{
    public function registerPage()
    {
        return view('auth.register');
    }

    public function register(RegisterRequest $r)
    {
        $create = AuthService::register($r);
        if($create) {
            return to_route('index')->with('success', 'Добро пожаловать!');
        }
        return to_route('index')->withErrors(['register' => 'Ошибка регистрации.']);
    }

    public function loginPage()
    {
        return view('auth.login');
    }

    public function login(Request $r)
    {
        $auth = AuthService::login($r);
        if($auth) {
            return to_route('index')->with('success', 'Добро пожаловать!');
        }
        return redirect()->back()->withErrors(['error' => 'Логин или пароль не совпадает!']);
    }

    public function logout()
    {
        Auth::logout();
        return redirect(route('index'));
    }
}
