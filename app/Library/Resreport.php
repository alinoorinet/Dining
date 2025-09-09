<?php
/**
 * Created by PhpStorm.
 * User: Ali
 * Date: 31/01/2018
 * Time: 09:56 PM
 */

namespace App\Library;


use App\Menu;
use App\Dorm;
use App\Food;
use App\FreeDdf;
use App\FreeDdo;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

class Resreport
{
    public $options;
    public $meal;
    public $beginDate;
    public $endDate;
    public $dormId;

    public $status;
    public $export  = [];
    public $filters = [];

    public function __construct($options = null)
    {
        $this->options   = $options;
        if ($options !== null)
            $this->filters();
    }

    public function make_report($meal, $beginDate, $endDate, $type = 1, $dormId = '')
    {
        $this->meal      = $meal;
        $this->beginDate = $beginDate;
        $this->endDate   = $endDate;
        $this->dormId    = $dormId;

        if(!empty($this->beginDate) && !empty($this->endDate) ) {
            $intervalTmp = $this->date_interval($this->beginDate,$this->endDate);
            if($type == 1) {
                $export = $this->calculator($intervalTmp);
                $this->status = 200;
                if(empty($export))
                    $this->status = 102;
                return json_encode([
                    'status'   => $this->status,
                    'response' => $export
                ]);
            }
            elseif ($type == 2) {
                $this->status = 200;
                if(empty($export))
                    $this->status = 102;
                $export = $this->dorm_person_info($intervalTmp,$dormId);
                return json_encode([
                    'status'   => $this->status,
                    'data'     => $export[0],
                    'view'     => $export[1],
                ]);
            }
            elseif ($type == 3) {
                $this->status = 200;
                $export = $this->fe_male_count($intervalTmp);
                if(empty($export))
                    $this->status = 102;
                return json_encode([
                    'status'   => $this->status,
                    'data'     => $export[0],
                    'view'     => $export[1],
                ]);
            }
            elseif ($type == 4) {
                $this->status = 200;
                if(empty($export))
                    $this->status = 102;
                $export = $this->guests_reserves($intervalTmp);
                return json_encode([
                    'status'   => $this->status,
                    'data'     => $export[0],
                    'view'     => $export[1],
                ]);
            }
        }
    }

    public function get_ddfs($date)
    {
        if (empty($this->meal))
            $ddfs = Menu::where('date',$date)->orderBy('date')->get();
        else
            $ddfs = Menu::where('date',$date)->where('meal',$this->meal)->orderBy('date')->get();

        if(isset($ddfs[0]->id))
            return $ddfs;
        return null;
    }

