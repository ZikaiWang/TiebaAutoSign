<?php

function return_value($data)
{
    header('Content-Type:application/json; charset=utf-8');
    die(json_encode($data));
}

function h_store($nname, $bduss): array
{
    require_once("pass.php");
    $link = new mysqli($host, $user, $password, $dbName);
    if ($link->connect_error) {
        return array("code" => "501", "str" => "艹, 数据库挂了, 有老六");
    }
    $sql = "select count(*) s from `testTable` where `data_id`='".$nname."'";
    $res = $link->query($sql);
    $datas = $res->fetch_all();
    if ($datas[0][0]>0){
        $sql = "update `testTable` set `data` = '".$bduss."' where `data_id` = '".$nname."';";
        $link->query($sql);
        return array("code" => "2001", "str" => "已更新");
    }else{
        //添加新行
        $sql = "INSERT INTO testTable ( `data_id`, `data` ) VALUES ( '" . $nname . "', '" . $bduss . "' )";
        $link->query($sql);
        return array("code" => "2001", "str" => "已储存");
    }
}

function h_sign($bduss): array
{
    require_once("tieBaSign.php");
    $sing_with_log = new tieba_sing_with_log();
    if ($sing_with_log->init("")) {
        return array("code" => "502", "str" => "贴吧签到初始化失败,服务失效了");
    }

    $t = $sing_with_log->work("游客", $bduss);
    if ($t[0] == 0) {
        return array("code" => "2002", "str" => $t[2]);
    }
    $sing_with_log->close();
    return array("code" => "504", "str" => $t[1]);

}

function call_function($name, $bduss, $function_num)
{
    switch (strval($function_num)) {

        //只储存
        case "1":
            return h_store($name, $bduss);
            break;
        //只签到
        case "2":
            return h_sign($bduss);
            break;
        //签到并储存
        case "3":
            $result = h_sign($bduss);
            if ($result['code'] == '2002') {
                h_store($name, $bduss);
            }
            return $result;
            break;
        default:
            return array("code" => "503", "str" => "你在调用啥?");
    }
}


//return_value($t);

$t = array();
$t["code"] = 600;
$t["str"] = "内部错误";

/*
$_REQUEST["nname"]="q";
$_REQUEST["bduss"]='w';
$_REQUEST["function_num"]='1';
*/

switch (((isset($_REQUEST["nname"]) && $_REQUEST["nname"] != "") ? 1 : 0) + ((isset($_REQUEST["bduss"]) && $_REQUEST["bduss"] != "") ? 2 : 0) + ((isset($_REQUEST["function_num"]) && $_REQUEST["function_num"] != "") ? 4 : 0)) {
    case 0:
        $t["code"] = 0;
        $t["str"] = "missing name, BDUSS, function_num";
        break;
    case 1:
        $t["code"] = 1;
        $t["str"] = "missing BDUSS, function_num";
        break;
    case 2:
        $t["code"] = 2;
        $t["str"] = "missing name, function_num";
        break;
    case 3:
        $t["code"] = 3;
        $t["str"] = "missing function_num";
        break;
    case 4:
        $t["code"] = 4;
        $t["str"] = "missing name, BDUSS";
        break;
    case 5:
        $t["code"] = 5;
        $t["str"] = "missing BDUSS";
        break;
    case 6:
        $t["code"] = 6;
        $t["str"] = "missing name";
        break;
    case 7:
        $t = call_function($_REQUEST["nname"], $_REQUEST["bduss"], $_REQUEST["function_num"]);
        break;
    default:
        $t["code"] = 500;
        $t["str"] = "unknown error";
}

return_value($t);