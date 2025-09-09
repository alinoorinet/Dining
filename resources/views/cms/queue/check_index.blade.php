@extends('layouts.cms')
@section('more_style')
    <link rel="stylesheet" href="/plugins/iziToast/iziToast.min.css">
    <link rel="stylesheet" href="/plugins/iziToast/demo.css">
@endsection
@section('content')
    <div class="section">
        <div class="container">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <h3 class="text-white">{{$qTitle}}</h3>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="card">
                        <div class="ui dimmer">
                            <div class="ui large text loader">چند لحظه صبر کنید...</div>
                        </div>
                        <div class="card-body">
                            <div class="card-title">صف انتظار</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="un-pre-tbl">
                                    <tbody id="un-prepared-tbody">
                                    @foreach($queuesUnPrepared as $queue)
                                        @php $user = $queue->user; @endphp
                                        <tr id="upTr{{$queue->id}}">
                                            <td>
                                                <table class="table">
                                                    <tbody>
                                                    <tr>
                                                        <td class="text-center p-0" colspan="2">{{$user->name}}</td>
                                                    </tr>
                                                    {!! $queue->orders !!}
                                                    <tr>
                                                        <td class="text-center p-0">{{$queue->bill_number}}</td>
                                                        <td class="text-center p-0"><button class="btn btn-link set-prepared" id="{{$queue->id}}"><i class="fa fa-check text-danger"></i></button></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="card-title">تحویل داده شده</div>
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="pre-tbl">
                                    <tbody id="prepared-tbody">
                                    @foreach($queuesPrepared as $queue)
                                        @php $user = $queue->user; @endphp
                                        <tr id="upTr{{$queue->id}}">
                                            <td>
                                                <table class="table">
                                                    <tbody>
                                                    <tr>
                                                        <td class="text-center p-0" colspan="2">{{$user->name}}</td>
                                                    </tr>
                                                    {!! $queue->orders !!}
                                                    <tr>
                                                        <td class="text-center p-0">{{$queue->bill_number}}</td>
                                                        <td class="text-center p-0"><button class="btn btn-link unset-prepared" id="{{$queue->id}}"><i class="fa fa-check text-success"></i></button></td>
                                                    </tr>
                                                    </tbody>
                                                </table>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/print/jQuery.print.js"></script>
    <script src="/plugins/iziToast/iziToast.min.js"></script>
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#un-pre-tbl').on('click','.set-prepared',function () {
                let btn = $(this);
                let qid = btn.attr('id');
                if(!jQuery.isNumeric(qid))
                    return false;

                $('.ui.dimmer').dimmer('show');
                $.ajax({
                    cache: false,
                    type: 'POST',
                    url: '/home/queue/set-prepared',
                    data: {
                        'qid' : qid,
                    },
                    dataType: 'json',
                    success: function(data)
                    {
                        $('.ui.dimmer').dimmer('hide');
                        if(data.status === 200) {
                            btn.html('<i class="fa fa-check text-success"></i>');
                            let currTr = $('#upTr'+qid);
                            let currTrInnerHtml = currTr.html();
                            currTrInnerHtml = '<tr id="pTr'+qid+'">' +currTrInnerHtml+ '</tr>';
                            currTr.fadeOut();
                            $('#prepared-tbody').append(currTrInnerHtml);
                        }
                        else
                            iziToast.show({
                                id: 'haduken',
                                theme: 'dark',
                                icon: 'icon-contacts',
                                title: 'پیام سیستم',
                                displayMode: 2,
                                message: data.res,
                                position: 'topCenter',
                                transitionIn: 'flipInX',
                                transitionOut: 'flipOutX',
                                progressBarColor: 'rgb(0, 255, 184)',
                                image: 'img/error.png',
                                imageWidth: 70,
                                layout: 2,
                                timeout: 2000,
                                resetOnHover: true,
                                rtl: true,
                                iconColor: 'rgb(0, 255, 184)'
                            });
                    },
                    error: function (error) {
                        $('.ui.dimmer').dimmer('hide');
                        //console.log(error);
                        location.reload();
                        iziToast.show({
                            id: 'haduken',
                            theme: 'dark',
                            icon: 'icon-contacts',
                            title: 'پیام سیستم',
                            displayMode: 2,
                            message: 'درحال حاضر انجام فرآیند امکان پذیر نیست',
                            position: 'topCenter',
                            transitionIn: 'flipInX',
                            transitionOut: 'flipOutX',
                            progressBarColor: 'rgb(0, 255, 184)',
                            image: 'img/error.png',
                            imageWidth: 70,
                            layout: 2,
                            timeout: 2000,
                            resetOnHover: true,
                            rtl: true,
                            iconColor: 'rgb(0, 255, 184)'
                        });
                    }
                });
            });
            var newEv = function () {
                setInterval(function () {
                    let qName = "{{$qName}}";
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/queue/get-queue',
                        data: JSON.stringify({qName:qName}),
                        dataContent: 'application/json',
                        processData: false,
                        dataType: 'json',
                        success: function (data) {
                            if(data.status === 200)
                                $('#un-prepared-tbody').append(data.res);
                        },
                        error: function (error) {
                            location.reload();
                        }
                    });
                },5000);
            };
            setTimeout(newEv,5000);
        });

    </script>
@endsection