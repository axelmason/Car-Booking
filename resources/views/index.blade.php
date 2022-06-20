@extends('layouts.app')

@section('title', 'Главная')

@section('content')
    <div class="container my-5">
        @if (Session::has('success'))
            <div class="alert alert-success">{{ Session::get('success') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if ($cars->count() > 0)
            <div class="table-responsive">
                <table class="table table-bordered" style="vertical-align: middle; font-size: 18px;">
                    <thead>
                        <th scope="col">#</th>
                        <th scope="col">Автомобиль</th>
                        <th scope="col">Количество мест (всего)</th>
                        <th scope="col">Количество мест (свободных)</th>
                        <th scope="col">Дата и время поездки</th>
                        <th scope="col">Цена за место</th>
                        @if (auth()->check())
                            <th scope="col"></th>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($cars as $car)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $car->name }}</td>
                                <td>{{ $car->seats->count() }}</td>
                                <td>{{ App\Services\CarService::freeSeats($car) }}</td>
                                <td>
                                    {{ \Carbon\Carbon::parse($car->booking_date)->format('d.m.Y') }}
                                    {{ \Carbon\Carbon::parse($car->booking_time)->format('H:i') }}
                                </td>
                                <td>{{ $car->seat_price }} руб.</td>
                                <td class="text-center"><a href="{{ route('detail', $car->id) }}"
                                        class="btn btn-info">Подробнее</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info my-5">Доступных автомобилей пока нет.</div>
        @endif
    </div>
@endsection
