<?php
/**
 * Created by PhpStorm.
 * User: a.noori
 * Date: 1397/05/09
 * Time: 8:32 AM
 */

namespace App\Library;

use App\Facades\Activity;
use Illuminate\Support\Facades\Auth;

class Transaction_Handler
{
    public function save_log($task,$desc,$ids = null)
    {
        Activity::create([
            'ip_address'  => \Request::ip(),
            'user_agent'  => \Request::header('user-agent'),
            'task'        => $task,
            'description' => $desc,
            'user_id'     => Auth::user()->id,
            'ids'         => $ids,
        ]);
    }

    public function user_transaction_result($status,$invNum = null,$referenceId = null,$msg = '')
    {
        switch ($status) {
            case 101:
                $thead = '<tr>
                             <th class="text-center text-white bg-danger">ارتباط با درگاه پرداخت ناموفق بود</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                             <td colspan="3" class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                          </tr>';
                break;
            case 102:
                $thead = '<tr>
                             <th class="text-center text-white bg-danger">ارتباط با درگاه پرداخت ناموفق بود - فرآیند افزایش موجودی ناموفق و مبلغ کسر شده حداکثر تا 72 ساعت آینده به حساب شما برگشت داده می شود</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                             <td colspan="3" class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                          </tr>';
                break;
            case 103:
                $thead = '<tr>
                             <th class="text-center text-white bg-danger">افزایش موجودی ناموفق بود در صورتی که از حساب شما مبلغ کسر شده است حداکثر تا 72 ساعت به حساب بانکی شما برگشت خواهد خورد در غیر این صورت در قسمت تماس با ما این مورد را جهت پیگیری اعلام کنید</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                             <td class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                          </tr>';
                break;
            case 104:
                $thead = '<tr>
                             <th class="text-center text-white bg-danger">به علت قطعی شبکه بانکی امکان بررسی تایید تراکنش شما در این لحظه وجود ندارد.این مورد توسط کارشناسان بررسی خواهد شد.شما میتوانید نتیجه را در منو "تراکنش های من" پیگیری کنید</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                             <td class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                          </tr>';
                break;
            case 105:
                $thead = '<tr>
                             <th colspan="2" class="text-center text-white bg-danger">فرآیند افزایش موجودی انجام نشد</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                             <th class="text-center">شرح فرآیند</th>
                          </tr>';
                $tbody = '<tr>
                             <td class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                             <td class="text-center">' . $msg . '</td>
                          </tr>';
                break;
            case 106:
                $thead = '<tr>
                             <th class="text-center text-white bg-danger">ارتباط شبکه بین بانکی در این لحظه قطع می باشد - فرآیند افزایش موجودی ناموفق و مبلغ کسر شده حداکثر تا 72 ساعت آینده به حساب شما برگشت داده می شود</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                             <td colspan="3" class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                          </tr>';
                break;
            case 107:
                $thead = '<tr>
                             <th class="text-center text-white bg-danger">فرآیند افزایش موجودی ناموفق بود لطفاً در ساعات دیگر مجدداً تلاش کنید. در صورت کسر از حساب، مبلغ حداکثر تا 72 ساعت آینده به حساب شما برگشت داده می شود</th>
                          </tr>
                          <tr>
                             <th class="text-center">وضعیت پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                             <td colspan="3" class="text-center">
                                 <i class="fa fa-times-circle fa-2x" style="color: red"></i>
                             </td>
                          </tr>';
                break;
            case 200:
                $thead = '<tr>
                              <th colspan="2" class="text-center bg-success">پرداخت با موفقیت انجام شد</th>
                          </tr>
                          <tr>
                              <th class="text-center">شماره فاکتور سیستمی</th>
                              <th class="text-center">کد پیگیری پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                              <td class="text-center" style="color: blue">' . $invNum . '</td>
                              <td class="text-center" style="color: blue">' . $referenceId . '</td>
                          </tr>';
                break;
                case 201:
                $thead = '<tr>
                              <th colspan="2" class="text-center bg-success">تراکنش تکراری است و قبلا اعتبارسنجی شده است</th>
                          </tr>
                          <tr>
                              <th class="text-center">شماره فاکتور سیستمی</th>
                              <th class="text-center">کد پیگیری پرداخت</th>
                          </tr>';
                $tbody = '<tr>
                              <td class="text-center" style="color: blue">' . $invNum . '</td>
                              <td class="text-center" style="color: blue">' . $referenceId . '</td>
                          </tr>';
                break;
        }
        $resp = '<div class="table-responsive">
                     <table class="table table-bordered">
                         <thead>'.$thead.'</thead>
                         <tbody>'.$tbody.'</tbody>
                     </table>
                 </div>';
        return $resp;
    }
}
