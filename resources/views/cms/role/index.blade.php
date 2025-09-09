@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">گروه های کاربری
                <a href="/home/roles/add" class="btn btn-outline-success pull-left" title="افزودن گروه کاربری جدید">
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
                    <th class="text-center" colspan="8">لیست گروه های کاربری ثبت شده</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">عنوان</th>
                    <th class="text-center">توضیحات</th>
                    <th class="text-center">وضعیت</th>
                    <th class="text-center">فعال</th>
                    <th class="text-center">تاریخ ثبت</th>
                    <th class="text-center">ویرایش</th>
                    <th class="text-center">حذف</th>
                </tr>
                </thead>
                <tbody>
                @if(isset($roles))
                    @foreach($roles as $role)
                        <tr>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$role->title}}</td>
                            <td class="text-center">{{$role->description}}</td>
                            <td class="text-center">{{$role->status}}</td>
                            <td class="text-center">@if($role->locked == 0) بله @else خیر @endif</td>
                            <td class="text-center">{{$role->GetCreateDate()}}</td>
                            <td class="text-center"><a href="/admin/news/edit/{{$role->id}}"><i class="fa fa-edit"></i></a> </td>
                            <td class="text-center"><a href="/admin/news/delete/{{$role->id}}"><i class="fa fa-trash-o"></i></a> </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="8" class="text-center thead-inverse">هیچ گروه کاربری ثبت نشده است</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('more_script')
    <script>

    </script>
@endsection