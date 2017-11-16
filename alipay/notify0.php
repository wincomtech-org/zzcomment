<?php
require_once("config.php");
require_once("alipay_notify.class.php");
logResult('alipay-notify-start');
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
if($verify_result) {//验证成功
    $notify_data = $alipayNotify->decrypt($_POST['notify_data']);
    $doc = new DOMDocument();
    $doc->loadXML($notify_data);
    if( ! empty($doc->getElementsByTagName( "notify" )->item(0)->nodeValue) ) {
        
        $out_trade_no = $doc->getElementsByTagName( "out_trade_no" )->item(0)->nodeValue;
        
        $trade_no = $doc->getElementsByTagName( "trade_no" )->item(0)->nodeValue;
        
        $trade_status = $doc->getElementsByTagName( "trade_status" )->item(0)->nodeValue;
        if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
            //以下是数据库操作代码
             logResult('alipay-notify-success');
            //数据库操作结束
            echo "success";
        }
    }
}
else {
    logResult('alipay-notify-fail');
    echo "fail";
}