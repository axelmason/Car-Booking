<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @yield('customCSS')
    <title>@yield('title')</title>
</head>
<body>
    <header class="header bg-dark">
        <div class="links d-flex align-items-center justify-content-around">
            <div class="logo">
                <a href="{{ route('index') }}" class="nav-link bg-white"><img src="{{ asset('img/car-svgrepo-com.svg') }}" alt="" width="50"></a>
            </div>
            <div class="nav__links d-flex">
                <a href="{{ route('index') }}" class="nav-link">Главная</a>
                <a href="{{ route('index') }}" class="nav-link">Забронировать</a>
            </div>
            <div class="auth__links d-flex align-items-center">
                @guest
                    <a href="{{ route('auth.registerPage') }}" class="btn btn-outline-light mx-2">Регистрация</a>
                    <a href="{{ route('auth.loginPage') }}" class="btn btn-outline-light mx-2">Войти</a>
                @endguest
                @if (auth()->check())
                    @if (auth()->user()->login == 'admin')
                        <a href="{{ route('admin.index') }}" class="btn btn-outline-light mx-2">Администрирование</a>
                    @endif
                @endif
                @auth
                    <a href="{{ route('balancePage') }}" class="text-light mx-2">Баланс: {{ auth()->user()->balance }} руб.</a>
                    <a href="https://t.me/{{ config('app.telegram_bot') }}?start={{ strrev(auth()->user()->token) }}" class="btn btn-outline-primary mx-2">Использовать телеграмм</a>
                    <a href="{{ route('logout') }}" class="text-danger text-decoration-none mx-2">Выйти</a>
                @endauth
            </div>
        </div>
    </header>

    @yield('content')

    <script src="https://code.jquery.com/jquery-3.6.0.js" integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="{{ url('js/app.js') }}"></script>
    @yield('customJS')
</body>
</html>
