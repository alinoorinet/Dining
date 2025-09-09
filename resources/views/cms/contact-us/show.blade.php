@extends('layouts.cms')
@section('content')
    <div class="modal fade" id="responseModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="ui dimmer" id="responseDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title" id="responseModalLabel">ارسال پاسخ به کاربر</h5>
                    <a class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <form id="responseForm" novalidate>
                        <div class="col-sm-12 mt-2">
                            <label for="message">متن پیام</label>
                            <textarea rows="5" class="form-control" name="message" id="message" required>طراح سامانه - مرکز آی تی دانشگاه</textarea>
                            <input type="hidden" id="cuId">
                            <div class="invalid-feedback">
                            </div>
                        </div>
                    </form>
                    <div class="alert d-none mt-2 ml-3 mr-3" role="alert" id="resultAlert">
                        <h5 class="alert-heading"></h5>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary ml-2" data-dismiss="modal">انصراف</button>
                    <button type="button" class="btn btn-primary" id="submitBtn">ارسال
                        <i class="fa fa-paper-plane pull-right"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">پیام ها، پیشنهادات و مشکلات کاربران</h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-responsive table-bordered">
                <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">نام</th>
                    <th class="text-center">ش.دانشجویی</th>
                    <th class="text-center">همراه</th>
                    <th class="text-center">ایمیل</th>
                    <th class="text-center">ش.کاربری</th>
                    <th class="text-center">درخواست</th>
                    <th class="text-center">مخاطب</th>
                    <th class="text-center">ز.ارسال</th>
                    <th class="text-center">پیام</th>
                    <th class="text-center">پاسخ</th>
                </tr>
                </thead>
                <tbody>
                @if(!empty($cu))
                    @foreach($cu as $value)
                        <tr @if($value->answered != 0) class="text-secondary" @endif>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$value->name}}</td>
                            <td class="text-center">{{$value->std_no}}</td>
                            <td class="text-center">{{$value->mobile}}</td>
                            <td class="text-center">{{$value->email}}</td>
                            <td class="text-center">{{$value->username}}</td>
                            <td class="text-center">{{$value->inOrOut}}</td>
                            <td class="text-center">{{$value->receiver}}</td>
                            <td class="text-center">{{$value->created_at}}</td>
                            <td class="text-center"><a href="javascript:void(0)" data-toggle="popover" title="متن پیام" data-content="{{$value->request}}"><i class="fa fa-envelope"></i></a></td>
                            <td class="text-center">@if($value->username != '')<a href="javascript:void(0)" class="fireModal" id="{{$value->id}}"><i class="fa fa-paper-plane text-success"></i></a>@endif</td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="10" class="text-center thead-inverse">پیامی دریافت نشده است</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('more_script')
    {{--<script src="/plugins/print/jQuery.print.min.js"></script>--}}
    <script>
        $('[data-toggle="popover"]').popover();
        $('#responseModal').on('click','#submitBtn',function () {
            var id = $('#cuId').val();
            var response = $('#message').val();
            if(response == '')
                return false;
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#responseDimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/contact-us/response',
                data: JSON.stringify({
                    message:response,
                    id:id
                }),
                dataType: 'json',
                dataContent: 'application/json',
                processData: false,
                success: function (data) {
                    $('#responseDimmer').dimmer('hide');
                    if(data.status == 101)
                        $.each(data.errors,function (key,value) {
                            $('#'+key).addClass('is-invalid').closest('div').find('.invalid-feedback').text(value);
                        });
                    else if(data.status == 102)
                    {
                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').text('');
                        $('#responseForm')[0].reset();
                        $('#resultAlert').removeClass('d-none').removeClass('alert-success').addClass('alert-danger').find('.alert-heading').text(data.res);
                    }
                    else if(data.status == 200)
                    {
                        $('.form-control').removeClass('is-invalid');
                        $('.invalid-feedback').text('');
                        $('#responseForm')[0].reset();
                        $('#resultAlert').removeClass('d-none').removeClass('alert-danger').addClass('alert-success').find('.alert-heading').text(data.res);
                    }
                },
                error: function (data) {
                    $('#responseDimmer').dimmer('hide');
                }
            });
        });
        /*$(function PrintThere () {
            $("#xxx").print();
        });*/
        $('.fireModal').on('click',function () {
            $('form#responseForm #cuId').val($(this).attr('id'));
            $('#responseModal').modal();
        })
    </script>
@endsection