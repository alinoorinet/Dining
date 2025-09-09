@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">موجودی کاربران</h4>
        </div>
    </div>
    <div class="card">
        <div class="ui dimmer">
            <div class="ui large text loader">چند لحظه صبر کنید...</div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-sm-4">
                    <div class="card mt-2" style="min-height: 550px">
                        <div class="card-header">فرم جست و جو اطلاعات کاربر و تأییدیه آخرین مبلغ موجودی</div>
                        <div class="card-body">
                            <button type="button" class="btn btn-light" id="print"><i class="fa fa-print text-primary"></i></button>
                            <form id="checkInventory">
                                <div class="col-sm-12 mb-3">
                                    <label for="identify">شناسه کاربری یا شماره دانشجویی</label>
                                    <input type="text" class="form-control" id="identify" name="identify">
                                    <div class="invalid-feedback d-none"></div>
                                </div>
                                <div class="col-md-12 mb-3">
                                    <button class="btn btn-secondary btn-block" type="button" id="searchUidOrStd">بررسی</button>
                                </div>
                            </form>
                            <div id="forPrint" style="direction: rtl">
                                <div class="mt-2" id="processBox" style="display: block">
                                    <div class="text-center">
                                        <h2>اطلاعات کاربر</h2><hr>
                                        <div id="sec1">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <p>موارد فوق را جهت تسویه حساب تغذیه تأیید می کنم.</p>
                                    <p class="mt-5">امضاء دانشجو:</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-info mt-2" style="min-height: 550px;display: none" id="addCard">
                        <div class="card-header bg-info text-light border-info">فرم افزایش موجودی<i class="fa fa-arrow-up pull-left"></i></div>
                        <div class="card-body">
                            <form id="addWalletAmount">
                                <div class="col-sm-12 mb-3">
                                    <label for="amount">مبلغ(ریال)</label>
                                    <input type="text" class="form-control" id="amount" name="amount">
                                    <div class="invalid-feedback d-none"></div>
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="billCheckbox">
                                        <input type="checkbox" id="billCheckbox" style="vertical-align: middle"> شماره قبض دارد
                                    </label>
                                    <input type="text" class="form-control" id="billId" name="billId" disabled="disabled">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="trCheckbox">
                                        <input type="checkbox" id="trCheckbox" style="vertical-align: middle"> کد پیگیری پرداخت اینترنتی دارد
                                    </label>
                                    <input type="text" class="form-control" id="trackCode" name="trackCode" disabled="disabled" placeholder="کد مرجع فرعی">
                                    <input type="text" class="form-control mt-1" id="tId" name="tId" disabled="disabled" placeholder="آی دی تراکنش در جدول پایین">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="description">توضیحات</label>
                                    <textarea class="form-control" id="description" name="description"></textarea>
                                    <input type="hidden" id="userId">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <button class="btn btn-info btn-block" type="button" id="addWalletBtn">ذخیره</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="card border-warning mt-2" style="min-height: 550px;display: none" id="subCard">
                        <div class="card-header border-warning bg-warning text-dark">فرم کسر از موجودی<i class="fa fa-arrow-down pull-left"></i></div>
                        <div class="card-body">
                            <form id="subWalletAmount">
                                <div class="col-sm-12 mb-3">
                                    <label for="amount">مبلغ</label>
                                    <input type="text" class="form-control" id="amount" name="amount">
                                    <div class="invalid-feedback d-none"></div>
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="description">توضیحات</label>
                                    <textarea class="form-control" id="description" name="description"></textarea>
                                    <input type="hidden" id="userId">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <button class="btn btn-warning btn-block" type="button" id="subWalletBtn">ذخیره</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-1 d-none" id="paygates">
        <div class="card-body">
            <div class="card-title bg-light">پرداخت های اینترنتی کاربر</div>
            <div class="row">
                <div class="col-sm-12" id="paygates-body">
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/sweetalert/sweetalert.min.js"></script>
    <script src="/plugins/print/jQuery.print.js"></script>
    <script src="/js/cms/inventory/inventory.js"></script>
@endsection
