<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <title>{{config('app.name')}}</title>
    <meta name="description" content="">
    <meta name="Keywords" content="">
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link rel="icon" type="image/png" href="/img/mainLogo.png">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @include('includes.styles')
    @yield('more_style')
</head>
<body>
<div class="container">
    <div id="srchResultBox"></div>
    @if(session()->has('successMsg'))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-success" role="alert">
                    <strong>{{ session('successMsg') }}</strong>
                </div>
            </div>
        </div>
    @elseif(session()->has('warningMsg'))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-warning" role="alert">
                    <strong>{{ session('warningMsg') }}</strong>
                </div>
            </div>
        </div>
    @elseif(session()->has('dangerMsg'))
        <div class="row">
            <div class="col-sm-12">
                <div class="alert alert-danger" role="alert">
                    <strong>{{ session('dangerMsg') }}</strong>
                </div>
            </div>
        </div>
    @endif
    @yield('content')
</div>
@include('includes.scripts')
@yield('more_script')
</body>
</html>
