@extends('layouts.app')

@section('title', 'Автомобиль ' . $car->name)

@section('content')
    <div class="container fs-5">
        <h2 class="text-center my-5">Информация об автомобиле {{ $car->name }}</h2>
        <p>{{ $car->name }}</p>
        <p>Количество свободных мест:
            <span class="free_seats">{{ App\Services\CarService::freeSeats($car) }}</span> из
            {{ $car->seats->count() }}
        </p>
        <p class="d-flex">Дата и время поездки:
            {{ \Carbon\Carbon::parse($car->booking_date)->format('d.m.Y') }}
            {{ \Carbon\Carbon::parse($car->booking_time)->format('H:i') }}
        </p>
        <p>Цена за место: <b class="price_field">{{ $car->seat_price }}</b> руб.</p>
        <div class="error-alert alert alert-danger" style="display: none; width: max-content;">Недостаточно средств.</div>
        <form class="booking-form" data-car-id="{{ $car->id }}">
            <div class="d-flex">
                @foreach ($car->seats as $seat)
                    <input type="checkbox" name="seat_number-{{ $seat->seat_number }}"
                        id="seat_number-{{ $seat->seat_number }}" class="btn-check" autocomplete="off"
                        @if ($seat->user_id !== null) disabled @endif data-id="{{ $seat->seat_number }}">
                    <label for="seat_number-{{ $seat->seat_number }}"
                        class="btn @if (auth()->check()) @if (auth()->user()->id == $seat->user_id) btn-success @else btn-outline-dark @endif @endif me-2 fs-5">{{ $seat->seat_number }}</label>
                @endforeach
            </div>
            <div class="sum_field">Итого: <span>0</span> руб.</div>
            <button type="submit" class="booking-btn btn btn-outline-success my-2"
                style="display: none">Забронировать</button>
        </form>
    </div>
@endsection

@section('customJS')
    <script>
        $(document).ready(function() {
            var check = 0;
            $('input[type=checkbox]').on('change', function() {
                if ($(this).is(':checked')) {
                    check += 1;
                } else if (!$(this).is(':checked')) {
                    check -= 1;
                }
                console.log($('.price_field').html());
                $('.sum_field span').html($('.price_field').html() * check);

                if (check > 0) {
                    $('.booking-btn').show();
                } else {
                    $('.booking-btn').hide();
                }
                return false;
            });

            $('.booking-form').on('submit', function() {
                var car_id = $(this).data('car-id');
                var checked = [];
                $.each($('input[type=checkbox]'), function(indexInArray, valueOfElement) {
                    if ($(valueOfElement).is(':checked')) {
                        checked.push($(this).data('id'));
                        $(this).attr('disabled', 'disabled');
                        $(this).next().removeClass('btn-outline-dark').addClass('btn-success')
                    }
                });
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    type: "POST",
                    url: "{{ route('booking') }}",
                    data: {
                        'checked': checked,
                        'car_id': car_id,
                        'sum': $('.sum_field span').html(),
                        'seat_price': $('.price_field').html()
                    },
                    statusCode: {
                        200: function(response) {
                            $('.booking-btn').hide();
                        },
                        409: function(response) {
                            $.each($('input[type=checkbox]'), function(indexInArray, valueOfElement) {
                                if ($(valueOfElement).is(':checked')) {
                                    checked.push($(this).data('id'));
                                    $(this).attr('checked', false);
                                    $(this).attr('disabled', false);
                                    $(this).next().removeClass('btn-success').addClass('btn-outline-dark');
                                }
                            });
                            $('.error-alert').show();
                        }
                    }
                });
                return false;
            });
            window.Echo.channel('booking')
                .listen('Booking', (e) => {
                    let free_seats = $('.free_seats').html();
                    $.each(e['seats'], function(indexInArray, valueOfElement) {
                        $(`input[data-id|=${valueOfElement.seat_number}]`).attr('disabled', 'disabled');
                        $(`input[data-id|=${valueOfElement.seat_number}]`).next().removeClass(
                            'btn-outline-dark').addClass('btn-success');
                        free_seats--;
                    });
                    $('.free_seats').html(free_seats);
                });
            // channel.listen('Booking', function(data) {

            //     // $.each(checked, function(indexInArray, valueOfElement) {
            //     //     $(valueOfElement).attr('disabled', 'disabled');
            //     //     $(valueOfElement).next().removeClass('btn-outline-dark').addClass('btn-success')
            //     // });
            // });
        });
    </script>
@endsection
