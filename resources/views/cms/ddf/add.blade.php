@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="modal fade" id="menu-modal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document" style="max-width: 1000px">
            <div class="modal-content">
                <div class="modal-header">
                    <strong>فرم تنظیم برنامه غذایی</strong>
                    <button type="button" class="close float-left p-0 m-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ui dimmer" id="modal-dimmer">
                        <div class="ui large text loader">چند لحظه صبر کنید...</div>
                    </div>
                    <div class="row">
                        <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                            <div class="card text-right">
                                <div class="card-header">
                                    <ul class="nav nav-tabs card-header-tabs">
                                        {!! $mealTabs !!}
                                    </ul>
                                </div>
                                <div class="card-body">
                                    <form id="ddf-form">
                                        @csrf
                                        <input id="ddf-date" type="hidden">
                                        <div class="tab-content">
                                            {!! $mealTabsContent !!}
                                        </div>
                                        <div class="row">
                                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                                <p><strong>لیست مجموعه ها:</strong></p>
                                                {!! $collects !!}
                                                <span class="text-muted small">انتخاب یا عدم انتخاب هر کدام در ذخیره سازی برنامه غذایی تفاوت ایجاد میکند</span>
                                            </div>
                                            <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 col-12">
                                                <p><strong>لیست رستوران ها/سلف سرویس ها:</strong></p>
                                                {!! $rests !!}
                                                <span class="text-muted small">انتخاب یا عدم انتخاب هر کدام در ذخیره سازی برنامه غذایی تفاوت ایجاد میکند</span>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="mt-4 mb-2" id="price-list"></div>
                                        <div class="form-group mt-3">
                                            <button class="btn btn-success">ذخیره</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div id="user-group-wrapper" class="d-none">
        {!! $userGroups !!}
    </div>
    <div id="event-wrapper" class="d-none">
        {!! $events !!}
    </div>
    <div id="desserts-wrapper" class="d-none">
        {!! $desserts !!}
    </div>
    <div id="foods-wrapper" class="d-none">
        {!! $foods !!}
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card text-center mt-2">
                <div class="ui dimmer" id="ddf_dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-header p-1">
                    <p class="m-0">
                        <a class="btn btn-secondary btn-sm float-right text-dark" href="javascript:void(0)" id="prevWeek" title="هفته قبل"><i class="fa fa-arrow-circle-right align-middle"></i> هفته قبل</a>
                        <a class="btn btn-secondary btn-sm text-center text-dark" href="javascript:void(0)" id="currWeek" title="هفته جاری"><span id="be"></span>هفته جاری<span id="af"></span></a>
                        <a class="btn btn-secondary btn-sm float-left text-dark" href="javascript:void(0)" id="nextWeek" title="هفته بعد">هفته بعد <i class="fa fa-arrow-circle-left align-middle"></i></a>
                    </p>
                </div>
                <div class="card-body" id="week-box">
                    {!! $week !!}
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 col-md-8 col-sm-8">
            <div class="card">
                <div class="ui dimmer" id="ddfFormDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-header text-white bg-danger"> تنظیمات عمومی منو غذایی</div>
                <div class="card-body">
                    <form action="/home/define-day-food/common-setting" method="post">
                        @csrf
                        <div class="form-group">
                            <label class="font-lg font-weight-bold">نحوه اعمال سوبسید</label>
                            <label class="d-block">
                                <input type="radio" class="align-middle text-muted" name="discount_type" value="sub" @if($setting->discount_type == 'sub') checked @endif> کسر از قیمت غذا
                            </label>
                            <label class="d-block">
                                <input type="radio" class="align-middle text-muted" name="discount_type" value="percent"  @if($setting->discount_type == 'percent') checked @endif> به صورت درصد
                            </label>
                            @if($errors->has('discount_type'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('discount_type')}}</strong>
                                </div>
                            @endif
                        </div><hr>
                        <div class="form-group">
                            <label>حداقل موجودی لازم برای رزرو(کمتر از صفر)</label>
                            <input type="number" min="0" minlength="0" class="form-control" name="min_possible_cash" value="{{$setting->min_possible_cash}}">
                        </div><hr>
                        <div class="form-group">
                            <label>محدودیت رزرو کاربران غیر خوابگاهی(مختص دانشجو)</label>
                            <label class="d-block">
                                <input type="checkbox" class="align-middle text-muted" name="block_bf_non_dorm" @if($setting->block_bf_non_dorm == 1) checked @endif> صبحانه
                            </label>
                            @if($errors->has('block_bf_non_dorm'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('block_bf_non_dorm')}}</strong>
                                </div>
                            @endif

                            <label class="d-block">
                                <input type="checkbox" class="align-middle text-muted" name="block_lu_non_dorm" @if($setting->block_lu_non_dorm == 1) checked @endif> نهار
                            </label>
                            @if($errors->has('block_lu_non_dorm'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('block_lu_non_dorm')}}</strong>
                                </div>
                            @endif

                            <label class="d-block">
                                <input type="checkbox" class="align-middle text-muted" name="block_dn_non_dorm" @if($setting->block_dn_non_dorm == 1) checked @endif> شام
                            </label>
                            @if($errors->has('block_dn_non_dorm'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('block_dn_non_dorm')}}</strong>
                                </div>
                            @endif
                        </div><hr>
                        <div class="form-group">
                            <label>محدودیت رزرو کاربرانی که کارت خود را هوشمند نکرده اند</label>
                            <label class="d-block">
                                <input type="checkbox" class="align-middle text-muted" name="block_bf_no_card" @if($setting->block_bf_no_card == 1) checked @endif> صبحانه
                            </label>
                            @if($errors->has('block_bf_no_card'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('block_bf_no_card')}}</strong>
                                </div>
                            @endif

                            <label class="d-block">
                                <input type="checkbox" class="align-middle text-muted" name="block_lu_no_card" @if($setting->block_lu_no_card == 1) checked @endif> نهار
                            </label>
                            @if($errors->has('block_lu_no_card'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('block_lu_no_card')}}</strong>
                                </div>
                            @endif

                            <label class="d-block">
                                <input type="checkbox" class="align-middle text-muted" name="block_dn_no_card" @if($setting->block_dn_no_card == 1) checked @endif> شام
                            </label>
                            @if($errors->has('block_dn_no_card'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('block_dn_no_card')}}</strong>
                                </div>
                            @endif
                        </div><hr>
                        <div class="form-group">
                            <label class="font-lg font-weight-bold"><u>محدودیت تعداد رزرو همزمان در چند رستوران</u> و <u>تعداد تخفیف قابل اعمال در هر وعده</u></label>
                            {!! $ugSimultaneous !!}
                            @if($errors->has('menu_date'))
                                <div class="invalid-feedback d-block text-danger">
                                    <strong>{{$errors->first('menu_date')}}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label class="font-lg font-weight-bold"><u>محدودیت ها و تنظیمات رزرو خوابگاه</u></label>
                            {!! $dorms !!}
                        </div>
                        <div class="form-group">
                            <label class="font-lg font-weight-bold"><u>لیست وعده های فعال در سامانه</u></label>
                            {!! $mealActivation !!}
                        </div>
                        <div class="form-group">
                            <button class="btn btn-danger">بروزرسانی</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script src="/js/cms/food/ddf.js?v=1.002"></script>
@endsection
