@extends('layouts.cms')
@section('more_style')
@endsection
@section('breadcrumb')
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">خانه</a></li>
        <li class="breadcrumb-item active">داشبورد</li>
        <!-- Breadcrumb Menu-->
        <li class="breadcrumb-menu d-md-down-none">
            <div class="btn-group" role="group" aria-label="Button group">
                <span class="text-muted font12">
                    @php
                        $date = new \App\Library\jdf();
                        $today = $date->jdate('امروز d F ماه Y');
                    @endphp
                    <i class="fa fa-clock-o ml-1"></i> {{$today}}
                </span>
            </div>
        </li>
    </ol>
@endsection
@section('content')
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 700px" role="document">
            <div class="modal-content">
                <div class="modal-header modalBg">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-clipboard"></i> جزئیات رزرو <span id="modal-date" class="badge badge-warning"> دوشنبه 12-03-1399 </span></h5>
                    <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ui dimmer" id="modal-dimmer">
                        <div class="ui large text loader">چند لحظه صبر کنید...</div>
                    </div>
                    <div id="modal-body-wrapper"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dribbble" onclick="$('#order-form').submit();">ثبت نهایی سفارشات</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">بستن منو</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="broadcastModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 700px" role="document">
            <div class="modal-content">
                <div class="modal-header modalBg" style="background: #bccaff;">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-envelope"></i> پیام سیستم </h5>
                    <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="ui dimmer" id="modal-dimmer">
                        <div class="ui large text loader">چند لحظه صبر کنید...</div>
                    </div>
                    <div id="modal-body-wrapper"></div>
                    @if(isset($broadcasts[0]->id))
                        @foreach($broadcasts as $broadcast)
                            <div class=" p-2 mb-3" style="border:1px solid #eac4ae ;border-radius: 6px">
                                <div class="mb-1 font-weight-bolder" style="font-size: 15px">{{$broadcast->title}}</div>
                                <div class="mb-3"><span class="text-muted" style="font-size: 12px">
                                        <i class="fa fa-clock ml-2 text-warning"></i>{{$broadcast->created_at()}}</span>
                                </div>
                                <div style="font-size: 13px">{{$broadcast->content}}</div>
                            </div>

                        @endforeach
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-info" data-dismiss="modal">خواندم</button>
                </div>
            </div>
        </div>
    </div>
    <div class="my-amount">موجودی <strong id="wallet-himself" class="ltr d-inline-block {{$walletAmount < 0 ? 'text-dribbble': ''}}">{{$walletAmount}}</strong> ریال <span class="text-muted"> | </span><a class="btn btn-dribbble" href="/home/wallet">افزایش اعتبار</a></div>
    @if($userRole == 'developer' || $userRole == 'admin')
        <div class='row'>
            <div class='col-12 mb-lg p-0'>
                <form id='change-user-form' method='post'>
                    @csrf
                    <div class='input-group'>
                        <input type='text' class='form-control' name='username' placeholder='شناسه کاربری| شماره دانشجویی' value='{{$user->username}}' aria-label='' aria-describedby='button-addon1'>
                        <div class='input-group-prepend'>
                            <button class='btn btn-outline-secondary' type='submit' id='button-addon1'>اعمال</button>
                        </div>
                    </div>
                    <h5 class='invalid-feedback d-block text-danger'></h5>
                    <h5 class='valid-feedback d-block text-success'></h5>
                </form>
            </div>
        </div>
    @endif
    <div id="week-box">
        {!! $weekBox !!}
    </div>
@endsection
@section('more_script')
    <script src="/js/cms/wb.js?v=1.001"></script>
    <script>
        $( document ).ready(function() {
            @if(isset($broadcasts[0]->id))
                $("#broadcastModal").modal('show');
            @endif
            // console.log( "ready!" );
        });
    </script>
@endsection
