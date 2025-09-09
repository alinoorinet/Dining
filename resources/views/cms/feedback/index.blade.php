@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title mb-0 font-16">صندوق پیشنهادات و انتقادات
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="common">
                    <table class="table table-responsive table-striped table-bordered table-sm">
                        <thead>
                        <tr>
                            <th class="text-center p-3 bg-secondary text-white" colspan="5"> لیست پیشنهادات و انتقادات بررسی نشده </th>
                        </tr>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">ارسال کننده</th>
                            <th class="text-center">تاریخ</th>
                            <th class="text-center">مربوط به :</th>
                            <th class="text-center">بررسی</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($unreads[0]->id))
                            @foreach($unreads as $unread)
                                <tr>
                                    <td class="text-center">{{$loop->index+1}}</td>
                                    <td class="text-center">@if($unread->private === 0){{$unread->user->name}} @else ناشناس @endif </td>
                                    <td class="text-center">{{$unread->created_at()}}</td>
                                    <td class="text-center">{{$unread->type}}</td>
                                    <td class="text-center">
                                        <button class="btn btn-info font13 checked" id="{{$unread->id}}" >انتقال به بررسی شده ها<i class="fa fa-arrow-circle-down mr-2 text-white"></i> </button>
                                    </td>
                                <tr>
                                    <td colspan="5" class="text-right p-3">{{$unread->content}}</td>
                                </tr>

                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-right thead-inverse">ثبت نشده است.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                {!! $unreads->links() !!}
            </div>
        </div>
    </div>
    <div class="card mt-3">
        <div class="card-body">
            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="common">
                    <table class="table table-responsive table-striped table-bordered table-sm">
                        <thead>
                        <tr>
                            <th class="text-center p-3 bg-secondary text-white" colspan="5"> لیست پیشنهادات و انتقادات بررسی شده </th>
                        </tr>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">ارسال کننده</th>
                            <th class="text-center">تاریخ</th>
                            <th class="text-center">مربوط به :</th>
                            <th class="text-center">حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($reads[0]->id))
                            @foreach($reads as $read)

                                <tr>
                                    <td class="text-center">{{$loop->index+1}}</td>
                                    <td class="text-center">@if($read->private === 0){{$read->user->name}} @else ناشناس @endif </td>
                                    <td class="text-center">{{$read->created_at()}}</td>
                                    <td class="text-center">{{$read->type}}</td>
                                    <td class="text-center">
                                        <button class="btn btn-danger font13 delete" id="{{$read->id}}">حذف<i class="fa fa-trash-o text-white mr-2"></i> </button>
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="5" class="text-right p-3">{{$read->content}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-right thead-inverse"> ثبت نشده است.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>
                {!! $reads->links() !!}
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
            $('.checked').on('click', function () {
                let btn = $(this);
                let fId = btn.attr('id');
//                console.log(btn);
                if (fId !== '' && fId !== undefined && fId !== null) {
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/feedback/checked',
                        data: JSON.stringify({fId: fId}),
                        processData: false,
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function (data) {
                            if (data.status === 200) {
                                location.reload();
                            }
                            else {
                                alert(data.res);
                                location.reload();
                            }
                        },
                        error: function (error) {
                            alert('انجام فرآیند در حال حاضر امکان پذیر نیست');
                            // console.log(error);
                            location.reload();
                        }
                    });
                }
            });

            $('.delete').on('click', function () {
                let btn = $(this);
                let fId = btn.attr('id');
//                console.log(btn);
                if (fId !== '' && fId !== undefined && fId !== null) {
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/feedback/delete',
                        data: JSON.stringify({fId: fId}),
                        processData: false,
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function (data) {
                            if (data.status === 200) {
                                location.reload();
                            }
                            else {
                                alert(data.res);
                                location.reload();
                            }
                        },
                        error: function (error) {
                            alert('انجام فرآیند در حال حاضر امکان پذیر نیست');
                            // console.log(error);
                            location.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection