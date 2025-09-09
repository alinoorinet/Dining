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
                        $today = $date->jdate('امروز d Fماه Y');
                    @endphp
                    <i class="fa fa-clock-o ml-1"></i> {{$today}}
                </span>
                {{--<a class="btn" href="/home/modules-action/add" data-toggle="tooltip" title="کاربران" data-placement="top">--}}
                {{--<i class="icon-user"></i>--}}
                {{--</a>--}}
            </div>
        </li>
    </ol>
@endsection
@section('content')
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" style="max-width: 700px" role="document">
            <div class="modal-content">
                <div class="modal-header modalBg">
                    <h5 class="modal-title" id="exampleModalLabel"><i class="fa fa-clipboard"></i> جرئیات رزرو <span class="badge badge-warning"> دوشنبه 12-03-1399 </span></h5>
                    <button type="button" class="close ml-0" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="col-12 mb-lg p-0">
                        <div class="widget-body p-0 support table-wrapper table-responsive">
                            <table class="editTbl table table-borderless mb-0 rad8x ">
                                <thead>
                                <tr class="text-muted text-center border-bottom">
                                    <th class="font13"> عنوان </th>
                                    <th class="font13"> حذف </th>
                                    <th class="font13"> تعداد </th>
                                    <th class="font13"> مبلغ به ریال </th>
                                </tr>
                                </thead>
                                <tbody class="text-dark text-center ">
                                <tr>
                                    <td class="align-middle">چلو کباب کوبیده-نارنجک-شنگه ار پی جی
                                    </td>
                                    <td class="align-middle"><i class="fa fa-2x text-danger fa-trash"></i>
                                    </td>
                                    <td class="align-middle"><a class="btn btn-dribbble ">1</a>
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">42/000</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="align-middle">برنج-چای- نان برنجی
                                    </td>
                                    <td class="align-middle"><i class="fa fa-2x text-danger fa-trash"></i>
                                    </td>
                                    <td class="align-middle"><a class="btn btn-dribbble ">2</a>
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">92/000</a>
                                    </td>
                                </tr>
                                <tr class="border-top no-bot-padd">
                                    <td colspan="2" class="align-middle text-left">جمع
                                    </td>
                                    <td class="align-middle"><a class="btn btn-dribbble ">3</a>
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">134/000</a>
                                    </td>
                                </tr>
                                <tr class="no-padd">
                                    <td colspan="2" class="align-middle text-left">تخفیف
                                    </td>
                                    <td class="align-middle">
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">0</a>
                                    </td>
                                </tr>
                                <tr class="no-padd">
                                    <td colspan="2" class="align-middle text-left">قابل پرداخت
                                    </td>
                                    <td class="align-middle">
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">134/000</a>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-12 mb-lg mt-3 p-0 ">
                        <div class="widget-body p-0 support table-wrapper table-responsive">
                            <table class="table table-borderless mb-0 rad8x my-border">
                                <thead>
                                <tr class="text-muted text-center border-bottom">
                                    <th class="font13"> عنوان </th>
                                    <th class="font13"> ارزش غذایی </th>
                                    <th class="font13"> مبلغ به ریال </th>
                                    <th class="font13 text-success"> سفارش </th>
                                </tr>
                                </thead>
                                <tbody class="text-dark text-center ">
                                <tr>
                                    <td class="align-middle">چلو کباب کوبیده-نارنجک-شنگه ار پی جی
                                    </td>
                                    <td class="align-middle"><i class="fa fa-question-circle-o text-info"></i>
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">42/000</a>
                                    </td>
                                    <td class="align-middle"><i class="fa fa-2x fa-plus-square-o text-success"></i>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="align-middle">برنج-چای- نان برنجی
                                    </td>
                                    <td class="align-middle"><i class="fa fa-question-circle-o text-info"></i>
                                    </td>
                                    <td class="align-middle"><a class="btn w-100">92/000</a>
                                    </td>
                                    <td class="align-middle"><i class="fa fa-2x fa-plus-square-o text-success"></i>
                                    </td>
                                </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dribbble" data-dismiss="modal">ثبت نهایی سفارشات</button>
                    <button type="button" class="btn btn-danger" data-dismiss="modal">لغو</button>
                </div>
            </div>
        </div>
    </div>
    <div class="row myBg ">
        <div class="col-12 mb-lg ">
            <div class="row pb-1">
                <div class="col-lg-4 col-sm-4 col-md-4  mt-2 pt-3 pb-3">
                    <label for="1" >{{__('مجموعه')}} :</label>
                    <select class="form-control " id="1" >
                        <option value="0">انتخاب کنید ...</option>
                    </select>
                    @if($errors->has('1'))
                        <div class="invalid-feedback">
                            {{$errors->first('1')}}
                        </div>
                    @endif
                </div>
                <div class="col-lg-4 col-sm-4 col-md-4  mt-2 pt-3 pb-3">
                    <label for="2" >{{__('مجموعه')}} :</label>
                    <select class="form-control" id="2" >
                        <option value="0">انتخاب کنید ...</option>
                    </select>
                    @if($errors->has('2'))
                        <div class="invalid-feedback">
                            {{$errors->first('2')}}
                        </div>
                    @endif
                </div>
                <div class="col-lg-4 col-sm-4 col-md-4 mt-2 pt-3 text-center">
                    <div class="my-border rad8x p-2 bg-white">
                        <div class="row">
                            <div class="col-lg-3 pr-0">
                                <i class="fa fa-4x fa-arrow-circle-o-right text-success"></i>
                            </div>
                            <div class="col-lg-6 pr-0 pl-0">
                                <div class="badge badge-info p-2 mb-2 text-white">هفته جاری</div>
                                <div class="col-lg-12 pr-0 pl-0">برنامه غذایی <span>12/02</span> تا <span> 08/12</span></div>
                            </div>
                            <div class="col-lg-3 pl-0">
                                <i class="fa fa-4x fa-arrow-circle-o-left text-success"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 mb-lg ">
            <div class="widget-body p-0 support table-wrapper table-responsive">

                <table class="table table-bordered mb-0 tblBg rad8x my-border">
                    <thead>
                    <tr class="text-muted text-center">
                        <th class="font13"><i class="fa fa-clock-o"></i> تاریخ </th>
                        <th class="font13"><i class="fa fa-cogs"></i> تنظیمات </th>
                        <th colspan="2" class="font13"><i class="fa fa-sun-o"></i> صبحانه </th>
                        <th colspan="2" class="font13"><i class="fa fa-"></i> نهار </th>
                        <th colspan="2" class="font13"><i class="fa fa-moon-o"></i> شام </th>
                    </tr>
                    </thead>
                    <tbody class="text-dark text-center mailTbl">
                    <tr>
                        <td class="align-middle">
                            <span class="d-block">شنبه</span>
                            <span class="d-block">1399-03-12</span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#"  class="btn btn-light w-100">مشاهده تمام رزروهای امروز</a></span>
                            <span class="d-block"><a href="#" class="btn btn-light w-100">حذف تمام رزروهای امروز</a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle">
                            <span class="d-block">یکشنبه</span>
                            <span class="d-block">1399-03-13</span>
                        </td>
                        <td class="">
                            <span class="d-block"><a class="btn btn-light w-100">مشاهده تمام رزروهای امروز</a></span>
                            <span class="d-block"><a class="btn btn-light w-100">حذف تمام رزروهای امروز</a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle">
                            <span class="d-block">دوشنبه</span>
                            <span class="d-block">1399-03-14</span>
                        </td>
                        <td class="">
                            <span class="d-block"><a class="btn btn-light w-100">مشاهده تمام رزروهای امروز</a></span>
                            <span class="d-block"><a class="btn btn-light w-100">حذف تمام رزروهای امروز</a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">3</a></span>
                        </td>
                        <td class="">
                            <span class="d-block"><a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-check w-100">مشاهده / ویرایش</a></span>
                            <span class="d-block"><a class="btn border-dribbble text-right w-100 text-muted">مبلغ <span class="price">260000</span></a></span>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle">
                            <span class="d-block">سه شنبه</span>
                            <span class="d-block">1399-03-15</span>
                        </td>
                        <td class="">
                            <span class="d-block"><a disabled="" class="btn btn-light w-100">مشاهده تمام رزروهای امروز</a></span>
                            <span class="d-block"><a disabled="" class="btn btn-light w-100">حذف تمام رزروهای امروز</a></span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">0</a></span>
                        </td>
                        <td class="">
                            <a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-order w-100"><img src="/img/icons/reserve.png" class="reserveImg" width="30px">انتخاب غذا</a>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">0</a></span>
                        </td>
                        <td class="">
                            <a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-order w-100"><img src="/img/icons/reserve.png" class="reserveImg" width="30px">انتخاب غذا</a>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">0</a></span>
                        </td>
                        <td class="">
                            <a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-order w-100"><img src="/img/icons/reserve.png" class="reserveImg" width="30px">انتخاب غذا</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle">
                            <span class="d-block"> چهارشنبه </span>
                            <span class="d-block">1399-03-16</span>
                        </td>
                        <td class="">
                            <span class="d-block"><a disabled="" class="btn btn-light w-100">مشاهده تمام رزروهای امروز</a></span>
                            <span class="d-block"><a disabled="" class="btn btn-light w-100">حذف تمام رزروهای امروز</a></span>
                        </td>

                        <td colspan="2" class="">
                            <span class="btn btn-order w-100">تعریف نشده</span>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">0</a></span>
                        </td>
                        <td class="">
                            <a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-order w-100"><img src="/img/icons/reserve.png" class="reserveImg" width="30px">انتخاب غذا</a>
                        </td>
                        <td class="">
                            <span class="countB">تعداد</span>
                            <span class="d-block"><a class="btn btn-dribbble w-100">0</a></span>
                        </td>
                        <td class="">
                            <a href="#" data-toggle="modal" data-target="#editModal" class="btn btn-order w-100"><img src="/img/icons/reserve.png" class="reserveImg" width="30px">انتخاب غذا</a>
                        </td>
                    </tr>
                    <tr>
                        <td class="align-middle">
                            <span class="d-block">پنجشنبه</span>
                            <span class="d-block">1399-03-17</span>
                        </td>


                        <td colspan="7" class="">
                            <span class="btn btn-order w-100">تعطیل</span>
                        </td>

                    </tr>
                    </tbody>
                </table>
            </div>
        </div>



    </div>
@endsection
@section('more_script')
    <script>
        $('.req-refer').on('click', function () {
        $(function () {
            $('[data-toggle="tooltip"]').tooltip()
        })
    </script>
@endsection
