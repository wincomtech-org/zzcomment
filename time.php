<?php
date_default_timezone_set('PRC');
set_time_limit(600);
$url="http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
/*
 function
 */
error_log(date('Y-m-d H:i:s')."\r\n",'3','1.log');


    
    //$today=date('Y-m-d');
    //$time=strtotime($today)+3600*24+2-time();
   $time=3600;
    sleep($time);
    file_get_contents($url);

exit;
?>