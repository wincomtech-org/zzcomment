<?php
header("content-type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
$config=(require_once dirname(dirname(__FILE__)).'/data/conf/db.php');
$alipay_config=$config['ALIPAY_CONFIG'];
$mysqli=new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME'],$config['DB_PORT']);
$mysqli->set_charset('utf8');
//$index="http://zzhuachuang.com/user/order/index";
$index="http://www.zypjw.cn/User/Info/index";
$log='alipay.log';
$line=PHP_EOL;
$time=time(); 
 
error_log(date('Y-m-d H:i:s').'alipay-config'.$line,3,$log);
 

