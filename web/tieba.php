<?php

require_once("tieBaSign.php");
require_once("pass.php");


$link = new mysqli($host, $user, $password, $dbName);

$app = new tieba_sing_with_log();
if ($app->init("/var/log1/daily_log.txt")) {
    die(date("Y-m-d H:i:s", time()) . " 贴吧签到初始化失败");
}

if ($link->connect_error) {
    $app->ex_log("连接失败：\n" . $link->connect_error);
    die();
}

$sql = "select * from `testTable`";
$res = $link->query($sql);
$datas = $res->fetch_all();
//truncate table testTable;
foreach ($datas as $data) {
    $app->work($data[0], $data[1]);
}

$app->close();