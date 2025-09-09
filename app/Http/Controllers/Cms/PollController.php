<?php
/**
 * Created by PhpStorm.
 * User: pc
 * Date: 27/11/2019
 * Time: 02:47 PM
 */

namespace App\Http\Controllers\Cms;


use App\Facades\Activity;
use App\Facades\Rbac;
use App\Http\Controllers\Controller;
use App\Poll;
use App\PollAnswer;
use App\PollQuestion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PollController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function index()
    {
        if(Rbac::check_access('poll','index')) {

            $polls = Poll::orderBy('id','desc')->get();

            return view('cms.poll.index',[
                'polls' => $polls
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function add()
    {
        if(Rbac::check_access('poll','add')) {
            return view('cms.poll.add');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function create(Request $request)
    {
        if(Rbac::check_access('poll','add')) {
            $validator = Validator::make($request->all(), [
                'title'            => 'required|string|max:300',
                'pos1'             => 'required',
                'pos2'             => 'required',
                'pos3'             => 'required',
                'pos4'             => 'required',
            ],[
                'title.required'    => 'متن را وارد نمایید.',
                'title.string'      => 'فرمت متن اشتباه می باشد.',
                'title.max'         => 'متن می بایست حداکثر 300 کاراکتر باشد.',
                'pos1.required'     => 'گزینه اول را وارد نمایید.',
                'pos2.required'     => 'گزینه دوم را وارد نمایید.',
                'pos3.required'     => 'گزینه سوم را وارد نمایید.',
                'pos4.required'     => 'گزینه چهارم را وارد نمایید.',

            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator->errors());

            $poll = new Poll();
            $poll->title   = $request->title;
            $poll->user_id   = Auth::user()->id;
            $poll->active  = 0;
            $questions = [
                '1' => $request->pos1,
                '2' => $request->pos2,
                '3' => $request->pos3,
                '4' => $request->pos4
            ];

            if ($poll->save())
            {
                for ($i=1 ; $i<=4 ; $i++){
                    $question = new PollQuestion();
                    $question->title   = $questions[$i];
                    $question->poll_id = $poll->id;
                    $question->pos     = $i;
                    $question->active  = 1;
                    $question->save();
                }
                Activity::create([
                    'ip_address' => \Request::ip(),
                    'user_agent' => \Request::header('user-agent'),
                    'task' => 'create-poll',
                    'description' => 'ایجاد نظرسنجی',
                    'user_id' => Auth::user()->id,
                    'ids' => $poll->id ,
                ]);

                return redirect('/home/poll')->with('successMsg','نظرسنجی ایجاد شد.');

            }
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function active($pollId)
    {
        if(Rbac::check_access('poll','de-active')) {
            Poll::where('id','>=',0)->update(['active' => 0]);
            Poll::where('id',$pollId)->update(['active' => 1]);

            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'active-poll',
                'description' => 'فعالسازی نظرسنجی',
                'user_id' => Auth::user()->id,
                'ids' => $pollId ,
            ]);

            return redirect('/home/poll');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function deactive()
    {
        if(Rbac::check_access('poll','de-active')) {
            Poll::where('id','>=',0)->update(['active' => 0]);

            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'deactive-poll',
                'description' => 'غیرفعالسازی نظرسنجی',
                'user_id' => Auth::user()->id,
                'ids' => '' ,
            ]);

            return redirect('/home/poll');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function delete($pollId)
    {
        if(Rbac::check_access('poll','delete')) {

            $poll = Poll::where('id',$pollId)->first();
            if ($poll->active)
                return redirect()->back()->with('dangerMsg','این نظرسنجی در حال حاضر فعال می باشد.');

            if ($poll->have_records())
                return redirect()->back()->with('dangerMsg','این نظرسنجی دارای رکورد پاسخ از طرف کاربران می باشد.');

            Poll::destroy($pollId);

            Activity::create([
                'ip_address' => \Request::ip(),
                'user_agent' => \Request::header('user-agent'),
                'task' => 'delete-poll',
                'description' => 'حذف نظرسنجی',
                'user_id' => Auth::user()->id,
                'ids' => $pollId ,
            ]);

            return redirect('/home/poll')->with('successMsg','نظرسنجی حذف شد.');
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');
    }

    public function edit($pollId)
    {
        if(Rbac::check_access('poll','add')) {

            $poll = Poll::where('id',$pollId)->first();
            $questions = PollQuestion::where('poll_id',$poll->id)->get();
            if ($poll->active)
                return redirect()->back()->with('dangerMsg','این نظرسنجی در حال حاضر فعال می باشد.');

            if ($poll->have_records())
                return redirect()->back()->with('dangerMsg','این نظرسنجی دارای رکورد پاسخ از طرف کاربران می باشد.');

            return view('cms.poll.edit',[
                'poll'      => $poll,
                'questions' => $questions
            ]);
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');
    }

    public function update(Request $request)
    {
        if(Rbac::check_access('poll','add')) {

            $validator = Validator::make($request->all(), [
                'title'            => 'required|string|max:300',
                'pos1'             => 'required',
                'pos2'             => 'required',
                'pos3'             => 'required',
                'pos4'             => 'required',
            ],[
                'title.required'    => 'متن را وارد نمایید.',
                'title.string'      => 'فرمت متن اشتباه می باشد.',
                'title.max'         => 'متن می بایست حداکثر 300 کاراکتر باشد.',
                'pos1.required'     => 'گزینه اول را وارد نمایید.',
                'pos2.required'     => 'گزینه دوم را وارد نمایید.',
                'pos3.required'     => 'گزینه سوم را وارد نمایید.',
                'pos4.required'     => 'گزینه چهارم را وارد نمایید.',

            ]);
            if ($validator->fails())
                return redirect()->back()->withInput()->withErrors($validator->errors());

            $poll = Poll::where('id',$request->pollId)->first();

            $poll->title   = $request->title;
            $poll->user_id   = Auth::user()->id;
            $poll->active  = 0;
            $questions = [
                '1' => $request->pos1,
                '2' => $request->pos2,
                '3' => $request->pos3,
                '4' => $request->pos4
            ];

            if ($poll->update())
            {
                for ($i=1 ; $i<=4 ; $i++){
                    $question = PollQuestion::where('poll_id',$poll->id)->where('pos',$i)->first();
                    $question->title   = $questions[$i];
                    $question->update();
                }

                Activity::create([
                    'ip_address' => \Request::ip(),
                    'user_agent' => \Request::header('user-agent'),
                    'task' => 'edit-poll',
                    'description' => 'ویرایش نظرسنجی',
                    'user_id' => Auth::user()->id,
                    'ids' => $poll->id ,
                ]);

                return redirect('/home/poll')->with('successMsg','نظرسنجی بروزرسانی شد.');

            }
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این بخش امکان پذیر نمی باشد');
    }

    public function current_poll()
    {
        $poll = Poll::where('active', 1)->first();

        if (!isset($poll->id)) {
            $html = '<div class="text-muted text-center">نظرسنجی فعالی وجود ندارد.</div>';
            return response()->json([
                'status'    => 200,
                'res'       => $html,

            ]);
        }

        $questions = PollQuestion::where('poll_id', $poll->id)->orderBy('pos' ,'asc')->get();
        $total = 0;
        $html = "
                
                   
                    <ul class='widget w-pool'>
                                <li>
                                    <p>$poll->title</p>
                                </li>
                ";

        foreach ($questions as $question){
            $total = $total + PollAnswer::where('question_id' ,$question->id)->count();
        }
        foreach ($questions as $question){

            $counter = PollAnswer::where('question_id' ,$question->id)->count();
            if ($total == 0)
                $total = 1;
            $avg = number_format(($counter/$total)*100 , 1) . '%' ;

            $html .="
                    
                                <li>
                                    <div class='skills-item'>
                                        <div class='skills-item-info'>
									<span class='skills-item-title text-right'>
										<span class='radio'>
											<label>
												<input type='radio' name='pollOption' value='$question->id'><span class='circle'></span><span class='check'></span>
												$question->title
											</label>
										</span>
									</span>
                                            <span class='skills-item-count'>
										<span class='count-animate' data-speed='1000' data-refresh-interval='50' data-to='$avg' data-from='0'></span>
										<span class='units'>$avg</span>
									</span>
                                        </div>
                                        <div class='skills-item-meter'>
                                            <span class='skills-item-meter-active bg-primary skills-animate' style='width: $avg; opacity: 1;'></span>
                                        </div>
                                        <div class='counter-friends'>$counter رأی</div>
                                    </div>
                                </li>
                           
                    ";
        }

        $html .="
                    </ul>
                    <div id='msg' class='col-sm-12 p-0'></div>
                    <div class='col-sm-12 p-0'>
                        <button class='btn btn-info  w-100 vote' >ثبت نظر</button>
                    </div>
                
                ";

        return response()->json([
            'status'    => 200,
            'res'       => $html,

        ]);

    }

    public function vote(Request $request)
    {
        $check = PollQuestion::find($request->radioValue);

        $poll = Poll::where('active', 1)->first();
        $questions = PollQuestion::where('poll_id', $poll->id)->orderBy('pos' ,'asc')->get();

        if ($check->poll_id === $poll->id){
            foreach ($questions as $question){
                $ans = PollAnswer::where('question_id',$question->id)->where('user_id',Auth::user()->id)->first();
                if ($ans){
                    $ans->question_id = $request->radioValue;
                    if ($ans->update()){
                        return response()->json([
                            'status' => 200,
                            'res' => "<div class='alert alert-success font12 w-100 text-center'>رای شما بروزرسانی شد.</div>",
                        ]);
                    }
                }
            }

            $vote = new PollAnswer();
            $vote->user_id = Auth::user()->id;
            $vote->question_id = $request->radioValue;

            if ($vote->save()) {
                return response()->json([
                    'status' => 200,
                    'res' => "<div class='alert alert-success font12 w-100 text-center'>رای شما ثبت شد.</div>",

                ]);
            }
        }
        return response()->json([
            'status' => 200,
            'res' => "<div class='alert alert-success font12 w-100 text-center'>شناسه سوال معتبر نمی باشد.</div>",

        ]);

    }

    public function records($pollId)
    {
        if(Rbac::check_access('poll','index')) {

            $poll = Poll::where('id',$pollId)->first();
            $questions = PollQuestion::where('poll_id',$poll->id)->get();

            if ($poll->have_records()) {

                Activity::create([
                    'ip_address' => \Request::ip(),
                    'user_agent' => \Request::header('user-agent'),
                    'task' => 'visit-poll',
                    'description' => 'مشاهده آمار نظرسنجی',
                    'user_id' => Auth::user()->id,
                    'ids' => $pollId ,
                ]);

                return view('cms.poll.records', [
                    'poll' => $poll,
                    'questions' => $questions
                ]);

            }
        }
        return redirect()->back()->with('dangerMsg','دسترسی شما به این قابلیت امکان پذیر نمی باشد');
    }

    public function test()
    {
        return view('cms.poll.test');
    }
}
