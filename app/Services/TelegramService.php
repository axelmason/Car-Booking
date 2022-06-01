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
    public $current_car;
    public $cars_list;
    public $seats_list;

    public function __construct($current_car, $cars_list, $seats_list)
    {
        $this->current_car = $current_car;
        $this->cars_list = $cars_list;
        $this->seats_list = $seats_list;
    }

    /**
     * /start command
     *
     * @param  array $update
     * @return 'Message Params'
     */
    public function startCommand($update)
    {
        $explode = explode(' ', $update['message']['message']);
        if (isset($explode[1])) {
            $user = TelegramService::selectUser($update, 'token', strrev($explode[1]));
            $user->telegram_id = $update['message']['from_id']['user_id'];
            $user->save();
            $message = "Добро пожаловать, $user->login!\nВаш баланс: $user->balance";
        } else {
            $message = "Добро пожаловать";
        }
        $keyboard = $this->makeKeyBoard($update, 'Выбрать автомобиль', false);
        return $this->messageParamsGenerate($update, $message, isset($keyboard) ? $keyboard : null);
    }

    /**
     * Selects a car, sends a message and pushes in the $cars_list array
     *
     * @param  array $update
     * @param  array $cars_list
     * @return message_params
     */
    public function selectCar(array $update)
    {
        $cars = Car::all();
        $cars_buttons = [];
        if ($cars->count() > 0) {
            foreach ($cars as $car) {
                $cars_buttons [] = $car->name;
                $this->cars_list[$car->name]['car_id'] = $car->id;
                $this->cars_list[$car->name]['car_name'] = $car->name;
            }
            $keyboard = $this->makeKeyBoard($update, $cars_buttons, true);
            $message = 'Выберите автомобиль';
        } else {
            $message = 'Автомобилей пока нет';
        }
        $this->updateCarsList($update, $this->cars_list);
        return $this->messageParamsGenerate($update, $message, isset($keyboard) ? $keyboard : null);
    }

    public function selectSeat(array $update, $current_car)
    {
        $current_car[$update['message']['from_id']['user_id']] = ['car_info' => $this->cars_list[$update['message']['message']]];
        \print_r($current_car);
        $car = Car::find($this->cars_list[$update['message']['message']]['car_id']);
        $seats = Seat::where('car_id', $this->cars_list[$update['message']['message']]['car_id'])->where('user_id', null)->get();
        $seats_buttons = [];
        if($seats->count() > 0) {
            foreach ($seats as $seat) {
                $seats_buttons [] = $seat->seat_number;
                $this->seats_list['seats_number'][] = $seat->seat_number;
            }
            $message = "Выберите место\nЦена за место: " . strval($car->seat_price) . " руб.";
            $keyboard = $this->makeKeyBoard($update, $seats_buttons, true);
        } else {
            \print_r('123');
        }
        $this->updateCurrentCar($update, $current_car);
        return $this->messageParamsGenerate($update, $message, isset($keyboard) ? $keyboard : null);
    }

    /**
     * Select user using $p1 and $p2 params
     *
     * @param  array $update
     * @param  string $o1
     * @param  string $o2
     * @return User
     */
    public function selectUser(array $update, string $p1, string $p2): User
    {
        return User::where($p1, $p2)->first();
    }

    /**
     * Generates message params
     *
     * @param  array $update
     * @param  string $message
     * @param  array $keyboard
     * @return void
     */
    public function messageParamsGenerate(array $update, string $message, array $keyboard = null)
    {
        return ['peer' => $update, 'message' => $message, 'parse_mode' => 'HTML', 'reply_markup' => $keyboard];
    }
    
    /**
     * Generates keyboard
     *
     * @param  array $update
     * @param  array|string $buttons
     * @param  bool $back_button
     * @return array Keyboard Params
     */
    public function makeKeyBoard(array $update, $buttons, bool $back_button = false)
    {
        $buttons_list = [];
        if(gettype($buttons) == 'array') {
            foreach ($buttons as $button) {
                $buttons_list [] = ['_' => 'keyboardButton', 'text' => $button];
            }
        } elseif(gettype($buttons) == 'string') {
            $buttons_list [] = ['_' => 'keyboardButton', 'text' => $buttons];
        }
        if ($back_button == true) {
            $back_button = ['_' => 'keyboardButton', 'text' => "В главное меню"];
            $back_row = ['_' => 'keyboardButtonRow', 'buttons' => [$back_button]];
        }
        $keyboard_button_row = ['_' => 'keyboardButtonRow', 'buttons' => $buttons_list];
        return ['_' => 'replyKeyboardMarkup', 'resize' => true, 'rows' => [$keyboard_button_row, isset($back_row) ? $back_row : null]];
    }
}