    public function calculator($intervalTmp)
    {
        $dorms = Dorm::all();
        $i = 0;
        $export = [];
        foreach ($intervalTmp as $date=>$day)
        {
            $ddfs = $this->get_ddfs($date);
            if($ddfs)
                foreach ($ddfs as $ddf) {
                    $uidDormId = [];
                    foreach ($dorms as $dorm) {
                        $uidDormId[$dorm->id]['title'] = $dorm->title;
                        $uidDormId[$dorm->id][$dorm->uid_dormid] = 0;
                    }

                    $foodTypeCounter = [];
                    $reserve_count = $ddf->reservation()->count();
                    $g = 0;
                    $b = 0;
                    $dorm = 0;
                    $ms = 0;
                    $bs = 0;
                    $phd = 0;
                    if ($reserve_count > 0) {
                        $reserves = $ddf->reservation;
                        foreach ($reserves as $reserve) {
                            $user = $reserve->user;
                            $userDorm = Dorm::find($user->dorm_id);

                            $food = Food::find($ddf->food_id);
                            $foodTypeCounter[$food->title] = [$g, $b];

                            if ($user->sex == 2)
                                $b += 1;
                            else if ($user->sex == 1)
                                $g += 1;

                            if($userDorm) {
                                if ($userDorm->title != 'غیر خوابگاهی')
                                    $dorm += 1;

                                $uidDormId[$userDorm->id][$userDorm->uid_dormid] += 1;
                            }

                            if ($user->ou == 'ms' || $user->ou == 'MS')
                                $ms += 1;

                            if ($user->ou == 'bs' || $user->ou == 'BS')
                                $bs += 1;

                            if ($user->ou == 'phd' || $user->ou == 'PHD')
                                $phd += 1;
                        }
                    }
                    if (empty($export)) {
                        $export[$i]['date'] = $ddf->date;
                        $export[$i]['day'] = $ddf->day;
                        if(in_array('g',$this->filters))
                            $export[$i]['g'] = $g;
                        if(in_array('b',$this->filters))
                            $export[$i]['b'] = $b;
                        if(in_array('dorm',$this->filters))
                            $export[$i]['dorm'] = $dorm;
                        if(in_array('ms',$this->filters))
                            $export[$i]['ms'] = $ms;
                        if(in_array('bs',$this->filters))
                            $export[$i]['bs'] = $bs;
                        if(in_array('phd',$this->filters))
                            $export[$i]['phd'] = $phd;
                        if(in_array('total',$this->filters))
                            $export[$i]['total'] = $reserve_count;
                        if(in_array('dorms',$this->filters))
                            $export[$i]['dorms'] = $uidDormId;
                        if(in_array('foodType',$this->filters))
                            $export[$i]['foodType'] = $foodTypeCounter;
                        if(in_array('non_dorm',$this->filters))
                            $export[$i]['non_dorm'] = ($reserve_count - $dorm);
                        $i++;
                    }
                    else {
                        if ($export[$i - 1]['date'] != $ddf->date) {
                            $export[$i] = [
                                'date' => $ddf->date,
                                'day' => $ddf->day,
                            ];
                            if(in_array('g',$this->filters))
                                $export[$i]['g'] = $g;
                            if(in_array('b',$this->filters))
                                $export[$i]['b'] = $b;
                            if(in_array('dorm',$this->filters))
                                $export[$i]['dorm'] = $dorm;
                            if(in_array('ms',$this->filters))
                                $export[$i]['ms'] = $ms;
                            if(in_array('bs',$this->filters))
                                $export[$i]['bs'] = $bs;
                            if(in_array('phd',$this->filters))
                                $export[$i]['phd'] = $phd;
                            if(in_array('total',$this->filters))
                                $export[$i]['total'] = $reserve_count;
                            if(in_array('dorms',$this->filters))
                                $export[$i]['dorms'] = $uidDormId;
                            if(in_array('foodType',$this->filters))
                                $export[$i]['foodType'] = $foodTypeCounter;
                            if(in_array('non_dorm',$this->filters))
                                $export[$i]['non_dorm'] = ($reserve_count - $dorm);
                            $i++;
                        }
                        else {
                            if(in_array('g',$this->filters))
                                $export[$i - 1]['g'] += $g;
                            if(in_array('b',$this->filters))
                                $export[$i - 1]['b'] += $b;
                            if(in_array('dorm',$this->filters))
                                $export[$i - 1]['dorm'] += $dorm;
                            if(in_array('ms',$this->filters))
                                $export[$i - 1]['ms'] += $ms;
                            if(in_array('bs',$this->filters))
                                $export[$i - 1]['bs'] += $bs;
                            if(in_array('phd',$this->filters))
                                $export[$i - 1]['phd'] += $phd;
                            if(in_array('total',$this->filters))
                                $export[$i - 1]['total'] += $reserve_count;
                            if(in_array('dorms',$this->filters))
                                $export[$i - 1]['dorms'] += $uidDormId;
                            if(in_array('foodType',$this->filters))
                                $export[$i - 1]['foodType'] += $foodTypeCounter;
                            if(in_array('non_dorm',$this->filters))
                                $export[$i - 1]['non_dorm'] += ($reserve_count - $dorm);
                        }
                    }
                }
            else {
                $export[$i]['date'] = $date;
                $export[$i]['day'] = $day;
                if(in_array('g',$this->filters))
                    $export[$i]['g'] = '-';
                if(in_array('b',$this->filters))
                    $export[$i]['b'] = '-';
                if(in_array('dorm',$this->filters))
                    $export[$i]['dorm'] = '-';
                if(in_array('ms',$this->filters))
                    $export[$i]['ms'] = '-';
                if(in_array('bs',$this->filters))
                    $export[$i]['bs'] = '-';
                if(in_array('phd',$this->filters))
                    $export[$i]['phd'] = '-';
                if(in_array('total',$this->filters))
                    $export[$i]['total'] = '-';
                if(in_array('dorms',$this->filters))
                    $export[$i]['dorms'] = '-';
                if(in_array('foodType',$this->filters))
                    $export[$i]['foodType'] = '-';
                if(in_array('non_dorm',$this->filters))
                    $export[$i]['non_dorm'] = '-';
                $i++;
            }
        }
        return $export;
    }

