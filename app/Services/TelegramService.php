<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Handlers\TelegramEventHandler;


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
            $back_row = ['_' => 'keyboardButtonRow', 'buttons' => [$back_button]];
        }
        $keyboard_button_row = ['_' => 'keyboardButtonRow', 'buttons' => $buttons_list];
        return ['_' => 'replyKeyboardMarkup', 'resize' => true, 'rows' => [$keyboard_button_row, isset($back_row) ? $back_row : null]];
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
        return $message;
    }
}
