<?php

class tieBaSign
{
    private string $warning_msg = "警告 签到失败: ";
    private string $error_msg = "错误 签到错误: ";
    private string $log_msg = "正常 签到成功: ";

    public function doSign($BDUSS): array
    {
        $result = array();
        $result[0] = 0;
        $result[1] = "";

        $temp = $this->getTbs($BDUSS);
        if ($temp[0] == "null") {
            $result[0] = 1;
            $result[1] = $this->error_msg . "无法登录,buss过期\n";
            return $result;
        }

        $favList = $this->getfavList($BDUSS);
        if (count($favList) == 0) {
            $result[0] = 2;
            $result[1] = $this->error_msg . "无法获取关注列表\n";
            return $result;
        }

        $counter = 0;
        foreach ($favList as $info) {
            if (isset($info["id"]) && isset($info["name"])) {
                $this->tieBaSignIn($temp[0], $temp[1], $info["id"], $info["name"], $BDUSS);
            } else {
                if (isset($info["name"])) {
                    $result[1] .= $this->warning_msg . $info["name"] . " 吧无法签到\n";
                } else {
                    $result[1] .= $this->warning_msg . " 百度抽风\n";
                }
            }
            $counter++;
        }
        $result[1] .= $this->log_msg . strval($counter) . "个吧签到完成\n";
        return $result;
    }

    /**
     * help method
     */
//encode
    /**
     * get tbs
     */
    private function getTbs($BDUSS)
    {
        $BAIDUID = array();
        $url = "http://tieba.baidu.com/dc/common/tbs";
        $ch = curl_init();
        $headers = array(
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36",
            "Cookie: BDUSS=" . $BDUSS,
            "Accept: application/json",
        );
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        $res = curl_exec($ch);
        curl_close($ch);

        preg_match("/Set-Cookie: BAIDUID=([A-Za-z0-9_\:\=]*);\s/", $res, $BAIDUID);

        $BAIDUID = $BAIDUID[1] ?? "";

        preg_match("/{.*}/", $res, $res);
        $tbs = json_decode($res[0], true)["tbs"] ?? "null";
        return [$tbs, $BAIDUID];
    }

    /**
     * fav list
     */
    private function getfavList($BDUSS)
    {
        $url = "http://c.tieba.baidu.com/c/f/forum/like";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        $headers = array(
            "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/76.0.3809.100 Safari/537.36",
            "Accept: */*",
            "Connection: keep-alive",
            "Content-Type: application/x-www-form-urlencoded",
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $data = array(
            'BDUSS' => $BDUSS,
            '_client_id' => 'wappc_1534235498291_488',
            '_client_type' => '2',
            '_client_version' => '9.7.8.0',
            '_phone_imei' => '000000000000000',
            'from' => '1008621y',
            'model' => 'MI+5',
            'net_type' => '1',
            'page_no' => (string)1,
            'page_size' => '500',
            'timestamp' => 1654163216,//time(),
            'vcode_tag' => '11',
        );

        $data["sign"] = $this->BaiduEncode($data);
        $data["model"] = "MI%2B5";

        $encodeData = "";
        foreach ($data as $key => $value) {
            $encodeData .= $key . "=" . $value . "&";
        }
        unset($key, $value);
        $encodeData = rtrim($encodeData, "&");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodeData);
        unset($encodeData);

        $resp = curl_exec($ch);
        $favList = json_decode($resp, true);
        if (isset($favList["forum_list"]) && isset($favList["forum_list"]["non-gconforum"])) {
            $favList = $favList["forum_list"]["non-gconforum"];
        } else {
            $favList = array();
        }
        return $favList;
    }


    private function BaiduEncode($data): string
    {
        $encodeData = "";
        foreach ($data as $key => $value) {
            $encodeData .= $key . "=" . $value;
        }
        unset($key, $value);
        $encodeData .= "tiebaclient!!!";
        $rs = Strtoupper(md5($encodeData));
        return $rs;
    }

    /**
     * sign
     */
    private function tieBaSignIn($tbs, $BAIDUID, $fid, $kw, $BDUSS)
    {
        $url = "http://c.tieba.baidu.com/c/c/forum/sign";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $headers = array(
            "User-Agent: python-requests/2.27.1",
            "Accept: */*",
            "Connection: keep-alive",
            "Cookie: BAIDUID=" . $BAIDUID,
            "Content-Type: application/x-www-form-urlencoded",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $timestp = ((string)time());

        $inputstr = "BDUSS=" . $BDUSS . "_client_type=2_client_version=9.7.8.0_phone_imei=000000000000000fid=" . $fid . "kw=" .
            $kw . "model=MI+5net_type=1tbs=" . $tbs . "timestamp=" . $timestp . "tiebaclient!!!";

        $data = "_client_type=2&_client_version=9.7.8.0&_phone_imei=000000000000000&model=MI%2B5&net_type=1&BDUSS=" .
            $BDUSS . "&fid=" . $fid . "&kw=" . Urlencode($kw) . "&tbs=" . $tbs .
            "&timestamp=" . $timestp . "&sign=" . Strtoupper(md5($inputstr));

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $resp = curl_exec($curl);
        curl_close($curl);
        //var_dump($resp);
    }

}

class tieba_sing_with_log
{
    private $app;
    private $myfile;

    private bool $writeLog;

    public function init($log_file_name): bool
    {
        $state = false;
        $this->app = new tieBaSign();

        if ($log_file_name == "") {
            $this->writeLog = false;
            return false;
        } else {
            $this->writeLog = true;
        }

        $this->myfile = fopen($log_file_name, "a") or $state = true;
        if ($state) {
            return true;
        } else {
            fwrite($this->myfile, "\n\n日期: " . date("Y-m-d H:i:s", time()) . "\n初始化成功\n");
            return false;
        }
    }

    public function work($name, $b): array
    {
        $start_time = microtime(true);
        $t = $this->app->doSign($b);
        $time_use = round((microtime(true) - $start_time), 3);
        if ($this->writeLog) {
            fwrite($this->myfile, "\n" . $name . "\n");
            fwrite($this->myfile, $t[1]);
            fwrite($this->myfile, "耗时: " . $time_use . "秒\n");
        }
        $t[] = $time_use;
        return $t;
    }

    public function ex_log($data)
    {
        if ($this->writeLog) {
            fwrite($this->myfile, $data . "\n");
        }
    }

    public function close()
    {
        if ($this->writeLog) {
            fclose($this->myfile);
        }
    }
}