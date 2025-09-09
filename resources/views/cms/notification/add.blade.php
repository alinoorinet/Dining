@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">درج اطلاعیه
                <a href="/home/notification/" class="btn btn-outline-success pull-left" title="مشاهده اطلاعیه ها">
                    <i class="fa fa-plus-circle pull-right"></i>مشاهده
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class=" col-md-12">
                    <form method="post" action="/home/notification/store">
                        {{ csrf_field() }}
                        <div class="row">

                            <div class="col-md-6 mb-3">
                                <label for="title">عنوان</label>
                                <input type="text" id="title" class="form-control @if($errors->has('title'))is-invalid @endif"
                                       value="@if(old('title')){{old('title')}}@endif" name="title">
                                @if($errors->has('title'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('title')}}
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6">
                                <label for="self" >محل درج</label>
                                <select class="form-control @if($errors->has('self'))is-invalid @endif" id="self" name="self" >
                                    <option value="0" >سلف دانشجویی</option>
                                    <option value="1" >سلف آزاد</option>
                                </select>
                                @if($errors->has('self'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('self')}}
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-12 mb-3">
                                <label for="contents" >متن</label>
                                <textarea id="contents" class="form-control @if($errors->has('contents'))is-invalid @endif" name="contents"
                                >@if(old('contents')){{old('contents')}}@endif </textarea>
                                @if($errors->has('contents'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('contents')}}
                                    </div>
                                @endif
                            </div>

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
    </script>
@endsection