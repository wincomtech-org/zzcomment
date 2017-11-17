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
    
    
    
    if($_GET['trade_status'] == 'TRADE_FINISHED' || $_GET['trade_status'] == 'TRADE_SUCCESS' ) {
        //判断该笔订单是否在商户网站中已经做过处理
        //如果没有做过处理，根据订单号（out_trade_no）在商户网站的订单系统中查到该笔订单的详细，并执行商户的业务程序
        //如果有做过处理，不执行商户的业务程序
        echo '支付成功'.$_GET['trade_status'];
       
        $sql="select * from cm_paypay where oid='{$out_trade_no}' limit 1";
         $res=$mysqli->query($sql);
         $pay=$res->fetch_assoc();
         if(empty($pay['trade_no'])){
             //error_log($date.'alipay-return'.$out_trade_no.$line,3,$log);
             $arr=explode('-', $out_trade_no);
             $uid=$arr[0];
             
             $mysqli->autocommit(FALSE);
             //$mysqli->rollback();
             //$mysqli->commit();
             $sql="insert into cm_paypay(uid,oid,money,trade_no,buyer_id,time,type) 
             values({$uid},'{$out_trade_no}',{$total_fee},'{$trade_no}','{$buyer_id}',{$time},1)";
             $mysqli->query($sql);
             $paypayid=$mysqli->insert_id;
             if($paypayid>0){
                 $content='支付宝充值'.$trade_no;
                 
                 $sql="insert into cm_pay(uid,money,time,content)
                 values({$uid},{$total_fee},{$time},'{$content}')";
                 $mysqli->query($sql);
                 $payid=$mysqli->insert_id;
                 if($payid>0){
                     $sql="select account from cm_users where id={$uid}";
                     $res=$mysqli->query($sql);
                     $account=$res->fetch_assoc();
                     $account_old=$account['account'];
                     $account_new=bcadd($account_old, $total_fee,2);
                     $sql="update cm_users set account={$account_new} where id={$uid}";
                     $mysqli->query($sql);
                     $row=$mysqli->affected_rows;
                     if($row===1){
                         echo '支付成功，返回个人中心首页';
                         $mysqli->commit();
                     }else{
                         $mysqli->rollback();
                         echo '支付成功，但用户余额保存失败，请记住交易号咨询客服';
                     }
                 }else{
                     $mysqli->rollback();
                     echo '支付成功，但用户收支记录保存失败，请记住交易号咨询客服';
                 }
                 //
             }else{
                 $mysqli->rollback();
                 echo '支付成功，但网站支付记录保存失败，请记住交易号咨询客服'.$paypayid;
             }
              
             
         }else{
             echo '支付成功，数据早已保存';
         }
    }else {
        
        //error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'支付失败'."\r\n",3,$log);
        echo '支付失败';
        
    }
    
}else {
    //验证失败
    //如要调试，请看alipay_notify.php页面的verifyReturn函数
    
    //error_log(date('Y-m-d H:i:s').':订单'.$out_trade_no.'验证失败'."\r\n",3,$log);
    echo '验证失败';
    
}
echo '<br/><a href="'.$index.'">返回</a>';
 
$mysqli->close();
exit;