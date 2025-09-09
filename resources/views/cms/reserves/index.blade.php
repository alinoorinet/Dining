@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
    {{--<style type="text/css" media="print">
        #dynamicContent {
            transform: rotate(-90deg);
        }
    </style>--}}
@endsection
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item">رزرو</li>
        <li class="breadcrumb-item active">
            <a href="/home/reserves-report">آمار کلی</a>
        </li>
    </ol>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
            <div id="report-section">
                <div class="card">
                    <div class="card-header">آمار کلی رزرو
                        <button class='btn btn-light btn-sm pt-1 pb-1 pr-2 pl-2 print-btn float-left' id="print-total" title="چاپ گزارش رزروها"><i class='fa fa-print'></i></button>
                    </div>
                    <div class="card-body pt-2 pb-2">
                        <form id="resReportForm">
                            <div class="row">
                                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12">
                                    <label class="control-label">از تاریخ</label>
                                    <input type="text" class="form-control" id="beginDate" autocomplete="off">
                                </div>
                                <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-12">
                                    <label class="control-label">تا تاریخ</label>
                                    <input type="text" class="form-control" id="endDate" autocomplete="off">
                                </div>
                                <div class="col-xl-1 col-lg-1 col-md-1 col-sm-1 col-12">
                                    <label class="control-label d-block"> &nbsp;</label>
                                    <button type="button" class="btn btn-success" id="getReportByDate">دریافت</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div id="dynamicContent" style="direction: rtl">
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
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#beginDate,#endDate").persianDatepicker({
                initialValue: false,
                format: 'YYYY-MM-DD',
                autoClose: true,
                formatter: function (unixDate) {
                    var self = this;
                    var pdate = new persianDate(unixDate);
                    pdate.formatPersian = false;
                    return pdate.format(self.format);
                }
            });
            $('#getReportByDate').on('click',function () {
                const btn = $(this);
                var beginDate = jQuery.trim($('#beginDate').val());
                var endDate   = jQuery.trim($('#endDate').val());
                if(beginDate === '' || endDate === '') {
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
                btn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/reserves-report/total',
                    data: {
                        'beginDate': beginDate,
                        'endDate'  : endDate
                    },
                    dataType: 'json',
                    success: function(data)
                    {
                        btn.html('دریافت');
                        if(data.status === true)
                            $('#dynamicContent').empty().html(data.view);
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        btn.html('دریافت');
                        alert("انجام فرآیند امکان پذیر نیست");
                        //location.reload();
                        console.log(error);
                    }

                });
            });
            $('#report-section')
                .on('click','.print-btn',function () {
                    const btn = $(this);
                    const id = btn.attr('id');

                    let selector;
                    if(id === 'print-total')
                        selector = $('#dynamicContent');
                    else {
                        const card = btn.closest('.card');
                        selector = $(card);
                    }
                    selector.print({
                        globalStyles : true,
                        mediaPrint : true,

                        stylesheet : "{{asset('/css/style.min.css')}}"
                    });
                })
                .on('click','.dorm-btn',function () {
                    const btn = $(this);
                    const date = btn.attr('date');
                    const meal = btn.attr('meal');

                    if(date === '')
                        return false;

                    const validate1 = date.match(/(\d{4})-(\d{2})-(\d{2})/);
                    if(validate1 === null)
                        return false;

                    const closestCard = btn.closest('.card');
                    const dormView    = closestCard.find('.dorm-view');

                    btn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/reserves-report/total-dorm',
                        data: {
                            'date': date,
                            'meal': meal
                        },
                        dataType: 'json',
                        success: function(data)
                        {
                            btn.html('آمار خوابگاه');
                            if(data.status === 200)
                                dormView.empty().append(data.view);
                            else
                                alert(data.res);
                        },
                        error: function (error) {
                            btn.html('آمار خوابگاه');
                            alert("انجام فرآیند امکان پذیر نیست");
                            //location.reload();
                            console.log(error);
                        }

                    });

                });
        })
    </script>
@endsection
