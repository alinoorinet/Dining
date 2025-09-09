@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">جزییات {{$event->name}}</div>
        <div class="card-body">
            <div class="row mt-3">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <tbody>
                                @php
                                    $userGroups = $event->user_groups;
                                    $userGroupsStr = "";
                                    foreach ($userGroups as $userGroup)
                                        $userGroupsStr .= "<span class='badge badge-info p-2 m-2'>$userGroup->title</span>";
                                @endphp
                                <tr>
                                    <td class="text-right">توضیحات</td>
                                    <td class="text-right">{{$event->description}}</td>
                                </tr>
                                <tr>
                                    <td class="text-right">گروه های کاربری درخواستی مجاز به رزرو</td>
                                    <td class="text-center">
                                        {!! $userGroupsStr !!}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
@endsection
