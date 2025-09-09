@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">فرم ثبت گروه کاربری
                <a href="/home/roles" class="btn btn-outline-info pull-left" title="لیست گروه های کاربری">
                    <i class="fa fa-list pull-right"></i>لیست گروه های کاربری
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" action="/home/roles/store">
                {{ csrf_field() }}
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="title">عنوان</label>
                        <input type="text" class="form-control @if($errors->has('title'))is-invalid @endif" value="{{old('title')}}" name="title">
                        @if($errors->has('title'))
                            <div class="invalid-feedback">
                                {{$errors->first('title')}}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="validationServer02">توضیحات</label>
                        <textarea class="form-control @if($errors->has('description'))is-invalid @endif" name="description" required>{{old('description')}}</textarea>
                        @if($errors->has('description'))
                            <div class="invalid-feedback">
                                {{$errors->first('description')}}
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="validationServer03"> فعال؟ </label>
                        <select class="custom-select mb-2 mr-sm-2 mb-sm-0 @if($errors->has('locked'))is-invalid @endif" name="locked">
                            <option selected>انتخاب...</option>
                            <option value="0">بله</option>
                            <option value="1">خیر</option>
                        </select>
                        @if($errors->has('locked'))
                            <div class="invalid-feedback">
                                {{$errors->first('locked')}}
                            </div>
                        @endif
                    </div>
                </div>

                <button class="btn btn-primary" type="submit">ثبت</button>
            </form>
        </div>
    </div>
@endsection
@section('more_script')
<script>

</script>
@endsection