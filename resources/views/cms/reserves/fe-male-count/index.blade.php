@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <div class="ui dimmer">
                <div class="ui large text loader">چند لحظه صبر کنید...</div>
            </div>
            <h4 class="card-title">بررسی تعداد رزرو به تفکیک جنسیت
                {{--<a href="/home/reserves-report/print" class="btn btn-outline-primary pull-left" target="_blank" title="چاپ گزارش رزروها">
                    <i class="fa fa-print pull-right"></i>چاپ گزارش
                </a>--}}
            </h4>
            <hr>
            <form id="resReportForm">
                <div class="row">
                    <div class="col-sm-2">
                        <label class="control-label">از تاریخ</label>
                        <input type="text" class="form-control" id="beginDate" autocomplete="off">
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label">تا تاریخ</label>
                        <input type="text" class="form-control" id="endDate" autocomplete="off">
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label">وعده</label>
                        <select class="custom-select" name="meal" id="meal" title="وعده">
                            @foreach($meals as $meal)
                                <option value="{{$meal}}">{{$meal}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label">جنسیت</label>
                        <select class="custom-select" name="sex" id="sex" title="جنسیت">
                            <option value="مرد و زن">مرد و زن</option>
                            <option value="مرد">مرد</option>
                            <option value="زن">زن</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label"> &nbsp;</label>
                        <button type="button" class="btn btn-outline-success form-control" id="getReportByDate">دریافت</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body" id="dynamicContent">
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script>
        $("#beginDate,#endDate").persianDatepicker({
            initialValue: false,
            format: 'YYYY-MM-DD',
            formatter: function (unixDate) {
                var self = this;
                var pdate = new persianDate(unixDate);
                pdate.formatPersian = false;
                return pdate.format(self.format);
            }
        });
        $('#getReportByDate').on('click',function () {
            var beginDate = jQuery.trim($('#beginDate').val());
            var endDate = jQuery.trim($('#endDate').val());
            var sex  = jQuery.trim($('select#sex option:selected').val());
            var meal  = jQuery.trim($('select#meal option:selected').val());

            if(beginDate == '' || endDate == '') {
                alert('فیلدهای از تاریخ - تا تاریخ را پر کنید');
                return false;
            }

            var validate1 = beginDate.match(/(\d{4})-(\d{2})-(\d{2})/);
            var validate2 = endDate.match(/(\d{4})-(\d{2})-(\d{2})/);
            if(validate1 === null) {
                alert("فرمت فیلد از تاریخ نامعتبر است. تاریخ باید به صورت 01-01-1396 باشد.");
                return false;
            }
            if(validate2 === null) {
                alert("فرمت فیلد تا تاریخ نامعتبر است. تاریخ باید به صورت 01-01-1396 باشد.");
                return false;
            }
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.ui.dimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/reserves-report/fe-male-count-by-date',
                data: {
                    'beginDate' : beginDate,
                    'endDate'   : endDate,
                    'sex'       : sex,
                    'meal'      : meal
                },
                dataType: 'json',
                success: function(data)
                {
                    $('.ui.dimmer').dimmer('hide');
                    if(data.status == true) {
                        $('#dynamicContent').empty().html(data.res.view);
                    }
                    else {
                        alert(data.res);
                    }
                },
                error: function (error) {
                    $('.ui.dimmer').dimmer('hide');
                    console.log(error);
                }
            });
        });
    </script>
@endsection
