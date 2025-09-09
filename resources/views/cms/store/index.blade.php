@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">مدیریت انبار</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                    <div class="bg-light mb-2">فرم ثبت انبار جدید</div>
                    <form action="/home/store/store" method="post">
                        @csrf
                        <div class="form-group">
                            <label>نام انبار</label>
                            <input name="name" class="form-control @if($errors->has('name')) is-invalid @endif" tabindex="1" autofocus value="{{old('name')}}">
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('name')}}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>انباردار</label>
                            <select name="user_id" class="form-control @if($errors->has('user_id')) is-invalid @endif">
                                <option value="">انباردار نامشخص است</option>
                                @foreach($users as $user)
                                    <option value="{{$user->id}}">{{$user->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('user_id'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('user_id')}}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">ذخیره</button>
                        </div>
                    </form>
                </div>
                <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 col-12">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-right">نام</th>
                            <th class="text-right">انباردار</th>
                            <th class="text-right">ایجاد</th>
                            <th class="text-center">فعال</th>
                            <th class="text-center">ویرایش</th>
                            <th class="text-center">حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($stores as $store)
                            <tr>
                                <td class="text-center">{{$loop->index + 1}}</td>
                                <td class="text-right"><a href="/home/store/goods/details/{{$store->id}}" class="btn btn-link btn-sm">{{$store->name}}</a></td>
                                <td class="text-right">{{isset($store->user->id) ? $store->user->title : "-"}}</td>
                                <td class="text-right">{{$store->created_at()}}</td>
                                <td class="text-center">
                                    <a href="/home/store/de-active/{{$store->id}}" class="btn btn-light btn-sm">
                                        @if($store->active)
                                            <i class="fa fa-check-circle text-success"></i>
                                        @else
                                            <i class="fa fa-times-circle text-warning"></i>
                                        @endif
                                    </a>
                                </td>
                                <td class="text-center"><a class="btn btn-light btn-sm" href="/home/store/edit/{{$store->id}}"><i class="fa fa-edit"></i></a> </td>
                                <td class="text-center"><a class="btn btn-light btn-sm" href="/home/store/delete/{{$store->id}}"><i class="fa fa-trash"></i></a> </td>
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
