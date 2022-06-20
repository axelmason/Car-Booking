<?php

/**
 * Example combined event handler bot.
 *
 * Copyright 2016-2020 Daniil Gentili
 * (https://daniil.it)
 * This file is part of MadelineProto.
 * MadelineProto is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.
 * MadelineProto is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 * You should have received a copy of the GNU General Public License along with MadelineProto.
 * If not, see <http://www.gnu.org/licenses/>.
 *
 * @author    Daniil Gentili <daniil@daniil.it>
 * @copyright 2016-2020 Daniil Gentili <daniil@daniil.it>
 * @license   https://opensource.org/licenses/AGPL-3.0 AGPLv3
 *
 * @link https://docs.madelineproto.xyz MadelineProto documentation
 */

namespace App\Handlers;

use App\Models\User;
use App\Models\Car;
use App\Models\Seat;
use App\Services\DrawService;
use danog\MadelineProto\API;
use danog\MadelineProto\EventHandler;
use danog\MadelineProto\Logger;
use App\Services\TelegramService;
use PhpOption\None;
use PhpParser\JsonDecoder;

include 'current_cars.json';

/*
 * Various ways to load MadelineProto
 */
// if (\file_exists('vendor/autoload.php')) {
//     include 'vendor/autoload.php';
// } else {
//     if (!\file_exists('madeline.php')) {
//         \copy('https://phar.madelineproto.xyz/madeline.php', 'madeline.php');
//     }
//     include 'madeline.php';
// }

/**
 * Event handler class.
 */
class TelegramEventHandler extends EventHandler
{
    /**
     * @var int|string Username or ID of bot admin
     */
    const ADMIN = 762586481; // Change this
    /**
     * Get peer(s) where to report errors.
     *
     * @return int|string|array
     */
    public function getReportPeers()
    {
        return [self::ADMIN];
    }
    /**
     * Handle updates from supergroups and channels.
     *
     * @param array $update Update
     *
     * @return \Generator
     */
    public function onUpdateNewChannelMessage(array $update): \Generator
    {
        return $this->onUpdateNewMessage($update);
    }

    static $cars_list = [];
    static $current_car;
    public $seats_list = [];
    /**
     * Handle updates from users.
     *
     * @param array $update Update
     *
     * @return \Generator
     */
    public function onUpdateNewMessage(array $update): \Generator
    {
        $j = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . 'current_cars.json', true);
        self::$current_car = json_decode($j, true);

        if ($update['message']['_'] === 'messageEmpty' || $update['message']['out'] ?? false) {
            return;
        }

        if ($update['message']['message'] == stristr($update['message']['message'], '/start')) {
            $explode = explode(' ', $update['message']['message']);
            if (isset($explode[1])) {
                $user = User::where('token', strrev($explode[1]))->first();
                $user->telegram_id = $update['message']['from_id']['user_id'];
                $user->save();
            }
            $message = TelegramService::menu($update);
            \print_r($message);
            yield $this->messages->sendMessage($message);
        }

        if (array_key_exists($update['message']['message'], self::$cars_list)) {
            self::$current_car[$update['message']['from_id']['user_id']] = ['car_info' => self::$cars_list[$update['message']['message']]];
            $car = Car::find(self::$cars_list[$update['message']['message']]['car_id']);
            $seats = Seat::where('car_id', self::$cars_list[$update['message']['message']]['car_id'])->where('user_id', null)->get();
            $seats_buttons = [];
            if ($seats->count() > 0) {
                foreach ($seats as $seat) {
                    $seats_buttons[] = strval($seat->seat_number);
                    $this->seats_list['seats_number'][] = $seat->seat_number;
                }
                $keyboard = TelegramService::makeKeyBoard($update, $seats_buttons, true);
                $message = "Выберите место\nЦена за место: " . strval($car->seat_price) . " руб.";
            } else {
                $message = "Нет свободных мест";
            }
            yield $this->messages->sendMessage(TelegramService::messageParamsGenerate($update, $message, isset($keyboard) ? $keyboard : null));
        }

        if ((isset($this->seats_list['seats_number']) ? in_array($update['message']['message'], $this->seats_list['seats_number']) : '') && in_array($update['message']['from_id']['user_id'], array_keys(self::$current_car)) && !empty(self::$current_car[$update['message']['from_id']['user_id']])) {
            self::$current_car[$update['message']['from_id']['user_id']]['car_info']['seat_number'] = $update['message']['message'];
            $car = Car::find(self::$current_car[$update['message']['from_id']['user_id']]['car_info']['car_id']);
            $keyboard = TelegramService::makeKeyBoard($update, ["Да"], true);
            $message = "Выбрано: " . $update['message']['message'] . " место\nАвтомобиль: " . self::$current_car[$update['message']['from_id']['user_id']]['car_info']['car_name'] . "\nСтоимость брони: $car->seat_price руб.\n<strong>Забронировать?</strong>";
            yield $this->messages->sendMessage(TelegramService::messageParamsGenerate($update, $message, $keyboard));
        }

        if ($update['message']['message'] == 'Да' && isset(self::$current_car[$update['message']['from_id']['user_id']]) && !empty(self::$current_car[$update['message']['from_id']['user_id']])) {
            if (!empty(User::where('telegram_id', $update['message']['from_id']['user_id'])->first())) {
                $message = TelegramService::seatBooking($update);
            } else {
                $message = "Ваш Telegram аккаунт не привязан к сайту\n[Перейти на сайт](vk.com/feed)";
            }
            yield $this->messages->sendMessage(TelegramService::messageParamsGenerate($update, $message));
        }


        if ($update['message']['message'] == "В главное меню") {
            $user = User::where('telegram_id', $update['message']['from_id']['user_id'])->first();
            self::$current_car[$update['message']['from_id']['user_id']] = [];
            self::$cars_list = [];
            self::$cars_list = [];
            $message = TelegramService::menu($update);
            yield $this->messages->sendMessage($message);
        }

        $encode = json_encode(self::$current_car, JSON_PRETTY_PRINT);
        file_put_contents(__DIR__ . DIRECTORY_SEPARATOR . 'current_cars.json', $encode);

        yield $this->winnerMessage($update);
    }

    public function winnerMessage()
    {
        $cars = Car::all();
        foreach ($cars as $car) {
            if ($car->booking_date . ' ' . $car->booking_time <= date('Y-m-d H:i:s') && $car->winner_seat == null) {
                $random_seat = Seat::where('car_id', $car->id)->where('user_id', '!=', null)->get()->random(1)[0]->seat_number;
                $car->winner_seat = $random_seat;
                $car->save();
                $winner = Seat::where('seat_number', $random_seat)->where('car_id', $car->id)->first()->user->telegram_id;
                $message = "Ваше место №$random_seat в автомобиле $car->name оказалось счастливым!";
                return $this->messages->sendMessage(['peer' => $winner, 'text' => $message]);
            }
        }
    }
}

$MadelineProtos = [];
foreach ([
    'bot.madeline' => 'Bot Login',
] as $session => $message) {
    Logger::log($message, Logger::WARNING);
    $MadelineProtos[] = new API($session);
}

API::startAndLoopMulti($MadelineProtos, TelegramEventHandler::class);
