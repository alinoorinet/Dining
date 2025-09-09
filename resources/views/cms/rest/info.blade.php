@extends('layouts.cms')
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item"><a href="/home/rest">رستوران/ سلف سرویس</a></li>
        <li class="breadcrumb-item active">{{$rest->name}}</li>
    </ol>
@endsection
@section('content')
    <div class="card mb-1">
        <div class="card-header">مشخصات {{$rest->name}}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    @php
                        $restInfos = $rest->info;
                    @endphp
                    @if(isset($restInfos[0]->id))
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped table-sm">
                                <thead>
                                <tr>
                                    <th class="text-center">#</th>
                                    <th class="text-center">Ip</th>
                                    <th class="text-center">وضعیت ارتباط</th>
                                    <th class="text-center">سرعت ارتباط(ms)</th>
                                    <th class="text-center">آخرین بررسی ارتباط</th>
                                    <th class="text-right">توضیحات</th>
                                    <th class="text-center">حذف</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($restInfos as $restInfo)
                                    <tr>
                                        <td class="text-center">{{$loop->index + 1}}</td>
                                        <td class="text-center">{{$restInfo->ip}}</td>
                                        <td class="text-center">{!! $restInfo->status !!}</td>
                                        <td class="text-center">{{$restInfo->avg_rtt}}</td>
                                        <td class="text-center">{{$restInfo->updated_At()}}</td>
                                        <td class="text-right">{{$restInfo->description}}</td>
                                        <td class="text-center"><a class="btn btn-light btn-sm" href="/home/rest/info/delete/{{$restInfo->id}}"><i class="fa fa-trash"></i></a> </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-1 mt-2">
        <div class="card-header">{{$rest->name}}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="bg-light mb-2">فرم ایجاد دسترسی به {{$rest->name}}</div>
                    <form action="/home/rest/info/store" method="post">
                        <input type="hidden" value="{{$rest->id}}" name="rest_id">
                        @csrf
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>شناسه کاربری کاربر</label>
                                    <input name="username" class="form-control @if($errors->has('username')) is-invalid @endif" tabindex="1" autofocus value="{{old('username')}}">
                                    @if($errors->has('username'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('username')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">ذخیره</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-right">نام و نام خانوادگی</th>
                                <th class="text-right">نام کاربری</th>
                                <th class="text-center">فعال</th>
                                <th class="text-center">حذف</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($users as $user)
                                <tr>
                                    <td class="text-center">{{$loop->index + 1}}</td>
                                    <td class="text-right">{{$user->name}} {{$user->family}}</td>
                                    <td class="text-right">{{$user->username}}</td>
                                    <td class="text-center">
                                        <a href="/home/rest/info/de-active-user/{{$user->pivot->id}}" class="btn btn-light btn-sm">
                                            @if($user->pivot->active)
                                                <i class="fa fa-check-circle text-success"></i>
                                            @else
                                                <i class="fa fa-times-circle text-warning"></i>
                                            @endif
                                        </a>
                                    </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/rest/info/delete/{{$user->pivot->id}}"><i class="fa fa-trash"></i></a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $users->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
