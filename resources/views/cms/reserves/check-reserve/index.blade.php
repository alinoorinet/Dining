@extends('layouts.cms')
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item">رزرو</li>
        <li class="breadcrumb-item active">
            <a href="/reserves-report/manual-check">بررسی رزرو</a>
        </li>
    </ol>
@endsection
@section('content')
    <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                <h5 id="msg-box"></h5>
                <div class="card">
                    <div class="card-header">بررسی وضعیت رزرو بر اساس تاریخ</div>
                    <div class="ui dimmer">
                        <div class="ui large text loader">چند لحظه صبر کنید...</div>
                    </div>
                    <div class="card-body">
                        <div id="searchReserveTbl">
                            <div class="form-group">
                                <label>کد ملی / ش.دانشجویی</label>
                                <input type="text" class="form-control bg-white ltr" id="uid" tabindex="1">
                            </div>
                            <div class="form-group">
                                <label>وعده</label>
                                <select class="custom-select" id="meal" title="وعده">
                                    @foreach($meals as $meal)
                                        <option value="{{$meal}}" @if($meal == 'نهار') selected @endif>{{$meal}}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>تاریخ</label>
                                <input type="text" class="form-control bg-white" id="date" title="تاریخ" placeholder="1396-01-01" value="{{$today}}">
                            </div>
                            <div class="form-group">
                                <a class="btn btn-warning btn-block searchBtn" href="javascript:void(0)" id="searchBtn1">
                                    <i class="fa fa-search pull-right"></i> بررسی و استفاده
                                </a>
                            </div>
                            <div class="form-group">
                                <a class="btn btn-info btn-block text-white searchBtn" href="javascript:void(0)" id="searchBtn2">
                                    <i class="fa fa-search pull-right"></i> بررسی
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8 col-md-8 col-sm-8 col-12 mb-3" id="editModal"></div>
            <div class="col-lg-12 col-md-12 col-sm-12">
                <div class="card" id="rfid-check-reserve-card">
                    <div class="card-header">بررسی با کارت خوان</div>
                    <div class="ui dimmer">
                        <div class="ui large text loader">چند لحظه صبر کنید...</div>
                    </div>
                    <div class="card-body">
                        <div class="card-title">قبل از کشیدن کارت روی کادر کلیک کنید</div>
                        <form method="post" id="rfid-check-reserve-form">
                            @csrf
                            <div class="form-group">
                                <input type="text" class="form-control text-left ltr" id="card_uid_input" name="card_uid" autofocus>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
