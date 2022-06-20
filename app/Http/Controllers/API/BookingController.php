<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Car;
use App\Models\Seat;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Events\Booking;

class BookingController extends \App\Http\Controllers\Controller
{
    public function booking(Request $r)
    {
        $car = Car::find($r->car_id);
        $seat = Seat::where('car_id', $r->car_id)->where('seat_number', $r->seat_number)->first();
        $user = User::find($r->user_id);

        if(date('Y-m-d H:i:s') <= $car->booking_date.' '.$car->booking_time) {
            if ($user->balance >= $car->seat_price) {
                if ($seat->user_id == null) {
                    $seat->user_id = $r->user_id;
                    $user->balance -= $car->seat_price;
                    $seat->save();
                    $user->save();
                } else {
                    return response()->json(['message' => 'Seat already booked.'], 409);
                }
                event(new Booking([$seat]));
                return response()->json(['code' => 200, 'message' => "Seat booked.", 'seat' => $seat], 200);
            } else {
                return response()->json(['message' => 'You don`t have enought money.'], 402);
            }
        }
        return response()->json([
            'message' => 'Trip was already started. Time is over.',
            'trip_time' => $car->booking_date.' '.$car->booking_time,
            'current_time' => date('Y-m-d H:i:s')
        ], 410);
    }
}
