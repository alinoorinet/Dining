@extends('layouts.cms')
@section('more_style')
    <link rel="stylesheet" href="/plugins/iziToast/iziToast.min.css">
    <link rel="stylesheet" href="/plugins/iziToast/demo.css">
    <link rel="stylesheet" href="/v2/css/qCSS.css">
@endsection
@section('content')
    <div class="section">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12">
                    <h3 class="text-white">{{$qTitle}}</h3>
                </div>
            </div>
            {{--<div class="row" >--}}
            <section class="cards" id="prepared-row">
                @foreach($queuesPrepared as $queue)

                    {{!!$queue->prepared_view()}}
                @endforeach
            </section>


            {{--</div>--}}
        </div>
    </div>
@endsection
@section('more_script')
    <script src="/plugins/iziToast/iziToast.min.js"></script>
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            var newEv = function () {
                setInterval(function () {
                    let qName = "{{$qName}}";
                    $.ajax({
                        cache: false,
                        type: 'POST',
                        url: '/home/queue/get-prepared-queue',
                        data: JSON.stringify({qName:qName}),
                        dataContent: 'application/json',
                        processData: false,
                        dataType: 'json',
                        success: function (data) {
                            if(data.status === 200)
                                $('#prepared-row').prepend(data.res);
                        },
                        error: function (error) {
                            console.log(error);
                            // location.reload();
                        }
                    });
                },5000);
            };
            setTimeout(newEv,5000);
        });

    </script>
@endsection