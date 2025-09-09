<div class="sidebar">
    <nav class="sidebar-nav">
        <ul class="nav">
            <li class="nav-item">
                <a class="nav-link" href="/home">
                    <i class="nav-icon icon-speedometer"></i> خانه
                </a>
            </li>
            <li class="nav-title">میز کار</li>
            @if(Rbac::check_access('foodmenu','index'))
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#">
                        <i class="fa fa-calendar-plus"></i> برنامه غذایی </a>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <a class="nav-link" href="/home/foods">
                                <i class="nav-icon icon-plus"></i> تعریف غذا و تعیین قیمت </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/define-day-food/add">
                                <i class="nav-icon icon-plus"></i> چیدمان منو </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if(Rbac::check_access('store','index'))
                <li class="nav-item">
                    <a class="nav-link" href="/home/store">
                        <i class="fa fa-store-alt"></i> انبار
                    </a>
                </li>
            @endif
            @if(Rbac::check_access('event','index'))
                <li class="nav-item">
                    <a class="nav-link" href="/home/event">
                        <i class="fa fa-calendar-day"></i> رویداد و مراسمات
                    </a>
                </li>
            @endif
            @if(Rbac::check_access('event','index'))
                <li class="nav-item">
                    <a class="nav-link" href="/home/notification">
                        <i class="fa fa-envelope"></i>ارسال اعلان
                    </a>
                </li>
            @endif
            @if(Rbac::check_access('reserves','index'))
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#">
                        <i class="fa fa-plus-square"></i>رزرو</a>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report">
                                <i class="fa fa-chart-bar"></i> آمار کلی رزرو </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/mode-2">
                                <i class="fa fa-chart-pie"></i> آمار به تفکیک خوابگاه </a>
                        </li>
                        {{--<li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/fe-male-count">
                                <i class="fa fa-restroom"></i> آمار به تفکیک جنسیت </a>
                        </li>--}}
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/manual-check">
                                <i class="fa fa-user-check"></i> بررسی رزرو </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/edit-reserve-name">
                                <i class="fa fa-pen"></i> ویرایش عنوان رزرو </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/pay-back">
                                <i class="fa fa-reply"></i> بازگشت رزرو </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/guests">
                                <i class="fa fa-chart-area"></i> آمار مهمان </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/active-users">
                                <i class="fa fa-chart-line"></i> آمار کاربران فعال </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/reserves-report/statistics">
                                <i class="fa fa-chart-line"></i> آمار گروه های کاربری </a>
                        </li>
                    </ul>
                </li>
            @endif
            <li class="nav-item nav-dropdown">
                <a class="nav-link nav-dropdown-toggle" href="#">
                    <i class="fa fa-money-check-alt"></i>مالی</a>
                <ul class="nav-dropdown-items">
                    <li class="nav-item">
                        <a class="nav-link" href="/home/wallet">
                            <i class="fa fa-wallet"></i> کیف پول و افزایش اعتبار </a>
                    </li>
                    @if(Rbac::check_access('transactions','index'))
                        <li class="nav-item">
                            <a class="nav-link" href="/home/inventory">
                                <i class="fa fa-wallet"></i> موجودی کاربر </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/transactions/pay-gate">
                                <i class="fa fa-university"></i> گزارش درگاه پرداخت </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/transactions/verify">
                                <i class="fa fa-search-dollar"></i> پیگیری تراکنش </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/luhe">
                                <i class="fa fa-university"></i> محدودیت هزینه خوابگاه </a>
                        </li>
                    @endif
                </ul>
            </li>
            @if(Rbac::check_access('setting','index'))
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#">
                        <i class="fa fa-cogs"></i>تنظیمات</a>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <a class="nav-link" href="/home/collection">
                                <i class="fa fa-window-restore"></i> مجموعه </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/rest">
                                <i class="fa fa-university"></i> رستوران/سلف سرویس </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/setting">
                                <i class="fa fa-cog"></i> تنظیمات اولیه </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/card">
                                <i class="fa fa-id-card"></i> تعریف کارت </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if(Rbac::check_access('users','index'))
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#">
                        <i class="fa fa-user"></i>کاربران</a>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <a class="nav-link" href="/home/users">
                                <i class="fa fa-users"></i> لیست کاربران </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/users/srch">
                                <i class="fa fa-search"></i> جستجوی کاربران </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/user-group">
                                <i class="fa fa-user-circle"></i> گروه های کاربری </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/roles">
                                <i class="fa fa-universal-access"></i> دسترسی ها </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/roles/roles-actions">
                                <i class="fa fa-ban"></i> محدودیت دسترسی </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if(Rbac::check_access('modules','index'))
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#">
                        <i class="fa fa-tasks"></i>پیکربندی</a>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <a class="nav-link" href="/home/modules">
                                <i class="fa fa-sliders-h"></i> ماژول </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/actions">
                                <i class="fa fa-directions"></i> اکشن </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/activitys">
                                <i class="fa fa-list"></i> لاگ </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if(Rbac::check_access('notification','new_notification_list'))
                <li class="divider"></li>
                <li class="nav-item nav-dropdown">
                    <a class="nav-link nav-dropdown-toggle" href="#">
                        <i class="fa fa-layer-group"></i>بیشتر</a>
                    <ul class="nav-dropdown-items">
                        <li class="nav-item">
                            <a class="nav-link" href="/home/notification">
                                <i class="fa fa-exclamation-triangle"></i> اطلاعیه </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/feedback">
                                <i class="fa fa-vote-yea"></i> انتقادات و پیشنهادات </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="/home/poll">
                                <i class="fa fa-vote-yea"></i> مدیریت نظرسنجی </a>
                        </li>
                        {{--<li class="nav-item">
                            <a class="nav-link" href="/home/contact-us/show">
                                <i class="nav-icon icon-people"></i> پیام های تماس با ما </a>
                        </li>--}}
                        <li class="nav-item">
                            <a class="nav-link" href="/home/dorm/dorm-exception">
                                <i class="fa fa-user-tag"></i> رفع محدودیت غیرخوابگاهی </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </nav>
    <button class="sidebar-minimizer brand-minimizer mb-0" type="button"></button>
</div>
