<?php

namespace App\Http\Controllers\Cms;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FingerPrintController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','Filter']);
    }

    public function enroll_index()
    {
        return view('cms.fingerprint.enroll');
    }

    public function create_session(Request $request)
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => "https://localhost:8083/api/createSessionID?dummy=".mt_rand(1,100000),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_FRESH_CONNECT  => true,
            //CURLOPT_FOLLOWLOCATION => true,
        ));
        $res = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        curl_close($curl);

        if(isset($res->sessionId)) {
            session()->put('bio_session_id', $res->sessionId);
            setcookie('username',$res->sessionId,time() + 86400,"/");
        }
        return response()->json(['status' => 200,'res' => $res]);
    }

    public function init()
    {
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => "https://localhost:8083/api/initDevice?dummy=".mt_rand(1,11111),
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            //CURLOPT_FRESH_CONNECT  => true,
            //CURLOPT_FOLLOWLOCATION => true,
        ));
        $res = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        curl_close($curl);

        $view = "";
        if(isset($res->retString)) {
            if($res->retString == "Success") {
                $deviceStatus = "<i class='fa fa-circle fa-lg text-success'></i>";
                $deviceName = $res->ScannerInfos[0]->ScannerName;
                $view .= "<p>وضعیت دستگاه: <span>$deviceStatus</span></p>";
                $view .= "<p>نام دستگاه:<span>$deviceName</span></p>";
            }
            else {
                $deviceStatus = "<i class='fa fa-circle fa-lg text-danger'></i>";
                $view .= "<p>وضعیت دستگاه: <span>$deviceStatus</span></p>";
                $view .= "<p>نام دستگاه:<span>-</span></p>";
            }
            session()->put('bio_device_handle',$res->ScannerInfos[0]->DeviceHandle);
        }
        else {
            $deviceStatus = "<i class='fa fa-circle fa-lg text-danger'></i>";
            $view .= "<p>وضعیت دستگاه: <span>$deviceStatus</span></p>";
            $view .= "<p>نام دستگاه:<span>-</span></p>";
        }

        return response()->json(['status' => 200,'res' => $view,'result' => $res]);
    }

    public function enroll(Request $request)
    {
        $v = Validator::make($request->all(),[
            'credential'  => 'required|string',
            'finger_name' => 'required|in:1,2,3,4,5,6,7,8,9,10'
        ]);
        if($v->fails())
            return response()->json(['status' => 101,'res' => 'مشخصات فرم ثبت اثر انگشت نامعتبر است']);
        $credential = $request->credential;
        $fingerName = $request->finger_name;
        $user = DB::table('users')
            ->where('username',$credential)
            ->orWhere('std_no',$credential)
            ->orWhere('national_code',$credential)
            ->first();
        if(!$user)
            return response()->json(['status' => 101,'res' => 'مشخصات کاربر پیدا نشد']);

        $nationalCode = $user->national_code;
        if (!is_numeric($nationalCode))
            return response()->json(['status' => 101,'res' => 'کد ملی کاربر نامعتبر است']);

        $curl       = curl_init();
        $sessionId  = session('bio_session_id');
        $pageId     = 0;
        $dummy      = mt_rand(1,111111111111111111);
        $sHandle    = session('bio_device_handle');
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL            => "https://localhost:8083/db/enroll?dummy=$dummy".
                "&sHandle=$sHandle&userID=$nationalCode&id=$pageId&userSerialNo=0&encrypt=0&extractEx=1&qualityLevel=1",
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_COOKIESESSION  => true,
            CURLOPT_COOKIE         => "sessionId=$sessionId" ,
        ));
        $res = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        if(isset($res->retString) && $res->retString == "Success") {
            $curl = curl_init();
            $dummy = mt_rand(1, 111111111111111111);
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_URL => "https://localhost:8083/db/getTemplateData?dummy=$dummy" .
                    "&sHandle=$sHandle&id=0&userSerialNo=0&encrypt=0&extractEx=1",
                CURLOPT_SSL_VERIFYPEER  => false,
                CURLOPT_CONNECTTIMEOUT  => 10,
                CURLOPT_TIMEOUT         => 10,
                CURLOPT_COOKIESESSION   => true,
                CURLOPT_COOKIE          => "sessionId=$sessionId",
            ));
            $res = json_decode(curl_exec($curl));
            $err = curl_error($curl);
            $info = curl_getinfo($curl);
            curl_close($curl);

            if(isset($res->retString) && $res->retString == "Success") {
                $template = $res->templateBase64;

                $curl = curl_init();
                $dummy = mt_rand(1, 111111111111111111);
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_URL => "https://localhost:8083/api/captureSingle?dummy=$dummy" .
                        "&sHandle=$sHandle&id=$pageId&resetTimer=30000",
                    CURLOPT_SSL_VERIFYPEER  => false,
                    CURLOPT_CONNECTTIMEOUT  => 10,
                    CURLOPT_TIMEOUT         => 10,
                    CURLOPT_COOKIESESSION   => true,
                    CURLOPT_COOKIE          => "sessionId=$sessionId",
                ));
                $res = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);

                $curl = curl_init();
                $dummy = mt_rand(1, 111111111111111111);
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_URL => "https://localhost:8083/api/getImageData?dummy=$dummy" .
                        "&sHandle=$sHandle&id=$pageId&fileType=1&compressionRatio=0.1",
                    CURLOPT_SSL_VERIFYPEER  => false,
                    CURLOPT_CONNECTTIMEOUT  => 10,
                    CURLOPT_TIMEOUT         => 10,
                    CURLOPT_COOKIESESSION   => true,
                    CURLOPT_COOKIE          => "sessionId=$sessionId",
                ));
                $res = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                $info = curl_getinfo($curl);
                curl_close($curl);

                if(isset($res->retString) && $res->retString == "Success") {
                    $imageBase64 = $res->imageBase64;
                    $bin = base64_decode($imageBase64);
                    $im = imageCreateFromString($bin);
                    $img_file = 'filename.png';
                    imagepng($im,public_path('/files/images/').$img_file,0);
                    return response()->json(['status' => 200,'res' => $res,'result' => '3','img' => '/files/images/'.$img_file]);
                }

                return response()->json(['status' => 200,'res' => $res,'result' => '3']);
            }

            return response()->json(['status' => 200,'res' => $res,'result' => '2']);
        }
        return response()->json(['status' => 200,'res' => $res,'result' => '1']);
    }

    public function auto_capture()
    {
        $curl = curl_init();
        $sessionId  = session('bio_session_id');
        $pageId     = 0;
        $dummy      = mt_rand(1,1111);
        $sHandle    = session('bio_device_handle');
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => "https://localhost:8083/db/create?dummy=$dummy" .
                "&sHandle=$sHandle&id=$pageId&extractEx=1&qualityLevel=1",
            CURLOPT_SSL_VERIFYPEER  => false,
            CURLOPT_COOKIESESSION   => true,
            CURLOPT_COOKIE          => "sessionId=$sessionId",
        ));
        $res = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        $info = curl_getinfo($curl);
        curl_close($curl);

        return response()->json(['status' => 200,'res' => $res]);
    }
}
