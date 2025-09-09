@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">بررسی وضعیت ارتباط با دستگاه اثرانگشت</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                    <form id="check-connection-form" method="post">
                        @csrf
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block">تست ارتباط</button>
                        </div>
                    </form>
                </div>
                <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 col-12">
                    <div id="connection-status" class="text-right"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-1">
        <div class="card-header">ثبت و بروزرسانی اثر انگشت</div>
        <div class="card-body">
            <form id="enroll-form" method="post">
                @csrf
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="form-group">
                            <label>َشناسه کاربری | کد ملی | شماره دانشجویی</label>
                            <input name="credential" class="form-control">
                        </div>
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block">ثبت</button>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="form-group">
                            <label>اثر انگشت دست راست</label>
                            <label class="d-block text-muted">
                                <input type="radio" value="1" name="finger_name"> شست
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="2" name="finger_name" checked> اشاره
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="3" name="finger_name"> میانی بلند
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="4" name="finger_name"> حلقه
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="5" name="finger_name"> کوچک
                            </label>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="form-group">
                            <label>اثر انگشت دست چپ</label>
                            <label class="d-block text-muted">
                                <input type="radio" value="6" name="finger_name"> شست
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="7" name="finger_name"> اشاره
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="8" name="finger_name"> میانی بلند
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="9" name="finger_name"> حلقه
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="10" name="finger_name"> کوچک
                            </label>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" id="enroll-result"></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card mb-1">
        <div class="card-header">auto</div>
        <div class="card-body">
            <form id="auto-capture-form" method="post">
                @csrf
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="form-group mb-0">
                            <button type="submit" class="btn btn-primary btn-block">شروع</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('more_script')
    {{--<script type="module">
        import {addText} from './utils.js';
        addText("sdsdasdasda");
    </script>--}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        //
        $(function () {
            $(document).ready(function () {
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/fingerprint/create-session',
                    data: {},
                    dataType: 'json',
                    success: function (data) {
                        console.log(data);
                        /*if (data.res && data.res.sessionId) {
                            const sessionId = data.res.sessionId;
                            const current = new Date();
                            const expires = new Date();
                            expires.setTime(new Date(Date.parse(current) + 1000 * 60 * 60));

                            //AppendLogData("[Session ID]" + msg.sessionId);
                            document.cookie = "username=" + data.res.sessionId + "; expires=" + expires.toUTCString();
                        }*/
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
            $('#check-connection-form').on('submit',function (e) {
                e.preventDefault();
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/fingerprint/init',
                    data: {},
                    dataType: 'json',
                    contentType: 'application/json',
                    processData: false,
                    success: function (data) {
                        $('#connection-status').html(data.res);
                        console.log(data);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
            $('#enroll-form').on('submit',function (e) {
                e.preventDefault();
                const form = $(this);
                const crdt = form.find('input[name="credential"]');
                if($(crdt).val() === '') {
                    alert("یکی از مشخصات کاربر را وارد کنید");
                    return false;
                }

                const data = new FormData(this);
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/fingerprint/enroll',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        console.log(data);
                        if(data.status === 200) {
                            if (data.img)
                                $('#enroll-result').html("<img width='100' height='100' src='" + data.img + "'>");
                        }
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
            $('#auto-capture-form').on('submit',function (e) {
                e.preventDefault();
                const data = new FormData(this);
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/fingerprint/auto-capture',
                    data: {},
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        console.log(data);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            });
        })
    </script>
@endsection
