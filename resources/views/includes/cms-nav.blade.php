<header class="app-header navbar">
    <button class="navbar-toggler sidebar-toggler d-lg-none mr-auto" type="button" data-toggle="sidebar-show">
        <span class="navbar-toggler-icon"></span>
    </button>
    <a class="navbar-brand" href="#">
        <img class="navbar-brand-full" src="/img/brand/logo.png" width="auto" height="49" alt="ISTPark">
        <img class="navbar-brand-minimized" src="/img/brand/logonet.png" width="30" height="30" alt="ISTPark">
    </a>
    <button class="navbar-toggler sidebar-toggler d-md-down-none" type="button" data-toggle="sidebar-lg-show">
        <span class="navbar-toggler-icon"></span>
    </button>
    <ul class="nav navbar-nav d-md-down-none">
        @if(\App\Facades\Rbac::check_access('blog','آخرین مطالب'))
            <li class="nav-item px-3">
                <a class="nav-link" href="/home/">آخرین مطالب</a>
            </li>
        @endif
        @if(\App\Facades\Rbac::check_access('contact-us','تماس با پارک'))
            <li class="nav-item px-3">
                <a class="nav-link" href="/home/">تماس با پارک</a>
            </li>
        @endif
    </ul>
    <ul class="nav navbar-nav ml-0 mr-auto">
        {{--<li class="nav-item d-md-down-none">--}}
            {{--<a class="nav-link" href="#">--}}
                {{--<i class="icon-bell"></i>--}}
                {{--<span class="badge badge-pill badge-danger">5</span>--}}
            {{--</a>--}}
        {{--</li>--}}
        {{--<li class="nav-item d-md-down-none">--}}
            {{--<a class="nav-link" href="#">--}}
                {{--<i class="icon-list"></i>--}}
            {{--</a>--}}
        {{--</li>--}}
        {{--<li class="nav-item d-md-down-none">--}}
            {{--<a class="nav-link" href="#">--}}
                {{--<i class="icon-location-pin"></i>--}}
            {{--</a>--}}
        {{--</li>--}}
        <li class="nav-item dropdown">
            <a class="nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                <span class="nameTag">{{\Illuminate\Support\Facades\Auth::user()->name}}</span>
                <img class="img-avatar" src="@if(!empty(Auth::user()->img)){{Auth::user()->img}} @else  /img/prof-default.png @endif" alt="{{\Illuminate\Support\Facades\Auth::user()->name}}">
            </a>
            <div class="dropdown-menu dropdown-menu-left">
                <a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    <i class="nav-icon icon-logout ml-2"></i> خروج
                </a>
                <form id="logout-form" action="/logout" method="POST" style="display: none;">
                    {{ csrf_field() }}
                </form>
            </div>
        </li>
    </ul>
    <button class="navbar-toggler aside-menu-toggler d-md-down-none" type="button" data-toggle="aside-menu-lg-show">
        <span class="navbar-toggler-icon"></span>
        @if(\Illuminate\Support\Facades\Auth::user()->notificationsC() != 0)
            <span class="badge badge-danger position-absolute" style="left: 20px;border-radius: 50%;font-size: 9px">{{\Illuminate\Support\Facades\Auth::user()->notificationsC()}}</span>
        @endif
    </button>
    <button class="navbar-toggler aside-menu-toggler d-lg-none" type="button" data-toggle="aside-menu-show">
        <span class="navbar-toggler-icon"></span>
        @if(\Illuminate\Support\Facades\Auth::user()->notificationsC() != 0)
            <span class="badge badge-danger position-absolute" style="left: 20px;border-radius: 50%;font-size: 9px">{{\Illuminate\Support\Facades\Auth::user()->notificationsC()}}</span>
        @endif
    </button>
</header>
