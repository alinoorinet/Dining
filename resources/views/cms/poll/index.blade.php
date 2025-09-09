@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">نظر سنجی
                <a href="/home/poll/add" class="btn btn-outline-success pull-left" title="افزودن نظرسنجی جدید">
                    <i class="fa fa-plus-circle pull-right"></i>افزودن
                </a>
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
                            <th class="text-center" colspan="7">لیست نظرسنجی ثبت شده</th>
                        </tr>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-center">عنوان</th>
                            <th class="text-center">تاریخ ثبت</th>
                            <th class="text-center">مشاهده سوابق</th>
                            <th class="text-center">ویرایش</th>
                            <th class="text-center">حذف</th>
                            <th class="text-center">فعال/غیرفعال</th>

                        </tr>
                        </thead>
                        <tbody>
                        @if(isset($polls[0]->id))
                            @foreach($polls as $poll)
                                @php
                                    $poll->title =  mb_substr($poll->title,0,30). '...';
                                @endphp
                                <tr>
                                    <td class="text-center">{{$loop->index+1}}</td>
                                    <td class="text-center"><span class="my-form-control">{{$poll->title}}</span></td>
                                    <td class="text-center"><span class="my-form-control">{{$poll->created_at()}}</span></td>
                                    @if($poll->have_records())
                                        <td class="text-center">
                                            <a class="my-form-control" href="/home/poll/records/{{$poll->id}}"><i class="fa fa-eye ml-1"></i> مشاهده </a>
                                        </td>
                                        <td class="text-center">
                                            -
                                        </td>
                                        <td class="text-center">
                                            -
                                        </td>
                                    @else
                                        <td class="text-center"><span class="my-form-control">بدون سابقه</span>
                                        </td>
                                        @if($poll->active)
                                            <td class="text-center">
                                                -
                                            </td>
                                            <td class="text-center">
                                                -
                                            </td>
                                        @else
                                            <td class="text-center">
                                                <a class="my-form-control" href="/home/poll/edit/{{$poll->id}}"><i class="fa fa-cog ml-1"></i> ویرایش </a>
                                            </td>
                                            <td class="text-center ">
                                                <a class="text-danger my-form-control" href="/home/poll/delete/{{$poll->id}}"><i class="fa fa-trash-o ml-1"></i> حذف </a>
                                            </td>
                                        @endif
                                    @endif
                                    <td class="text-center allow_td">
                                        @if($poll->active)
                                            <a class="btn btn-success" href="/home/poll/deactive"><i class="fa fa-check"></i> فعال </a>
                                        @else
                                            <a class="btn btn-danger" href="/home/poll/active/{{$poll->id}}"><i class="fa fa-times"></i> غیرفعال </a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center thead-inverse">نظرسنجی ثبت نشده است.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

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
            $('.allow_td').on('click', '.active', function () {
                let btn = $(this);
                let wId = btn.attr('id');
//                console.log(btn);
                if (wId !== '' && wId !== undefined && wId !== null) {
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/poll/active',
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
                        url: '/home/poll/disallow-this',
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