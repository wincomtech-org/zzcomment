<?php
 
 
//error_reporting(E_ERROR); 
require_once "lib/WxPay.Api.php";
require_once "WxPay.NativePay.php";
  
$data=$_GET['dat'];
 
  $input = new WxPayOrderQuery();
        $input->SetOut_trade_no($data);
        $result = WxPayApi::orderQuery($input);
      
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS"
            && $result['trade_state'] == 'SUCCESS'
            )
        {
            $arr = explode('-', $result['out_trade_no']);
            
            $pay_id= $arr[1]; // 订单号log_id
            
            $pay_id= trim($pay_id);
             /* 改变订单状态 */
            order_paid($pay_id, 2);
               header("Location: http://www.onlmt.com/user.php");  
           exit;
        }
        exit();
       
?>