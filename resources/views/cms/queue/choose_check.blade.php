@extends('layouts.cms')
@section('content')
    <div class="section">
        <div class="container">
            <div class="row justify-content-center align-items-center">
                <div class="col-lg-6 col-md-6 col-sm-6">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3>انتخاب صف انتظار</h3>
                            @foreach($queues as $key=>$queue)
                                <p class="mt-5 mb-5 p-5">
                                    <a href="/home/queue/check/{{$key}}" class="btn btn-info btn-lg">{{$queue}}</a>
                                </p>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection