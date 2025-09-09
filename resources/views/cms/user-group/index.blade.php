@extends('layouts.cms')
@section('content')
    <div class="card mb-1">
        <div class="card-header">گروه های کاربری</div>
        <div class="card-body">
            <div class="bg-light mb-2">فرم ثبت گروه کاربری جدید</div>
            <form action="/home/user-group/store" method="post">
                @csrf
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label>نام گروه</label>
                            <input name="title" class="form-control @if($errors->has('title')) is-invalid @endif" tabindex="1" autofocus value="{{old('title')}}">
                            @if($errors->has('title'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('title')}}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label>شناسه kind</label>
                            <input name="kindid" class="form-control @if($errors->has('kindid')) is-invalid @endif" value="{{old('kindid')}}">
                            <span class="text-muted">0 = نامشخص</span>
                            @if($errors->has('kindid'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('kindid')}}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label>زیرمجموعه گروه کاربری دیگر است؟</label>
                            <select name="parent_id" class="form-control @if($errors->has('parent_id')) is-invalid @endif">
                                <option value="">گروه کاربری مستقل است</option>
                                @foreach($groups as $group)
                                    <option value="{{$group->id}}">{{$group->title}}</option>
                                @endforeach
                            </select>
                            @if($errors->has('parent_id'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('parent_id')}}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label>تعداد رزرو همزمان</label>
                            <input name="max_reserve_simultaneous" type="number" minlength="0" min="0" class="form-control @if($errors->has('max_reserve_simultaneous')) is-invalid @endif" value="{{old('max_reserve_simultaneous')}}">
                            <span class="text-muted small">محدودیت تعداد رزرو همزمان در چند رستوران</span>
                            @if($errors->has('max_reserve_simultaneous'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('max_reserve_simultaneous')}}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label>تعداد غذای قابل تخفیف</label>
                            <input name="max_discount" type="number" minlength="0" min="0" class="form-control @if($errors->has('max_discount')) is-invalid @endif" value="{{old('max_discount')}}">
                            <span class="text-muted small">تعداد غذایی هایی که شامل تخفیف در یک وعده غذایی می شود</span>
                            @if($errors->has('max_discount'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('max_discount')}}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label class="d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-success">ذخیره</button>
                        </div>
                    </div>
                </div>
            </form>
            <form action="/home/user-group/add-user" method="post" id="add-to-group-form">
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm" id="user-group-tbl">
                        <thead>
                        <tr>
                            <th class="text-center"></th>
                            <th class="text-center">#</th>
                            <th class="text-right">نام</th>
                            <th class="text-right">گروه سرشاخه</th>
                            <th class="text-right">شناسه kind</th>
                            <th class="text-right">رزرو همزمان</th>
                            <th class="text-right">تعداد غذای تخفیف دار</th>
                            <th class="text-right">ایجاد</th>
                            <th class="text-center">ویرایش</th>
                            <th class="text-center">حذف</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($groups as $group)
                            <tr>
                                <td class="text-center align-middle"><input type="checkbox" name="user_groups[]" id="ug-{{$group->id}}" value="{{$group->id}}"></td>
                                <td class="text-center">{{$loop->index + 1}}</td>
                                <td class="text-right" id="ug-name-{{$group->id}}">{{$group->title}}</td>
                                <td class="text-right">{{isset($group->parent->id) ? $group->parent->title : "-"}}</td>
                                <td class="text-right">{{$group->kindid}}</td>
                                <td class="text-right">{{$group->max_reserve_simultaneous}}</td>
                                <td class="text-right">{{$group->max_discount}}</td>
                                <td class="text-right">{{$group->created_at()}}</td>
                                <td class="text-center"><a class="btn btn-light btn-sm" href="/home/user-group/edit/{{$group->id}}"><i class="fa fa-edit"></i></a> </td>
                                <td class="text-center"><a class="btn btn-light btn-sm" href="/home/user-group/delete/{{$group->id}}"><i class="fa fa-trash"></i></a> </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <hr>
                @csrf
                <h5 class="mt-5">فرم تخصیص کاربر به گروه های کاربری با تعیین اولویت</h5>
                <div class="row">
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label>نام کاربری/ ش.دانشجویی/ کد ملی</label>
                            <input name="credential" class="form-control @if($errors->has('credential')) is-invalid @endif" tabindex="1" value="{{old('credential')}}" required>
                            @if($errors->has('credential'))
                                <div class="invalid-feedback">
                                    <strong>{{$errors->first('credential')}}</strong>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                        <label>تعیین اولویت</label>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm" id="user-group-priority-tbl">
                                <thead>
                                <tr>
                                    <th class="text-center">گروه کاربری</th>
                                    <th class="text-center">اولویت از <span id="min-priority">1</span> تا<span id="max-priority">{{$count}}</span></th>
                                    <th class="text-center">گروه پایه</th>
                                </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                        <div class="form-group">
                            <label class="d-block">&nbsp;</label>
                            <button type="submit" class="btn btn-success">ثبت</button>
                        </div>
                    </div>
                </div>
            </form>
            <hr>
            <h5>فرم جست و جو گروه کاربر</h5>
            <form method="post" id="group-search-form">
                @csrf
                <div class="row">
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                        <div class="form-group">
                            <label>نام کاربری/ ش.دانشجویی/ کد ملی</label>
                            <input name="credential" class="form-control" tabindex="1" required>
                        </div>
                    </div>
                    <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4 col-12">
                        <div class="form-group">
                            <label class="d-block">&nbsp</label>
                            <button type="submit" class="btn btn-primary"><i class="fa fa-search"></i></button>
                        </div>
                    </div>
                </div>
            </form>
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12" id="ug-search-result">
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
            $('input[name="user_groups[]"]').on('change', function () {
                const checkbox = $(this);
                const mixedId = checkbox.attr('id');
                const id = mixedId.split('-')[1];
                const userGroupTxt = $('td[id=ug-name-'+id+']').text();
                const priorityTblTbody = $('#user-group-priority-tbl').find('tbody');
                if(checkbox.is(':checked')) {
                    const maxPriority = parseInt($('#max-priority').text());
                    $(priorityTblTbody).append(
                        '<tr>\n' +
                        '    <td class="text-center">'+ userGroupTxt +'</td>\n' +
                        '    <td class="text-center" id="ug-name-same-'+id+'"><input type="number" class="form-control" min="1" max="'+maxPriority+'" name="priority_'+id+'" style="width: 70px"></td>\n' +
                        '</tr>'
                    );
                }
                else
                    $('td[id=ug-name-same-'+id+']').closest('tr').remove();
            });
            $('#group-search-form').on('submit', function (e) {
                e.preventDefault();
                const form = $(this);
                const btn  = form.find('button');
                const data = new FormData(this);

                $('.invalid-feedback').remove();
                $('.form-control').removeClass('is-invalid');
                $('#ug-search-result').html('');

                $(btn).html('<i class="fa fa-spinner fa-spin fa-lg fa-fw"></i>');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/user-group/search',
                    data: data,
                    dataType: 'json',
                    contentType: false,
                    processData: false,
                    success: function (data) {
                        $(btn).html('<i class="fa fa-search"></i>');
                        if (data.status === 200)
                            $('#ug-search-result').html(data.res);
                        else if(data.status === 101) {
                            $.each(data.res,function (k,v) {
                                let name = form.find("input[name='"+k+"'],textarea[name='"+k+"']");
                                if(k.indexOf(".") !== -1){
                                    let arr = k.split(".");
                                    name = $("input[name='"+arr[0]+"[]']:eq("+arr[1]+")");
                                }
                                $(name).after('<div class="invalid-feedback text-right d-block">'+v[0]+'</div>');
                            });
                        }
                    },
                    error: function (error) {
                        $(btn).html('<i class="fa fa-search"></i>');
                        alert('خطای اتصال به شبکه');
                        //location.reload();
                        console.log(error);
                    }
                });
            });
        });
    </script>
@endsection
