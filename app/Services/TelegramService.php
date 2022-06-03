<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Handlers\TelegramEventHandler;
use App\Events\Booking;


class TelegramService extends TelegramEventHandler
{
    public static function makeKeyBoard(array $update, $buttons, bool $back_button = false)
    {
        $buttons_list = [];
        if(gettype($buttons) == 'array') {
            foreach ($buttons as $button) {
                $buttons_list [] = ['_' => 'keyboardButton', 'text' => $button];
            }
        } else {
            $buttons_list [] = ['_' => 'keyboardButton', 'text' => $buttons];
        }
        if ($back_button == true) {
            $back_button = ['_' => 'keyboardButton', 'text' => "В главное меню"];
        } else {
            $back_button = ['_' => 'keyboardButton', 'text' => null];
        }
        $keyboard_button_row = ['_' => 'keyboardButtonRow', 'buttons' => $buttons_list];
        $back_row = ['_' => 'keyboardButtonRow', 'buttons' => [$back_button]];
        return ['_' => 'replyKeyboardMarkup', 'resize' => true, 'rows' => [$keyboard_button_row, $back_row]];
    }

    public static function messageParamsGenerate(array $update, string $message, array $keyboard = null)
    {
        return ['peer' => $update, 'message' => $message, 'parse_mode' => 'HTML', 'reply_markup' => $keyboard];
    }

    public static function seatBooking($update)
    {
        $seat = Seat::where('car_id', parent::$current_car[$update['message']['from_id']['user_id']]['car_info']['car_id'])->where('seat_number', parent::$current_car[$update['message']['from_id']['user_id']]['car_info']['seat_number'])->first();
        $telegram_user = User::where('telegram_id', $update['message']['from_id']['user_id'])->first();
        $car = Car::where('id', parent::$current_car[$update['message']['from_id']['user_id']]['car_info']['car_id'])->first();
        if ($telegram_user->balance >= $car->seat_price) {
            $telegram_user->balance -= $car->seat_price;
            $seat->user_id = $telegram_user->id;
            $seat->save();
            $telegram_user->save();
            $message = "Место забронировано.";
        } else {
            $message = "Недостаточно средств.";
        }
        event(new Booking([$seat]));
        return $message;
    }

    public static function menu($update)
    {
        $user = User::where('telegram_id', $update['message']['from_id']['user_id'])->first();
        if(!empty($user)) {
            $balance_string = "\nВаш баланс: $user->balance руб.";
        }
        $cars = Car::all();
        $cars_buttons = [];
        if ($cars->count() > 0) {
            foreach ($cars as $car) {
                $cars_buttons[] = $car->name;
                parent::$cars_list[$car->name]['car_id'] = $car->id;
                parent::$cars_list[$car->name]['car_name'] = $car->name;
            }
            $message = 'Выберите автомобиль';
            $keyboard = TelegramService::makeKeyBoard($update, $cars_buttons, true);
        } else {
            $message = 'Автомобилей пока нет';
        }
        return self::messageParamsGenerate($update, $message.$balance_string ?? null, $keyboard ?? null);
    }
}
