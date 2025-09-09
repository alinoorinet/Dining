@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title text-center">لیست ماژول ها، نقش های کاربری و اکشن های نسبت داده شده</h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <div class="card bg-primary">
                <div class="card-body">
                    <h4 class="card-title text-white text-center">ماژول</h4>
                </div>
                <ul class="list-group list-group-flush" id="modules">
                    @foreach($modules as $module)
                        <li class="list-group-item font-weight-bold" id="{{$module->id}}" style="cursor: pointer"><input type="radio" name="module" value="{{$module->id}}" style="vertical-align: middle">{{$module->title}}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-secondary">
                <div class="ui dimmer">
                    <div class="ui large text loader">چند لحظه صبر کنید...</div>
                </div>
                <div class="card-body">
                    <h4 class="card-title text-white text-center">اکشن</h4>
                </div>
                <ul class="list-group list-group-flush" id="actions">
                </ul>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-body bg-info">
                    <h4 class="card-title text-white text-center">نقش</h4>
                </div>
                <ul class="list-group list-group-flush" id="roles">
                    @foreach($roles as $role)
                        <li class="list-group-item font-weight-bold" id="{{$role->id}}" style="cursor: pointer"><input type="radio" name="role" value="{{$role->id}}" style="vertical-align: middle">{{$role->title}}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
@section("more_script")
    <script src="/plugins/dimmer/dimmer.min.js"></script>
    <script>
        $("#modules").on("change",'li input[type=radio]',function () {
            var module = $(this);
            var module_id = module.val();
            var role = $('input[name=role]:checked');
            var role_id = role.val();
            if(module_id !== null && module_id != '' && module_id != undefined && role.is(':checked') && module.is(':checked')) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('.ui.dimmer').dimmer('show');

                var data = JSON.stringify({module_id: module_id, role_id:role_id});
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/roles/roles-actions/get-actions',
                    data: data,
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        if (data.status === true) {
                            $('.ui.dimmer').dimmer('hide');
                            var actions = $('#actions');
                            actions.empty();
                            $.each(data.res,function (k,v) {
                                actions.append('<li class="list-group-item"><label class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0"><input type="checkbox" class="custom-control-input" name="actionChecked[]" '+v.checked+' value="'+v.id+'"><span class="custom-control-indicator"></span><span class="custom-control-description">'+v.title+'</span></label></li>');
                            });
                        }
                        else {
                            alert(data.res);
                        }
                    },
                    error: function (data) {
                        $('.ui.page.dimmer').dimmer('hide');
                        alert('خطای اتصال به شبکه');
                    }
                });
            }
        });
        $("#roles").on("change",'li input[type=radio]',function () {
            var role = $(this);
            var role_id = role.val();
            var module = $('input[name=module]:checked');
            var module_id = module.val();
            if(module_id !== null && module_id != '' && module_id != undefined && role_id !== null && role_id != '' && role_id != undefined  && role.is(':checked') && module.is(':checked')) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('.ui.dimmer').dimmer('show');

                var data = JSON.stringify({module_id: module_id, role_id:role_id});
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/roles/roles-actions/get-actions',
                    data: data,
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        if (data.status === true) {
                            $('.ui.dimmer').dimmer('hide');
                            var actions = $('#actions');
                            actions.empty();
                            $.each(data.res,function (k,v) {
                                actions.append('<li class="list-group-item"><label class="custom-control custom-checkbox mb-2 mr-sm-2 mb-sm-0"><input type="checkbox" class="custom-control-input" name="actionChecked[]" '+v.checked+' value="'+v.id+'"><span class="custom-control-indicator"></span><span class="custom-control-description">'+v.title+'</span></label></li>');
                            });
                        }
                        else {
                            alert(data.res);
                        }
                    },
                    error: function (data) {
                        $('.ui.page.dimmer').dimmer('hide');
                        alert('خطای اتصال به شبکه');
                    }
                });
            }
        });
        $("#actions").on("change",'li input[type=checkbox]',function () {
            var action    = $(this);
            var action_id = action.val();
            var checked   = !!action.is(':checked');
            var role = $('input[name=role]:checked');
            var role_id = role.val();
            var module = $('input[name=module]:checked');
            var module_id = module.val();
            if(module_id !== null && module_id != '' && module_id != undefined && role_id !== null && role_id != '' && role_id != undefined  && role.is(':checked') && module.is(':checked')) {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $('.ui.dimmer').dimmer('show');
                var data = JSON.stringify({
                    module_id: module_id,
                    role_id:role_id,
                    action_id:action_id,
                    checked:checked
                });
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/roles/roles-actions/set-actions',
                    data: data,
                    dataType: 'json',
                    dataContent: 'application/json',
                    processData: false,
                    success: function (data) {
                        if (data.status === true) {
                            $('.ui.dimmer').dimmer('hide');
                            alert(data.res);
                        }
                        else {
                            alert(data.res);
                        }
                    },
                    error: function (data) {
                        $('.ui.page.dimmer').dimmer('hide');
                        alert('خطای اتصال به شبکه');
                    }
                });
            }
        })
    </script>
@endsection