@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">پیگیری پرداخت های اینترنتی از طریق درگاه</h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="row">
                <div class="col-sm-3">
                    <div class="card mt-2" style="min-height: 505px">
                        <div class="card-header">به صورت روزانه</div>
                        <div class="card-body">
                            <form method="post" action="/home/transactions/inquiry">
                                {{ csrf_field() }}
                                <div class="col-sm-12 mb-3">
                                    <label for="offset">از شماره ی</label>
                                    <input type="text" class="form-control" value="{{old('offset')}}" name="offset">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="limit">تعداد</label>
                                    <input type="number" class="form-control" value="{{old('limit')}}" name="limit">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <input type="hidden" name="type" value="1">
                                    <button class="btn btn-success btn-block" type="submit">ثبت</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-2" style="min-height: 505px">
                        <div class="card-header">به صورت offline</div>
                        <div class="card-body">
                            <form method="post" action="/home/transactions/inquiry">
                                {{ csrf_field() }}
                                <div class="col-sm-12 mb-3">
                                    <label for="offset">از شماره ی</label>
                                    <input type="text" class="form-control" value="{{old('offset')}}" name="offset">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="limit">تعداد</label>
                                    <input type="number" class="form-control" value="{{old('limit')}}" name="limit">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="fromDate">از تاریخ</label>
                                    <input type="text" class="form-control" value="{{old('fromDate')}}" name="fromDate">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="toDate">تا تاریخ</label>
                                    <input type="text" class="form-control" value="{{old('toDate')}}" name="toDate">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <input type="hidden" name="type" value="2">
                                    <button class="btn btn-success btn-block" type="submit">ثبت</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-2" style="min-height: 505px">
                        <div class="card-header">استعلام تایید تراکنش</div>
                        <div class="card-body">
                            <form method="post" action="/home/transactions/inquiry">
                                {{ csrf_field() }}
                                <div class="col-sm-12 mb-3">
                                    <label for="invoiceNo">کد رهگیری</label>
                                    <input type="text" class="form-control" value="{{old('invoiceNo')}}" name="invoiceNo">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="referenceNo">کد مرجع</label>
                                    <input type="text" class="form-control" value="{{old('referenceNo')}}" name="referenceNo">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <input type="hidden" name="type" value="3">
                                    <button class="btn btn-success btn-block" type="submit">ثبت</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-sm-3">
                    <div class="card mt-2" style="min-height: 505px">
                        <div class="card-header">استعلام تراکنش</div>
                        <div class="card-body">
                            <form method="post" action="/home/transactions/inquiry">
                                {{ csrf_field() }}
                                <div class="col-sm-12 mb-3">
                                    <label for="invoiceNo">کد رهگیری</label>
                                    <input type="text" class="form-control" value="{{old('invoiceNo')}}" name="invoiceNo">
                                </div>
                                <div class="col-sm-12 mb-3">
                                    <label for="amount">مبلغ</label>
                                    <input type="text" class="form-control" value="{{old('amount')}}" name="amount">
                                </div>
                                <div class="col-md-12 mb-3">
                                    <input type="hidden" name="type" value="4">
                                    <button class="btn btn-success btn-block" type="submit">ثبت</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script>

    </script>
@endsection