@extends('layouts.cms')
@section('print')
    <style>
        .container-fluid,
        .navbar.navbar-expand-lg.navbar-dark.fixed-top,.btn {
            display: none;
        }
        body{
            padding-top: 0;
        }
    </style>
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <div class="card mb-3">
                    <div class="card-body" id="dynamicContent">
                        {!! $res->view !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection