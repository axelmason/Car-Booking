<?php

namespace App\Services;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;

class UserService
{
    public static function addBalance(Request $r)
    {
        $val = $r->balance;
        $user = User::find(Auth::id());
        $user->balance += $val;
        $user->save();
    }
}
