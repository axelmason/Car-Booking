@extends('layouts.app')

@section('title', 'баланс')

@section('content')
    <div class="container my-5">
        <div class="d-flex align-items-center flex-column">
            @if (Session::has('success'))
                <div class="col-4"><div class="alert alert-success">{{ Session::get('success') }}</div></div>
            @endif
            <h3>Ваш баланс: <span class="current_balance">{{ auth()->user()->balance }}</span> руб. <span class="to_balance"></span></h3>
            <form action="{{ route('addBalance') }}" method="post">
                @csrf
                <input type="text" name="balance" placeholder="Сумма для пополнения" class="form-control my-2">
                <button type="submit" class="btn btn-outline-success w-100">Добавить</button>
            </form>
        </div>
    </div>
@endsection

@section('customJS')
    <script>
        $(document).ready(function () {
            $('input[name=balance]').on('keyup', function() {
                let sum = Number($('.current_balance').html())+Number($(this).val());
                if($(this).val() == '') {
                    $('.to_balance').hide();
                } else {
                    $('.to_balance').show();
                    $('.to_balance').html('-> '+sum+' руб.');
                }
            });
        });
    </script>
@endsection
