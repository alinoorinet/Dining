@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="row mt-2">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="card">
                <div class="ui dimmer" id="ddfFormDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-header">فرم افزایش موجودی کیف پول</div>
                <div class="card-body">
                    <div class="card-title bg-light">موجودی من: <strong class="float-left">{{$walletAmount}} ریال</strong></div>
                    <form action="/home/reservation/pay" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="amount">مبلغ (ریال)</label>
                            <input type="text" class="form-control ltr @if($errors->has('amount'))is-invalid @endif" value="{{old('amount')}}" placeholder="1000" name="amount" required>
                            @if($errors->has('amount'))
                                <div class="invalid-feedback">
                                    {{$errors->first('amount')}}
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success">پرداخت آنلاین</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
@endsection
