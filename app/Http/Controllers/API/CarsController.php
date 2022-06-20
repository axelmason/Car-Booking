<?php

namespace App\Http\Controllers\API;

use App\Http\Requests\CreateCarRequest;
use App\Models\Car;
use App\Models\Seat;
use Illuminate\Http\Request;

class CarsController extends \App\Http\Controllers\Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return response()->json(Car::all(), 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\CreateCarRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CreateCarRequest $request)
    {
        $date = date("Y.m.d", strtotime($request->date));
        $car = Car::create([
            'name' => $request->name,
            'seat_price' => $request->seat_price,
            'booking_date' => $date,
            'booking_time' => $request->time
        ]);

        foreach(range(1, $request->seats) as $i) {
            Seat::create(['car_id' => $car->id, 'seat_number' => $i]);
        }

        return response()->json(['car_id' => $car->id, 'seats' => $car->seats], 201);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $car = Car::find($id);
        if(isset($car)) {
            return response()->json(Car::find($id), 200);
        }
        return response()->json(['message' => "Car not found", 'code' => 404], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $r, $id)
    {
        $car = Car::find($id);
        if(!empty($car)) {
            $changes = null;
            foreach ($r->all() as $k => $v) {
                if(!empty($k) && $v != $car->$k) {
                    $val = $car->$k;
                    $car->update([$k => $v]);
                    $changes [] = [$k => $val.' -> '.$v];
                }
            }
            return response()->json(['code' => 200, 'message' => "Car ".$car->first()->name." updated", 'changes' => $changes], 200);
        }
        return response()->json(['message' => "Car not found", 'code' => 404], 404);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $car = Car::withTrashed($id)->where('id', $id)->first();
        if(!empty($car)) {
            if($car->deleted_at == null) {
                $car->delete();
                return response()->json(['message' => "Car $car->name deleted", 'code' => 200], 200);
            }
            return response()->json(['message' => "Car $car->name already in the trash", 'code' => 417], 417);
        }
        return response()->json(['message' => "Car not found", 'code' => 404], 404);
    }

    /**
     * Restore car from trash.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function restore($id)
    {
        $car = Car::onlyTrashed()->where('id', $id)->first();
        if(!empty($car)) {
            $car->restore();
            $car->save();
            return response()->json(['message' => "Car $car->name restored", 'code' => 200], 200);
        }
        return response()->json(['message' => "Car not found in the trash.", 'code' => 404], 404);
    }

    public function seats($id)
    {
        $seats = Car::find($id)->seats;
        if(isset($seats)) {
            return response()->json(['code' => 200, 'seats' => $seats], 200);
        }
        return response()->json(['message' => "There are no seats in this car.", 'code' => 404], 404);
    }
}
