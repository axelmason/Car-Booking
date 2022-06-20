<?php

namespace App\Http\Controllers;

use App\Console\Commands\TelegramListener;
use App\Models\Car;
use App\Models\Seat;
use App\Services\BookingService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use \DefStudio\Telegraph\Models\TelegraphChat;
use Illuminate\Support\Facades\Artisan;
use Telegram\Bot\Api;
use TelegramBot\Api\Client;
use danog\MadelineProto\Settings;
use App\Handlers\TelegramEventHandler;
use App\Services\DrawService;

// if (\file_exists('vendor/autoload.php')) {
//     include 'vendor/autoload.php';
// } else {
//     if (!\file_exists('madeline.php')) {
//         \copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
//     }
//     include 'madeline.php';
// }

class HomeController extends Controller
{
    public function index()
    {
        $cars = Car::all();
        return view('index', \compact('cars'));
    }

    public function detail(int $car_id)
    {
        $car = Car::find($car_id);
        return view('detail', compact('car'));
    }

    public function booking(Request $r)
    {
        $result = BookingService::booking($r);
        return $result;
    }

    public function balancePage()
    {
        return view('balance');
    }

    public function addBalance(Request $r)
    {
        UserService::addBalance($r);
        return to_route('balancePage')->with('success', 'Баланс пополнен на '. $r->balance. ' руб.');
    }
}
