@extends('layouts.cms')
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item">رزرو</li>
        <li class="breadcrumb-item active">
            <a href="/reserves-report/manual-check">بازگشت رزرو</a>
        </li>
    </ol>
@endsection
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12 col-12">
            <h5 id="msg-box"></h5>
            <div class="card">
                <div class="card-header">بازگشت رزرو گروهی</div>
                <div class="ui dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">
                    <div id="searchReserveTbl">
                        <div class="row">
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>تاریخ</label>
                                    <input type="text" class="form-control bg-white" id="date" title="تاریخ" placeholder="1396-01-01" value="">
                                </div>
                            </div>
                            <div class="col-sm-3">
                                <div class="form-group">
                                    <label>وعده</label>
                                    <select class="custom-select" id="meal" title="وعده">
                                        @foreach($meals as $meal)
                                            <option value="{{$meal}}">{{$meal}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label>دسته</label>
                                    <select class="custom-select" id="cat" title="دسته">
                                        @foreach($cats as $cat)
                                            <option value="{{$cat->id}}">{{$cat->title}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group text-right">
                                    <label>برگشت مبالغ</label>
                                    <input type="checkbox" class="form-control" id="delete" name="delete" >
                                </div>
                            </div>
                            <div class="col-sm-2">
                                <div class="form-group">
                                    <label></label>
                                    <a class="btn btn-info btn-block text-white searchBtn" href="javascript:void(0)" id="searchBtn">
                                        <i class="fa fa-search pull-right"></i> مشاهده
                                    </a>
                                </div>
                            </div>
                            <div class="col-12">
                                <p><i class="fa fa-exclamation text-warning ml-2"></i>در صورتی که قصد بازگشت مبالغ در تاریخ مورد نظر را دارید، حتما تیک برگشت مبلغ را علامت بزنید.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-8 col-md-8 col-sm-8 col-12 mb-3" id="editModal"></div>
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card" id="rfid-check-reserve-card">
                <div class="card-header">لیست</div>
                <div class="ui dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">

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
                    let searchBtn = $(".searchBtn");
                    const btnHtml = searchBtn.html();
                    var cat = $('#searchReserveTbl #cat').val();
                    var meal = $('#searchReserveTbl #meal').val();
                    var date = $('#searchReserveTbl #date').val();
                    var del = $('#searchReserveTbl #delete').is(":checked");
                    if (cat === '' || meal === '' || date === '')
                        return false;
                    console.log(date);
                    console.log(cat);
                    console.log(meal);

                    var data = JSON.stringify({
                        cat: cat,
                        date: date,
                        meal: meal,
                        del: del
                    });
                    searchBtn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                    $.ajaxSetup({
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        }
                    });
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/reserves-report/pay-back-res',
                        data: data,
                        dataType: 'json',
                        dataContent: 'application/json',
                        processData: false,
                        success: function (data) {
                            searchBtn.html(btnHtml);
                            if (data.status === true) {
                                const resBox = $('#rfid-check-reserve-card');
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
                            // location.reload();
                            console.log(error);
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
