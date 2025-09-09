<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="{{config('app.name')}}">
    <meta name="author" content="{{config('app.name')}}">
    <meta name="keyword" content="{{config('app.name')}}">
    <title>{{config('app.name')}}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/png" href="/img/brand/logonet.png">
    @include('includes.cms-styles')
    @yield('more_style')
</head>
<body class="app header-fixed sidebar-fixed aside-menu-fixed sidebar-lg-show">
@include('includes.cms-nav')
<div class="app-body">
    @include('includes.sidebar')
    <main class="main">
        @yield('breadcrumb')
        <div class="container-fluid">
            @if(session()->has('successMsg'))
                <div class="row mt-1">
                    <div class="col-sm-12">
                        <div class="alert alert-success" role="alert">
                            <strong>{{ session('successMsg') }}</strong>
                        </div>
                    </div>
                </div>
            @elseif(session()->has('warningMsg'))
                <div class="row mt-1">
                    <div class="col-sm-12">
                        <div class="alert alert-warning" role="alert">
                            <strong>{{ session('warningMsg') }}</strong>
                        </div>
                    </div>
                </div>
            @elseif(session()->has('dangerMsg'))
                <div class="row mt-1">
                    <div class="col-sm-12">
                        <div class="alert alert-danger" role="alert">
                            <strong>{{ session('dangerMsg') }}</strong>
                        </div>
                    </div>
                </div>
            @endif
            @if(session()->has('payResult'))
                <div class="row mt-1">
                    <div class="col-sm-12">
                        <div class="card">
                            <div class="card-header text-center">نتیجه فرآیند افزایش موجودی شما</div>
                            <div class="card-body">
                                {!!session('payResult') !!}
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
    <aside class="aside-menu">
        <ul class="nav nav-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#messages" role="tab">
                    <i class="position-absolute icon-speech" style="top: 17px"></i>
                    <span style="margin-right: 20px">شخصی</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link " data-toggle="tab" href="#timeline" role="tab">
                    <i class="position-absolute icon-speech" style="top: 17px"></i>
                    <span style="margin-right: 20px">عمومی</span>
                </a>
            </li>

            {{--<li class="nav-item">--}}
                {{--<a class="nav-link" data-toggle="tab" href="#settings" role="tab">--}}
                    {{--<i class="icon-settings"></i>--}}
                {{--</a>--}}
            {{--</li>--}}
        </ul>
        <div class="tab-content">

            <div class="tab-pane p-3 active" id="messages" role="tabpanel">
                @if(isset(\Illuminate\Support\Facades\Auth::user()->notifications[0]->id))
                    @foreach(\Illuminate\Support\Facades\Auth::user()->notifications as $notification)
                        <div class="message">
                            <div>
                                <small class="text-muted">{{$notification->created_at()}}</small>
                            </div>
                            <div class="text-truncate font-weight-bold">{{$notification->title}}</div>
                            <small class="text-muted">{{$notification->content}}</small>
                        </div>
                        <hr>
                    @endforeach
                @else
                    <div class="message">پیامی موجود نیست.</div>
                @endif

            </div>
            <div class="tab-pane p-3 " id="timeline" role="tabpanel">
                @if(isset(\Illuminate\Support\Facades\Auth::user()->broadcasts()[0]->id))
                    @foreach(\Illuminate\Support\Facades\Auth::user()->broadcasts() as $broadcast)
                        <div class="message">
                            <div>
                                <small class="text-muted">{{$broadcast->created_at()}}</small>
                            </div>
                            <div class="text-truncate font-weight-bold">{{$broadcast->title}}</div>
                            <small class="text-muted">{{$broadcast->content}}</small>
                        </div>
                        <hr>
                    @endforeach
                @else
                    <div class="message">پیامی موجود نیست.</div>
                @endif
            </div>
            <div class="tab-pane p-3" id="settings" role="tabpanel">
                {{--<h6>Settings</h6>
                <div class="aside-options">
                    <div class="clearfix mt-4">
                        <small>
                            <b>Option 1</b>
                        </small>
                        <label class="switch switch-label switch-pill switch-success switch-sm float-right">
                            <input class="switch-input" type="checkbox" checked="">
                            <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                    </div>
                    <div>
                        <small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
                    </div>
                </div>
                <div class="aside-options">
                    <div class="clearfix mt-3">
                        <small>
                            <b>Option 2</b>
                        </small>
                        <label class="switch switch-label switch-pill switch-success switch-sm float-right">
                            <input class="switch-input" type="checkbox">
                            <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                    </div>
                    <div>
                        <small class="text-muted">Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</small>
                    </div>
                </div>
                <div class="aside-options">
                    <div class="clearfix mt-3">
                        <small>
                            <b>Option 3</b>
                        </small>
                        <label class="switch switch-label switch-pill switch-success switch-sm float-right">
                            <input class="switch-input" type="checkbox">
                            <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                    </div>
                </div>
                <div class="aside-options">
                    <div class="clearfix mt-3">
                        <small>
                            <b>Option 4</b>
                        </small>
                        <label class="switch switch-label switch-pill switch-success switch-sm float-right">
                            <input class="switch-input" type="checkbox" checked="">
                            <span class="switch-slider" data-checked="On" data-unchecked="Off"></span>
                        </label>
                    </div>
                </div>
                <hr>
                <h6>System Utilization</h6>
                <div class="text-uppercase mb-1 mt-4">
                    <small>
                        <b>CPU Usage</b>
                    </small>
                </div>
                <div class="progress progress-xs">
                    <div class="progress-bar bg-info" role="progressbar" style="width: 25%" aria-valuenow="25" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">348 Processes. 1/4 Cores.</small>
                <div class="text-uppercase mb-1 mt-2">
                    <small>
                        <b>Memory Usage</b>
                    </small>
                </div>
                <div class="progress progress-xs">
                    <div class="progress-bar bg-warning" role="progressbar" style="width: 70%" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">11444GB/16384MB</small>
                <div class="text-uppercase mb-1 mt-2">
                    <small>
                        <b>SSD 1 Usage</b>
                    </small>
                </div>
                <div class="progress progress-xs">
                    <div class="progress-bar bg-danger" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">243GB/256GB</small>
                <div class="text-uppercase mb-1 mt-2">
                    <small>
                        <b>SSD 2 Usage</b>
                    </small>
                </div>
                <div class="progress progress-xs">
                    <div class="progress-bar bg-success" role="progressbar" style="width: 10%" aria-valuenow="10" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <small class="text-muted">25GB/256GB</small>--}}
            </div>
        </div>
    </aside>
</div>
<footer class="app-footer">
    <div>

        <span>&copy; @php  @endphp</span>
    </div>
    <div class="ml-auto">
        <span class="font12">طراح وب سایت کنسرسیوم <a target="_blank" href="https://mirkan.ir/"> میرکان </a> </span>
    </div>
</footer>
@include('includes.cms-scripts')
@yield('more_script')
</body>
</html>
