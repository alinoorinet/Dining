@extends('layouts.cms')
@section('content')
        <div class="card mb-3">
            <div class="card-body">
                <h4 class="card-title">گزارش فعالیت های یک ماهه اخیر
                    {{--<a href="/home/actions/add" class="btn btn-outline-success pull-left" title="افزودن اکشن جدید به ماژول">
                        <i class="fa fa-plus-circle pull-right"></i>افزودن
                    </a>--}}
                </h4>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">نام کاربری</th>
                            <th class="text-center">نام</th>
                            <th class="text-center">نوع فعالیت</th>
                            <th class="text-center">توضیحات</th>
                            <th class="text-center">شناسه ها</th>
                            <th class="text-center">IP</th>
                            <th class="text-center">تاریخ</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($activitys as $activity)
                            <tr>
                                <td class="text-center">{{$loop->index+1}}</td>
                                <td class="text-center">{{$activity->username}}</td>
                                <td class="text-center">{{$activity->name}} {{$activity->family}}</td>
                                <td class="text-center">{{$activity->task}}</td>
                                <td class="text-center">{{$activity->description}}</td>
                                <td class="text-center">{{$activity->ids}}</td>
                                <td class="text-center">{{$activity->ip_address}}</td>
                                <td class="text-center">{{$activity->created_at}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection