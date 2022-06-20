<?php

namespace App\Services;

use App\Models\Car;
use App\Models\Seat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CarService
{
    public static function create(Request $r)
    {
        $car = Car::create(['name' => $r->name, 'booking_time' => $r->time, 'booking_date' => $r->date, 'seat_price' => $r->seat_price]);
        foreach(range(1, $r->seats) as $i) {
            Seat::create(['car_id' => $car->id, 'seat_number' => $i]);
        }
    }

    public static function edit(int $car_id, Request $r)
    {
        $car = Car::find($car_id);
        $car->name = $r->name;
        $car->booking_date = $r->date;
        $car->booking_time = $r->time;
        $car->save();
    }

    public static function getTrashed()
    {
        if(!empty(Car::onlyTrashed()->get())) {
            return Car::onlyTrashed()->get();
        }
    }

    public static function restoreCar(int $car_id) : Car
    {
        if(!empty(Car::withTrashed()->where('id', $car_id)->first())) {
            $car = Car::withTrashed()->where('id', $car_id)->first();
            $car->restore();
            $car->save();
            return $car;
        }
    }

    public static function deleteCar(int $car_id) : Car
    {
        $car = Car::find($car_id);
        $car->delete();
        return $car;
    }

    public static function freeSeats(Car $car) : int
    {
        $i = 0;
        foreach ($car->seats as $seat) {
            if ($seat->user_id == null) {
                $i++;
            }
        }
        return $i;
    }
}
