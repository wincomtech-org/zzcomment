<?php
$alipay=array(
    //APPID 2017111409930176
    //partner 2088102733706456
    "partner"=>"2088102733706456",//合作身份者ID
    "seller_id"=>"2088102733706456",//收款支付宝账号 meifyulai@yahoo.cn
    "key"=>"7razwlr26uaoh578qy2vk8lnwj2lj5gm",//MD5密钥
    "notify_url"=>"http://127.0.0.1/zzcomment/alipay/notify0.php",//服务器异步通知页面路径
    "return_url"=>"http://127.0.0.1/zzcomment/alipay/return0.php",//页面跳转同步通知页面路径
    "sign_type"=>strtoupper('MD5'),//签名方式
    "input_charset"=>strtolower('utf-8'),//字符编码格式 目前支持 gbk 或 utf-8
    "cacert"=>getcwd().'/cacert.pem',//ca证书路径地址
    "transport"=>"http",//访问模式
    "payment_type"=>"1",//支付类型 ，无需修改
    "service"=>"create_direct_pay_by_user",//产品类型，无需修改
    "anti_phishing_key"=>"",//
    "exter_invoke_ip"=>"",//
);
