@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">کالاهای انبار {{$store->name}}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="bg-light mb-2">فرم ثبت کالای جدید</div>
                    <form action="/home/store/goods/store" method="post">
                        <input type="hidden" value="{{$store->id}}" name="store_id">
                        @csrf
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>نام کالا</label>
                                    <input name="name" class="form-control @if($errors->has('name')) is-invalid @endif" tabindex="1" autofocus value="{{old('name')}}">
                                    @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('name')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>برند</label>
                                    <input name="brand" class="form-control @if($errors->has('brand')) is-invalid @endif" tabindex="2" value="{{old('brand')}}">
                                    @if($errors->has('brand'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('brand')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>واحد مقدار</label>
                                    <select name="amount_unit" class="form-control @if($errors->has('amount_unit')) is-invalid @endif">
                                        <option value="">...</option>
                                        <option value="گرم">گرم</option>
                                        <option value="کیلوگرم">کیلوگرم</option>
                                        {{--<option value="تن">تن</option>
                                        <option value="لیوان">لیوان</option>
                                        <option value="قاشق چای خوری">قاشق چای خوری</option>
                                        <option value="دانه">دانه</option>
                                        <option value="بسته">بسته</option>--}}
                                    </select>
                                    @if($errors->has('amount_unit'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('amount_unit')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>آخرین قیمت</label>
                                    <input name="last_price" class="form-control @if($errors->has('last_price')) is-invalid @endif" tabindex="4" value="{{old('last_price') ? old('last_price') : 0}}">
                                    @if($errors->has('last_price'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('last_price')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>آخرین موجودی</label>
                                    <input name="last_amount" class="form-control @if($errors->has('last_amount')) is-invalid @endif" tabindex="5" value="{{old('last_amount') ? old('last_amount') : 0}}">
                                    @if($errors->has('last_amount'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('last_amount')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <label>ارزش غذایی</label>
                                    <input name="nut_value" class="form-control @if($errors->has('nut_value')) is-invalid @endif" tabindex="6" value="{{old('nut_value')}}">
                                    @if($errors->has('nut_value'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('nut_value')}}</strong>
                                        </div>
                                    @endif
                                    <span class="text-muted small">بر حسب میزان کالری در واحد مقدار کالا - مثال: 1000 یعنی هزار کالری در هر کیلو</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">ذخیره</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <div class="row mt-3">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="bg-light mb-2">لیست کالا های ثبت شده</div>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-right">نام کالا</th>
                                <th class="text-right">برند</th>
                                <th class="text-right">واحد مقدار</th>
                                <th class="text-center">آخرین قیمت</th>
                                <th class="text-center">آخرین موجودی</th>
                                <th class="text-center">ارزش غذایی<br><span class="text-muted small">بر حسب میزان کالری در واحد مقدار کالا</span></th>
                                <th class="text-center">ویرایش</th>
                                <th class="text-center">حذف</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($storeGoods as $storeGood)
                                <tr>
                                    <td class="text-center">{{$loop->index + 1}}</td>
                                    <td class="text-right"><a href="/home/store/goods/inventory/details/{{$storeGood->id}}" class="btn btn-link btn-sm">{{$storeGood->goods_name}}</a></td>
                                    <td class="text-right">{{$storeGood->brand}}</td>
                                    <td class="text-right">{{$storeGood->amount_unit}}</td>
                                    <td class="text-center">{{$storeGood->last_price}}</td>
                                    <td class="text-center">{{$storeGood->last_amount}}</td>
                                    <td class="text-center">{{$storeGood->nut_value}}</td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/store/goods/edit/{{$storeGood->id}}"><i class="fa fa-edit"></i></a> </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/store/goods/delete/{{$storeGood->id}}"><i class="fa fa-trash"></i></a> </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>
            <div class="row mt-3">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                    <div class="bg-light mb-2">کم کردن رزرو شده های اخیر از انبار</div>
                    <div class="alert alert-info">در هر بار کلیک روی دکمه محاسبه تنها 500 رکورد از رزرو ها بررسی می شوند</div>
                    <form action="/home/store/goods/store" method="post" id="sync-with-reserves-form">
                        <input type="hidden" value="{{$store->id}}" name="store_id">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-block">محاسبه</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script>
        $(function () {
            $('#sync-with-reserves-form').on('submit',function (e) {
                e.preventDefault();
                const form = $(this);
                const btn = form.find('button[type=submit]');
                const data = new FormData(this);
                $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');

                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/store/goods/details/sync-reserves',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(btn).html('محاسبه');
                        console.log(data);
                        if (data.status === 200) {

                            alert(data.res);
                        }
                        else if(data.status === 101){
                            data.res.map((err,k) =>{
                                alert(err);
                            })
                        }
                        else
                            alert(data.res);

                    },
                    error: function (error) {
                        $(btn).html('محاسبه');
                        alert('انجام فرآیند ناموفق بود.لطفاَ پس از بارگذاری خودکار صفحه، مجدداَ امتحان کنید');
                        //location.reload();
                        console.log(error)
                    }
                });
            });
        })
    </script>
@endsection
