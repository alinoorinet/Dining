@extends('layouts.cms')
@section('more_style')
    <link href="/plugins/datepicker/persian-datepicker.min.css" rel="stylesheet">
@endsection
@section('content')
    <div class="card mb-1">
        <div class="card-header">رویدادها و مراسمات</div>
        <div class="card-body">
            <div class="row">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="bg-light mb-2">فرم ثبت رویداد جدید</div>
                    <form action="/home/event/store" method="post">
                        @csrf
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>عنوان مراسم/رویداد</label>
                                    <input name="name" class="form-control @if($errors->has('name')) is-invalid @endif" tabindex="1" autofocus value="{{old('name')}}">
                                    @if($errors->has('name'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('name')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>تعداد مهمان</label>
                                    <input name="guest_count" type="number" class="form-control @if($errors->has('guest_count')) is-invalid @endif" tabindex="2" value="{{old('guest_count')}}">
                                    @if($errors->has('guest_count'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('guest_count')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>معاونت/مرکز برگزار کننده</label>
                                    <input name="organization" class="form-control @if($errors->has('organization')) is-invalid @endif" tabindex="2" value="{{old('organization')}}">
                                    @if($errors->has('organization'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('organization')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>نوع مهمانان</label>
                                    <select name="guest_type" class="form-control @if($errors->has('guest_type')) is-invalid @endif">
                                        <option value="">...</option>
                                        <option value="داخلی">داخلی</option>
                                        <option value="خارجی">خارجی</option>
                                        <option value="داخلی و خارجی">داخلی و خارجی</option>
                                    </select>
                                    @if($errors->has('guest_type'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('guest_type')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>حداکثر رزرو هر نفر</label>
                                    <input type="number" name="max_user_reserve" class="form-control @if($errors->has('max_user_reserve')) is-invalid @endif" tabindex="5" value="{{old('max_user_reserve')}}">
                                    @if($errors->has('max_user_reserve'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('max_user_reserve')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>برگزاری از تاریخ</label>
                                    <input name="from_date" id="from_date" autocomplete="off" class="form-control @if($errors->has('from_date')) is-invalid @endif" tabindex="6" value="{{old('from_date')}}">
                                    @if($errors->has('from_date'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('from_date')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-xl-3 col-lg-3 col-md-3 col-sm-3 col-12">
                                <div class="form-group">
                                    <label>برگزاری تا تاریخ</label>
                                    <input name="to_date" id="to_date" autocomplete="off" class="form-control @if($errors->has('to_date')) is-invalid @endif" tabindex="7" value="{{old('to_date')}}">
                                    @if($errors->has('to_date'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('to_date')}}</strong>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label class="control-label d-block">گروه های کاربری</label>
                                    <div class="form-control" style="max-height: 90px; overflow: auto">
                                        @foreach($userGroups as $i=>$userGroup)
                                            @php
                                                $children = $userGroup->children;
                                            @endphp
                                            @if(isset($children[0]->id))
                                                <label>{{$userGroup->title}}</label>
                                                @foreach($children as $child)
                                                    <label class="d-block mr-5">
                                                        <input type="checkbox" name="user_group[]" class="d-inline-block @if($errors->has('user_group.'.$i)) is-invalid @endif" value="{{$child->id}}"> {{$child->title}}

                                                        <label class="d-inline-block text-primary mr-2">حداکثر رزرو</label>
                                                        <input type="number" class="form-control d-inline-block" name="max_reserve{{$child->id}}" value="@if(old('max_reserve'.$child->id)){{old('max_reserve'.$child->id)}}@else{{__('0')}}@endif" style="width: 70px">
                                                    </label>
                                                    @if($errors->has('user_group.'.$i))
                                                        <div class="invalid-feedback d-block text-danger">{{$errors->first('user_group.'.$i)}}</div>
                                                    @endif
                                                @endforeach
                                            @else
                                                <label class="d-block">
                                                    <input type="checkbox" name="user_group[] @if($errors->has('user_group.'.$i)) is-invalid @endif" value="{{$userGroup->id}}"> {{$userGroup->title}}

                                                    <label class="d-inline-block text-primary mr-2">حداکثر رزرو</label>
                                                    <input type="number" class="form-control d-inline-block" name="max_reserve{{$userGroup->id}}" value="@if(old('max_reserve'.$userGroup->id)){{old('max_reserve'.$userGroup->id)}}@else{{__('0')}}@endif" style="width: 70px">
                                                </label>
                                                @if($errors->has('user_group.'.$i))
                                                    <div class="invalid-feedback d-block text-danger">{{$errors->first('user_group.'.$i)}}</div>
                                                @endif
                                            @endif
                                        @endforeach
                                    </div>
                                    <span class="text-muted small">گروه های کاربری که می توانند در این رویداد غذا رزرو کنند</span>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6 col-12">
                                <div class="form-group">
                                    <label class="control-label d-block">توضیحات</label>
                                    <textarea style="height: 90px" class="form-control @if($errors->has('description')) is-invalid @endif" name="description">{{old('description')}}</textarea>
                                    @if($errors->has('description'))
                                        <div class="invalid-feedback">
                                            <strong>{{$errors->first('description')}}</strong>
                                        </div>
                                    @endif
                                    <span class="text-muted small">توضیحات اختیاری شامل وعده ها و تعداد غذای هر وعده و نوع منوی غذایی</span>
                                </div>
                            </div>
                        </div>
                        <div class="row">
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
            <hr>
            <div class="row mt-3">
                <div class="col-xl-12 col-lg-12 col-md-12 col-sm-12 col-12">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                            <tr>
                                <th class="text-center">#</th>
                                <th class="text-right">رویداد</th>
                                <th class="text-center">تعداد مهمان</th>
                                <th class="text-right">معاونت/مرکز برگزار کننده</th>
                                <th class="text-right">نوع مهمانان</th>
                                <th class="text-center">حداکثر رزرو هر نفر</th>
                                <th class="text-right">برگزاری از تاریخ</th>
                                <th class="text-right">برگزاری تا تاریخ</th>
                                <th class="text-right">درخواست دهنده</th>
                                <th class="text-center">گروه های کاربری</th>
                                <th class="text-center">فعال</th>
                                <th class="text-center">تایید</th>
                                <th class="text-center">ویرایش</th>
                                <th class="text-center">حذف</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($events as $event)
                                @php
                                    $userGroups = $event->user_groups;
                                    $userGroupsStr = "";
                                    foreach ($userGroups as $userGroup)
                                        $userGroupsStr .= $userGroup->title.', ';
                                @endphp
                                <tr>
                                    <td class="text-center">{{$loop->index + 1}}</td>
                                    <td class="text-right"><a class="btn btn-link btn-sm" href="/home/event/details/{{$event->id}}" title="جزییات">{{$event->name}}</a></td>
                                    <td class="text-center">{{$event->guest_count}}</td>
                                    <td class="text-center">{{$event->organization}}</td>
                                    <td class="text-right">{{$event->guest_type}}</td>
                                    <td class="text-center">{{$event->max_user_reserve}}</td>
                                    <td class="text-right">{{$event->from_date}}</td>
                                    <td class="text-right">{{$event->to_date}}</td>
                                    <td class="text-right">{{$event->organizer->name}} {{$event->organizer->family}}</td>
                                    <td class="text-center">
                                        <a class="btn btn-light btn-sm" href="javascript:void(0)" data-toggle="popover" data-content="{{$userGroupsStr}}">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-light btn-sm" href="/home/event/de-active/{{$event->id}}">
                                            {!! $event->active == 1 ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' !!}
                                        </a>
                                    </td>
                                    <td class="text-center">
                                        <a class="btn btn-light btn-sm" href="/home/event/confirm/{{$event->id}}">
                                            {!! $event->confirmed == 1 ? '<i class="fa fa-check text-success"></i>' : '<i class="fa fa-times text-danger"></i>' !!}
                                        </a>
                                    </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/event/edit/{{$event->id}}"><i class="fa fa-edit"></i></a> </td>
                                    <td class="text-center"><a class="btn btn-light btn-sm" href="/home/event/delete/{{$event->id}}"><i class="fa fa-trash"></i></a> </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $events->links() !!}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/datepicker/persian-date.min.js"></script>
    <script src="/plugins/datepicker/persian-datepicker.min.js"></script>
    <script>
        $("#from_date,#to_date").persianDatepicker({
            initialValue: false,
            autoClose:true,
            format: 'YYYY-MM-DD',
            formatter: function (unixDate) {
                var self = this;
                var pdate = new persianDate(unixDate);
                pdate.formatPersian = false;
                return pdate.format(self.format);
            }
        });
        $('[data-toggle=popover]').popover();
    </script>
@endsection
