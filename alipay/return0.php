<?php
require_once 'AlipayNotify.class.php';
require_once 'AlipaySubmit.class.php';
require_once 'config.php';
//计算得出通知验证结果 
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyReturn();


if($verify_result) {//验证成功
    //请在这里加上商户的业务逻辑程序代码
    
    $out_trade_no = $_GET['out_trade_no'];//商户订单号
    $trade_no = $_GET['trade_no'];//支付宝交易号
    $trade_status = $_GET['trade_status'];//交易状态
    $total_fee=$_GET['total_fee'];//支付金额
    $buyer_id=$_GET['buyer_id']; //买家付款账号buyer_id
    $buyer_id=$_GET['buyer_id']; //买家付款账号buyer_id
    
    
    if($_GET['trade_status'] == 'TRADE_SUCCESS' || $_GET['trade_status'] == 'TRADE_SUCCESS' ) {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        echo '支付成功';
        $time=time();
         $sql="select * from cm_paypay where trade_no='{$trade_no}' limit 1";
         $res=$mysqli->query($sql);
         $pay=$res->fetch_assoc();
         if(empty($pay['trade_no'])){
             
             $arr=explode('-', $pay['trade_no']);
             $uid=$arr[0];
             $sql="insert into cm_paypay(uid,oid,money,trade_no,buyer_id,time,type) 
             values({$uid},'{$out_trade_no}',{$total_fee},'{$trade_no}','{$buyer_id}',{$time},1)";
             $mysqli->query($sql);
             $payid=$mysqli->insert_id;
             if($payid>0){
                 $content='支付宝充值'.$trade_no;
                 
                 $sql="insert into cm_pay(uid,money,time,content)
                 values({$uid},{$total_fee},{$time},'{$content}')";
                 //
             }else{
                 echo '支付成功，但网站数据保存失败，请记住交易号咨询客服';
             }
              
             
         }
    }else {
        
        error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'支付失败'."\r\n",3,$log);
        echo '支付失败';
        
    }
    
}else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数
    
    error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'验证失败'."\r\n",3,$log);
    echo '验证失败';
    
}
echo '<a href="'.$index.'">返回</a>';
 
$mysqli->close();
exit;