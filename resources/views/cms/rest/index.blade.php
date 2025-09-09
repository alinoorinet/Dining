@extends('layouts.cms')
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item active">رستوران/ سلف سرویس</li>
    </ol>
@endsection
@section('content')
    <div class="card mb-1">
        <div class="card-header">فرم ثبت رستوران و سلف سرویس جدید</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <form action="/home/rest/store" method="post">
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                @csrf
                                <div class="form-group">
                                    <label>نام</label>
                                    <input name="name" class="form-control @if($errors->has('name')) is-invalid @endif" tabindex="1" autofocus value="{{old('name')}}">
                                    @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('name')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>نوع</label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="دولتی" class="@if($errors->has('type')) is-invalid @endif" name="type" checked> دولتی
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="مکمل" class="@if($errors->has('type')) is-invalid @endif" name="type">مکمل
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="آزاد" class="@if($errors->has('type')) is-invalid @endif" name="type">آزاد
                                    </label>
                                    @if($errors->has('type'))
                                        <div class="invalid-feedback d-block text-danger">
                                            <strong>{{$errors->first('type')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>جنسیت</label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="برادران" class="@if($errors->has('sex')) is-invalid @endif" name="sex" checked> برادران
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="خواهران" class="@if($errors->has('sex')) is-invalid @endif" name="sex">خواهران
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="مختلط" class="@if($errors->has('sex')) is-invalid @endif" name="sex">مختلط
                                    </label>
                                    @if($errors->has('sex'))
                                        <div class="invalid-feedback d-block text-danger">
                                            <strong>{{$errors->first('sex')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>انتخاب مجموعه مرتبط</label>
                                    <select name="collection_id" class="form-control @if($errors->has('collection_id')) is-invalid @endif">
                                        <option value="">...</option>
                                        @foreach($collections as $collection)
                                            <option value="{{$collection->id}}">{{$collection->name}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('collection_id'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('collection_id')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>انتخاب انبار</label>
                                    <select name="store_id" class="form-control @if($errors->has('store_id')) is-invalid @endif">
                                        <option value="">...</option>
                                        @foreach($stores as $store)
                                            <option value="{{$store->id}}">{{$store->name}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('store_id'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('store_id')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>انتخاب پیمانکار</label>
                                    <select name="contractor_id" class="form-control @if($errors->has('contractor_id')) is-invalid @endif">
                                        <option value="">...</option>
                                        @foreach($contractors as $contractor)
                                            <option value="{{$contractor->id}}">{{$contractor->name}} {{$contractor->family}}</option>
                                        @endforeach
                                    </select>
                                    @if($errors->has('contractor_id'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('contractor_id')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>زمان بسته شدن قابلیت رزرو(ساعت)</label>
                                    <input name="close_at" type="number" class="form-control ltr text-left @if($errors->has('close_at')) is-invalid @endif" value="{{old('close_at')}}" placeholder="48">
                                    @if($errors->has('close_at'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('close_at')}}</strong>
                                        </div>
                                    @endif
                                    <span class="small text-muted">به عنوان مثال مقدار 48 قابلیت رزرو منو غذایی را از 48 ساعت قبل از روز استفاده محدود می کند</span>
                                </div>
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">ذخیره</button>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>آدرس Ip 1</label>
                                            <input name="ips[]" class="form-control @if($errors->has('ips.0')) is-invalid @endif" value="{{old('ips.0')}}">
                                            @if($errors->has('ips.0'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('ips.0')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>توضیحات 1</label>
                                            <input name="description[]" class="form-control @if($errors->has('description.0')) is-invalid @endif" value="{{old('description.0')}}">
                                            @if($errors->has('description.0'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('description.0')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>آدرس Ip 2</label>
                                            <input name="ips[]" class="form-control @if($errors->has('ips.1')) is-invalid @endif" value="{{old('ips.1')}}">
                                            @if($errors->has('ips.1'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('ips.1')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>توضیحات 2</label>
                                            <input name="description[]" class="form-control @if($errors->has('description.1')) is-invalid @endif" value="{{old('description.1')}}">
                                            @if($errors->has('description.1'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('description.1')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>آدرس Ip 3</label>
                                            <input name="ips[]" class="form-control @if($errors->has('ips.2')) is-invalid @endif" value="{{old('ips.2')}}">
                                            @if($errors->has('ips.2'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('ips.2')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>توضیحات 3</label>
                                            <input name="description[]" class="form-control @if($errors->has('description.2')) is-invalid @endif" value="{{old('description.2')}}">
                                            @if($errors->has('description.2'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('description.2')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>آدرس Ip 4</label>
                                            <input name="ips[]" class="form-control @if($errors->has('ips.3')) is-invalid @endif" value="{{old('ips.3')}}">
                                            @if($errors->has('ips.3'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('ips.3')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>توضیحات 4</label>
                                            <input name="description[]" class="form-control @if($errors->has('description.3')) is-invalid @endif" value="{{old('description.3')}}">
                                            @if($errors->has('description.3'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('description.3')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>آدرس Ip 5</label>
                                            <input name="ips[]" class="form-control @if($errors->has('ips.4')) is-invalid @endif" value="{{old('ips.4')}}">
                                            @if($errors->has('ips.4'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('ips.4')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                        <div class="form-group">
                                            <label>توضیحات 5</label>
                                            <input name="description[]" class="form-control @if($errors->has('description.4')) is-invalid @endif" value="{{old('description.4')}}">
                                            @if($errors->has('description.4'))
                                                <div class="invalid-feedback">
                                                    <strong>{{$errors->first('description.4')}}</strong>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-1 mt-1">
        <div class="card-header">لیست رستوران ها و سلف سرویس ها</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12 mt-2">
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-right">نام</th>
                                <th class="text-right">نوع</th>
                                <th class="text-right">جنسیت</th>
                                <th class="text-right">مجموعه</th>
                                <th class="text-right">انبار</th>
                                <th class="text-right">پیمانکار</th>
                                <th class="text-center">زمان بستن رزرو(h)</th>
                                <th class="text-right">ایجاد</th>
                                <th class="text-center">فعال</th>
                                <th class="text-center">ویرایش</th>
                                <th class="text-center">حذف</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($rests as $rest)
                                <tr>
                                    <td class="text-center">{{$loop->index + 1}}</td>
                                    <td class="text-right">
                                        <a href="/home/rest/info/{{$rest->id}}" class="btn btn-sm btn-link">
                                            <strong>{{$rest->name}}</strong>
                                        </a>
                                    </td>
                                    <td class="text-right">{{$rest->type}}</td>
                                    <td class="text-right">{{$rest->sex}}</td>
                                    <td class="text-right">{{isset($rest->collection->id) ? $rest->collection->name : "-"}}</td>
                                    <td class="text-right">{{isset($rest->store->id) ? $rest->store->name : "-"}}</td>
                                    <td class="text-right">{{isset($rest->contractor->id) ? $rest->contractor->name : "-"}}</td>
                                    <td class="text-center">{{$rest->close_at}}</td>
                                    <td class="text-right">{{$rest->created_at()}}</td>
                                    <td class="text-center">
                                        <a href="/home/rest/de-active/{{$rest->id}}" class="btn btn-light btn-sm">
                                            @if($rest->active)
                                                <i class="fa fa-check-circle text-success"></i>
                                            @else
                                                <i class="fa fa-times-circle text-warning"></i>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/rest/edit/{{$rest->id}}"><i class="fa fa-edit"></i></a> </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/rest/delete/{{$rest->id}}"><i class="fa fa-trash"></i></a> </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#define-btn').on('click',function () {
                $('#define-form').submit();
            });
            $('#card_hex').on('change',function () {

                $(this).focus().select();
            });
            $('#search-card-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn  = form.find('button');
                const data = new FormData(this);
                $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/card/search',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(btn).html('جست و جو');
                        if (data.status === 200)
                            $('#search-result').html(data.res);
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        $(btn).html('جست و جو');
                        alert('خطای اتصال به شبکه');
                        location.reload();
                        //console.log(error);
                    }
                });
            });
            $('#define-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn  = form.find('button');
                const data = new FormData(this);

                $('.invalid-feedback').remove();
                $('.form-control').removeClass('is-invalid');
                $('#define-result').html('');

                $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/card/define',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(btn).html('ثبت');
                        if (data.status === 200)
                            $('#define-result').html(data.res);
                        else if(data.status === 101) {
                            $.each(data.res,function (k,v) {
                                let name = $("input[name='"+k+"'],textarea[name='"+k+"']");
                                if(k.indexOf(".") !== -1){
                                    let arr = k.split(".");
                                    name = $("input[name='"+arr[0]+"[]']:eq("+arr[1]+")");
                                }
                                name.after('<div class="invalid-feedback text-right d-block">'+v[0]+'</div>');
                            });
                        }
                    },
                    error: function (error) {
                        $(btn).html('ثبت');
                        alert('خطای اتصال به شبکه');
                        //location.reload();
                        console.log(error);
                    }
                });
            });
        });
    </script>
@endsection
