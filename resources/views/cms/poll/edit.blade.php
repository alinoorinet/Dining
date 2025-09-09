@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">ویرایش نظر سنجی
                <a href="/home/poll" class="btn btn-outline-success pull-left" title=" نظرسنجی ها ">
                    <i class="fa fa-eye pull-right"></i>مشاهده
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class=" col-md-12">
                    <form method="post" action="/home/poll/update">
                        @csrf
                        <input type="hidden" name="pollId" value="{{$poll->id}}">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="title">عنوان</label>
                                <input type="text" id="title" class="form-control @if($errors->has('title'))is-invalid @endif"
                                       value="{{$poll->title}}" name="title">
                                @if($errors->has('title'))
                                    <div class="invalid-feedback">
                                        {{$errors->first('title')}}
                                    </div>
                                @endif
                            </div>
                        </div>
                        @foreach($questions as $k=>$question)
                            <div class="row">

                                <div class="col-md-6 mb-3 pr-5 d-">
                                    <label for="pos{{$k+1}}">گزینه @if($k == 0) اول @elseif($k == 1) دوم @elseif($k == 2) سوم @elseif($k == 3) چهارم @endif</label>
                                    <input type="text" id="pos{{$k+1}}" class="form-control @if($errors->has('pos'.($k+1)))is-invalid @endif"
                                           value="{{$question->title}}" name="pos{{$k+1}}">
                                    @if($errors->has('pos'.($k+1)))
                                        <div class="invalid-feedback">
                                            {{$errors->first('pos'.($k+1))}}
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach

                        <div class="row">
                            <div class="col-md-2 mb-3">
                                <button class="btn btn-info btn-block" type="submit">ویرایش</button>
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
