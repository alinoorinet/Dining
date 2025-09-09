@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/plugins/iziToast/iziToast.min.css">
    <link rel="stylesheet" href="/plugins/iziToast/demo.css">
@endsection
@section('content')
    @if (Rbac::get_active_temp() == 'stu')
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="card-title">رزرو دستی و روز فروش</h4>
            </div>
        </div>
        <div class="card">
            <div class="ui dimmer">
                <div class="ui large text loader">چند لحظه صبر کنید...</div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="card mt-2" style="min-height: 505px">
                            <div class="card-header">فرم رزرو دستی</div>
                            <div class="card-body">
                                <div class="alert alert-danger text-right d-none" id="formAlert" role="alert">
                                    <ul class="text-right pr-1">
                                    </ul>
                                </div>
                                <form id="manualResForm">
                                    <div class="col-sm-12 mb-3">
                                        <label for="date">تاریخ</label>
                                        <input type="text" class="form-control" id="date" title="تاریخ" placeholder="1396-01-01">
                                        <div class="invalid-feedback d-none"></div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label for="identify">وعده</label>
                                        <select class="custom-select" id="meal" title="وعده">
                                            <option value="صبحانه" @if($meal == 'صبحانه') selected @endif>صبحانه</option>
                                            <option value="نهار" @if($meal == 'نهار') selected @endif>نهار</option>
                                            <option value="شام" @if($meal == 'شام') selected @endif>شام</option>
                                        </select>
                                        <div class="invalid-feedback d-none"></div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label for="identify">منو غذا</label>
                                        <select class="custom-select" id="food" title="منو غذا">
                                            @foreach($foods as $food)
                                                <option value="{{$food->ddfId}}">{{$food->title}}</option>
                                            @endforeach
                                        </select>
                                        <div class="invalid-feedback d-none"></div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <label for="identify">شناسه کاربری یا شماره دانشجویی</label>
                                        <input type="text" class="form-control" id="identify" name="identify">
                                        <div class="invalid-feedback d-none"></div>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <strong class="font-weight-bold text-info border-bottom-1">نوع رزرو:</strong>
                                        <label class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="1" name="resType">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description"> عادی</span>
                                        </label>
                                        <label class="custom-control custom-radio">
                                            <input type="radio" class="custom-control-input" value="2" name="resType">
                                            <span class="custom-control-indicator"></span>
                                            <span class="custom-control-description"> روز فروش</span>
                                        </label>
                                    </div>
                                    <div class="col-sm-12 mb-3">
                                        <button class="btn btn-secondary btn-block" type="button" id="check">بررسی</button>
                                    </div>
                                </form>
                                <div class="d-none mt-2" id="processBox">
                                    <div class="text-center">
                                        <h2>اطلاعات کاربر</h2><hr>
                                        <div id="sec1">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="mt-2" id="priceBox">
                        </div>
                        <div class="alert alert-danger text-right mt-2 d-none" id="priceBoxAlert" role="alert">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @elseif (Rbac::get_active_temp() == 'freeSelf')
        <div class="section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12">
                        <div class="card">
                            <div class="ui dimmer">
                                <div class="ui large text loader">چند لحظه صبر کنید...</div>
                            </div>
                            <div class="card-body">
                                <form id="sale-day-form" method="post">
                                    @csrf
                                    <div class="row">
                                        <div class="col-lg-5 col-md-5 col-sm-5">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm" id="foodsTbl">
                                                    <thead>
                                                    <tr>
                                                        <th class="text-right">#</th>
                                                        <th class="text-center"><i class="fa fa-check-square"></i></th>
                                                        <th class="text-center">لیست غذا</th>
                                                        <th class="text-center">تعداد</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($foods as $food)
                                                        @php
                                                            $nimPors = $food->pors == "پرس" ? "" : "<span class='badge badge-warning p-1'>ن پ</span>";
                                                        @endphp
                                                        <tr>
                                                            <td class="text-right align-middle">{{$loop->index +1}}</td>
                                                            <td class="text-center align-middle">
                                                                <div class="form-check mt-0">
                                                                    <label class="form-check-label">
                                                                        <input class="form-check-input" name="chosenFoods[]" type="checkbox" value="{{$food->id}}">
                                                                        <span class="form-check-sign"></span> &nbsp;
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">{{$food->food_menu->title}} {!! $nimPors !!}</td>
                                                            <td class="text-center align-middle">
                                                                <input type="number" class="text-center" min="1" name="food-count-{{$food->id}}" style="max-width: 70px">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-5 col-sm-5">
                                            <div class="table-responsive">
                                                <table class="table table-bordered table-sm" id="optsTbl">
                                                    <thead>
                                                    <tr>
                                                        <th scope="col" class="text-right">#</th>
                                                        <th scope="col" class="text-center"><i class="fa fa-check-square"></i></th>
                                                        <th scope="col" class="text-center">لیست مخلفات</th>
                                                        <th scope="col" class="text-center">تعداد</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @foreach($opts as $opt)
                                                        <tr>
                                                            <td class="text-right align-middle">{{$loop->index +1}}</td>
                                                            <td class="text-center align-middle">
                                                                <div class="form-check mt-0">
                                                                    <label class="form-check-label">
                                                                        <input class="form-check-input" name="chosenOpts[]" type="checkbox" value="{{$opt->id}}">
                                                                        <span class="form-check-sign"></span> &nbsp;
                                                                    </label>
                                                                </div>
                                                            </td>
                                                            <td class="text-center align-middle">{{$opt->opt->title}}</td>
                                                            <td class="text-center align-middle">
                                                                <input type="number" class="text-center" min="1" name="opt-count-{{$opt->id}}" style="max-width: 70px">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        <div class="col-lg-2 col-md-2 col-sm-2">
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="form-group">
                                                        <label>تاریخ</label>
                                                        <input type="text" class="form-control" name="date" id="date" autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="form-group">
                                                        <label>روز</label>
                                                        <input type="text" class="form-control" name="day" id="day" value="{{$day}}">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="form-group">
                                                        <label>ثبت رزرو برای:</label>
                                                        <div class="d-block">
                                                            <input type="radio" class="d-inline-block align-middle" name="user-mode" id="x1" value="user" checked>
                                                            <label for="x1" class="d-inline-block">کاربران دانشگاهی</label>
                                                            <input type="text" class="form-control" name="uid" id="uid" placeholder="کدملی/ش.دانشجویی">
{{--                                                            <button type="button" class="btn btn-info checkInfo d-none" id="check-info-user">بررسی رزرو</button>--}}
                                                        </div>
                                                        <div class="d-block">
                                                            <input type="radio" class="d-inline-block align-middle" name="user-mode" id="x2" value="out">
                                                            <label for="x2" class="d-inline-block">غیردانشگاهی</label>
                                                            <label class="text-muted d-none" id="out-add-wallet-label">افزایش اعتبار کیف پول برای غیر دانشگاهیان</label>
                                                            <input type="number" class="form-control d-none" value="0" name="out_add_wallet" id="out-add-wallet" placeholder="مبلغ به ریال" min="0">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-lg-12 col-md-12 col-sm-12">
                                                    <div class="form-group">
                                                        <button class="btn btn-secondary" type="submit">ثبت</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <hr style="margin-top: 3rem; margin-bottom: 3rem">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12" id="msg-box">
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    @if (Rbac::get_active_temp() == 'stu')
        <script>
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $("#date").persianDatepicker({
                initialValue: true,
                format: 'YYYY-MM-DD',
                autoClose : true,
                formatter: function (unixDate) {
                    var self = this;
                    var pdate = new persianDate(unixDate);
                    pdate.formatPersian = false;
                    return pdate.format(self.format);
                },
                onSelect: function (unixDate) {
                    $('.ui.dimmer').dimmer('show');
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/sale-day/date-changed',
                        data: JSON.stringify({date:$("#date").val()}),
                        dataType: 'json',
                        dataContent: 'application/json',
                        processData: false,
                        success: function (data) {
                            $('.ui.dimmer').dimmer('hide');
                            if(data.status == false) {
                                alert(data.res);
                            }
                            else {
                                $('#meal').empty().html(data.meal);
                                $('#food').empty().html(data.food);
                            }
                        },
                        error: function (data) {
                            $('.ui.dimmer').dimmer('show');
                            location.reload();
                        }
                    });
                }
            });
            $('#manualResForm').on('click','#check',function () {
                var id = $('#food option:selected').val();
                var identify = $('#identify').val();
                var date = $('#date').val();
                var meal = $('#meal').val();
                var errors = [];
                var resType = $('input[name=resType]:checked');
                var alert = '#formAlert';
                if(id == '' || id == undefined)
                    errors.push('غذا را انتخاب کنید');
                if(identify == '')
                    errors.push('شماره دانشجویی یا شناسه کاربری یکتا را وارد کنید');
                if(date == '')
                    errors.push('تاریخ را وارد کنید');
                if(!resType.is(':checked'))
                    errors.push('نوع رزرو را انتخاب کنید');
                if(errors != '') {
                    $(alert+ ' ul').empty();
                    $.each(errors, function (key, value) {
                        $('#formAlert ul').append('<li>' + value + '</li>');
                    });
                    $(alert).removeClass('d-none');
                    return false;
                }
                else {
                    errors = [];
                    $(alert).find('ul').empty();
                    $(alert).addClass('d-none');
                }
                var data = JSON.stringify({
                    id: id,
                    identify: identify,
                    type: resType.val(),
                    date: date,
                    meal: meal
                });
                $('.ui.dimmer').dimmer('show');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/sale-day/check-info',
                    data: data,
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        $('.ui.dimmer').dimmer('hide');
                        if(data.status == 101)
                            $('#processBox').removeClass('d-none').find('h2').addClass('text-danger').text(data.res);
                        else if(data.status == 102)
                            $('#processBox').removeClass('d-none').find('h2').text('اطلاعات کاربر').closest('div').find('#sec1').html(data.res);
                        else if(data.status == 103) {
                            if(data.deny)
                                $('#processBox').removeClass('d-none').find('h2').text('اطلاعات کاربر').closest('div').find('#sec1').html(data.info + data.deny);
                            else
                                $('#processBox').removeClass('d-none').find('h2').text('اطلاعات کاربر').closest('div').find('#sec1').html(data.info);
                            $('#priceBox').html(data.price);
                        }
                        else if(data.status == 104) {
                            $('#processBox').removeClass('d-none').find('h2').text('اطلاعات کاربر').closest('div').find('#sec1').html(data.info);
                            $('#priceBox').html(data.price);
                        }
                        else if(data.status == 105)
                            alert(data.res);
                        else if(data.status == 106) {
                            $('#processBox').removeClass('d-none').find('h2').text('اطلاعات کاربر').closest('div').find('#sec1').html(data.info + data.reserved);
                        }
                    },
                    error: function (error) {
                        $('.ui.dimmer').dimmer('hide');
                        //console.log(error);
                        location.reload();
                    }
                });
            }).on('change','#meal',function () {
                var meal = $(this).val();
                var date = $('#date').val();
                if(date == '') {
                    alert('تاریخ را وارد کنید');
                    return false;
                }
                $('.ui.dimmer').dimmer('show');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/sale-day/meal-changed',
                    data: JSON.stringify({
                        meal : meal,
                        date: date
                    }),
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        if(data.status == false) {
                            alert(data.res);
                        }
                        else {
                            $('.ui.dimmer').dimmer('hide');
                            $('#food').empty().html(data.food);
                        }
                    },
                    error: function (data) {
                        $('.ui.dimmer').dimmer('hide');
                        location.reload();
                    }
                });
            });
            $('#priceBox').on('click','#submitRes',function () {
                var price      = null;
                var newPrice   = null;
                var newPriceCh = $('#priceBox #newPriceCh');
                var pricelist  = $('#priceBox input[name=prices]');
                $('.invalid-feedback').addClass('d-none').text('');
                if(!newPriceCh.is(':checked') && !pricelist.is(':checked')) {
                    alert('لطفاً قیمت را از [لیست قیمت ها] و یا به عنوان [قیمت جدید] وارد کنید');
                    return false;
                }
                if(newPriceCh.is(':checked')) {
                    newPrice = $('#priceBox #newPrice').val();
                    if(newPrice == '') {
                        $('#priceBox #newPrice').closest('div').find('.invalid-feedback').addClass('d-block').text('قیمت جدید را وارد کنید');
                        return false;
                    }
                }
                if(pricelist.is(':checked'))
                {
                    price = $('#priceBox input[name=prices]:checked').val();
                }
                var data = JSON.stringify({
                    newPrice: newPrice,
                    price   : price
                });
                $('.ui.dimmer').dimmer('show');
                $('#priceBoxAlert').text('').addClass('d-none');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/sale-day/set-reserve',
                    data: data,
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        $('.ui.dimmer').dimmer('hide');
                        if(data.status == 102)
                            $('#priceBoxAlert').removeClass('d-none').text(data.res +'<br>'+'موجودی کاربر در این لحظه: '+data.amount);
                        else
                            $('#priceBoxAlert').removeClass('d-none').text(data.res);

                    },
                    error: function (data) {
                        $('.ui.dimmer').dimmer('hide');
                        location.reload();
                    }
                });
            }).on('change','#newPriceCh',function () {
                var chbox = $(this);
                if(chbox.is(':checked'))
                    $('#priceBox #newPrice').removeClass('d-none').val('');
                else {
                    $('.invalid-feedback').text('').addClass('d-none');
                    $('#priceBox #newPrice').val('').addClass('d-none');
                }
            });
            $('#processBox').on('click','#cancelReserve',function () {
                $('.ui.dimmer').dimmer('show');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/sale-day/cancel-reserve',
                    data: {},
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        $('.ui.dimmer').dimmer('hide');
                        if (data.status == false)
                            alert(data.res);
                        else {
                            alert(data.res);
                            location.reload();
                        }
                    },
                    error: function (error) {
                        $('.ui.dimmer').dimmer('hide');
                        location.reload();
                    }
                });
            })
        </script>
    @elseif (Rbac::get_active_temp() == 'freeSelf')
        <script src="/plugins/iziToast/iziToast.min.js"></script>
        <script>
            $(function () {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                let date = $('#date');
                date.persianDatepicker({
                    initialValue: false,
                    format: 'YYYY-MM-DD',
                    autoClose : true,
                    formatter: function (unixDate) {
                        var self = this;
                        var pdate = new persianDate(unixDate);
                        pdate.formatPersian = false;
                        return pdate.format(self.format);
                    },
                    onSelect: function (unixDate) {
                        var pdate = new persianDate(unixDate);
                        var days = [];
                        days[1] = ["شنبه"];
                        days[2] = ["یکشنبه"];
                        days[3] = ["دوشنبه"];
                        days[4] = ["سه شنبه"];
                        days[5] = ["چهارشنبه"];
                        days[6] = ["پنج شنبه"];
                        days[7] = ["جمعه"];
                        let d = pdate.days();
                        $('#day').val(days[d]);

                        $.ajax({
                            cache: false,
                            type: 'POST',
                            url: '/home/sale-day/date-changed',
                            data: JSON.stringify({date:$("#date").val()}),
                            dataType: 'json',
                            dataContent: 'application/json',
                            processData: false,
                            success: function (data) {
                                $('.ui.dimmer').dimmer('hide');
                                if(data.status === 200) {
                                    $('#foodsTbl tbody').html(data.foods);
                                    $('#optsTbl tbody').html(data.opts);
                                }
                                else
                                    iziToast.show({
                                        id: 'haduken',
                                        theme: 'dark',
                                        icon: 'icon-contacts',
                                        title: 'پیام سیستم',
                                        displayMode: 2,
                                        message: data.res,
                                        position: 'topCenter',
                                        transitionIn: 'flipInX',
                                        transitionOut: 'flipOutX',
                                        progressBarColor: 'rgb(0, 255, 184)',
                                        image: '/img/error.png',
                                        imageWidth: 70,
                                        layout: 2,
                                        timeout: 2000,
                                        resetOnHover: true,
                                        rtl: true,
                                        iconColor: 'rgb(0, 255, 184)'
                                    });
                            },
                            error: function (error) {
                                $('.ui.dimmer').dimmer('show');
                                iziToast.show({
                                    id: 'haduken',
                                    theme: 'dark',
                                    icon: 'icon-contacts',
                                    title: 'پیام سیستم',
                                    displayMode: 2,
                                    message: 'درحال حاضر انجام فرآیند امکان پذیر نیست',
                                    position: 'topCenter',
                                    transitionIn: 'flipInX',
                                    transitionOut: 'flipOutX',
                                    progressBarColor: 'rgb(0, 255, 184)',
                                    image: '/img/error.png',
                                    imageWidth: 70,
                                    layout: 2,
                                    timeout: 2000,
                                    resetOnHover: true,
                                    rtl: true,
                                    iconColor: 'rgb(0, 255, 184)'
                                });
                                //console.log(error);
                                location.reload();
                            }
                        });
                    }
                });
                date.val("{{$date}}");
                $('input[name=user-mode]').on('change',function () {
                    let rdo = $(this);
                    let rdoVal = rdo.val();
                    if(rdoVal === 'user') {
                        $('#uid').removeClass('d-none').addClass('d-inline-block');
                        $('#check-info-user').removeClass('d-none');
                        $('#check-info-guest').addClass('d-none');
                        $('#out-add-wallet,#out-add-wallet-label').addClass('d-none');
                        $('#out-add-wallet-label').removeClass('d-block');
                    }
                    else if(rdoVal === 'out') {
                        $('#uid').removeClass('d-inline-block').addClass('d-none');
                        $('#check-info-user').addClass('d-none');
                        $('#out-add-wallet,#out-add-wallet-label').removeClass('d-none');
                        $('#out-add-wallet-label').addClass('d-block');
                    }
                });
                $('#sale-day-form').on('submit',function (e) {
                    e.preventDefault();
                    var msgBox =  $('#msg-box');
                    msgBox.html('');
                    $('.form-control').removeClass('is-invalid');
                    $('.invalid-feedback').remove();
                    var form = $(this);
                    var date   = form.find('#date');
                    $('.ui.dimmer').dimmer('show');
                    var data = new FormData(this);
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/sale-day/set-reserve',
                        data: data,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function (data) {
                            $('.ui.dimmer').dimmer('hide');
                            if(data.status === 200) {
                                iziToast.show({
                                    id: 'haduken',
                                    theme: 'dark',
                                    icon: 'icon-contacts',
                                    title: 'پیام سیستم',
                                    displayMode: 2,
                                    message: data.res,
                                    position: 'topCenter',
                                    transitionIn: 'flipInX',
                                    transitionOut: 'flipOutX',
                                    progressBarColor: 'rgb(0, 255, 184)',
                                    image: '/img/success.png',
                                    imageWidth: 70,
                                    layout: 2,
                                    timeout: 2000,
                                    resetOnHover: true,
                                    rtl: true,
                                    iconColor: 'rgb(0, 255, 184)'
                                });
                                msgBox.html(data.orders);
                            }
                            else if(data.status === 101)
                                $.each(data.res,function (k,v) {
                                    let name = $("input[name='"+k+"'],textarea[name='"+k+"']");
                                    if(k.indexOf(".") !== -1) {
                                        const arr = k.split(".");
                                        name = $("input[name='"+arr[0]+"[]']:eq("+arr[1]+")");
                                    }
                                    name.addClass('is-invalid').after('<div class="invalid-feedback text-right">'+v[0]+'</div>');
                                });
                            else
                                iziToast.show({
                                    id: 'haduken',
                                    theme: 'dark',
                                    icon: 'icon-contacts',
                                    title: 'پیام سیستم',
                                    displayMode: 2,
                                    message: data.res,
                                    position: 'topCenter',
                                    transitionIn: 'flipInX',
                                    transitionOut: 'flipOutX',
                                    progressBarColor: 'rgb(0, 255, 184)',
                                    image: '/img/error.png',
                                    imageWidth: 70,
                                    layout: 2,
                                    timeout: 2000,
                                    resetOnHover: true,
                                    rtl: true,
                                    iconColor: 'rgb(0, 255, 184)'
                                });
                        },
                        error: function (error) {
                            $('.ui.dimmer').dimmer('hide');
                            alert("انجام فرآیند در حال حاضر امکان پذیر نیست");
                            //location.reload();
                            console.log(error);
                        }
                    });
                });
                $('#msg-box').on('click','.cancel-reserve',function (e) {
                    e.preventDefault();
                    var msgBox = $('#msg-box');
                    let btn = $(this);
                    let id = btn.attr('id');
                    $('.ui.dimmer').dimmer('show');
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/sale-day/cancel-reserve',
                        data: {"dd" : id},
                        dataType: 'json',
                        success: function (data) {
                            $('.ui.dimmer').dimmer('hide');
                            if(data.status === 200) {
                                iziToast.show({
                                    id: 'haduken',
                                    theme: 'dark',
                                    icon: 'icon-contacts',
                                    title: 'پیام سیستم',
                                    displayMode: 2,
                                    message: data.res,
                                    position: 'topCenter',
                                    transitionIn: 'flipInX',
                                    transitionOut: 'flipOutX',
                                    progressBarColor: 'rgb(0, 255, 184)',
                                    image: '/img/success.png',
                                    imageWidth: 70,
                                    layout: 2,
                                    timeout: 2000,
                                    resetOnHover: true,
                                    rtl: true,
                                    iconColor: 'rgb(0, 255, 184)'
                                });
                                btn.closest('.list-group-item').remove();
                            }
                            else if(data.status === 101) {
                                let htm = '';
                                $.each(data.res,function (k,v) {
                                    htm += '<div class="alert alert-danger text-right">'+v+'</div><br>';
                                });
                                msgBox.html(htm);
                            }
                            else
                                iziToast.show({
                                    id: 'haduken',
                                    theme: 'dark',
                                    icon: 'icon-contacts',
                                    title: 'پیام سیستم',
                                    displayMode: 2,
                                    message: data.res,
                                    position: 'topCenter',
                                    transitionIn: 'flipInX',
                                    transitionOut: 'flipOutX',
                                    progressBarColor: 'rgb(0, 255, 184)',
                                    image: '/img/error.png',
                                    imageWidth: 70,
                                    layout: 2,
                                    timeout: 2000,
                                    resetOnHover: true,
                                    rtl: true,
                                    iconColor: 'rgb(0, 255, 184)'
                                });
                        },
                        error: function (error) {
                            $('.ui.dimmer').dimmer('hide');
                            alert("انجام فرآیند در حال حاضر امکان پذیر نیست");
                            location.reload();
                            //console.log(error);
                        }
                    });
                });
                $('.checkInfo').on('click',function (e) {
                    if(!$('input[name=user-mode]').is(':checked'))
                        return false;
                    var userMode = $('input[name=user-mode]:checked').val();
                    let uid;
                    if(userMode === 'user')
                        uid = $('#uid').val();
                    else if(userMode === 'guest')
                        uid = 'guest';
                    var date   = $('#sale-day-form').find('#date');
                    date = $(date).val();
                    if(date === '' || uid === '') {
                        alert('تاریخ و (کد ملی/ش.دانشجویی) را وارد کنید');
                        return false;
                    }
                    $('.ui.dimmer').dimmer('show');

                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/sale-day/check-reserve',
                        data: {"date":date,"credential":uid},
                        dataType: 'json',
                        success: function (data) {
                            $('.ui.dimmer').dimmer('hide');
                            if(data.status === 200)
                                $('#msg-box').html(data.res);
                            else
                                iziToast.show({
                                    id: 'haduken',
                                    theme: 'dark',
                                    icon: 'icon-contacts',
                                    title: 'پیام سیستم',
                                    displayMode: 2,
                                    message: data.res,
                                    position: 'topCenter',
                                    transitionIn: 'flipInX',
                                    transitionOut: 'flipOutX',
                                    progressBarColor: 'rgb(0, 255, 184)',
                                    image: 'img/error.png',
                                    imageWidth: 70,
                                    layout: 2,
                                    timeout: 2000,
                                    resetOnHover: true,
                                    rtl: true,
                                    iconColor: 'rgb(0, 255, 184)'
                                });
                                //console.log(data.res);
                        },
                        error: function (error) {
                            $('.ui.dimmer').dimmer('hide');
                            alert("انجام فرآیند در حال حاضر امکان پذیر نیست");
                            location.reload();
                            //console.log(error);
                        }
                    });
                });
            });
        </script>
    @endif
@endsection