    public function dorm_person_info($intervalTmp,$dormId)
    {
        $dorms = Dorm::all();
        $dormPersonInfo = [];
        $i = 0;
        foreach ($intervalTmp as $date=>$day)
        {
            $dormPersonInfo[$i]['day'] = $day;
            $dormPersonInfo[$i]['date'] = $date;
            $ddfs = $this->get_ddfs($date);
            if($ddfs) {
                $j = 0;
                foreach ($ddfs as $ddf) {
                    $uidDormId = [];
                    foreach ($dorms as $dorm) {
                        $uidDormId[$dorm->id]['title'] = $dorm->title;
                        $uidDormId[$dorm->id][$dorm->uid_dormid] = 0;
                    }

                    $foodTypeCounter = [];
                    $reserve_count = $ddf->reservation()->count();
                    $g = 0;
                    $b = 0;
                    $dorm = 0;

                    if ($reserve_count > 0) {
                        //$reserves = $ddf->reservation;
                        $reserves = $ddf->reservation()->get();
                        foreach ($reserves as $reserve) {
                            $user = $reserve->user;
                            $userDorm = Dorm::find($user->dorm_id);

                            $food      = Food::find($ddf->food_id);
                            $foodTitle = isset($food->title)?$food->title:'تعریف نشده';

                            if ($userDorm && $userDorm->title != 'غیر خوابگاهی')
                                $dorm += 1;
                            //filter
                            foreach ($this->filters as $filter)
                                switch ($filter) {
                                    case 'g':
                                        if($user->sex == 1)
                                            if ($userDorm && $userDorm->id == $dormId) {
                                                $name = isset($user->name) ? $user->name : '';
                                                if(strpos($name,'-') > 0) {
                                                    $name = explode('-',$name);
                                                    $name = trim($name[1].' '.$name[0]);
                                                }
                                                $dormPersonInfo[$i]['info'][] = [
                                                    'name'        => $name,
                                                    'std_no'      => $user->std_no,
                                                    'dorm'        => $userDorm->title,
                                                    'food'        => $foodTitle,
                                                    'eaten-status' => $reserve->eaten == 1 ? '<i class="fa fa-check text-success"></i>':'<i class="fa fa-times text-danger"></i>',
                                                    'eaten'        => $reserve->eaten,
                                                ];
                                            }
                                        break;
                                    case 'b':
                                        if ($user->sex == 2)
                                            if ($userDorm && $userDorm->id == $dormId) {
                                                $name = isset($user->name) ? $user->name : '';
                                                if(strpos($name,'-') > 0) {
                                                    $name = explode('-',$name);
                                                    $name = trim($name[1].' '.$name[0]);
                                                }
                                                $dormPersonInfo[$i]['info'][] = [
                                                    'name'          => $name,
                                                    'std_no'        => $user->std_no,
                                                    'dorm'          => $userDorm->title,
                                                    'food'          => $foodTitle,
                                                    'eaten-status'  => $reserve->eaten == 1 ? '<i class="fa fa-check text-success"></i>':'<i class="fa fa-times text-danger"></i>',
                                                    'eaten'         => $reserve->eaten,
                                                ];
                                            }
                                        break;
                                }
                            if($userDorm)
                                $uidDormId[$userDorm->id][$userDorm->uid_dormid] += 1;
                            $j++;
                        }
                    }
                }
            }
            $i++;
        }
        $view = $this->make_view($dormPersonInfo,2);
        return [$dormPersonInfo,$view];
    }

