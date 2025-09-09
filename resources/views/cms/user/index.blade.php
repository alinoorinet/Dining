@extends('layouts.cms')
@section('content')
    <div class="modal fade bd-example-modal-sm" id="secureModal" tabindex="-1" role="dialog" aria-labelledby="responseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-sm" role="document">
            <div class="modal-content">
                <div class="ui dimmer" id="responseDimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title text-center" id="responseModalLabel"></h5>
                    <i class="fa fa-user-secret fa-2x"></i>
                    <a class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </a>
                </div>
                <div class="modal-body">
                    <input class="form-control" type="password" id="secPass">
                    <input type="button" id="submitModal" class="btn btn-danger btn-block mt-1" value="تایید">
                    <input type="hidden" id="href">
                </div>
            </div>
        </div>
    </div>
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">کاربران
                <a href="/home/users/add" class="btn btn-outline-success pull-left" title="افزودن کاربر جدید">
                    <i class="fa fa-plus-circle pull-right"></i>افزودن
                </a>
            </h4>
        </div>
    </div>
    <div class="col-sm-2">
        <div class="alert alert-primary text-center m-1" role="alert">
            <h4 class="alert-heading">تعداد کاربران</h4>
            <hr>
            <h3>{{$usersCounter}}</h3>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <table class="table table-responsive table-striped table-bordered table-sm">
                <thead>
                <tr>
                    <th class="text-center" colspan="11">لیست کاربران ثبت شده</th>
                </tr>
                <tr>
                    <th class="text-center">شناسه</th>
                    <th class="text-center">نام</th>
                    <th class="text-center">نام کاربری</th>
                    <th class="text-center">ش.دانشجویی</th>
                    <th class="text-center">موبایل</th>
                    <th class="text-center">ایمیل</th>
                    <th class="text-center">وضعیت</th>
                    <th class="text-center">دسترسی</th>
                    <th class="text-center">آخرین ورود</th>
                    <th class="text-center">ویرایش</th>
                    <th class="text-center">حذف</th>
                </tr>
                </thead>
                <tbody>
                @if(isset($usersTmp))
                    @foreach($usersTmp as $user)
                        <tr>
                            <td class="text-center"><a class="btn btn-link fireModal" href="/home/users/{{$user->id}}">{{$user->id}}</a></td>
                            <td class="text-center">{{$user->name}}</td>
                            <td class="text-center">{{$user->username}}</td>
                            <td class="text-center">{{$user->std_no}}</td>
                            <td class="text-center">{{$user->mobile}}</td>
                            <td class="text-center">{{$user->email}}</td>
                            <td class="text-center">@if($user->active === 1) فعال @else غیرفعال @endif</td>
                            <td class="text-center">
                                @if($user->roles)
                                    @foreach($user->roles as $role)
                                        {{$role}} &nbsp;
                                    @endforeach
                                @endif
                            </td>
                            <td class="text-center">{{$user->last_login}}</td>
                            <td class="text-center"><a href="/admin/news/edit/{{$user->id}}"><i class="fa fa-edit"></i></a> </td>
                            <td class="text-center"><a href="/admin/news/delete/{{$user->id}}"><i class="fa fa-trash-o"></i></a> </td>
                        </tr>
                    @endforeach
                @else
                    <tr>
                        <td colspan="11" class="text-center thead-inverse">کاربری برای نمایش وجود ندارد</td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>
    </div>
@endsection
@section('more_script')
    <script>
        $('.fireModal').on('click',function (e) {
            e.preventDefault();
            var href = $(this).attr('href');
            $('#secureModal #href').val(href);
            $('#secureModal').modal();
        });
        $('#secureModal').on('click','#submitModal',function () {
            var href = $('#secureModal #href').val();
            var pass= $('#secureModal #secPass').val();
            location.href = href+'/'+pass;
        });
    </script>
@endsection