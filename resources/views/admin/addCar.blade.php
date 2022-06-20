@extends('layouts.app')

@section('title', 'Добавить автомобиль')

@section('content')
    <div class="container">
        <div class="d-flex flex-column align-items-center">
            <h1 class="my-5">Добавить автомобиль</h1>
            <form action="{{ route('admin.addCar') }}" method="POST" class="car__form d-flex flex-column col-4">
                @csrf
                <div class="form-group my-2">
                    <input type="text" name="name" placeholder="Название автомобиля" class="form-control">
                    <div class="form-text">Например, Chevrolet Cruze</div>
                </div>
                <div class="form-group my-2 row">
                    <div class="col-6">
                        <input type="number" name="seats" placeholder="Количество мест" min="1" max="15"
                            class="form-control">
                        <div class="form-text">Нельзя будет изменить.</div>
                    </div>
                    <div class="col-6">
                        <input type="number" name="seat_price" placeholder="Цена за место"
                            class="form-control">
                        <div class="form-text">У всех одинаковое.</div>
                    </div>
                </div>
                <div class="form-group my-2 row">
                    <div class="col-6">
                        <label for="date">Дата поездки</label>
                        <input type="date" name="date" class="form-control">
                        <div class="form-text">Точный день поездки</div>
                    </div>
                    <div class="col-6">
                        <label for="date">Время поездки</label>
                        <input type="time" name="time" class="form-control">
                        <div class="form-text">Точное время поездки</div>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary my-2">Добавить автомобиль</button>
            </form>
        </div>
    </div>
@endsection