    public function guests_reserves($intervalTmp)
    {
        $dorms = Dorm::all();
        $guestsInfo = [];
        $i = 0;
        foreach ($intervalTmp as $date=>$day)
        {
            $guestsInfo[$i]['day']  = $day;
            $guestsInfo[$i]['date'] = $date;
            $ddfs = $this->get_ddfs($date);
            if($ddfs) {
                $j = 0;
                foreach ($ddfs as $ddf) {
                    $uidDormId = [];
                    foreach ($dorms as $dorm) {
                        $uidDormId[$dorm->id]['title'] = $dorm->title;
                        $uidDormId[$dorm->id][$dorm->uid_dormid] = 0;
                    }
                    $reserve_count = $ddf->reservation()->count();
                    $dorm = 0;

                    if ($reserve_count > 0) {
                        $reserves = $ddf->reservation;
                        foreach ($reserves as $reserve) {
                            $user = $reserve->user;
                            if($user->ou == 'OU-Guest' || $user->ou == 'Guest' || $user->kindid == 7) {
                                $userDorm = Dorm::find($user->dorm_id);

                                $food = Food::find($ddf->food_id);
                                $foodTitle = isset($food->title) ? $food->title : 'تعریف نشده';

                                if ($userDorm && $userDorm->title != 'غیر خوابگاهی')
                                    $dorm += 1;
                                //filter
                                foreach ($this->filters as $filter)
                                    switch ($filter) {
                                        case 'g':
                                            if ($user->sex == 1) {
                                                $name = isset($user->name) ? $user->name : '';
                                                if (strpos($name, '-') > 0) {
                                                    $name = explode('-', $name);
                                                    $name = trim($name[1] . ' ' . $name[0]);
                                                }
                                                $guestsInfo[$i]['info'][] = [
                                                    'name' => $name,
                                                    'std_no' => $user->std_no,
                                                    'dorm' => $userDorm->title,
                                                    'food' => $foodTitle,
                                                ];
                                            }
                                            break;
                                        case 'b':
                                            if ($user->sex == 2) {
                                                $name = isset($user->name) ? $user->name : '';
                                                if (strpos($name, '-') > 0) {
                                                    $name = explode('-', $name);
                                                    $name = trim($name[1] . ' ' . $name[0]);
                                                }
                                                $guestsInfo[$i]['info'][] = [
                                                    'name' => $name,
                                                    'std_no' => $user->std_no,
                                                    'dorm' => $userDorm->title,
                                                    'food' => $foodTitle,
                                                ];
                                            }
                                            break;
                                    }
                                if ($userDorm)
                                    $uidDormId[$userDorm->id][$userDorm->uid_dormid] += 1;
                                $j++;
                            }
                        }
                    }
                }
            }
            $i++;
        }
        $view = $this->make_view($guestsInfo,4);
        return [$guestsInfo,$view];
    }

