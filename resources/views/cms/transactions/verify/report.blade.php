@extends('layouts.cms')
@section('more_style')
    <style>
        @media (min-width: 1200px) {
            #trans-inquiry-report.table-responsive {
                display: block;
                width: 100%;
                overflow-x: auto;
                -ms-overflow-style: -ms-autohiding-scrollbar;
            }
        }
    </style>
@endsection
@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <h4 class="card-title">گزارش استعلام تراکنش
                <a href="/home/transactions/verify" class="btn btn-outline-success pull-left" title="فرم استعلام تراکنش ها">فرم استعلام تراکنش ها</a>
            </h4>
        </div>
    </div>
    <div class="card">
        <div class="card-body">
            @switch($type)
                @case(1)
                <table class="table table-responsive table-striped table-bordered table-md" id="trans-inquiry-report">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">CardNo</th>
                        <th class="text-center">PaymentID</th>
                        <th class="text-center">ReferenceNumber</th>
                        <th class="text-center">ResultCode</th>
                        <th class="text-center">RowNumber</th>
                        <th class="text-center">SpesialPaymentID</th>
                        <th class="text-center">TransDate</th>
                        <th class="text-center">VerifyDate</th>
                        <th class="text-center">VerifyResponse</th>
                        <th class="text-center">InvoiceNo</th>
                        <th class="text-center">ExtraParam1</th>
                        <th class="text-center">ExtraParam2</th>
                        <th class="text-center">ExtraParam3</th>
                        <th class="text-center">ExtraParam4</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($result->getDailyTransactionResult->transactionModel as $data)
                        <tr>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$data->AMOUNT}}</td>
                            <td class="text-center">{{$data->CARDNO}}</td>
                            <td class="text-center">{{$data->PAYMENTID}}</td>
                            <td class="text-center">{{$data->REFERENCENUMBER}}</td>
                            <td class="text-center">{{$data->RESULTCODE}}</td>
                            <td class="text-center">{{$data->ROWNUMBER}}</td>
                            <td class="text-center">{{$data->SPECIALPAYMENTID}}</td>
                            <td class="text-center">{{$data->TRANSDATE}}</td>
                            <td class="text-center">{{$data->VERIFYDATE}}</td>
                            <td class="text-center">{{$data->VERIFYRESPONSE}}</td>
                            <td class="text-center">{{$data->invoceNo}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM1}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM2}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM3}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM4}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @break
                @case(2)
                <table class="table table-responsive table-striped table-bordered table-md" id="trans-inquiry-report">
                    <thead>
                    <tr>
                        <th class="text-center">#</th>
                        <th class="text-center">Amount</th>
                        <th class="text-center">CardNo</th>
                        <th class="text-center">PaymentID</th>
                        <th class="text-center">ReferenceNumber</th>
                        <th class="text-center">ResultCode</th>
                        <th class="text-center">RowNumber</th>
                        <th class="text-center">SpesialPaymentID</th>
                        <th class="text-center">TransDate</th>
                        <th class="text-center">VerifyDate</th>
                        <th class="text-center">VerifyResponse</th>
                        <th class="text-center">InvoiceNo</th>
                        <th class="text-center">ExtraParam1</th>
                        <th class="text-center">ExtraParam2</th>
                        <th class="text-center">ExtraParam3</th>
                        <th class="text-center">ExtraParam4</th>
                        <th class="text-center">SettlementDate</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($result->getOfflineTransactionResult->transactionModel as $data)
                        <tr>
                            <td class="text-center">{{$loop->index+1}}</td>
                            <td class="text-center">{{$data->AMOUNT}}</td>
                            <td class="text-center">{{$data->CARDNO}}</td>
                            <td class="text-center">{{$data->PAYMENTID}}</td>
                            <td class="text-center">{{$data->REFERENCENUMBER}}</td>
                            <td class="text-center">{{$data->RESULTCODE}}</td>
                            <td class="text-center">{{$data->ROWNUMBER}}</td>
                            <td class="text-center">{{$data->SPECIALPAYMENTID}}</td>
                            <td class="text-center">{{$data->TRANSDATE}}</td>
                            <td class="text-center">{{$data->VERIFYDATE}}</td>
                            <td class="text-center">{{$data->VERIFYRESPONSE}}</td>
                            <td class="text-center">{{$data->invoceNo}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM1}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM2}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM3}}</td>
                            <td class="text-center">{{$data->EXTRAPARAM4}}</td>
                            <td class="text-center">{{$data->SETTLEMENTDATE}}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                @break
                @case(3)
                <table class="table table-responsive table-striped table-bordered table-md" id="trans-inquiry-report">
                    <thead>
                    <tr>
                        <th class="text-center">Amount</th>
                        <th class="text-center">CardNo</th>
                        <th class="text-center">PaymentID</th>
                        <th class="text-center">ReferenceNumber</th>
                        <th class="text-center">ResultCode</th>
                        <th class="text-center">RowNumber</th>
                        <th class="text-center">SpesialPaymentID</th>
                        <th class="text-center">TransDate</th>
                        <th class="text-center">VerifyDate</th>
                        <th class="text-center">VerifyResponse</th>
                        <th class="text-center">InvoiceNo</th>
                        <th class="text-center">ExtraParam1</th>
                        <th class="text-center">ExtraParam2</th>
                        <th class="text-center">ExtraParam3</th>
                        <th class="text-center">ExtraParam4</th>
                        <th class="text-center">SettlementDate</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">{{$result->getTransactionResult->AMOUNT}}</td>
                            <td class="text-center">{{$result->getTransactionResult->CARDNO}}</td>
                            <td class="text-center">{{$result->getTransactionResult->PAYMENTID}}</td>
                            <td class="text-center">{{$result->getTransactionResult->REFERENCENUMBER}}</td>
                            <td class="text-center">{{$result->getTransactionResult->RESULTCODE}}</td>
                            <td class="text-center">{{$result->getTransactionResult->ROWNUMBER}}</td>
                            <td class="text-center">{{$result->getTransactionResult->SPECIALPAYMENTID}}</td>
                            <td class="text-center">{{$result->getTransactionResult->TRANSDATE}}</td>
                            <td class="text-center">{{$result->getTransactionResult->VERIFYDATE}}</td>
                            <td class="text-center">{{$result->getTransactionResult->VERIFYRESPONSE}}</td>
                            <td class="text-center">{{$result->getTransactionResult->invoceNo}}</td>
                            <td class="text-center">{{$result->getTransactionResult->EXTRAPARAM1}}</td>
                            <td class="text-center">{{$result->getTransactionResult->EXTRAPARAM2}}</td>
                            <td class="text-center">{{$result->getTransactionResult->EXTRAPARAM3}}</td>
                            <td class="text-center">{{$result->getTransactionResult->EXTRAPARAM4}}</td>
                            <td class="text-center">{{$result->getTransactionResult->SETTLEMENTDATE}}</td>
                        </tr>
                    </tbody>
                </table>
                @break
                @case(4)
                <table class="table table-responsive table-striped table-bordered table-md" id="trans-inquiry-report">
                    <thead>
                    <tr>
                        <th class="text-center">Amount</th>
                        <th class="text-center">CardNo</th>
                        <th class="text-center">PaymentID</th>
                        <th class="text-center">ReferenceNumber</th>
                        <th class="text-center">ResultCode</th>
                        <th class="text-center">RowNumber</th>
                        <th class="text-center">SpesialPaymentID</th>
                        <th class="text-center">TransDate</th>
                        <th class="text-center">VerifyDate</th>
                        <th class="text-center">VerifyResponse</th>
                        <th class="text-center">InvoiceNo</th>
                        <th class="text-center">ExtraParam1</th>
                        <th class="text-center">ExtraParam2</th>
                        <th class="text-center">ExtraParam3</th>
                        <th class="text-center">ExtraParam4</th>
                        <th class="text-center">SettlementDate</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->AMOUNT}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->CARDNO}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->PAYMENTID}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->REFERENCENUMBER}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->RESULTCODE}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->ROWNUMBER}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->SPECIALPAYMENTID}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->TRANSDATE}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->VERIFYDATE}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->VERIFYRESPONSE}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->invoceNo}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->EXTRAPARAM1}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->EXTRAPARAM2}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->EXTRAPARAM3}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->EXTRAPARAM4}}</td>
                            <td class="text-center">{{$result->getLimitedTransacctionResult->SETTLEMENTDATE}}</td>
                        </tr>
                    </tbody>
                </table>
                @break
            @endswitch
        </div>
    </div>
@endsection
