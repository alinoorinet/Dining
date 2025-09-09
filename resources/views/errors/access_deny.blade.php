@extends('layouts.cms')
@section('content')
    <div class="col-sm-12">
        <div class="card text-center text-white bg-dark mb-3">
            <div class="card-header">محدودیت دسترسی</div>
            <div class="card-body">
                <h4 class="card-title">دسترسی شما به این صفحه امکان پذیر نیست</h4>
                @if(Auth::check())
                        {{--<a class="btn btn-outline-info" href="/home">برگشت به پنل کاربری</a>|--}}
                        <a class="btn btn-outline-info" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">خروج<i class="fa fa-power-off fa-lg pull-left"></i></a>
                        <form id="logout-form" action="/logout" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                @else
                        <a class="btn btn-outline-info" href="/">برگشت به صفحه اصلی</a>
                @endif
            </div>
        </div>

    </div>
@endsection