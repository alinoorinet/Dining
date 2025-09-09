@extends('layouts.cms')
@section('content')
    <div class="modal fade bd-example-modal-sm" id="activeModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="ui dimmer" id="responseDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="responseModalLabel"></h5>
                    <i class="fa fa-user-secret fa-2x"></i>
                    <a class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <label>توضیحات</label>
                    <textarea class="form-control" id="description"></textarea>
                    <input type="button" id="submitModal" class="btn btn-success btn-block mt-1" value="ثبت">
                    <input type="hidden" id="userId">
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-4 wow bounceInDown animated" data-wow-offset="0" data-wow-delay="0.4s" style="animation-duration: 1.5s">
        <div class="card">
            <div class="card-body">
                <div class="card-title text-right bg-light">
                    فرم جست و جو کاربر
                </div>
                <form method="post" action="#" id="srchUserForm">
                    <div class="col-sm-12 mb-3">
                        <label for="">جستو جو بر اساس:</label>
                        <label class="custom-control d-block custom-radio mt-1">
                            <input type="radio" class="custom-control-input" id="t1" value="1" name="type">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description text-muted"> نام کاربری</span>
                        </label>
                        <label class="custom-control d-block custom-radio mt-1">
                            <input type="radio" class="custom-control-input" id="t2" value="2" name="type">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description text-muted"> شماره دانشجویی</span>
                        </label>
                        <label class="custom-control d-block custom-radio mt-1">
                            <input type="radio" class="custom-control-input" id="t3" value="3" name="type">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description text-muted"> نام</span>
                        </label>
                        <label class="custom-control d-block custom-radio mt-1">
                            <input type="radio" class="custom-control-input" id="t4" value="4" name="type">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description text-muted"> نام خانوادگی</span>
                        </label>
                        <label class="custom-control d-block custom-radio mt-1">
                            <input type="radio" class="custom-control-input" id="t5" value="5" name="type">
                            <span class="custom-control-indicator"></span>
                            <span class="custom-control-description text-muted"> همراه</span>
                        </label>
                        <div id="radioErrBox"></div>
                    </div>
                    <div class="col-sm-12 mb-3">
                        <input type="text" class="form-control" name="srchTxt" id="srchTxt" placeholder="شماره دانشجویی | نام کاربری | نام | نام خانوادگی | همراه">
                    </div>
                    <div class="col-sm-12 mb-1">
                        <button class="btn btn-primary btn-block" id="srchBtnSub" type="submit"><i class="fa fa-search ml-1"></i>جست و جو</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="col-sm-12" id="srchResBox">
    </div>
@endsection
@section('more_script')
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#srchUserForm').on('submit',function (e) {
                e.preventDefault();
                $('#srchBtnSub').html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                var form    = '#srchUserForm ';
                var schText = $(form + '#srchTxt').val();
                var type    = $(form + 'input[name=type]:checked').val();
                if(type === '' || type === undefined) {
                    $('#radioErrBox').html('<div class="invalid-feedback d-block text-right">انتخاب نحوه جست و جو الزامی است</div>');
                    return false;
                }
                if(schText === '' || schText === undefined) {
                    $(form + '#srchTxt').addClass('is-invalid').after('<div class="invalid-feedback text-right">پر کردن این فیلد الزامی است</div>');
                    return false;
                }
                $(form + '#srchTxt').removeClass('is-invalid');
                $(form + '#radioErrBox').html('');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/users/srch',
                    data: JSON.stringify({schText:schText,type:type}),
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        var resTbl;
                        $('#srchBtnSub').html('<i class="fa fa-search ml-1"></i>جست و جو');
                        if(data.status === 200) {
                            resTbl = '<div class="card mt-1">\n' +
                                '        <div class="card-body table-responsive">\n' +
                                '            <table class="table table-striped table-bordered table-sm">\n' +
                                '                <thead>\n' +
                                '                <tr>\n' +
                                '                    <th class="text-center" colspan="13">لیست کاربران ثبت شده</th>\n' +
                                '                </tr>\n' +
                                '                <tr>\n' +
                                '                    <th class="text-center">-</th>\n' +
                                '                    <th class="text-center">#</th>\n' +
                                '                    <th class="text-center">نام</th>\n' +
                                '                    <th class="text-center">نام کاربری</th>\n' +
                                '                    <th class="text-center">شماره دانشجویی</th>\n' +
                                '                    <th class="text-center">موبایل</th>\n' +
                                '                    <th class="text-center">خوابگاه</th>\n' +
                                '                    <th class="text-center">وضعیت</th>\n' +
                                '                    <th class="text-center">دسترسی</th>\n' +
                                '                    <th class="text-center">ویرایش</th>\n' +
                                '                    <th class="text-center">حذف</th>\n' +
                                '                </tr>\n' +
                                '                </thead>\n' +
                                '                <tbody>'+data.res+'</tbody></table></div></div>';
                            $('#srchResBox').html(resTbl);
                        }
                        else if(data.status === 101) {
                            resTbl = '<div class="card mt-1">\n' +
                                '        <div class="card-body">\n' +
                                '            <table class="table table-responsive table-striped table-bordered table-sm">\n' +
                                '                <thead>\n' +
                                '                <tr>\n' +
                                '                    <th class="text-center" colspan="11">کاربری با مشخصات مورد نظر پیدا نشد</th>\n' +
                                '                </tr>\n' +
                                '                </thead>\n' +
                                '                </table></div></div>\n';
                            $('#srchResBox').html(resTbl);
                        }
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        alert('خطای اتصال به شبکه');
                        location.reload();
                    }
                });
            });
            $('#srchResBox').on('click','.activeMode',function () {
                var btn = $(this);
                var id  = btn.attr('id');
                if(id === '' || !jQuery.isNumeric(id))
                    return false;
                $('#userId').val(id);
                $('#activeModal').modal();
            });
            $('#activeModal').on('click','#submitModal',function () {
                var userId = $('#userId').val();
                if(userId === '' || !jQuery.isNumeric(userId))
                    return false;
                var description = $('#description').val();
                var btn = $('#srchResBox').find('.activeMode'+userId);
                $.ajax({
                    cache: false,
                    type : 'POST',
                    url  : '/home/users/de-active',
                    data : JSON.stringify({id:userId,description:description}),
                    processData: true,
                    contentType: 'application/json',
                    dataType   : 'json',
                    success: function (data) {
                        if(data.status === 200) {
                            $('#activeModal').modal('hide');
                            if(data.res)
                                btn.html('<i class="fa fa-check-circle text-success"></i>');
                            else
                                btn.html('<i class="fa fa-times-circle text-danger"></i>');
                        }
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        alert("انجام فرآیند در حال حاضر امکان پذیر نیست");
                        location.reload();
                        //console.log(error);
                    }
                });
            });
        });
    </script>
@endsection
