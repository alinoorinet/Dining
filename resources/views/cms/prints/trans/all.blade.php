@extends('layouts.cms')
@section('print')
    <style>
        .container-fluid,
        .navbar.navbar-expand-lg.navbar-dark.fixed-top {
            display: none;
        }
        body{
            padding-top: 0;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div class="col-sm-12 text-center align-items-center">
                            <img class="img-fluid" style="max-height: 80px" src="/img/print-header.png">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-6" id="transCounter1">
                        <div class="card mt-1">
                            <div class="card-body">
                                <div class="card-text text-center text-info" style="font-size: 32px">اینترنتی {{$sum1}} ریال</div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="table-responsive mt-1">
                    <table class="table table-responsive table-striped table-sm">
                        <thead>
                        <tr>
                            <th class="text-center" colspan="5">لیست تراکنش های درگاه پرداخت</th>
                        </tr>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">نام</th>
                            <th class="text-center">کد پیگیری</th>
                            <th class="text-center">مبلغ پرداخت شده(ریال)</th>
                            <th class="text-center">تاریخ پرداخت</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php $i=1; ?>
                        @foreach($trans as $data)
                            <tr>
                                <td>{{$i}}</td>
                                <td class="text-center">{{$data->name}}</td>
                                <td class="text-center">{{$data->reference_id}}</td>
                                <td class="text-center">{{$data->amount}}</td>
                                <td class="text-center">{{$data->created_at}}</td>
                            </tr>
                            <?php $i++; ?>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
