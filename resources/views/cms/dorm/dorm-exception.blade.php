@extends('layouts.cms')
@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <h1 class="h3 display">رفع محدودیت صبحانه و شام برای دانشجویان غیرخوابگاهی</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-4 col-md-4 col-sm-4">
            <div class="card bg-light text-dark">
                <div class="ui dimmer" id="formDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">
                    <div class="alert alert-danger text-dark d-none" id="formMsg"></div>
                    <form id="searchUserLimits" method="post" action="/home/dorm/dorm-exception/store">
                        {!! csrf_field() !!}
                        <div class="form-group">
                            <label>شناسه کاربری/شماره دانشجویی</label>
                            <input class="form-control @if($errors->has('stdOrUid'))is-invalid @endif" id="stdOrUid" name="stdOrUid">
                            @if($errors->has('stdOrUid'))
                                <div class="invalid-feedback">
                                    {{$errors->first('stdOrUid')}}
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">رفع محدودیت</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-12 col-md-12 col-sm-12">
            <div class="card mt-3">
                <div class="ui dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">
                    <h4 class="card-title text-dark text-center">کاربرانی که رفع محدودیت شده اند</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                            <tr>
                                <th class="text-right">#</th>
                                <th class="text-center">نام</th>
                                <th class="text-center">شماره دانشجویی</th>
                                <th class="text-center">ایجاد کننده</th>
                                <th class="text-center">ثبت</th>
                                <th class="text-center">-</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($dExceptions as $entity)
                                <tr>
                                    <td class="text-right">{{$loop->index+1}}</td>
                                    <td class="text-center">{{$entity->user->name}}</td>
                                    <td class="text-center">{{$entity->user->std_no}}</td>
                                    <td class="text-center">{{$entity->creator->name}}</td>
                                    <td class="text-center">{{$entity->created_at()}}</td>
                                    <td class="text-center"><a href="/home/dorm/dorm-exception/delete/{{$entity->id}}" class="btn btn-link"><i class="fa fa-trash text-danger"></i></a></td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $dExceptions->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
