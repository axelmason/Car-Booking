<?php

namespace App\Services;

use App\Events\Booking;
use App\Models\Car;
use App\Models\Seat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingService
{
    /**
     * Create a new redirect response to a named route.
     *
     * @param  Request $r
     * @return JSON
     */
    public static function booking(Request $r) : object
    {
        $seats = Seat::where('car_id', $r->car_id)->get();
        $seatt = [];
        $user = User::find(Auth::id());
        if(auth()->user()->balance < $r->sum) {
            return response()->json('Недостаточно средств.', 409);
        }
        foreach ($seats as $seat) {
            if (in_array($seat->seat_number, $r->checked)) {
                if ($seat->user_id == null) {
                    $seat->user_id = Auth::id();
                    $user->balance -= $r->seat_price;
                    $seat->save();
                    $user->save();
                    array_push($seatt, $seat);
                } else {
                    return response()->json('Место уже занято.', 409);
                }
            }
            }
        Booking::dispatch($seatt);
        return response()->json($seatt, 200);
    }
}
