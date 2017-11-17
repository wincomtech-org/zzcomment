<?php
header("content-type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
$config=(require_once dirname(dirname(__FILE__)).'/data/conf/db.php');
$alipay_config=$config['ALIPAY_CONFIG'];
$mysqli=new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME'],$config['DB_PORT']);
$mysqli->set_charset('utf8');
//$index="http://zzhuachuang.com/user/order/index";
$index="http://www.zypjw.cn/zzcomment/User/Info/index";
$log='alipay.log';
$line=PHP_EOL;
$time=time(); 
$date=date('Y-m-d H:i:s');
error_log($date.'alipay-config'.$line,3,$log);
 

