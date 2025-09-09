@extends('layouts.app')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">لیست کارت های چاپ شده
                {{--<a href="/home/actions/add" class="btn btn-outline-success pull-left" title="افزودن اکشن جدید به ماژول">
                    <i class="fa fa-plus-circle pull-right"></i>افزودن
                </a>--}}
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th colspan="4" class="text-center">تعداد کارت های چاپ شده <div class="badge badge-info font-weight-bold" style="font-size: 16px">{{$cardCount}}</div></th>
                    </tr>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">شماره کارت</th>
                        <th class="text-center">نام کاربری</th>
                        <th class="text-center">تاریخ ایجاد</th>
                        <th class="text-center">حذف</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cards as $card)
                        <tr>
                            <td class="text-center">{{$loop->count - ($loop->index)}}</td>
                            <td class="text-center">{{$card->cardNumber}}</td>
                            <td class="text-center">{{$card->username}}</td>
                            <td class="text-center">{{$card->created_at()}}</td>
                            <td class="text-center"><a class="btn btn-link" href="/welcome/card/delete/{{$card->id}}"><i class="fa fa-trash-o text-danger"></i></a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection