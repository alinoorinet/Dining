@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">لیست اطلاعیه ها
                <a href="/home/notification/add" class="btn btn-success float-left" title="افزودن اطلاعیه جدید">
                    <i class="fa fa-plus-circle pull-right"></i>افزودن
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                    <table class="table  table-striped table-bordered table-sm">
                        <thead>
                        <tr>
                            <th class="text-center" colspan="8">لیست اعلان های عمومی ثبت شده</th>
                        </tr>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">عنوان</th>
                            <th class="text-center">تاریخ</th>
                            <th class="text-center">محل درج</th>
                            <th class="text-center">ایجاد کننده</th>
                            <th class="text-center">ویرایش</th>
                            <th class="text-center">فعال/غیرفعال</th>
                            <th class="text-center">حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($notifications[0]->id))
                            @foreach($notifications as $notification)
                                <tr style="line-height: 37px">
                                    <td class="text-center">{{$loop->index+1}}</td>
                                    <td class="text-center">{{$notification->title}}</td>
                                    <td class="text-center">{{$notification->updated_at()}}</td>
                                    <td class="text-center">@if($notification->self === 0) سلف دانشجویی @else سلف آزاد @endif</td>
                                    <td class="text-center">{{$notification->user->name}}</td>
                                    <td class="text-center"><a href="/home/notification/edit/{{$notification->id}}"><i class="fa fa-edit"></i></a> </td>
                                    <td class="text-center allow_td">
                                        @if($notification->active)
                                            <button class="btn btn-light disallow_this" id="{{$notification->id}}" ><i class="fa fa-check text-success"></i> فعال </button>
                                        @else
                                            <button class="btn btn-light allow_this" id="{{$notification->id}}" ><i class="fa fa-times text-danger"></i> غیرفعال </button>
                                        @endif
                                    </td>
                                    <td class="text-center"><a href="/home/notification/delete/{{$notification->id}}"><i class="fa fa-trash"></i></a> </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="8" class="text-center thead-inverse">هیچ اطلاعیه ایی ثبت نشده است.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script>
        $(function () {
            "use strict";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('.allow_td').on('click', '.allow_this', function () {
                let btn = $(this);
                let wId = btn.attr('id');
//                console.log(btn);
                if (wId !== '' && wId !== undefined && wId !== null) {
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/notification/allow-this',
                        data: JSON.stringify({nId: wId}),
                        processData: false,
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function (data) {
                            if (data.status === 200) {
                                $(btn).html('<i class="fa fa-check text-success"></i> فعال');
                                $(btn).removeClass('allow_this').addClass('disallow_this');
                            }
                            else
                                alert(data.res);
                        },
                        error: function (error) {
                            alert('انجام فرآیند در حال حاضر امکان پذیر نیست');
                            console.log(error);
//                            location.reload();
                        }
                    });
                }
            }).on('click', '.disallow_this', function () {
                let btn = $(this);
                let wId = btn.attr('id');
                console.log(btn);
                if (wId !== '' && wId !== undefined && wId !== null) {
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/notification/disallow-this',
                        data: JSON.stringify({nId: wId}),
                        processData: false,
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function (data) {
                            if (data.status === 200) {
                                $(btn).html('<i class="fa fa-times text-danger"></i> غیر فعال ');
                                $(btn).removeClass('disallow_this').addClass('allow_this');
                            }
                            else
                                alert(data.res);
                        },
                        error: function (error) {
                            alert('انجام فرآیند در حال حاضر امکان پذیر نیست');
                            console.log(error);
//                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection
