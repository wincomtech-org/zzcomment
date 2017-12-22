<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);
require_once "lib/WxPay.Api.php";
require_once 'example/log.php';
if(isset($_REQUEST["dat"]) && $_REQUEST["out_trade_no"] != ""){
    $out_trade_no = $_REQUEST["out_trade_no"];
    $input = new WxPayOrderQuery();
    $input->SetOut_trade_no($out_trade_no);
    //printf_info(WxPayApi::orderQuery($input));
    $result=WxPayApi::orderQuery($input);
    echo $result['trade_state'];
    // $a=Log::DEBUG("query:" . json_encode($result));
    //     if(array_key_exists("return_code", $result)
    //         && array_key_exists("result_code", $result)
    //         && $result["return_code"] == "SUCCESS"
    //         && $result["result_code"] == "SUCCESS"
    //         &&$result['trade_state']=='SUCCESS'
    //         ){
    //         order_paid($order_id, 2);
           
    //     }else{
    //     	 order_paid($order_id);
    //     }
   	
    exit();
}
?>