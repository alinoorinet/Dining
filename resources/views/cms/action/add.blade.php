@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">فرم ثبت اکشن جدید
                <a href="/home/actions" class="btn btn-outline-info pull-left" title="لیست اکشن ها و ماژول های مربوطه">
                    <i class="fa fa-list pull-right"></i>لیست اکشن ها و ماژول های مربوطه
                </a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            <form method="post" action="/home/actions/store">
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
                        <label for="validationServer03"> ماژول </label>
                        <select class="custom-select mb-2 mr-sm-2 mb-sm-0 @if($errors->has('module_id'))is-invalid @endif" name="module_id">
                            <option selected>انتخاب...</option>
                            @foreach($modules as $module)
                                <option value="{{$module->id}}">{{$module->title}}</option>
                            @endforeach
                        </select>
                        @if($errors->has('module_id'))
                            <div class="invalid-feedback">
                                {{$errors->first('module_id')}}
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