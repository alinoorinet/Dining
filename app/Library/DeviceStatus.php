<?php


namespace App\Library;


use App\Rest;

class DeviceStatus
{
    public function ping()
    {
        $selfs = Rest::where('active',1)->get();
        foreach ($selfs as $self) {
            $ips = $self->info;
            foreach ($ips as $ip) {
                $curl = curl_init();
                curl_setopt_array($curl, array(
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_URL            => "http://openport.ir/ping/@IlamD2019@/$ip->ip/",
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_CONNECTTIMEOUT => 10,
                    CURLOPT_TIMEOUT        => 10,
                    CURLOPT_FOLLOWLOCATION => true,
                ));
                $res = json_decode(curl_exec($curl));
                $err = curl_error($curl);
                curl_close($curl);

                $status = "<i class='fa fa-times text-danger animated infinite pulse delay-2s fa-lg'></i>";
                $rtt    = '-';
                if(isset($res->rtt_avg) && $res->rtt_avg != null) {
                    $status = "<i class='fa fa-check text-success fa-lg'></i>";
                    $rtt    = $res->rtt_avg;
                }
                $ip->avg_rtt = $rtt;
                $ip->status  = $status;
                $ip->updated_at = date('Y-m-d H:i:s');
                $ip->update();
            }
        }
    }
}