    public function fe_male_count($intervalTmp)
    {
        $feMaleInfo = [];
        $i = 0;
        foreach ($intervalTmp as $date=>$day)
        {
            $feMaleInfo[$i]['day'] = $day;
            $feMaleInfo[$i]['date'] = $date;
            $ddfs = $this->get_ddfs($date);
            if($ddfs) {
                $j = 0;
                $foodTypeCounter = [];
                $eatenCount = [];
                foreach ($ddfs as $ddf) {
                    $reserve_count = $ddf->reservation()->count();
                    $eaten_count   = $ddf->reservation()->where('eaten',1)->count();
                    $g = 0;
                    $b = 0;
                    $food = Food::find($ddf->food_id);
                    if ($reserve_count > 0) {
                        $reserves = $ddf->reservation;
                        foreach ($reserves as $reserve) {
                            $user = $reserve->user;
                            foreach ($this->filters as $filter)
                                switch ($filter) {
                                    case 'g':
                                        if($user->sex == 1)
                                            $g++;
                                        break;
                                    case 'b':
                                        if ($user->sex == 2)
                                            $b++;
                                }
                        }
                    }
                    $foodTypeCounter[$food->title]['sex'] = [$g, $b];
                    $foodTypeCounter[$food->title]['eaten_count'] = $eaten_count;
                    if (in_array('b',$this->filters)) {
                        if (isset($feMaleInfo[$i]['male']))
                            $feMaleInfo[$i]['male'] += $b;
                        else
                            $feMaleInfo[$i]['male']  = $b;
                    }
                    if (in_array('g',$this->filters)) {
                        if (isset($feMaleInfo[$i]['female']))
                            $feMaleInfo[$i]['female'] += $g;
                        else
                            $feMaleInfo[$i]['female']  = $g;
                    }
                }
                $feMaleInfo[$i]['food']        = $foodTypeCounter;
            }
            else {
                if (in_array('b',$this->filters))
                    $feMaleInfo[$i]['male']   = 0;
                if (in_array('g',$this->filters))
                    $feMaleInfo[$i]['female'] = 0;
            }
            $i++;
        }
        $view = $this->make_view($feMaleInfo,3);
        return [$feMaleInfo,$view];
    }

    public function filters()
    {
        $filters = [];
        foreach ($this->options as $option)
            if($option == 'زن')
                $filters[] = 'g';
            elseif($option == 'مرد')
                $filters[] = 'b';
            elseif($option == 'نوع غذا')
                $filters[] = 'foodType';
            elseif($option == 'خوابگاهی')
                $filters[] = 'dorm';
            elseif($option == 'غیر خوابگاهی')
                $filters[] = 'non_dorm';
            elseif($option == 'کارشناسی')
                $filters[] = 'bs';
            elseif($option == 'ارشد')
                $filters[] = 'ms';
            elseif($option == 'دکتری')
                $filters[] = 'phd';
            elseif($option == 'خوابگاه ها')
                $filters[] = 'dorms';
            elseif($option == 'کل')
                $filters[] = 'total';
        $this->filters = $filters;
    }

    public function date_interval($beginDate,$endDate)
    {
        $jdf = new jdf();
        $today = $jdf->jdate('d F Y ساعت H:i');
        $d1 = explode('-',trim($beginDate));
        $d2 = explode('-',trim($endDate));
        $jTog1 = $jdf->jalali_to_gregorian($d1[0],$d1[1],$d1[2]);
        $jTog2 = $jdf->jalali_to_gregorian($d2[0],$d2[1],$d2[2]);
        $beginDateTime  = \DateTime::createFromFormat('Y-m-d',$jTog1[0].'-'.$jTog1[1].'-'.$jTog1[2]);
        $beginTimestamp = mktime(00,00,00,$beginDateTime->format('m'),$beginDateTime->format('d'),$beginDateTime->format('Y'));
        $endDateTime    = \DateTime::createFromFormat('Y-m-d',$jTog2[0].'-'.$jTog2[1].'-'.$jTog2[2]);
        $endTimestamp   = mktime(00,00,00,$endDateTime->format('m'),$endDateTime->format('d'),$endDateTime->format('Y'));
        $intervalTmp = [];
        for($i = $beginTimestamp; $i <= $endTimestamp; $i+= 86400)
            $intervalTmp[$jdf->jdate('Y-m-d',$i)] = $jdf->jdate('l',$i);
        return $intervalTmp;
    }

