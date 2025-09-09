@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">ماژول های سامانه
                <a href="/home/modules/add" class="btn btn-outline-success pull-left" title="افزودن ماژول جدید">
                    <i class="fa fa-plus-circle pull-right"></i>افزودن
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm">
                <thead>
                <tr>
                    <th class="text-center" colspan="6">لیست ماژول های ثبت شده</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">عنوان</th>
                    <th class="text-center">توضیحات</th>
                    <th class="text-center">تاریخ ثبت</th>
                    <th class="text-center">ویرایش</th>
                    <th class="text-center">حذف</th>
                </tr>
                </thead>
                <tbody>
                @if(isset($modules))
                    @foreach($modules as $module)
                        <tr>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$module->title}}</td>
                            <td class="text-center">{{$module->description}}</td>
                            <td class="text-center">{{$module->GetCreateDate()}}</td>
                            <td class="text-center"><a href="/admin/news/edit/{{$module->id}}"><i class="fa fa-edit"></i></a> </td>
                            <td class="text-center"><a href="/admin/news/delete/{{$module->id}}"><i class="fa fa-trash-o"></i></a> </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="6" class="text-center thead-inverse">هیچ ماژولی ثبت نشده است</td>
                    </tr>
                @endif
                </tbody>
            </table>
            </div>
        </div>
    </div>
@endsection
