<?php
//往“”里填BDUSS就行，有几个号填几个，不够就加
$BDUSSs = array("","","",);

/**
 * get tbs
 */
function getTbs($BDUSS)
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
    $BAIDUID = $BAIDUID[1];

    preg_match("/{.*}/", $res, $res);
    $tbs = json_decode($res[0], true)["tbs"] ?? "null";
    if ($tbs == "null") {
        exit("寄，卡第一步tbs都没获取到，你BDUSS估摸整错了或者你号无了");
    }
    return [$tbs, $BAIDUID];
}

/**
 * help method
 */
//encode
function BaiduEncode($data): string
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
 * fav list
 */
function getfavList($BDUSS)
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
        'page_size' => '200',
        'timestamp' => 1654163216,//time(),
        'vcode_tag' => '11',
    );

    $data["sign"] = BaiduEncode($data);
    $data["model"] = "MI%2B5";//shit, it will not auto change

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
        exit("寄,贴吧列表没获取到");
    }
    return $favList;
}

/**
 * sign
 */
function tieBaSignIn($tbs, $BAIDUID, $fid, $kw, $BDUSS)
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

/**
 * main
 */
foreach ($BDUSSs as $BDUSS) {
    if ($BDUSS==="")
        continue;
    $temp = getTbs($BDUSS);
    $favList = getfavList($BDUSS);
    foreach ($favList as $info) {
        if (isset($info["id"]) && isset($info["name"])) {
            tieBaSignIn($temp[0], $temp[1], $info["id"], $info["name"], $BDUSS);
        } else {
            if (isset($info["name"])) {
                print "have error on sign " . $info["name"];
            } else {
                print "unKnown error";
            }
        }
    }
}

