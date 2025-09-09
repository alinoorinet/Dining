@extends('layouts.cms')
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <h1 class="h3 display">رفع محدودیت عدم پرداخت هزینه خوابگاه</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="card bg-light text-dark">
                <div class="ui dimmer" id="formDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger text-dark d-none" id="formMsg"></div>
                    <form id="searchUserLimits" method="post" action="/home/luhe/store">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label>شناسه کاربری/شماره دانشجویی</label>
                            <input class="form-control @if($errors->has('stdOrUid'))is-invalid @endif" id="stdOrUid" name="stdOrUid">
                            @if($errors->has('stdOrUid'))
                                <div class="invalid-feedback">
                                    {{$errors->first('stdOrUid')}}
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>ترم</label>
                            <input class="form-control @if($errors->has('term'))is-invalid @endif" id="term" name="term" placeholder="971">
                            @if($errors->has('term'))
                                <div class="invalid-feedback">
                                    {{$errors->first('term')}}
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">رفع محدودیت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card mt-3">
                <div class="ui dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">
                    <h4 class="card-title text-dark text-center">کاربرانی که رفع محدودیت شده اند</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th class="text-right">#</th>
                                <th class="text-center">نام</th>
                                <th class="text-center">شماره دانشجویی</th>
                                <th class="text-center">ترم</th>
                                <th class="text-center">ثبت</th>
                                <th class="text-center">وضعیت</th>
                                <th class="text-center">-</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($luhes as $luhe)
                                <tr>
                                    <td class="text-right">{{$loop->index+1}}</td>
                                    <td class="text-center">{{$luhe->user->name}}</td>
                                    <td class="text-center">{{$luhe->user->std_no}}</td>
                                    <td class="text-center">{{$luhe->term}}</td>
                                    <td class="text-center">{{$luhe->created_at()}}</td>
                                    <td class="text-center"><a href="/home/luhe/de-active/{{$luhe->id}}" class="btn btn-link">@if($luhe->active == 1) <i class="fa fa-check-circle"></i> @else <i class="fa fa-times-circle"></i> @endif</a></td>
                                    <td class="text-center"><a href="/home/luhe/delete/{{$luhe->id}}" class="btn btn-link"><i class="fa fa-trash"></i></a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section("more_script")
    {{--<script src="/plugins/dimmer/dimmer.min.js"></script>--}}
    <script>
        "use strict";
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    </script>
@endsection