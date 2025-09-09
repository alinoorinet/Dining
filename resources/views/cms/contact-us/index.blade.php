@extends('layouts.cms')
@section('content')
    <div class="card card-body">
        <p class="text-right mb-0">انتقادات، پیشنهادات، پیام ها و گزارش مشکلات خود را از طریق <a href="https://support.ilam.ac.ir" class="btn btn-primary btn-sm">سامانه پشتیبانی آنلاین</a> برای ما ارسال نمایید.</p>
{{--        <h4 class="card-title text-center">انتقادات، پیشنهادات، پیام ها و گزارش مشکلات خود در این قسمت برای ما ارسال نمایید.</h4>--}}
{{--        <form  id="contactForm" method="post" action="/home/contact-us/store" novalidate>--}}
{{--            {!! csrf_field() !!}--}}
{{--            <div class="col-sm-12 mt-2">--}}
{{--                <label for="message">متن پیام</label>--}}
{{--                <textarea rows="5" class="form-control @if($errors->has('message'))is-invalid @endif" name="message" id="message">{{old('message')}}</textarea>--}}
{{--                <div class="invalid-feedback">--}}
{{--                    {{$errors->first('message')}}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-12 mt-2">--}}
{{--                <label class="d-block">مخاطب پیام</label>--}}
{{--                <label class="d-block" id="receiver">--}}
{{--                    <input type="radio" class="formalign-middle" name="receiver" value="1"> مدیریت تغذیه--}}
{{--                    <input type="radio" class="align-middle" name="receiver" value="0"> طراحان سامانه--}}
{{--                </label>--}}
{{--                <div class="invalid-feedback @if($errors->has('receiver')) d-block mb-3 @endif">--}}
{{--                    {{$errors->first('receiver')}}--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="col-sm-12">--}}
{{--                <button type="submit" class="btn btn-primary" id="sendMessageButton">ارسال</button>--}}
{{--            </div>--}}
{{--        </form>--}}
    </div>
    <div class="card mt-2">
        <div class="card-body">
            <table class="table table-responsive table-bordered">
                <thead>
                <tr>
                    <th class="text-center" colspan="5">پیام های من</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">مخاطب پیام</th>
                    <th class="text-center">متن پیام</th>
                    <th class="text-center">پاسخ</th>
                    <th class="text-center">حذف</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($cu))
                    @foreach($cu as $value)
                        <tr @if($value->readed != 0) class="text-secondary" @endif>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$value->receiver}}</td>
                            <td class="text-center"><a href="javascript:void(0)" class="btn btn-link req" data-toggle="popover" title="متن پیام" data-content="{{$value->request}}"><i class="fa fa-envelope"></i></a></td>
                            <td class="text-center">@if(!empty($value->response))<a href="javascript:void(0)" id="res-{{$value->id}}" data-toggle="popover" class="btn btn-link res" title="متن پاسخ" data-content="{{$value->response}}"><i class="fa fa-envelope-o"></i></a>@endif</td>
                            <td class="text-center"><a href="/home/contact-us/delete/{{$value->id}}" class="btn btn-link"><i class="fa fa-trash-o"></i></a></td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="5" class="text-center thead-inverse">لیست پیام ها خالی است</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section("more_script")
    <script>
        $('[data-toggle="popover"]').popover();
        $('.btn.btn-link.res').on('click',function () {
            var btn = $(this);
            var resp = $(this).attr('id').split('-')[1];
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/contact-us/readed',
                data: JSON.stringify({
                    resp:resp
                }),
                dataType: 'json',
                dataContent: 'application/json',
                processData: false
            });
        });
        $('form').on('submit',function(e){
            e.preventDefault();
            var msg = $('#message');
            if(msg.val() == '') {
                msg.removeClass('is-invalid').addClass('is-invalid').closest('div').find('.invalid-feedback').text('متن پیام را وارد کنید');
                return false;
            }
            var receiver = $('input[name=receiver]:checked');
            if(!receiver.is(':checked')) {
                $('#receiver').closest('div').find('.invalid-feedback').addClass('d-block mb-3').text('مخاطب پیام را انتخاب کنید');
                return false;
            }
            $(this).unbind('submit').submit();
        });
    </script>
@endsection
