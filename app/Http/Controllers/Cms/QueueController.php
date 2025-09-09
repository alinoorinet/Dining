<?php

namespace App\Http\Controllers\Cms;

use App\Facades\Rbac;
use App\FreeQueue;
use App\Library\jdf;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class QueueController extends Controller
{
    public function __construct()
    {
        return $this->middleware(['auth','Filter']);
    }

    public function choose_check()
    {
        $queues = [
            0 => 'طبقه همکف',
            1 => 'طبقه بالا',
        ];
        return view('cms.queue.choose_check',compact('queues'));
    }

    public function choose_prepared()
    {
        $queues = [
            0 => 'طبقه همکف',
            1 => 'طبقه بالا',
        ];
        return view('cms.queue.choose_prepared',compact('queues'));
    }

    public function check_index($qName)
    {
        if ($qName != 0 && $qName != 1)
            return redirect()->back();
        if (Rbac::check_access('queue','check')) {
            $jdf  = new jdf();
            $date  = $jdf->jdate('Y-m-d');
            $clock = $jdf->jdate('H:i:s');

            $meal = 0;
            if('01:00:00' < $clock && $clock <= '09:30:00')
                $meal = 1;
            elseif('09:30:00' < $clock && $clock <= '15:00:00')
                $meal = 2;
            elseif('15:00:00' < $clock && $clock <= '23:00:00')
                $meal = 3;

            $qTitle = $qName == 0 ?'طبقه همکف':'طبقه بالا';
            $queuesPrepared   = FreeQueue::where('date',$date)->where('meal',$meal)->where('queue_name',$qName)->where('prepared',1)->orderBy('id','desc')->get();
            $queuesUnPrepared = FreeQueue::where('date',$date)->where('meal',$meal)->where('queue_name',$qName)->where('prepared',0)->orderBy('id','desc')->get();
            if(count($queuesUnPrepared) > 0)
                session()->put('lastUpQId',$queuesUnPrepared[count($queuesUnPrepared) - 1]->id);

            return view('cms.queue.check_index',compact('queuesPrepared','queuesUnPrepared','qTitle','qName'));
        }
        return redirect()->back()->with('warningMsg','دسترسی  شما به این بخش امکان پذیر نیست');
    }

    public function set_prepared(Request $request)
    {
        if (Rbac::check_access('queue','set_prepared')) {
            $v = Validator::make($request->all(), [
                'qid' => 'required|digits_between:1,11|exists:free_queue,id',
            ]);
            if ($v->fails())
                return response()->json(['status' => 101, 'res' => 'مشخصات ورودی نامعتبر است']);
            $qId = $request->qid;
            FreeQueue::where('id', $qId)->update(['prepared' => 1,]);
            $freeQueue = FreeQueue::find($qId);

            $user   = $freeQueue->user;
            $mobile = $user->mobile;
            $msg  = 'سفارش شما آماده تحویل است.%0A شماره فیش:'.$freeQueue->bill_number.'%0Aرستوران ارغوان دانشگاه ایلام';
            $msg  = urlencode($msg);
            $curl = curl_init();
            $url  = "https://auth.ilam.ac.ir/sms/d8wl9co93uao2ab4czjp9ek4imz2np7r8/?receptor=$mobile&message=" . $msg;
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => $url,
                //CURLOPT_SSL_VERIFYPEER => False,
            ));
            curl_exec($curl);
            curl_close($curl);


            $freeQueue->prepared = 1;
            $freeQueue->update();
            return response()->json(['status' => 200,]);
        }
        return response()->json(['status' => 300,'res'=>'دسترسی شما به این بخش امکان پذیر نیست']);
    }

    public function get_queue(Request $request)
    {
        if (Rbac::check_access('queue','get_queue')) {
            $qName = $request->json()->get('qName');
            if ($qName != 0 && $qName != 1)
                return response()->json(['status' => 101,]);
            $jdf  = new jdf();
            $date  = $jdf->jdate('Y-m-d');
            $clock = $jdf->jdate('H:i:s');

            $meal = 0;
            if('01:00:00' < $clock && $clock <= '09:30:00')
                $meal = 1;
            elseif('09:30:00' < $clock && $clock <= '15:00:00')
                $meal = 2;
            elseif('15:00:00' < $clock && $clock <= '23:00:00')
                $meal = 3;

            $lastQueueId = session()->has('lastUpQId')?session('lastUpQId'):null;
            if($lastQueueId)
                $queuesUnPrepared = FreeQueue::where('id', '>', $lastQueueId)->where('queue_name',$qName)->where('date', $date)->where('meal', $meal)->where('prepared', 0)->limit(1)->orderBy('id','desc')->get();
            else
                $queuesUnPrepared = FreeQueue::where('queue_name',$qName)->where('date', $date)->where('meal', $meal)->where('prepared', 0)->limit(1)->orderBy('id','desc')->get();
            if(count($queuesUnPrepared) > 0)
                session()->put('lastUpQId',$queuesUnPrepared[count($queuesUnPrepared) - 1]->id);

            $tmp = '';
            foreach ($queuesUnPrepared as $queue)
                $tmp .= $queue->orders;

            return response()->json(['status' => 200,'res'=>$tmp]);
        }
        return response()->json(['status' => 300,'res'=>'دسترسی شما به این بخش امکان پذیر نیست']);
    }

    public function prepared_index($qName)
    {
        if ($qName != 0 && $qName != 1)
            return redirect()->back();
        if (Rbac::check_access('queue','prepared_index')) {
            $jdf  = new jdf();
            $date  = $jdf->jdate('Y-m-d');
            $clock = $jdf->jdate('H:i:s');

            $meal = 0;
            if('01:00:00' < $clock && $clock <= '09:30:00')
                $meal = 1;
            elseif('09:30:00' < $clock && $clock <= '15:00:00')
                $meal = 2;
            elseif('15:00:00' < $clock && $clock <= '23:00:00')
                $meal = 3;

            $qTitle = $qName == 0 ?'طبقه همکف':'طبقه بالا';

            $queuesPrepared   = FreeQueue::where('queue_name',$qName)->where('date',$date)->where('meal',$meal)->where('prepared',1)->limit(30)->orderBy('id','desc')->get();
            if(count($queuesPrepared) > 0)
                session()->put('lastPQId',$queuesPrepared[count($queuesPrepared) - 1]->id);

            return view('cms.queue.prepared_index',compact('queuesPrepared','qTitle','qName'));
        }
        return redirect()->back()->with('warningMsg','دسترسی  شما به این بخش امکان پذیر نیست');
    }

    public function get_prepared_queue(Request $request)
    {
        $qName = $request->json()->get('qName');
        if ($qName != 0 && $qName != 1)
            return response()->json(['status' => 101,]);
        if (Rbac::check_access('queue','get_prepared_queue')) {
            $jdf  = new jdf();
            $date  = $jdf->jdate('Y-m-d');
            $clock = $jdf->jdate('H:i:s');

            $meal = 0;
            if('01:00:00' < $clock && $clock <= '09:30:00')
                $meal = 1;
            elseif('09:30:00' < $clock && $clock <= '15:00:00')
                $meal = 2;
            elseif('15:00:00' < $clock && $clock <= '23:00:00')
                $meal = 3;

            $lastQueueId = session()->has('lastPQId')?session('lastPQId'):null;
            if($lastQueueId)
                $queuesPrepared = FreeQueue::where('id', '>', $lastQueueId)->where('queue_name',$qName)->where('date', $date)->where('meal', $meal)->where('prepared', 1)->limit(1)->orderBy('id','desc')->get();
            else
                $queuesPrepared = FreeQueue::where('queue_name',$qName)->where('date', $date)->where('meal', $meal)->where('prepared', 1)->limit(1)->orderBy('id','desc')->get();
            if(count($queuesPrepared) > 0)
                session()->put('lastPQId',$queuesPrepared[count($queuesPrepared) - 1]->id);

            $tmp = '';
            foreach ($queuesPrepared as $queue)
                $tmp .= $queue->orders;

            return response()->json(['status' => 200,'res'=>$tmp]);
        }
        return response()->json(['status' => 300,'res'=>'دسترسی شما به این بخش امکان پذیر نیست']);
    }
}
