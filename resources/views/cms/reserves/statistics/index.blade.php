@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="section" id="report-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="card" style="min-height: 330px">
                        <div class="card-header bg-danger text-white">آمار رزرو ها
                            <button type="button" class="btn btn-light btn-sm float-left print-btn" id="rp-total"><i class="fa fa-print"></i></button>
                        </div>
                        <div class="card-body">
                            <div class="ui dimmer">
                                <div class="ui large text loader">چند لحظه صبر کنید...</div>
                            </div>
                            <form id="resReportForm">
                                <div class="form-group">
                                    <label class="control-label">از تاریخ</label>
                                    <input type="text" class="form-control ltr" id="beginDate" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <label class="control-label">تا تاریخ</label>
                                    <input type="text" class="form-control ltr" id="endDate" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger btn-block get-report-btn" id="s-1">جمع تعداد رزرو و مصرف شده ها به تفکیک نوع غذا</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger btn-block get-report-btn" id="s-2">تعداد رزرو و مصرف شده ها به تفکیک تاریخ و نوع غذا</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger btn-block get-report-btn" id="s-3">رزرو ها به تفکیک تعداد کاربران</button>
                                </div>
                                <div class="form-group">
                                    <button type="button" class="btn btn-danger btn-block get-report-btn" id="s-4">رزرو ها به تفکیک مشخصات کاربران</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-4">
                    <div class="card" style="min-height: 330px">
                        <div class="card-header bg-danger text-white">دریافت فایل Excel لیست رزرو ها</div>
                        <div class="card-body">
                            <form id="resReportExcelForm">
                                <div class="form-group">
                                    <label class="control-label">تاریخ</label>
                                    <input type="text" class="form-control" id="excel-date" autocomplete="off">
                                </div>
                                <div class="form-group">
                                    <a href="/home/reserves-report/statistics/free-reserves/x" class="btn btn-danger" id="get-file-btn">دریافت فایل</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12" id="dynamicContent">
                    <div id="rt-1"></div>
                    <div id="rt-2"></div>
                    <div id="rt-3"></div>
                    <div id="rt-4"></div>
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
        $('.get-report-btn').on('click',function () {
            const beginDate = jQuery.trim($('#beginDate').val());
            const endDate   = jQuery.trim($('#endDate').val());
            const btn       = $(this);
            const btnId     = btn.attr('id');
            const type      = btnId.split('-')[1];

            if(!jQuery.isNumeric(type))
                return false;
            if(beginDate === '' || endDate === '') {
                alert('فیلدهای از تاریخ - تا تاریخ را پر کنید');
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

            $('.ui.dimmer').dimmer('show');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/reserves-report/free-statistics',
                data: {
                    'beginDate' : beginDate,
                    'endDate'   : endDate,
                    'type'      : type
                },
                dataType: 'json',
                success: function(data)
                {
                    $('.ui.dimmer').dimmer('hide');
                    if(data.status === 200)
                        $('#dynamicContent #rt-'+type).html(data.res);
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
                    $('.ui.dimmer').dimmer('hide');
                    alert("انجام فرآیند امکان پذیر نیست");
                    location.reload();
                }
            });
        });
        $('#dynamicContent').on('click','.close-report',function () {
            const id      = $(this).attr('id');
            const repType = id.split('-')[1];
            $('#rt-'+repType).html('');
        });
        $('#report-section').on('click','.print-btn',function () {
            const id = $(this).attr('id');
            const repType = id.split('-')[1];

            let selector;
            if(repType === 'total')
                selector = $('#dynamicContent');
            else
                selector = $('#table-'+repType);
            selector.print({
                globalStyles : true,
                mediaPrint : true,
                stylesheet : "{{asset('/css/assets/bootstrap.css')}}"
            });
        });
        $("#excel-date").persianDatepicker({
            initialValue: false,
            autoClose:true,
            format: 'YYYY-MM-DD',
            formatter: function (unixDate) {
                var self = this;
                var pdate = new persianDate(unixDate);
                pdate.formatPersian = false;
                return pdate.format(self.format);
            },
            onSelect: function (unixDate) {
                var self = this;
                var pdate = new persianDate(unixDate);
                pdate.formatPersian = false;
                $('#get-file-btn').attr('href','/home/reserves-report/statistics/free-reserves/'+pdate.format(self.format));
            }
        });
    </script>
@endsection
