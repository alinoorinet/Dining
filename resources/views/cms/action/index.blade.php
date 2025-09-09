@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">اکشن های ماژول
                <a href="/home/actions/add" class="btn btn-outline-success pull-left" title="افزودن اکشن جدید به ماژول">
                    <i class="fa fa-plus-circle pull-right"></i>افزودن
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-responsive table-striped table-bordered table-sm">
                <thead>
                <tr>
                    <th class="text-center" colspan="8">لیست اکشن ها و ماژول های ثبت شده</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">عنوان</th>
                    <th class="text-center">توضیحات</th>
                    <th class="text-center">ماژول</th>
                    <th class="text-center">توضیحات</th>
                    <th class="text-center">تاریخ ثبت</th>
                    <th class="text-center">ویرایش</th>
                    <th class="text-center">حذف</th>
                </tr>
                </thead>
                <tbody>
                @if(isset($actions))
                    @foreach($actions as $action)
                        <tr>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$action->action_title}}</td>
                            <td class="text-center">{{$action->action_desc}}</td>
                            <td class="text-center">{{$action->module_title}}</td>
                            <td class="text-center">{{$action->module_desc}}</td>
                            <td class="text-center">{{$action->created_at}}</td>
                            <td class="text-center"><a href="/home/actions/edit/{{$action->action_id}}" class="btn btn-sm btn-light"><i class="fa fa-pen"></i></a> </td>
                            <td class="text-center"><a href="/home/actions/delete/{{$action->action_id}}" class="btn btn-sm btn-light"><i class="fa fa-trash"></i></a> </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center thead-inverse">هیچ اکشنی ثبت نشده است</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
