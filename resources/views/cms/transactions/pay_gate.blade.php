@extends('layouts.cms')
@section('more_style')
    <link rel="stylesheet" href="/plugins/datepicker/persian-datepicker.min.css">
@endsection
@section('content')
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">{{$title}}
                <a href="/home/transactions/print-all" target="_blank" class="btn btn-info pull-left" title="نسخه قابل چاپ">چاپ
                    <i class="fa fa-print pull-left"></i>
                </a>
            </h4>
        </div>
    </div>
    <div class="card mt-1">
        <div class="card-body">
            <div class="col-sm-12">
                <form>
                    <div class="row">
                        <div class="col-sm-3">
                            <label class="control-label">از تاریخ</label>
                            <input type="text" class="form-control" id="beginDateTrans">
                        </div>
                        <div class="col-sm-3">
                            <label class="control-label">تا تاریخ</label>
                            <input type="text" class="form-control" id="endDateTrans">
                        </div>
                        <div class="col-sm-3">
                            <label class="control-label"> &nbsp;</label>
                            <button type="button" class="btn btn-success form-control" id="getTransByDate">دریافت</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6 d-none" id="transCounter1">
            <div class="card mt-1">
                <div class="card-body">
                    <div class="card-text text-center text-info" style="font-size: 32px"></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 d-none" id="transCounter2">
            <div class="card mt-1">
                <div class="card-body">
                    <div class="card-text text-center text-info" style="font-size: 32px"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-1">
        <div class="card-body table-responsive">
            <table class="table table-striped">
                <thead>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">نام</th>
                    <th class="text-center">کد پیگیری / شناسه قبض</th>
                    <th class="text-center">مبلغ (ریال)</th>
                    <th class="text-center">تاریخ پرداخت</th>
                </tr>
                </thead>
                <tbody id="tbodyTrans">
                <?php $i=1; ?>
                @foreach($trans as $data)
                    <tr>
                        <td>{{$i}}</td>
                        <td class="text-center">{{$data->name}}</td>
                        <td class="text-center">{{$data->reference_id}}</td>
                        <td class="text-center">{{$data->amount}}</td>
                        <td class="text-center">{{$data->created_at}}</td>
                    </tr>
                    <?php $i++; ?>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script>
        $("#beginDateTrans,#endDateTrans").persianDatepicker({
            initialValue: false,
            format: 'YYYY-MM-DD',
            formatter: function (unixDate) {
                var self = this;
                var pdate = new persianDate(unixDate);
                pdate.formatPersian = false;
                return pdate.format(self.format);
            }
        });
        $('#getTransByDate').on('click',function () {
            var beginDateTrans = jQuery.trim($('#beginDateTrans').val());
            var endDateTrans = jQuery.trim($('#endDateTrans').val());
            if(beginDateTrans == '' || endDateTrans == '') {
                alert('فیلدهای از تاریخ - تا تاریخ را پر کنید');
                return false;
            }

            var validate1 = beginDateTrans.match(/(\d{4})-(\d{2})-(\d{2})/);
            var validate2 = endDateTrans.match(/(\d{4})-(\d{2})-(\d{2})/);
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
            var btn = $(this);
            btn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
            $.ajax({
                cache: false,
                type: 'POST',
                url: '/home/transactions/get-by-date',
                data: {
                    'beginDateTrans' : beginDateTrans,
                    'endDateTrans'   : endDateTrans
                },
                dataType: 'json',
                success: function(data)
                {
                    if(data.status == true) {
                        var tr = '';
                        $.each(data.res,function (key,value) {
                            tr += '<tr>';
                            tr += '<td class="text-center">'+(key+1)+'</td>';
                            tr += '<td class="text-center">'+value.name+'</td>';
                            tr += '<td class="text-center">'+value.reference_id+'</td>';
                            tr += '<td class="text-center">'+value.amount+'</td>';
                            tr += '<td class="text-center">'+value.created_at+'</td>';
                            tr += '</tr>';
                        });
                        $('#transCounter3').removeClass('d-none');
                        $('#transCounter1').removeClass('d-none').find('.card-text').text('اینترنتی '+data.sum1+" ریال");
                        $('#tbodyTrans').empty().html(tr);
                        btn.html('دریافت');
                    }
                },
                error: function(error) {
                    alert('خطای ارتباط شبکه');
                    location.reload();
                    //console.log(error);
                }
            });
        });
    </script>
@endsection
