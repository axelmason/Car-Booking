@extends('layouts.app')

@section('title', 'Регистрация')

@section('content')
    <div class="container">
        <div class="d-flex flex-column align-items-center">
            <h1 class="my-5">Регистрация</h1>
            <form action="{{ route('auth.register') }}" method="post" class="d-flex flex-column col-4">
                @csrf
                @error('email')
                    <div class="text-danger auth-error-message">{{ $message }}</div>
                @enderror
                <input type="email" name="email" placeholder="Email" class="form-control mb-2">
                @error('login')
                    <div class="text-danger auth-error-message">{{ $message }}</div>
                @enderror
                <input type="login" name="login" placeholder="Логин" class="form-control mb-2">
                @error('password')
                    <div class="text-danger auth-error-message">{{ $message }}</div>
                @enderror
                <input type="password" name="password" placeholder="Пароль" class="form-control mb-2">
                <input type="password" name="password_confirmation" placeholder="Подтвердите пароль" class="form-control mb-2">
                <button type="submit" class="btn btn-outline-success">Зарегистрироваться</button>
            </form>
        </div>
    </div>
@endsection
