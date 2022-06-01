@extends('layouts.app')

@section('title', 'Автомобиль '.$car->name.' | Редактирование')

@section('content')
    <div class="container">
        <div class="d-flex flex-column align-items-center">
            <h1 class="my-5">Изменить автомобиль {{ $car->name }}</h1>
            <form action="{{ route('admin.editCar', $car->id) }}" method="POST" class="car__form d-flex flex-column col-4">
                @csrf
                <label for="name">Название автомобиля</label>
                <input type="text" name="name" placeholder="Название автомобиля" class="form-control my-2" value="{{ $car->name }}">
                <div class="form-group my-2 row">
                    <div class="col-6">
                        <input type="number" name="seats" placeholder="Количество мест" min="1" max="15"
                            class="form-control" value="{{ $car->seats->count() }}" disabled>
                        <div class="form-text">Нельзя изменить.</div>
                    </div>
                    <div class="col-6">
                        <input type="number" name="seat_price" placeholder="Цена за место"
                            class="form-control" value="{{ $car->seat_price }}">
                        <div class="form-text">У всех одинаковое.</div>
                    </div>
                </div>
                <div class="form-group my-2 row">
                    <div class="col-6">
                        <label for="date">Дата поездки</label>
                        <input type="date" name="date" class="form-control" value="{{ $car->booking_date }}">
                    </div>
                    <div class="col-6">
                        <label for="date">Время поездки</label>
                        <input type="time" name="time" class="form-control" value="{{ $car->booking_time }}">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary my-2">Сохранить изменения</button>
            </form>
            <form action="{{ route('admin.deleteCar', $car->id) }}" method="post" class="col-4">
                @csrf
                @method('delete')
                <button type="submit" class="btn btn-danger my-2 w-100">Удалить</button>
            </form>
        </div>
    </div>
@endsection
