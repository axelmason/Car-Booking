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
        $rows = [];

        if ($back_button == true) {
            $rows [] = ['_' => 'keyboardButtonRow', 'buttons' => [['_' => 'keyboardButton', 'text' => "В главное меню"]]];
        }

        if(gettype($buttons) == 'array') {
            foreach ($buttons as $button) {
                $rows [] = ['_' => 'keyboardButtonRow', 'buttons' => [['_' => 'keyboardButton', 'text' => $button]]];
            }
        } else {
            $rows [] = ['_' => 'keyboardButton', 'text' => [['_' => 'keyboardButton', 'text' => $buttons]]];
        }

        return ['_' => 'replyKeyboardMarkup', 'resize' => true, 'rows' => $rows];
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
                if($car->booking_date.' '.$car->booking_time > date('Y-m-d H:i:s')) {
                    $cars_buttons[] = $car->name;
                    parent::$cars_list[$car->name]['car_id'] = $car->id;
                    parent::$cars_list[$car->name]['car_name'] = $car->name;
                }
            }
            if(!empty($cars_buttons)) {
                $message = 'Доступные автомобили для поездки';
                $keyboard = TelegramService::makeKeyBoard($update, $cars_buttons, false);
            } else {
                $message = 'Автомобилей пока нет';
            }
        } else {
            $message = 'Автомобилей пока нет';
        }

        return self::messageParamsGenerate($update, $message.(isset($balance_string) ? $balance_string : null), $keyboard ?? null);
    }
}
