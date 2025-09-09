@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="row justify-content-center">
        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
            <div class="card">
                <div class="ui dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-header"> گزارش دانشجویان فعال<span class="badge badge-info p-2 font-14">کل کاربران {{$usersCount}}</span></div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                            <form id="active-users-form">
                                <div class="form-group">
                                    <label class="control-label">از تاریخ<strong class="text-danger"> * </strong></label>
                                    <input type="text" class="form-control ltr" id="beginDate" name="begin_date" autocomplete="off">
                                    <i class="text-muted">فرمت تاریخ به صورت "xxxx-xx-xx"</i>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">تا تاریخ<strong class="text-danger"> * </strong></label>
                                    <input type="text" class="form-control ltr" id="endDate" name="end_date" autocomplete="off">
                                    <i class="text-muted">فرمت تاریخ به صورت "xxxx-xx-xx"</i>
                                </div>
                                <div class="form-group">
                                    <label class="control-label">تعداد رزرو به عنوان شاخص فعال بودن<strong class="text-danger"> * </strong></label>
                                    <input type="number" class="form-control ltr" id="res_count" name="res_count" min="1" value="1" style="width: 100px">
                                </div>
                                <div class="form-group">
                                    <div class="d-block">
                                        <input type="radio" class="d-inline-block align-middle" name="sign" id="x1" value="1">
                                        <label for="x1" class="d-inline-block">بزرگتر از تعداد رزرو</label>
                                    </div>
                                    <div class="d-block">
                                        <input type="radio" class="d-inline-block align-middle" name="sign" id="x2" value="2" checked>
                                        <label for="x2" class="d-inline-block">برابر تعداد رزرو</label>
                                    </div>
                                    <div class="d-block">
                                        <input type="radio" class="d-inline-block align-middle" name="sign" id="x3" value="3">
                                        <label for="x3" class="d-inline-block">کوچکتر از تعداد رزرو</label>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success get-report-btn">دریافت</button>
                                </div>
                            </form>
                        </div>
                        <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 col-12">
                            <div class="row">
                                @foreach($enterTmp as $enter)
                                    <div class="col-sm-2 mb-1">
                                        <div class="card">
                                            <div class="card-body text-center">
                                                <div class="card-title bg-light">ورودی {{$enter->enter}}</div>
                                                <strong>{{$enter->count}}</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <hr>
                            <div id="dynamicContent"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script src="/plugins/print/jQuery.print.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#beginDate,#endDate").persianDatepicker({
            initialValue: false,
            autoClose:true,
            format: 'YYYY-MM-DD',
            formatter: function (unixDate) {
                var self = this;
                var pdate = new persianDate(unixDate);
                pdate.formatPersian = false;
                return pdate.format(self.format);
            }
        });
        $('#active-users-form').on('submit',function (e) {
            e.preventDefault();
            const beginDate = jQuery.trim($('#beginDate').val());
            const endDate   = jQuery.trim($('#endDate').val());
            const resCount  = jQuery.trim($('#res_count').val());
            const form      = $(this);
            const btn       = form.find('button[type=submit]');

            if(beginDate === '' || endDate === '') {
                alert('فیلدهای از تاریخ - تا تاریخ را پر کنید');
                return false;
            }
            if(resCount === '') {
                alert('تعداد رزرو را به عنوان مقیاس سنجش  وارد کنید');
                return false;
            }

            const validate1 = beginDate.match(/(\d{4})-(\d{2})-(\d{2})/);
            const validate2 = endDate.match(/(\d{4})-(\d{2})-(\d{2})/);
            if(validate1 === null) {
                alert("فرمت فیلد از تاریخ نامعتبر است. تاریخ باید به صورت 1396-01-01 باشد.");
                return false;
            }
            if(validate2 === null) {
                alert("فرمت فیلد تا تاریخ نامعتبر است. تاریخ باید به صورت 1396-01-01 باشد.");
                return false;
            }
            $(btn).html('دریافت <i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/reserves-report/active-users',
                data: new FormData(this),
                dataType: 'json',
                contentType: false,
                processData: false,
                success: function(data)
                {
                    $(btn).html('دریافت <i class="fa fa-check-circle"></i>');
                    if(data.status === 200) {
                        $('#dynamicContent').html(data.res);

                    }
                    else if(data.status === 101) {
                        $.each(data.res,function (k,v) {
                            let name = $("input[name='"+k+"'],textarea[name='"+k+"']");
                            if(k.indexOf(".") !== -1) {
                                const arr = k.split(".");
                                name = $("input[name='"+arr[0]+"[]']:eq("+arr[1]+")");
                            }
                            name.addClass('is-invalid').after('<div class="invalid-feedback text-right">'+v[0]+'</div>');
                        });
                    }
                    else
                        alert(data.res);
                },
                error: function (error) {
                    $(btn).html('دریافت <i class="fa fa-times-circle"></i>');
                    $('.ui.dimmer').dimmer('hide');
                    alert("انجام فرآیند امکان پذیر نیست");
                    location.reload();
                }
            });
        });
        $('#dynamicContent').on('click','.print-btn',function () {
            let selector = $('#dynamicContent').find('table');

            selector.print({
                globalStyles : true,
                mediaPrint : true,
                stylesheet : "{{asset('/css/assets/bootstrap.css')}}"
            });
        });
    </script>
@endsection
