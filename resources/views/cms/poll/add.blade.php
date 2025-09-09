@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">نظر سنجی
                <a href="/home/poll" class="btn btn-outline-success pull-left" title="افزودن نظرسنجی جدید">
                    <i class="fa fa-eye pull-right"></i>مشاهده
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class=" col-md-12">
                    <form method="post" action="/home/poll/create">
                        {{ csrf_field() }}
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title">عنوان</label>
                                <input type="text" id="title" class="form-control @if($errors->has('title'))is-invalid @endif"
                                       value="@if(old('title')){{old('title')}}@endif" name="title">
                                @if($errors->has('title'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('title')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">

                            <div class="col-md-6 mb-3 pr-5 d-">
                                <label for="pos1">گزینه اول</label>
                                <input type="text" id="pos1" class="form-control @if($errors->has('pos1'))is-invalid @endif"
                                       value="@if(old('pos1')){{old('pos1')}}@endif" name="pos1">
                                @if($errors->has('pos1'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('pos1')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 pr-5 d-inline">
                                <label for="pos2">گزینه دوم</label>
                                <input type="text" id="pos2" class="form-control @if($errors->has('pos2'))is-invalid @endif"
                                       value="@if(old('pos2')){{old('pos2')}}@endif" name="pos2">
                                @if($errors->has('pos2'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('pos2')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 pr-5 d-inline">
                                <label for="pos3">گزینه سوم</label>
                                <input type="text" id="pos3" class="form-control @if($errors->has('pos3'))is-invalid @endif"
                                       value="@if(old('pos3')){{old('pos3')}}@endif" name="pos3">
                                @if($errors->has('pos3'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('pos3')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3 pr-5 d-inline">
                                <label for="pos4">گزینه چهارم</label>
                                <input type="text" id="pos4" class="form-control @if($errors->has('pos4'))is-invalid @endif"
                                       value="@if(old('pos4')){{old('pos4')}}@endif" name="pos4">
                                @if($errors->has('pos4'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('pos4')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <button class="btn btn-success btn-block" type="submit">ثبت</button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script>
        $(function () {
            "use strict";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        });
    </script>
@endsection