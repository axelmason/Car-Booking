<?php

namespace App\Http\Controllers;

use App\Models\Car;
use App\Models\Seat;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\CarService;

class AdminController extends Controller
{
    public function index()
    {
        return view('admin.index');
    }

    public function carsPage()
    {
        return view('admin.carsPage');
    }

    public function addCarPage()
    {
        return view('admin.addCar');
    }

    public function addCar(Request $r)
    {
        $create = CarService::create($r);
        return to_route('admin.carsPage')->with('success', 'Автомобиль <b>'. $r->name .'</b> добавлен.');
    }

    public function editCarPage(int $car_id)
    {
        $car = Car::find($car_id);
        return view('admin.editCarPage', compact('car'));
    }

    public function editCar(int $car_id, Request $r)
    {
        CarService::edit($car_id, $r);
        return to_route('admin.carsPage')->with('success', 'Информация об автомобиле '.$r->name.' изменена.');
    }

    public function deleteCar(int $car_id)
    {
        $car = CarService::deleteCar($car_id);
        return to_route('admin.carsPage')->with('success', 'Автомобиль '.$car->name.' в корзине.');
    }

    public function carTrashPage()
    {
        $cars = CarService::getTrashed();
        return view('admin.carsTrash', compact('cars'));
    }

    public function restoreCar(int $car_id)
    {
        $car = CarService::restoreCar($car_id);
        return to_route('admin.carsPage')->with('success', 'Автомобиль '.$car->name.' восстановлен.');
    }
}
