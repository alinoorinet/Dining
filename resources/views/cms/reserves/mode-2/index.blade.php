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
            <h4 class="card-title">بررسی رزرو به تفکیک خوابگاه
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
                        <label class="control-label">خوابگاه</label>
                        <select class="custom-select @if($errors->has('dorm'))is-invalid @endif" name="dorm" id="dorm" title="خوابگاه">
                            <option selected>انتخاب...</option>
                            @foreach($dorms as $dorm)
                                <option value="{{$dorm->id}}">{{$dorm->title}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label">وعده</label>
                        <select class="custom-select @if($errors->has('meal'))is-invalid @endif" name="meal" id="meal" title="وعده">
                            @foreach($meals as $meal)
                                <option value="{{$meal}}">{{$meal}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <label class="control-label">جنسیت</label>
                        <select class="custom-select" name="sex" id="sex" title="جنسیت">
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
    <script src="/plugins/print/jQuery.print.min.js"></script>
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
            var dormId  = jQuery.trim($('select#dorm option:selected').val());
            var meal  = jQuery.trim($('select#meal option:selected').val());
            var sex  = jQuery.trim($('select#sex option:selected').val());

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
                url: '/home/reserves-report/mode-2-data',
                data: {
                    'beginDate' : beginDate,
                    'endDate'   : endDate,
                    'dormId'    : dormId,
                    'meal'      : meal,
                    'sex'       : sex
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
                error: function(error)
                {
                    location.reload();
                    //console.log(error);
                }
            });
        });
        $('#dynamicContent').on('click','#printBtn1',function () {
            $('#dynamicContent .table').print({
                globalStyles : true,
                mediaPrint : false,
                stylesheet : [
                    '/css/style.css',
                   //  '/css/cms.css',
                   //  '/css/app.css',
                   // '/plugins/datatable/datatables.min.css'
                ]
            });
        });
    </script>
@endsection
