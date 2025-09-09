@extends('layouts.cms')
{{--@section('more_style')
    <link href="/plugins/datatable/datatables.min.css" rel="stylesheet">
@endsection--}}
@section('content')
    <div class="card">
        <div class="card-header">جست و جو در غذاهای ثبت شده</div>
        <div class="card-body">
            <form method="post" id="food-search-form">
                @csrf
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                        <div class="form-group">
                            <label for="name">عنوان غذا</label>
                            <div class="input-group mb-3" id="inp-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" id="search-food-status">&nbsp;</span>
                                </div>
                                <input type="text" class="two-side form-control my-input" name="food_title" aria-label="جست و جو کاربر" required>
                                <div class="input-group-append">
                                    <button type="submit" id="search-food-btn" class="input-group-text btn btn-light my-input-btn"><i class="fa fa-search"></i></button>
                                </div>
                            </div>
                            <span class="invalid-feedback d-block"></span>
                        </div>
                    </div>
                </div>
            </form>
            <div class="table-responsive" id="food-search-result"></div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">تعریف و ویرایش غذا</div>
        <div class="card-body">
            <div class="ui dimmer" id="food-dimmer">
                <div class="ui large text loader">چند لحظه صبر کنید...</div>
            </div>
            <form method="post" id="food-submit-form" enctype="multipart/form-data">
                @csrf
                <fieldset id="food-properties">
                    <div class="row">
                        <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid #EEEEEE">
                            <div class="form-group">
                                <label class="d-block">انتخاب نوع:<strong class="text-danger">*</strong></label>
                                <label for="food-type1" class="text-muted">
                                    <input type="radio" name="food_type" class="align-middle" id="food-type1" value="0" checked> غذا
                                </label>
                                <label for="food-type2" class="text-muted">
                                    <input type="radio" name="food_type" class="align-middle" id="food-type2" value="1"> دسر
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="name">عنوان غذا<strong class="text-danger">*</strong></label>
                                <input type="text" id="title" name="title" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="name">کپشن</label>
                                <input type="text" id="caption" name="caption" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="swf_code">کد swf</label>
                                <input type="text" id="swf_code" name="swf_code" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="pic">درج تصویر</label>
                                <div class="form-control">
                                    <input type="file" id="pic" name="pic">
                                </div>
                            </div>
                        </div>
                        <div class="col-xl-8 col-lg-8 col-md-8 col-sm-8 col-12">
                            <label>مواد اولیه مورد نیاز</label>
                            <div style="max-height: 200px; overflow: auto">
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-sm" id="food-stuff-tbl">
                                        <thead>
                                        <tr>
                                            <th class="text-center">#</th>
                                            <th class="text-right">مواد اولیه</th>
                                            <th class="text-center">مقدار</th>
                                            <th class="text-right">واحد مقدار</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $tmp = [];
                                        @endphp
                                        @foreach($goodNames as $goodName)
                                            @if(in_array($goodName->goods_name,$tmp))
                                                @continue
                                            @endif
                                            @php
                                                array_push($tmp,$goodName->goods_name);
                                            @endphp
                                            <tr id="tr-{{$goodName->id}}" data-title="{{$goodName->goods_name}}">
                                                <td class="text-center align-middle">
                                                    <input type="checkbox" class="align-middle" name="food_stuff[id][]" value="{{$goodName->id}}">
                                                </td>
                                                <td class="text-right align-middle">{{$goodName->goods_name}}</td>
                                                <td class="text-center align-middle">
                                                    <input type="text" class="form-control text-center m-auto p-1" style="width: 80px" name="food_stuff[{{$goodName->id}}][amount]">
                                                </td>
                                                <td class="text-right align-middle">
                                                    <select name="food_stuff[{{$goodName->id}}][unit]" class="form-control p-1 short-select">
                                                        <option value="">...</option>
                                                        <option value="گرم">گرم</option>
                                                        <option value="کیلوگرم">کیلوگرم</option>
                                                        {{--<option value="تن">تن</option>
                                                        <option value="لیوان">لیوان</option>
                                                        <option value="قاشق چای خوری">قاشق چای خوری</option>
                                                        <option value="دانه">دانه</option>
                                                        <option value="بسته">بسته</option>--}}
                                                    </select>
                                                </td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="form-group mt-2">
                                <button type="submit" class="btn btn-success">ذخیره</button>
                            </div>
                        </div>
                    </div>
                </fieldset>
            </form>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-header">تعیین قیمت غذا</div>
        <div class="card-body">
            <div class="ui dimmer" id="price-dimmer">
                <div class="ui large text loader">چند لحظه صبر کنید...</div>
            </div>
            <form id="price-form">
                @csrf
                <p class="text-danger"><i class="fa fa-filter"></i> مجموعه ها و رستوران/سلف سرویس ها</p>
                <fieldset id="collection-rest" style="opacity: .3" disabled>
                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                            <p><strong>لیست مجموعه ها:</strong></p>
                            {!! $collects !!}
                            <span class="text-muted small">انتخاب یا عدم انتخاب هر کدام در ذخیره سازی قیمت تفاوت ایجاد میکند</span>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                            <p><strong>لیست رستوران ها/سلف سرویس ها:</strong></p>
                            {!! $rests !!}
                            <span class="text-muted small">انتخاب یا عدم انتخاب هر کدام در ذخیره سازی برنامه غذایی تفاوت ایجاد میکند</span>
                        </div>
                    </div>
                </fieldset>

                <div id="price-wrapper">
                    {!! $priceView !!}
                </div>
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-success">ثبت قیمت ها</button>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/js/cms/food/food.js"></script>
@endsection