    public function make_view($data,$type)
    {
        $jdf = new jdf();
        $today = $jdf->jdate('d F Y ساعت H:i');
        if($type == 2) {
            $dorm = Dorm::find($this->dormId);
            $dormTitle = isset($dorm->title)? $dorm->title:'';
            $tbl = '<div class="card">
                        <div class="card-header">
                            <div class="col-sm-12 text-center align-items-center">
                            <a href="#" id="printBtn1" class="btn btn-outline-primary pull-left" title="چاپ گزارش رزروها">
                                <i class="fa fa-print"></i> چاپ گزارش
                            </a><img class="img-fluid" style="max-height: 80px" src="/img/print-header.png"></div>
                        </div>
                    </div>';
            $i = 1;
            $noEatenCount = 0;
            foreach ($data as $value) {
                $tbl .= '<div class="table-responsive"><table class="table table-striped table-bordered table-sm">
                <thead>
                <tr><th colspan="5" class="card-title bg-light text-muted font11">تاریخ گزارش گیری : '.$today.'</th></tr>
                <tr>
                    <th class="text-center"></th>
                    <th class="text-center">'.$value['day'].'</th>
                    <th class="text-center">'.$value['date'].'</th>
                    <th class="text-center">'.$dormTitle.'</th>
                    <th class="text-center">'.$this->meal.'</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">نام و خانوادگی</th>
                    <th class="text-center">شماره دانشجویی</th>
                    <th class="text-center">مصرف شده</th>
                    <th class="text-center">غذا</th>
                </tr>
                </thead>
                <tbody>';
                if(isset($value['info'])) {
//                    usort($value['info'], function ($x, $y) {
//                        // $sub1 = substr(mb_convert_encoding($x['name'],"auto",'UTF-8'),0,1);
//                        $sub1 = substr(mb_convert_encoding($x['name'],'UTF-8','UTF-8'),0,1);
//                        $sub2 = substr(mb_convert_encoding($y['name'],'UTF-8','UTF-8'),0,1);
//                        $ord1 = ord(mb_convert_encoding($sub1,'UTF-8','UTF-8'));
//                        $ord2 = ord(mb_convert_encoding($sub2,'UTF-8','UTF-8'));
//                        return $ord1 > $ord2;
//                    });
                    sort($value['info']);
                    foreach ($value['info'] as $info) {
                        $tbl .= '<tr>';
                        $tbl .= '<td class="text-center">' . $i . '</td>
                             <td class="text-center">' . $info['name'] . '</td>
                             <td class="text-center">' . $info['std_no'] . '</td>
                             <td class="text-center">' . $info['eaten-status'] . '</td>
                             <td class="text-center">' . $info['food'] . '</td>';
                        $tbl .= '</tr>';
                        $i++;
                        if($info['eaten'] == 0)
                            $noEatenCount++;
                    }
                    $tbl .= '<tr>
                                <td colspan="3"></td>
                                <td class="text-center">مصرف نشده:'.$noEatenCount.'</td>
                                <td class="text-center"></td>
                            </tr>';
                }
                else
                    $tbl .= '<tr><td class="text-center" colspan="5">رزرو ندارد</td></tr>';
                $tbl .= '</tbody>
                         </table></div>';
            }
            return $tbl;
        }
        elseif($type == 3){
            $tbl = '<div class="card">
                        <div class="card-header">
                            <div class="col-sm-12 text-center align-items-center"><a href="/home/reserves-report/fe-male-count/print" class="btn btn-outline-primary pull-left" target="_blank" title="چاپ گزارش رزروها">
                                <i class="fa fa-print"></i> چاپ گزارش
                            </a><img class="img-fluid" style="max-height: 80px" src="/img/print-header.png"></div>
                        </div>
                    </div>';
            $tbl .= '<div class="table-responsive"><table class="table table-striped table-bordered table-sm">
                <thead>
                <tr>
                    <th class="text-center">لیست تعداد رزرو به تفکیک تاریخ و جنسیت</th>
                    <th class="text-center" colspan="3">'.$this->meal.'</th>
                </tr>
                <tr>
                    <th class="text-right">تاریخ</th>
                    <th class="text-right">تاریخ گزارش</th>
                    <th class="text-center">تعداد رزرو مرد</th>
                    <th class="text-center">تعداد رزرو زن</th>
                </tr>
                </thead>
                <tbody>';
            $tbl2 = '<div class="table-responsive"><table class="table table-striped table-bordered table-sm">
                <thead>
                <tr>
                    <th class="text-right">نوع غذا</th>
                    <th class="text-center">مرد</th>
                    <th class="text-center">زن</th>
                    <th class="text-center">مجموع رزرو</th>
                    <th class="text-center">مصرف شده</th>
                </tr>
                </thead>
                <tbody>';
            $totalF = 0;
            $totalM = 0;
            $totalEatenCount = 0;

            foreach ($data as $value) {
                $tbl .= '<tr>';
                $tbl .= '<td class="text-right">'.$value['date'].' - '.$value['day'].'</td>';
                $tbl .= '<td class="text-right">'.$today.'</td>';
                if(isset($value['male']))
                    $tbl .= '<td class="text-center">'.$value['male'].'</td>';
                else
                    $tbl .= '<td class="text-center">-</td>';
                if(isset($value['female']))
                    $tbl .= '<td class="text-center">'.$value['female'].'</td>';
                else
                    $tbl .= '<td class="text-center">-</td>';
                $tbl .= '</tr>';

                if(isset($value['food'])) {
                    foreach ($value['food'] as $foodTitle=>$v) {
                        $v0 = isset($v['sex'][1]) ? $v['sex'][1] : 0;
                        $v1 = isset($v['sex'][0]) ? $v['sex'][0] : 0;
                        $totalM += $v0;
                        $totalF += $v1;
                        $vTotal  = $v['sex'][0] + $v['sex'][1];
                        $eatenCount = $v['eaten_count'];
                        $totalEatenCount += $eatenCount;
                        $tbl2 .= '<tr>
                                  <td class="text-right font-15">'.$foodTitle.'</td>
                                  <td class="text-center font-15">'.$v0.'</td>
                                  <td class="text-center font-15">'.$v1.'</td>
                                  <td class="text-center font-15">'.$vTotal.'</td>
                                  <td class="text-center font-15">'.$eatenCount.'</td>
                                 </tr>';
                    }
                }
            }
            $total = ($totalM + $totalF);
            $tbl2 .= '<tr>
                          <td class="text-right">جمع کل </td>
                          <td class="text-center"><div class="badge badge-warning p-2 font-16 text-dark">'.$totalM.'</div></td>
                          <td class="text-center"><div class="badge badge-warning p-2 font-16 text-dark">'.$totalF.'</div></td>
                          <td class="text-center"><div class="badge badge-warning p-2 font-16 text-dark">'.$total.'</div></td>
                          <td class="text-center"><div class="badge badge-warning p-2 font-16 text-dark">'.$totalEatenCount.'</div></td>
                      </tr></tbody></table></div>';
            $tbl .= '</tbody>
                     </table></div>';
            $tbl .= $tbl2;
            return $tbl;
        }
        elseif($type == 4) {
            $tbl = '<div class="card">
                        <div class="card-header">
                            <div class="col-sm-12 text-center align-items-center"><a href="javascript:void(0)" class="btn btn-outline-primary pull-left" id="print" title="چاپ گزارش رزروها">
                                <i class="fa fa-print"></i> چاپ گزارش
                            </a><img class="img-fluid" style="max-height: 80px" src="/img/print-header.png"></div>
                        </div>
                    </div>';
            $i = 1;
            foreach ($data as $value) {
                $tbl .= '<div class="table-responsive"><table class="table table-striped table-bordered table-sm">
                <thead>
                <tr><th colspan="5" class="card-title bg-light text-muted font11">تاریخ گزارش گیری : '.$today.'</th></tr>
                <tr>
                    <th class="text-center">'.$value['day'].'</th>
                    <th class="text-center" colspan="2">'.$value['date'].'</th>
                    <th class="text-center" colspan="2">'.$this->meal.'</th>
                </tr>
                <tr>
                    <th class="text-center">#</th>
                    <th class="text-center">نام و خانوادگی</th>
                    <th class="text-center">شماره دانشجویی</th>
                    <th class="text-center">خوابگاه</th>
                    <th class="text-center">غذا</th>
                </tr>
                </thead>
                <tbody>';
                if(isset($value['info'])) {
                    usort($value['info'], function ($x, $y) {
                        $sub1 = substr(mb_convert_encoding($x['name'],"auto",'UTF-8'),0,1);
                        $sub2 = substr(mb_convert_encoding($y['name'],"auto",'UTF-8'),0,1);
                        $ord1 = ord(mb_convert_encoding($sub1,"auto",'UTF-8'));
                        $ord2 = ord(mb_convert_encoding($sub2,"auto",'UTF-8'));
                        return $ord1 > $ord2;
                    });
                    foreach ($value['info'] as $info) {
                        $tbl .= '<tr>';
                        $tbl .= '<td class="text-center">' . $i . '</td>
                             <td class="text-center">' . $info['name'] . '</td>
                             <td class="text-center">' . $info['std_no'] . '</td>
                             <td class="text-center">' . $info['dorm'] . '</td>
                             <td class="text-center">' . $info['food'] . '</td>';
                        $tbl .= '</tr>';
                        $i++;
                    }
                }
                else
                    $tbl .= '<tr><td class="text-center" colspan="5">رزرو ندارد</td></tr>';
                $tbl .= '</tbody>
                         </table></div>';
            }
            return $tbl;
        }
    }

