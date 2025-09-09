@extends('layouts.cms')
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item">
            <a href="/home/rest">رستوران/ سلف سرویس</a>
        </li>
        <li class="breadcrumb-item">
            <a href="/home/rest/info/{{$rest->id}}">رستوران {{$rest->title}}</a>
        </li>
        <li class="breadcrumb-item active">ویرایش رستوران</li>
    </ol>
@endsection
@section('content')
    <div class="card mb-1">
        <div class="card-header">فرم ویرایش رستوران {{$rest->title}}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <form action="/home/rest/update" method="post">
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                @csrf
                                <input type="hidden" name="rest_id" value="{{$rest->id}}">
                                <div class="form-group">
                                    <label>نام</label>
                                    <input name="name" class="form-control @if($errors->has('name')) is-invalid @endif" tabindex="1" autofocus value="@if(old('name')) {{old('name')}} @else{{$rest->name}}@endif">
                                    @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('name')}}</strong>
                                        </div>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>نوع</label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="دولتی" class="@if($errors->has('type')) is-invalid @endif" name="type" @if($rest->type == 'دولتی') checked @endif> دولتی
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="مکمل" class="@if($errors->has('type')) is-invalid @endif" name="type" @if($rest->type == 'مکمل') checked @endif>مکمل
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="آزاد" class="@if($errors->has('type')) is-invalid @endif" name="type" @if($rest->type == 'آزاد') checked @endif>آزاد
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
                                        <input type="radio" value="برادران" class="@if($errors->has('sex')) is-invalid @endif" name="sex" @if($rest->sex == 'برادران') checked @endif> برادران
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="خواهران" class="@if($errors->has('sex')) is-invalid @endif" name="sex" @if($rest->sex == 'خواهران') checked @endif>خواهران
                                    </label>
                                    <label class="d-block text-muted">
                                        <input type="radio" value="مختلط" class="@if($errors->has('sex')) is-invalid @endif" name="sex" @if($rest->sex == 'مختلط') checked @endif>مختلط
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
                                            <option value="{{$collection->id}}" @if($collection->id == $rest->collection_id) selected @endif>{{$collection->name}}</option>
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
                                            <option value="{{$store->id}}" @if($store->id == $rest->store_id) selected @endif>{{$store->name}}</option>
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
                                            <option value="{{$contractor->id}}" @if($contractor->id == $rest->contractor_id) selected @endif>{{$contractor->name}} {{$contractor->family}}</option>
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
                                    <input name="close_at" type="number" class="form-control ltr text-left @if($errors->has('close_at')) is-invalid @endif" value="@if(old('close_at')) {{old('close_at')}} @else{{$rest->close_at}}@endif" placeholder="48">
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
                                @foreach($restInfo as $info)
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>آدرس Ip {{$loop->index + 1}}</label>
                                                <input name="ips[]" class="form-control @if($errors->has('ips.'.($loop->index + 1))) is-invalid @endif" value="{{$info->ip}}">
                                                @if($errors->has('ips.'.($loop->index + 1)))
                                                    <div class="invalid-feedback">
                                                        <strong>{{$errors->first('ips.'.($loop->index + 1))}}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>توضیحات 1</label>
                                                <input name="description[]" class="form-control @if($errors->has('description.'.($loop->index + 1))) is-invalid @endif" value="{{$info->description}}">
                                                @if($errors->has('description.'.($loop->index + 1)))
                                                    <div class="invalid-feedback">
                                                        <strong>{{$errors->first('description.'.($loop->index + 1))}}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                                @for($i = count($restInfo) + 1; $i <= 5; $i++)
                                    <div class="row">
                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>آدرس Ip {{$i}}</label>
                                                <input name="ips[]" class="form-control @if($errors->has('ips.'.$i)) is-invalid @endif" value="{{old('ips.'.$i)}}">
                                                @if($errors->has('ips.'.$i))
                                                    <div class="invalid-feedback">
                                                        <strong>{{$errors->first('ips.'.$i)}}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                            <div class="form-group">
                                                <label>توضیحات {{$i}}</label>
                                                <input name="description[]" class="form-control @if($errors->has('description.'.$i)) is-invalid @endif" value="{{old('description.'.$i)}}">
                                                @if($errors->has('description.'.$i))
                                                    <div class="invalid-feedback">
                                                        <strong>{{$errors->first('description.'.$i)}}</strong>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endfor
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
@endsection
