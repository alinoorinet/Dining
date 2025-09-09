@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">موجودی کالای {{$storeGoods->goods_name}}</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="bg-light mb-2">فرم افزایش/کاهش موجودی کالای <span class="bg-info text-white pl-2 pr-2 font-15">{{$storeGoods->goods_name}}</span></div>
                    <form action="/home/store/goods/inventory/store" method="post">
                        <input type="hidden" value="{{$storeGoods->id}}" name="goods_id">
                        @csrf
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>مقدار  <span class="text-info small">( بر حسب {{$storeGoods->amount_unit}} )</span></label>
                                    <input name="amount" class="form-control @if($errors->has('amount')) is-invalid @endif" tabindex="1" autofocus value="{{old('amount')}}">
                                    @if($errors->has('amount'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('amount')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>قیمت خرید</label>
                                    <input name="price" class="form-control @if($errors->has('price')) is-invalid @endif" tabindex="2" value="{{old('price')}}">
                                    @if($errors->has('price'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('price')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>نوع فرآیند</label>
                                    <select name="operator" class="form-control @if($errors->has('operator')) is-invalid @endif">
                                        <option value="">...</option>
                                        <option value="افزایش">افزایش</option>
                                        <option value="کاهش">کاهش</option>
                                    </select>
                                    @if($errors->has('operator'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('operator')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label class="control-label d-block">&nbsp;</label>
                                    <button type="submit" class="btn btn-success">ذخیره</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <h6>آخرین موجودی: <span>{{$storeGoods->last_amount}} {{$storeGoods->amount_unit}}</span></h6>
                    <h6>آخرین قیمت: <span>{{$storeGoods->last_price}} ریال</span></h6>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-right">نام کالا</th>
                                <th class="text-center">مقدار</th>
                                <th class="text-center">قیمت خرید</th>
                                <th class="text-center">افزایش/کاهش</th>
                                <th class="text-center">ویرایش</th>
                                <th class="text-center">حذف</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($goodsInventory as $goodInventory)
                                <tr>
                                    <td class="text-center">{{$loop->index + 1}}</td>
                                    <td class="text-right">{{$goodInventory->good->goods_name}}</td>
                                    <td class="text-center">{{$goodInventory->amount}}</td>
                                    <td class="text-center">{{$goodInventory->price}}</td>
                                    <td class="text-center {{$goodInventory->operator == "افزایش" ? "text-success" : "text-danger"}}">{{$goodInventory->operator}}</td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/store/goods/inventory/edit/{{$goodInventory->id}}"><i class="fa fa-edit"></i></a> </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/store/goods/inventory/delete/{{$goodInventory->id}}"><i class="fa fa-trash"></i></a> </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
