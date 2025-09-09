@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">فرم ثبت کاربر
                <a href="/home/users" class="btn btn-outline-info pull-left" title="لیست کاربران">
                    <i class="fa fa-list pull-right"></i>لیست کاربران
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" action="/home/users/store">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="name">نام</label>
                        <input type="text" class="form-control @if($errors->has('name'))is-invalid @endif" value="{{old('name')}}" id="name" name="name">
                        @if($errors->has('name'))
                            <div class="invalid-feedback">
                                {{$errors->first('name')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="family">نام خانوادگی</label>
                        <input type="text" class="form-control @if($errors->has('family'))is-invalid @endif" value="{{old('family')}}" name="family" id="family">
                        @if($errors->has('family'))
                            <div class="invalid-feedback">
                                {{$errors->first('family')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="username">نام کاربری</label>
                        <input type="text" class="form-control @if($errors->has('username'))is-invalid @endif" value="{{old('username')}}" id="username" name="username">
                        @if($errors->has('title'))
                            <div class="invalid-feedback">
                                {{$errors->first('title')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="national_code">کد ملی</label>
                        <input type="text" class="form-control @if($errors->has('national_code'))is-invalid @endif" value="{{old('national_code')}}" id="national_code" name="national_code">
                        @if($errors->has('national_code'))
                            <div class="invalid-feedback">
                                {{$errors->first('national_code')}}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="std_no">شماره دانشجویی</label>
                        <input type="text" class="form-control @if($errors->has('std_no'))is-invalid @endif" value="{{old('std_no')}}" id="std_no" name="std_no">
                        @if($errors->has('std_no'))
                            <div class="invalid-feedback">
                                {{$errors->first('std_no')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="email">پست الکترونیک</label>
                        <input type="email" class="form-control @if($errors->has('email'))is-invalid @endif" value="{{old('email')}}" name="email" id="email">
                        @if($errors->has('email'))
                            <div class="invalid-feedback">
                                {{$errors->first('email')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="mobile">شماره همراه</label>
                        <input type="text" class="form-control @if($errors->has('mobile'))is-invalid @endif" value="{{old('mobile')}}" id="mobile" name="mobile">
                        @if($errors->has('mobile'))
                            <div class="invalid-feedback">
                                {{$errors->first('mobile')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="role_id">گروه کاربری</label>
                        <select class="form-control @if($errors->has('role_id'))is-invalid @endif" name="role_id[]" style="width: 100%" multiple id="role_id">
                            @if($roles)
                                @foreach($roles as $role)
                                    @if(Rbac::get_access('developer'))
                                        <option value="{{$role->id}}" @if(old('role_id') == $role->id) selected @endif>{{$role->title}}</option>
                                    @else
                                        @if($role->title != 'developer' && $role->title != 'staff' && $role->title != 'prof' && $role->title != 'bs' && $role->title != 'ms' && $role->title != 'phd')
                                            <option value="{{$role->id}}" @if(old('role_id') == $role->id) selected @endif>{{$role->title}}</option>
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                        </select>
                        @if($errors->has('role_id'))
                            <div class="invalid-feedback">
                                {{$errors->first('role_id')}}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="password">کلمه عبور</label>
                        <input type="password" class="form-control @if($errors->has('password'))is-invalid @endif" value="{{old('password')}}" id="password" name="password">
                        @if($errors->has('password'))
                            <div class="invalid-feedback">
                                {{$errors->first('password')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="password_confirmation">تکرار کلمه عبور</label>
                        <input type="password" class="form-control @if($errors->has('password-confirm'))is-invalid @endif" value="{{old('password_confirmation')}}" id="password_confirmation" name="password_confirmation">
                        @if($errors->has('password_confirmation'))
                            <div class="invalid-feedback">
                                {{$errors->first('password_confirmation')}}
                            </div>
                        @endif
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="d-block">&nbsp;</label>
                        <button class="btn btn-primary" type="submit">ثبت</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
@section('more_script')
<script>

</script>
@endsection
