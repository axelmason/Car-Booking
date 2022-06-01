@extends('layouts.app')

@section('title', 'Авторизация')

@section('content')
    <div class="container">
        <div class="d-flex flex-column align-items-center">
            <h1 class="my-5">Авторизация</h1>
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('auth.login') }}" method="post" class="d-flex flex-column col-4">
                @csrf
                @error('login')
                    <div class="text-danger auth-error-message">{{ $message }}</div>
                @enderror
                <input type="login" name="login" placeholder="Логин" class="form-control mb-2">
                @error('password')
                    <div class="text-danger auth-error-message">{{ $message }}</div>
                @enderror
                <input type="password" name="password" placeholder="Пароль" class="form-control mb-2">
                <button type="submit" class="btn btn-outline-success">Войти</button>
            </form>
        </div>
    </div>
@endsection
