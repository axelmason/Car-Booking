@extends('layouts.app')

@section('title', 'Администрирование')

@section('content')
    <div class="d-flex flex-column align-items-center">
        <h1 class="my-5">Регистрация</h1>
        <div class="admin-block d-flex flex-column align-items-center">
            <a href="{{ route('admin.carsPage') }}" class="admin-block-text">
                <img src="{{ asset('img/car_seat_icon_137808.svg') }}" alt="" width="100">
                <div class="">Автомобили</div>
            </a>
        </div>

    </div>
@endsection
