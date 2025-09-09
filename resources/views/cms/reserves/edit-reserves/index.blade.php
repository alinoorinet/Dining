@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item">رزرو</li>
        <li class="breadcrumb-item active">
            <a href="/reserves-report/edit-reserve-name">ویرایش عناوین رزرو</a>
        </li>
    </ol>
@endsection
@section('content')
    <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-4 col-12">
                <h5 id="msg-box"></h5>
                <div class="card">
                    <div class="card-header">ویرایش عناوین رزرو</div>
                    <div class="card-body">
                        <h5><i class="fa fa-exclamation-triangle"></i> در صورت نیاز ابتدا در صفحه <a href="/home/define-day-food/add">چیدمان منو</a> غذاها را ویرایش کنید</h5>
                        <form id="get-food-form">
                            @csrf
                            <div class="form-group">
                                <label>تاریخ</label>
                                <input type="text" class="form-control bg-white" id="date" autocomplete="off">
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
                                <button type="submit" class="btn btn-info btn-block text-white searchBtn" id="get-btn">
                                    <i class="fa fa-search pull-right"></i> دریافت عناوین
                                </button>
                            </div>
                        </form>
                        <form id="update-food-form">
                            @csrf
                            <div class="form-group">
                                <label>لیست غذاها</label>
                                <select class="custom-select" id="food_title">
                                </select>
                            </div>
                            <div class="form-group">
                                <label>عنوان جدید</label>
                                <input type="text" class="form-control bg-white" id="new_food_title">
                            </div>
                            <div class="form-group">
                                <button type="submit" class="btn btn-success btn-block text-white searchBtn" id="update-btn">بروزرسانی</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
@endsection
@section('more_script')
    <script src="/plugins/print/jQuery.print.js"></script>
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script>
        $(function () {
            $("#date").persianDatepicker({
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
            $('#get-food-form')
                .on('submit', function (e) {
                    e.preventDefault();
                    const form = $(this);
                    const submitBtn = form.find('button[type=submit]');
                    const btnHtml = submitBtn.html();
                    const date = $(form.find('input#date')).val();
                    const meal = $(form.find('select#meal')).val();
                    if (meal === '' || date === '') {
                        alert("پر کردن فرم الزامی است")
                        return false;
                    }
                    const data = JSON.stringify({
                        date: date,
                        meal: meal,
                    });
                    submitBtn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/reserves-report/edit-reserve-name/get_names',
                        data: data,
                        dataType: 'json',
                        dataContent: 'application/json',
                        processData: false,
                        success: function (data) {
                            submitBtn.html(btnHtml);
                            if (data.status === 200) {
                                console.log(data.res);
                                const foodTitleSelect = $('#food_title');
                                foodTitleSelect.empty().append('<option value="">...</option>');
                                $.each(data.res, function (index, foodTitle){
                                    foodTitleSelect.append('<option value="'+foodTitle+'">'+foodTitle+'</option>');
                                })
                            }
                            else {
                                alert(data.res);
                            }
                        },
                        error: function (error) {
                            submitBtn.html(btnHtml);
                            alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                            //location.reload();
                            console.log(error);
                        }
                    });
                })
            $('#update-food-form')
                .on('submit', function (e) {
                    e.preventDefault();
                    const form    = $(this);
                    const getForm = $('#get-food-form');
                    const submitBtn = form.find('button[type=submit]');
                    const btnHtml = submitBtn.html();
                    const date = $(getForm.find('input#date')).val();
                    const meal = $(getForm.find('select#meal')).val();
                    const oldFoodTitle = $(form.find('select#food_title')).val();
                    const newFoodTitle = $(form.find('input#new_food_title')).val();
                    if (oldFoodTitle === '' || newFoodTitle === '' || meal === '' || date === '') {
                        alert("پر کردن فرم الزامی است")
                        return false;
                    }
                    const data = JSON.stringify({
                        date: date,
                        meal: meal,
                        oldFoodTitle: oldFoodTitle,
                        newFoodTitle: newFoodTitle,
                    });
                    submitBtn.html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/reserves-report/edit-reserve-name',
                        data: data,
                        dataType: 'json',
                        dataContent: 'application/json',
                        processData: false,
                        success: function (data) {
                            submitBtn.html(btnHtml);
                            if (data.status === 200) {
                                alert(data.res);
                            }
                            else {
                                alert(data.res);
                            }
                        },
                        error: function (error) {
                            submitBtn.html(btnHtml);
                            alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                            //location.reload();
                            console.log(error);
                        }
                    });
                })
                .on('change','select#food_title', function () {
                    const select = $(this);
                    const form   = $('#update-food-form');
                    $(form.find('input#new_food_title')).val(select.val());
                });
        });
    </script>
@endsection
