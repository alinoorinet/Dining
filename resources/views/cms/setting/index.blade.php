@extends('layouts.cms')
@section('more_style')
    {{--    @if($active_temp == 'stu')--}}
    <link href="/plugins/toast/jquery.toast.css" rel="stylesheet">
    {{--    @elseif($active_temp == 'freeSelf')--}}
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
    {{--    @endif--}}
@endsection
@section('content')
    {{--    @if($active_temp == 'stu')--}}
    <div class="card mt-2">
        <div class="card-header text-right bg-light">
            فرم تنظیمات سامانه
        </div>
        <div class="card-body">
            <form method="post" action="/home/setting/update" id="settingForm">
                {!! csrf_field() !!}
                @if(isset($setting->id))
                    <div class="row">
                        <div class="col-sm-4 mb-3">
                            <label for="day_type_cd">تقویم آموزشی</label>
                            <select type="text" class="form-control @if($errors->has('day_type_cd'))is-invalid @endif" name="day_type_cd" id="day_type_cd">
                                <option value="عادی" @if($setting->day_type_cd == 'عادی') selected @endif>عادی</option>
                                <option value="امتحانات" @if($setting->day_type_cd == 'امتحانات') selected @endif>امتحانات</option>
                                <option value="رمضان" @if($setting->day_type_cd == 'رمضان') selected @endif>رمضان</option>
                                <option value="سایر" @if($setting->day_type_cd == 'سایر') selected @endif>سایر</option>
                            </select>
                            @if($errors->has('day_type_cd'))
                                <div class="invalid-feedback">
                                    {{$errors->first('day_type_cd')}}
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12 mb-1 text-center">
                            <button class="btn btn-primary" type="submit">ثبت و بروزرسانی</button>
                        </div>
                    </div>
                @endif
            </form>
        </div>
    </div>
@endsection
@section('more_script')

    <script src="/plugins/toast/jquery.toast.js"></script>

    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script>
        $(function () {
            $('#menu_date').persianDatepicker({
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
        })
    </script>
@endsection
