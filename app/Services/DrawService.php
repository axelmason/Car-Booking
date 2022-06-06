<?php

namespace App\Services;

use App\Handlers\TelegramEventHandler;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Seat;
use App\Models\Car;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DrawService extends TelegramEventHandler
{
    //
}
