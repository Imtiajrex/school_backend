<?php

namespace App\Jobs;

use App\Models\SMSAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class SendSMSJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $sms_data;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $sms_data)
    {
        //

        $this->sms_data = $sms_data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (count($this->sms_data) > 0) {
            $sms_account = SMSAccount::find(1);
            if ($sms_account->balance > 0) {
                $numbers = $this->sms_data;
                $i = 0;
                $sms_available = (int)($sms_account->balance / $sms_account->rate);
                $sms_d = [];
                foreach ($numbers as $num) {
                    if ($i >= $sms_available) break;
                    else array_push($sms_d, $num);
                    $i++;
                }

                $token = env("SMS_TOKEN");

                $url = env("SMS_URL") . "/api2.php?json";

                if (count($sms_d) > 0) {
                    $data = array(
                        "smsdata" => json_encode($sms_d),
                        'token' => "$token"
                    ); // Add parameters in key value

                    $ch = curl_init(); // Initialize cURL
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_ENCODING, '');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    $smsresult = curl_exec($ch);
                    $smsresult = json_decode($smsresult);

                    $sent = 0;
                    $sent_numbers = "";
                    $failed_numbers = "";
                    if ($smsresult != null) {
                        foreach ($smsresult as $res) {
                            if ($res->status == "SENT") {
                                $sent++;
                                $sent_numbers = $sent_numbers . " " . $res->to;
                            } else {
                                $failed_numbers = $failed_numbers . " " . $res->to;
                            }
                        }
                        if ($sent > 0) {
                            $sms_account->balance = $sms_account->balance - $sent * $sms_account->rate;
                            $sms_account->total_sent_sms = $sms_account->total_sent_sms + $sent;
                            $sms_account->save();
                        }
                    }
                }
            }
        }
    }
}
