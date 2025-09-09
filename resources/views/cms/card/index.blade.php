@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">تعریف کارت</div>
        <div class="card-body">
            <form id="define-form" method="post">
                @csrf
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="form-group">
                            <label>شناسه کارت</label>
                            <input name="card_hex" id="card_hex" class="form-control" tabindex="1" autofocus>
                        </div>
                        <div class="form-group">
                            <label>ش.دانشجویی | شناسه کاربری یکتا | کد ملی</label>
                            <input name="credential" class="form-control">
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                        <div class="form-group">
                            <label>نوع کاربری کارت</label>
                            <label class="d-block text-muted">
                                <input type="radio" value="دانشجو" name="user_type" checked> دانشجو
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="کارکنان" name="user_type"> کارکنان
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="اساتید" name="user_type">اساتید
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="مهمان" name="user_type"> مهمان
                            </label>
                            <label class="d-block text-muted">
                                <input type="radio" value="آزاد" name="user_type"> آزاد
                            </label>
                        </div>
                        <div class="form-group">
                            <button type="button" class="btn btn-success" id="define-btn">ثبت</button>
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12" id="define-result"></div>
                </div>
            </form>
        </div>
    </div>
    <div class="card mb-1">
        <div class="card-body">
            <p class="mb-0">لیست کارت های هوشمندسازی شده</p>
            <hr>
            <div class="row">
                <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12" style="border-left: 1px solid rgba(0, 0, 0, 0.1);">
                    <form id="search-card-form" method="post">
                        @csrf
                        <div class="form-group">
                            <label>ش.دانشجویی | شناسه کاربری یکتا</label>
                            <input name="std_or_uid" class="form-control">
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary" id="search-btn">جست و جو</button>
                        </div>
                    </form>
                </div>
                <div class="col-xl-9 col-lg-9 col-md-9 col-sm-9 col-12" id="search-result"></div>
            </div>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead>
                    <tr>
                        <th colspan="8" class="text-center">تعداد کارت های هوشمند <div class="badge badge-info p-2 font-weight-bold" style="font-size: 16px">{{$cardCount}}</div></th>
                    </tr>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-right">نام کاربری</th>
                        <th class="text-right">شماره اختصاصی</th>
                        <th class="text-right">کد hex</th>
                        <th class="text-right">نوع</th>
                        <th class="text-right">ثبت</th>
                        <th class="text-right">آخرین بروزرسانی</th>
                        <th class="text-center"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($cards as $card)
                        <tr>
                            <td class="text-center">{{$loop->count - ($loop->index)}}</td>
                            <td class="text-right">{{$card->username}}</td>
                            <td class="text-right">{{$card->cardNumber}}</td>
                            <td class="text-right">{{$card->cardUid}}</td>
                            <td class="text-right">{{$card->type}}</td>
                            <td class="text-right">{{$card->created_at()}}</td>
                            <td class="text-right">{{$card->updated_at()}}</td>
                            <td class="text-center"><a class="btn btn-light" href="/home/card/delete/{{$card->id}}"><i class="fa fa-trash"></i></a></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
            {!! $cards->links() !!}
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
