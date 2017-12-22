<?php
 
 
require_once "lib/WxPay.Api.php";
require_once "WxPay.NativePay.php"; 
 
 
$data = file_get_contents("php://input"); 
$postObj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
$transaction_id= $postObj->transaction_id;
error_log(date('Y-m-d H:i:s').'weixin/notify$transaction_id'.$transaction_id."\n",3,'zz.log');
$input = new WxPayOrderQuery();
$input->SetTransaction_id($transaction_id);
$result = WxPayApi::orderQuery($input);

if(array_key_exists("return_code", $result)
    && array_key_exists("result_code", $result)
    && $result["return_code"] == "SUCCESS"
    && $result['result_code']=='SUCCESS'
    && $result['trade_state']=='SUCCESS')
{
    $arr = explode('-', $result['out_trade_no']);
    
    $pay_id= $arr[1]; // 订单号log_id
     
     /* 改变订单状态 */
    order_paid($pay_id, 2);
    
    exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
 }
 error_log(date('Y-m-d H:i:s').'weixin/notify-false-$transaction_id'.$transaction_id."\n",3,'zz.log');
 
 

