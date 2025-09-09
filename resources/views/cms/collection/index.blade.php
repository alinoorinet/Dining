@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">مجموعه های سرویس گیرنده اتوماسیون تغذیه</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                    <div class="bg-light mb-2">فرم ثبت مجموعه جدید</div>
                    <form action="/home/collection/store" method="post">
                        @csrf
                        <div class="form-group">
                            <label>نام مجموعه</label>
                            <input name="name" class="form-control @if($errors->has('name')) is-invalid @endif" tabindex="1" autofocus value="{{old('name')}}">
                            @if($errors->has('name'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('name')}}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>نوع مجموعه</label>
                            <label class="d-block text-muted">
                                <input type="radio" value="1" name="independent" checked> مستقل
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="0" name="independent"> وابسته به مجموعه دیگر
                            </label>
                        </div>
                        <div class="form-group">
                            <label>انتخاب وابستگی مجموعه</label>
                            <select name="parent_id" class="form-control @if($errors->has('parent_id')) is-invalid @endif">
                                <option value="">مجموعه مستقل است</option>
                                @foreach($collections as $collection)
                                    <option value="{{$collection->id}}">{{$collection->name}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('parent_id'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('parent_id')}}</strong>
                                </div>
                            @endif
                        </div>
                        <div class="form-group">
                            <label>مقدار معادل در سیستم احراز هویت خارجی</label>
                            <input name="equal_param_auth" class="form-control @if($errors->has('equal_param_auth')) is-invalid @endif" tabindex="3" value="{{old('equal_param_auth')}}">
                            @if($errors->has('equal_param_auth'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('equal_param_auth')}}</strong>
                                </div>
                            @endif
                            <span class="small text-muted">فقط درصورتی که احراز هویت خارجی فعال است وارد کنید</span>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-success">ذخیره</button>
                        </div>
                    </form>
                </div>
                <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 col-12">
                    <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                        <tr>
                            <th class="text-center">#</th>
                            <th class="text-right">نام</th>
                            <th class="text-right">نوع مجموعه</th>
                            <th class="text-right">وابستگی مجموعه</th>
                            <th class="text-right">مقدار معادل در سیستم احراز هویت</th>
                            <th class="text-right">ایجاد</th>
                            <th class="text-center">فعال</th>
                            <th class="text-center">ویرایش</th>
                            <th class="text-center">حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($collections as $collection)
                            <tr>
                                <td class="text-center">{{$loop->index + 1}}</td>
                                <td class="text-right">{{$collection->name}}</td>
                                <td class="text-right">{{$collection->independent == 0 ? 'وابسته' : 'مستقل' }}</td>
                                <td class="text-right">{{isset($collection->parent->id) ? $collection->parent->name : "-"}}</td>
                                <td class="text-right">{{$collection->equal_param_auth}}</td>
                                <td class="text-right">{{$collection->created_at()}}</td>
                                <td class="text-center">
                                    <a href="/home/collection/de-active/{{$collection->id}}" class="btn btn-light btn-sm">
                                        @if($collection->active)
                                            <i class="fa fa-check-circle text-success"></i>
                                        @else
                                            <i class="fa fa-times-circle text-warning"></i>
                                        @endif
                                    </a>
                                </td>
                                <td class="text-center"><a class="btn btn-light btn-sm" href="/home/collection/edit/{{$collection->id}}"><i class="fa fa-edit"></i></a> </td>
                                <td class="text-center"><a class="btn btn-light btn-sm" href="/home/collection/delete/{{$collection->id}}"><i class="fa fa-trash"></i></a> </td>
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
@section('more_script')
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#define-btn').on('click',function () {
                $('#define-form').submit();
            });
            $('#card_hex').on('change',function () {

                $(this).focus().select();
            });
            $('#search-card-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn  = form.find('button');
                const data = new FormData(this);
                $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/card/search',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(btn).html('جست و جو');
                        if (data.status === 200)
                            $('#search-result').html(data.res);
                        else
                            alert(data.res);
                    },
                    error: function (error) {
                        $(btn).html('جست و جو');
                        alert('خطای اتصال به شبکه');
                        location.reload();
                        //console.log(error);
                    }
                });
            });
            $('#define-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn  = form.find('button');
                const data = new FormData(this);

                $('.invalid-feedback').remove();
                $('.form-control').removeClass('is-invalid');
                $('#define-result').html('');

                $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/card/define',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(btn).html('ثبت');
                        if (data.status === 200)
                            $('#define-result').html(data.res);
                        else if(data.status === 101) {
                            $.each(data.res,function (k,v) {
                                let name = $("input[name='"+k+"'],textarea[name='"+k+"']");
                                if(k.indexOf(".") !== -1){
                                    let arr = k.split(".");
                                    name = $("input[name='"+arr[0]+"[]']:eq("+arr[1]+")");
                                }
                                name.after('<div class="invalid-feedback text-right d-block">'+v[0]+'</div>');
                            });
                        }
                    },
                    error: function (error) {
                        $(btn).html('ثبت');
                        alert('خطای اتصال به شبکه');
                        //location.reload();
                        console.log(error);
                    }
                });
            });
        });
    </script>
@endsection
