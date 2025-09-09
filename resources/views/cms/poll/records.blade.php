@extends('layouts.cms')
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">نتایج نظرسنجی
                <a href="/home/poll" class="btn btn-outline-success pull-left" title=" نظرسنجی ها ">
                    <i class="fa fa-eye pull-right"></i>مشاهده
                </a>
            </h4>
        </div>
    </div>
    <div class="card">

        <div class="card-body">

            <div class="tab-content">
                <div role="tabpanel" class="tab-pane active" id="common">
                    <table class="table table-responsive table-striped table-bordered table-sm">
                        <thead>
                        <tr>
                            <th class="text-center" colspan="7">{{$poll->title}}</th>
                        </tr>
                        <tr>
                            <th class="text-center">گزینه ها</th>
                            <th class="text-center">تعداد</th>
                        </tr>



                        </thead>
                        <tbody>
                        @if(isset($questions[0]->id))
                            @foreach($questions as $question)
                                <tr>
                                    <td class="text-center">{{$question->title}}</td>
                                    <td class="text-center">{{$question->records()}}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="2" class="text-center thead-inverse">نظرسنجی ثبت نشده است.</td>
                            </tr>
                        @endif
                        </tbody>
                    </table>
                </div>

            </div>

        </div>
    </div>
@endsection
@section('more_script')
    <script>
        $(function () {
            "use strict";
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

        });
    </script>
@endsection