@section('more_script')
    <script src="/plugins/print/jQuery.print.js"></script>
    <script src="/js/cms/wb.js"></script>
    <script>
        $(function () {
            $('#searchReserveTbl')
                .on('click', '.searchBtn', function () {
                    var searchBtn = $(this);
                    const btnId   = searchBtn.attr('id');
                    const btnHtml = searchBtn.html();
                    var uid = $('#searchReserveTbl #uid').val();
                    var meal = $('#searchReserveTbl #meal').val();
                    var date = $('#searchReserveTbl #date').val();
                    if (uid === '' || meal === '' || date === '')
                        return false;

                    const mark_as_eaten = btnId === 'searchBtn1' ? 1: 0;

                    var data = JSON.stringify({
                        uid: uid,
                        date: date,
                        meal: meal,
                        read_type: "manual",
                        mark_as_eaten: mark_as_eaten
                    });
                    searchBtn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/reserves-report/check-reserve',
                        data: data,
                        dataType: 'json',
                        dataContent: 'application/json',
                        processData: false,
                        success: function (data) {
                            searchBtn.html(btnHtml);
                            if (data.status === true) {
                                const resBox = $('#editModal');
                                resBox.html(data.res);
                                // const autoPrint = resBox.find('.auto-print');
                                // $(autoPrint).trigger('click');
                            }
                            else {
                                alert(data.res);
                            }
                        },
                        error: function (error) {
                            searchBtn.html(btnHtml);
                            alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                            location.reload();
                            //console.log(error);
                        }
                    });
                })
                .on('keyup', function (e) {
                    if (e.keyCode === 13)
                        $('.searchBtn#searchBtn2').trigger('click');
            });
            $('#editModal')
                .on('change', 'input[class=set-one]', function () {
                    var ch = $(this);
                    var id = ch.attr('id');
                    var uid  = $('#searchReserveTbl #uid').val();
                    var meal = $('#searchReserveTbl #meal').val();
                    var date = $('#searchReserveTbl #date').val();
                    let queueSelector = $('#editModal').find('#queue-name');
                    let queueName = $(queueSelector).val();
                    if (id !== '' && uid !== '' || meal !== '' || date !== '') {
                        var data = null;
                        if (ch.is(':checked'))
                            data = JSON.stringify({
                                id: id,
                                uid: uid,
                                date: date,
                                meal: meal,
                                mode: 1,
                                queueName:queueName
                            });
                        else
                            data = JSON.stringify({
                                id: id,
                                uid: uid,
                                date: date,
                                meal: meal,
                                mode: 0,
                                queueName:queueName
                            });
                        $('.ui.dimmer').dimmer('show');
                        $.ajax({
                            cache: false,
                            type: 'POST',
                            url: '/home/reserves-report/mark-as-eaten',
                            data: data,
                            dataType: 'json',
                            dataContent: 'application/json',
                            processData: false,
                            success: function (data) {
                                $('.ui.dimmer').dimmer('hide');
                                alert(data.res);
                            },
                            error: function (error) {
                                $('.ui.dimmer').dimmer('hide');
                                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                                location.reload();
                                //console.log(error);
                            }
                        });
                    }
                    else {
                        alert('وارد کردن تاریخ، وعده و شناسه یکتا الزامی است');
                    }
                })
                .on('click', 'button[name=setAll]', function () {
                    var ch = $(this);
                    var id = 'setAll';
                    var uid = $('#searchReserveTbl #uid').val();
                    var meal = $('#searchReserveTbl #meal').val();
                    var date = $('#searchReserveTbl #date').val();
                    // let queueSelector = $('#editModal').find('#queue-name');
                    // let queueName = $(queueSelector).val();
                    if (id !== '' && uid !== '' || meal !== '' || date !== '') {
                        var data = JSON.stringify({
                                id: id,
                                uid: uid,
                                date: date,
                                meal: meal,
                                mode: ch.attr('value'),
                                queueName: 0
                            });
                        $('.ui.dimmer').dimmer('show');
                        $.ajax({
                            cache: false,
                            type: 'POST',
                            url: '/home/reserves-report/mark-as-eaten',
                            data: data,
                            dataType: 'json',
                            dataContent: 'application/json',
                            processData: false,
                            success: function (data) {
                                $('.ui.dimmer').dimmer('hide');
                                let msgColor = "success";
                                if(data.status === false)
                                    msgColor = "danger";
                                $('#searchBtn2').trigger('click');
                                $('#msg-box').html('<strong class="text-'+msgColor+'">'+data.res+'</strong>');
                                setTimeout(function (){
                                    $('#msg-box').html('');
                                }, 2000)
                            },
                            error: function (error) {
                                $('.ui.dimmer').dimmer('hide');
                                alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                                location.reload();
                                //console.log(error);
                            }
                        });
                    }
                    else {
                        alert('وارد کردن تاریخ، وعده و شناسه یکتا الزامی است');
                    }
                })
                .on('click','#print',function () {
                    printData();
                })
                .on('click','#submit-edit-modal',function (e) {
                    $('#editModal #order-form').trigger('submit');
                });
            function printData()
            {
                $('#resBox #order-tbl').find('.status-col').remove();
                let divToPrint = document.getElementById("order-tbl");
                newWin= window.open("");
                newWin.document.write('<html><head><link href="/v2/css/cms.css" rel="stylesheet" type="text/css"></head><body>' + divToPrint.outerHTML + '</body></html>');
                // newWin.document.write(divToPrint.outerHTML);
                // newWin.onload = function() { self.print(); }
                // newWin.focus();
                setTimeout(function() {
                    newWin.print();
                    newWin.close();
                }, 250);
                // newWin.print();
                // newWin.close();
                const cardUid = $('#rfid-check-reserve-form').find('input[name="card_uid"]');
                $(cardUid).val('').trigger('focus');
            }

            $('#rfid-check-reserve-card').on('click',function () {
                const cardUid = $('#rfid-check-reserve-form').find('input[name="card_uid"]');
                $(cardUid).trigger('focus');
            });
            $('#rfid-check-reserve-form').on('enter','input[name=card_uid]',function () {
                alert();
                // trigger #rfid-check-reserve-form submit
            }).on('submit',function (e) {
                e.preventDefault();
                $('.ui.dimmer').dimmer('show');
                const cardUid = $('#rfid-check-reserve-form').find('input[name="card_uid"]');
                if(cardUid.val() === '') {
                    alert("لطفا مجددا کارت را روی کارتخوان بگذارید");
                    return false;
                }

                const data = JSON.stringify({
                    read_type: "rfid",
                    uid : cardUid.val()
                });

                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/reserves-report/check-reserve',
                    data: data,
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        $('.ui.dimmer').dimmer('hide');
                        if (data.status === true) {
                            const resBox = $('#resBox');
                            resBox.html(data.res);
                            const autoPrint = resBox.find('.auto-print');
                            $(autoPrint).trigger('click');
                            $('#card_uid_input').val('');
                        }
                        else {
                            alert(data.res);
                        }
                    },
                    error: function (error) {
                        $('.ui.dimmer').dimmer('hide');
                        alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                        location.reload();
                        //console.log(error);
                    }
                });
            })
        });
    </script>
@endsection
