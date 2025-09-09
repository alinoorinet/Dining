<?php

namespace app\Library;


use App\Attack;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class Filtering
{
    public $input;
    public $beCheck;
    public $options;
    public $controller;
    public $action;

    public $requestHasMalware   = false;
    public $userHaveBeenBlocked = false;

    public $optionException = [
        'LoginController' => [
            'login'    => ['xss','badWord'],
        ],
        'ReservationController' => [
            'callback' => ['xss','badWord'],
        ],
    ];

    public function auto_filter(Request $request,$beCheck = null,$options = null)
    {
        $controllerAction = Route::currentRouteAction();
        if(isset(explode('@',$controllerAction)[0])) {
            $ep1 = explode('@',$controllerAction)[0];
            $ep2 = explode('\\',$ep1);
            $this->controller = end($ep2);
            $this->action     = isset(explode('@',$controllerAction)[1])?explode('@',$controllerAction)[1]:null;
        }
        if($options)
            $this->options = $options;
        elseif($this->controller && $this->action)
            $this->options = isset($this->optionException[$this->controller][$this->action]) ? $this->optionException[$this->controller][$this->action] : ['badCh', 'xss', 'badWord'];
        else
            $this->options = ['badCh','xss','badWord'];

        $this->beCheck = $beCheck == null?['_token','g-recaptcha-response']:$beCheck;
        switch ($request->method()) {
            case 'GET':
                $path = $request->path();
                $path = $this->stripTags($path);
                $path = $this->stripWord($path);
                $path = $this->stripBadCh($path);
                if($this->userHaveBeenBlocked)
                    return 2;
                if($path != $request->path())
                    return 1;
                return 0;
                break;
            case 'POST':
                if($request->wantsJson()) {
                    $type = $this->data_type($request->json()->all());
                    $request = $this->switch_to_process($type, $request->json()->all());
                }
                else {
                    $type    = $this->data_type($request->all());
                    $request = $this->switch_to_process($type, $request->all());
                }
                if($this->userHaveBeenBlocked)
                    return 2;
                return $request;
        }
        return $request;
    }

    private function data_type($input)
    {
        if(is_array($input))
            return "isArr";
        elseif (is_object($input))
            return "isObj";
        elseif (is_string($input))
            return "isStr";
        elseif (is_file($input))
            return "isFile";
        elseif (is_dir($input))
            return "isDir";
        else
            return "unknown";
    }

    private function switch_to_process($type,$request)
    {
        switch ($type) {
            case "isArr":
                $input = $this->array_request_chunking($request);
                break;
            case "isObj":
                $input = $this->object_request_chunking($request);
                break;
            case "isStr":
                break;
            case "isFile":
                break;
            case "isDir":
                //
        }
        return $input;
    }

    private function array_request_chunking($input = array())
    {
        $temp = [];
        foreach ($input as $key=>$value) {
            if (!in_array($key,$this->beCheck,true)) {
                if ($this->data_type($value) == 'isArr') {
                    $after = $this->array_request_chunking($value);
                    $temp[$key] = $after;
                }
                elseif ($this->data_type($value) == 'isObj') {
                    $after = $this->object_request_chunking($value);
                    $temp[$key] = $after;
                }
                elseif ($this->data_type($value) == 'isFile') {
                    $temp[$key] = $value;
                }
                else {
                    $stripTags    = $this->stripTags($value);
                    $stripWord    = $this->stripWord($stripTags);
                    $stripBadCh   = $this->stripBadCh($stripWord);
                    $temp[$key]   = $stripBadCh;
                }
            }
            else {
                $temp[$key] = $value;
            }
        }
        return $temp;
    }

    private function object_request_chunking($input)
    {
        $temp = [];
        foreach ($input as $key=>$value) {
            if (!in_array($key,$this->beCheck,true)) {
                if ($this->data_type($value) == 'isArr') {
                    $after = $this->array_request_chunking($value);
                    $temp[$key] = $after;
                }
                elseif ($this->data_type($value) == 'isObj') {
                    $after = $this->object_request_chunking($value);
                    $temp[$key] = $after;
                }
                elseif ($this->data_type($value) == 'isFile') {
                    $temp[$key] = $value;
                }
                elseif ($this->data_type($value) == 'isDir') {
                    $temp[$key] = $value;
                }
                else {
                    $stripTags  = $this->stripTags($value);
                    $stripWord  = $this->stripWord($stripTags);
                    $stripBadCh = $this->stripBadCh($stripWord);
                    $temp[$key] = $stripBadCh;
                }
            }
            else {
                $temp[$key] = $value;
            }
        }
        return $temp;
    }

    private function stripTags($input)
    {
        $input = trim($input);
        if(in_array('xss',$this->options,true)) {
            $tmp = strip_tags($input);
            if ($tmp !== $input) {
                $this->requestHasMalware = true;
                $this->storeMalware($input);
                $this->sendSMS('xss');
            }
            return $tmp;
        }
        return $input;
    }

    private function stripWord($input)
    {
        if(in_array('badWord',$this->options,true)) {
            $hasBadWord = false;
            $tmp = $input;
            $badWords = [
                'AND',
                'OR',
                'document.',
                'window.',
                'alert(',
                'cookie',
                'COOKIE',
                'SELECT',
                'DELETE',
                'UPDATE',
                'session',
                'SESSION',
                'GET',
                'POST',
                'PUT',
                'script',
                'SCRIPT',
                'style',
                'null',
            ];
            foreach ($badWords as $badWord) {
                $tmp = trim($tmp);
                if (strpos($tmp,$badWord) !== false) {
                    $hasBadWord = true;
                    $tmp = str_replace($badWord, '', $tmp);
                }
            }
            if ($hasBadWord) {
                $this->requestHasMalware = true;
                $this->storeMalware($input);
                $this->sendSMS('badWord');
            }
            return $tmp;
        }
        return $input;
    }

    private function stripBadCh($input)
    {
        if(in_array('badCh',$this->options,true)) {
            $hasBadCh = false;
            $tmp = $input;
            $badChs = '*&%!^%><~|()' . '"' . "'" . '`';
            for ($i = 0; $i < strlen($tmp); $i++) {
                if (strpos($badChs, $tmp[$i]) !== false) {
                    $hasBadCh = true;
                    $tmp = str_replace($tmp[$i], '', $tmp);
                    $i--;
                }
            }
            if ($hasBadCh) {
                $this->requestHasMalware = true;
                $this->storeMalware($input);
                $this->sendSMS('badCh');
            }
            return $tmp;
        }
        return $input;
    }

    private function storeMalware($input)
    {
        Attack::create([
            'attack'     => $input,
            'controller' => $this->controller != null?$this->controller : '',
            'action'     => $this->action     != null?$this->action     : '',
            'fullpath'   => \Request::fullUrl(),
            'ip_address' => \Request::ip(),
            'user_agent' => \Request::header('user-agent'),
            'user_id'    => isset(Auth::user()->id)?Auth::user()->id:null,
        ]);
        $this->checkForBlock();
    }

    private function sendSMS($attack_type)
    {
        $token = $attack_type;
        $token2 = \Request::url();
        /*$curl = curl_init();
        $url = "https://api.kavenegar.com/v1/30416E4238496C4633362B43645371456B62704266773D3D/verify/lookup.json?receptor=09185501060&template=wif&token=".$token."&token2=".$token2."&type=sms";
        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_URL => $url,
            CURLOPT_SSL_VERIFYPEER => False,
        ));
        curl_exec($curl);
        curl_close($curl);*/
    }

    private function checkForBlock()
    {
        if(Auth::check()) {
            $attackCount = Attack::where('user_id',Auth::user()->id)->count();
            if($attackCount > 5) {
                User::where('id', Auth::user()->id)->update(['active' => 0]);
                $this->userHaveBeenBlocked = true;
            }
        }
        else {
            $attackCount = Attack::where('ip_address',\Request::ip())
                ->where('controller',$this->controller)
                ->where('action',$this->action)
                ->where('user_agent',\Request::header('user-agent'))
                ->count();
            if($attackCount > 5)
                $this->userHaveBeenBlocked = true;
        }
    }
}
