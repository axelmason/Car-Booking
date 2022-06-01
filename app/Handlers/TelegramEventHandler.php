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

    public $cars_list;
    public $current_car;
    public $seats_list;
    /**
     * Handle updates from users.
     *
     * @param array $update Update
     *
     * @return \Generator
     */
    public function onUpdateNewMessage(array $update): \Generator
    {
        $j = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'current_cars.json', true);
        $this->current_car = json_decode($j, true);
        $service = new TelegramService($this->current_car, $this->cars_list, $this->seats_list);

        if ($update['message']['_'] === 'messageEmpty' || $update['message']['out'] ?? false) {
            return;
        }

        if ($update['message']['message'] == stristr($update['message']['message'], '/start')) {
            $message = $service->startCommand($update);
            yield $this->messages->sendMessage($message);
        }

        if ($update['message']['message'] == 'Выбрать автомобиль') {
            $message = $service->selectCar($update, $this->cars_list);
            yield $this->messages->sendMessage($message);
        }

        if (array_key_exists($update['message']['message'], $this->cars_list)) {
            print_r('123');
            $message = $service->selectSeat($update, $this->current_car);
            yield $this->messages->sendMessage($message);
        }

        if ((isset($this->seats_list['seats_number']) ? in_array($update['message']['message'], $this->seats_list['seats_number']) : '') && in_array($update['message']['from_id']['user_id'], array_keys($this->current_car)) && !empty($this->current_car[$update['message']['from_id']['user_id']])) {
            $this->current_car[$update['message']['from_id']['user_id']]['car_info']['seat_number'] = $update['message']['message'];
            $car = Car::find($this->current_car[$update['message']['from_id']['user_id']]['car_info']['car_id'])->first();
            $yes_buttons = ['_' => 'keyboardButton', 'text' => "Да"];
            $keyboardButtonRow = ['_' => 'keyboardButtonRow', 'buttons' => [$yes_buttons]];
            $keyboardButtonRowBack = ['_' => 'keyboardButtonRow', 'buttons' => [['_' => 'keyboardButton', 'text' => "В главное меню"]]];
            $replyKeyboardMarkup = ['_' => 'replyKeyboardMarkup', 'resize' => true, 'rows' => [$keyboardButtonRow, $keyboardButtonRowBack]];
            yield $this->messages->sendMessage(['peer' => $update['message']['from_id']['user_id'], 'message' => "Выбрано: " . $update['message']['message'] . " место\nАвтомобиль: " . $this->current_car[$update['message']['from_id']['user_id']]['car_info']['car_name'] . "\nСтоимость брони: $car->seat_price руб.\n<strong>Забронировать?</strong>", 'parse_mode' => 'HTML', 'reply_markup' => isset($replyKeyboardMarkup) ? $replyKeyboardMarkup : null]);
        }

        if ($update['message']['message'] == 'Да' && isset($this->current_car[$update['message']['from_id']['user_id']]) && !empty($this->current_car[$update['message']['from_id']['user_id']])) {
            if (!empty(User::where('telegram_id', $update['message']['from_id']['user_id'])->first())) {
                $seat = Seat::where('car_id', $this->current_car[$update['message']['from_id']['user_id']]['car_info']['car_id'])->where('seat_number', $this->current_car[$update['message']['from_id']['user_id']]['car_info']['seat_number'])->first();
                $telegram_user = User::where('telegram_id', $update['message']['from_id']['user_id'])->first();
                $car = Car::where('id', $this->current_car[$update['message']['from_id']['user_id']]['car_info']['car_id'])->first();
                if ($telegram_user->balance >= $car->seat_price) {
                    $telegram_user->balance -= $car->seat_price;
                    $seat->user_id = $telegram_user->id;
                    $seat->save();
                    $telegram_user->save();
                    yield $this->messages->sendMessage(['peer' => $update, 'message' => "Место забронировано.", 'parse_mode' => 'HTML', 'reply_markup' => isset($replyKeyboardMarkup) ? $replyKeyboardMarkup : null]);
                } else {
                    yield $this->messages->sendMessage(['peer' => $update, 'message' => "Недостаточно средств.", 'parse_mode' => 'HTML', 'reply_markup' => isset($replyKeyboardMarkup) ? $replyKeyboardMarkup : null]);
                }
            } else {
                yield $this->messages->sendMessage(['peer' => $update, 'message' => "Ваш Telegram аккаунт не привязан к сайту\n[Перейти на сайт](vk.com/feed)", 'parse_mode' => 'Markdown', 'reply_markup' => isset($replyKeyboardMarkup) ? $replyKeyboardMarkup : null]);
            }
        }


        if ($update['message']['message'] == "В главное меню") {
            $this->current_car[$update['message']['from_id']['user_id']] = [];
            $this->seats_list = [];
            $this->cars_list = [];
            yield $this->mainMenu($update, 'Выберите задачу');
        }

        $encode = json_encode($this->current_car, JSON_PRETTY_PRINT);
        file_put_contents(__DIR__.DIRECTORY_SEPARATOR.'current_cars.json', $encode);
    }

    public function updateCurrentCar($update, $current_car)
    {
        $this->current_car = $current_car;
        print_r($this->current_car);
    }
    public function updateCarsList($update, $cars_list)
    {
        $this->cars_list = $cars_list;
        print_r($this->cars_list);
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