    public function get_free_reserves_excel($date)
    {
        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('علی نوری')
            ->setLastModifiedBy('علی نوری')
            ->setTitle('آمار سامانه رستوران ارغوان دانشگاه ایلام')
            ->setSubject('آمار سامانه رستوران ارغوان دانشگاه ایلام')
            ->setDescription('آمار سامانه رستوران ارغوان دانشگاه ایلام');

        $columns = [
            1 => "A",
            2 => "B",
            3 => "C",
            4 => "D",
            5 => "E",
            6 => "F",
            7 => "G",
            8 => "H",
            9 => "I",
            10 => "J",
            11 => "K",
            12 => "L",
            13 => "M",
            14 => "N",
            15 => "O",
            16 => "P",
            17 => "Q",
            18 => "R",
            19 => "S",
            20 => "T",
            21 => "U",
            22 => "V",
            23 => "W",
            24 => "X",
            25 => "Y",
            26 => "Z"
        ];
        $meal = 'صبحانه';
        $ddfs = Menu::where('date', $date)->where('meal', $meal)->get();

        $tmp  = [];
        foreach ($ddfs as $ddf) {
            $reserves  = $ddf->reservation;
            $foodTitle = $ddf->food_title;
            if(isset($ddf->desserts[0]->id)) {
                foreach ($ddf->desserts as $dessert)
                    $foodTitle .= ' | '.$dessert->title;
            }
            foreach ($reserves as $reserve) {
                $user      = $reserve->user;
                $stdOrMeli = $user->std_no ? $user->std_no : $user->national_code;
                $tmp[$user->id]['name']      = $user->name;
                $tmp[$user->id]['stdOrMeli'] = $stdOrMeli;
                if(isset($tmp[$user->id]['reserves']))
                    array_push($tmp[$user->id]['reserves'],$foodTitle."(".$reserve->count.")");
                else
                    $tmp[$user->id]['reserves'][0] = $foodTitle."(".$reserve->count.")";
            }
        }

        $i = 1;
        $j = 1;
        foreach ($tmp as $userId=>$item) {
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue($columns[$i].$j, $item["name"])
                ->setCellValue($columns[$i+1].$j, $item["stdOrMeli"]);
            foreach ($item["reserves"] as $reserve) {
                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue($columns[$i+2].$j, $reserve);
                $i++;
            }
            $i = 1;
            $j++;
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="aliNooriDining"'.$date.'".xlsx"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: cache, must-revalidate');
        header('Pragma: public');
        $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
        $writer->save('php://output');
    }
}
