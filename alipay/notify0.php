<?php
require_once("config.php");
require_once 'AlipayNotify.class.php';
error_log("AlipayNotify-start".$line,3,$log);
$alipayNotify = new AlipayNotify($alipay_config);
$verify_result = $alipayNotify->verifyNotify();
 
error_log(date('Y-m-d H:i:s').'AlipayNotify-start'.$line,3,$log);
if($verify_result) {//验证成功
    error_log("AlipayNotify-验证成功".$line,3,$log);
  
    $out_trade_no = $_POST['out_trade_no'];
        
    $trade_no = $_POST['trade_no'];
        
    $trade_status =$_POST['trade_status'];
        $total_fee=$_POST['total_fee'];
        $buyer_id=$_POST['buyer_id'];
        $total_fee=$_POST['total_fee'];
        error_log(date('Y-m-d H:i:s').'充值订单号'.$out_trade_no.'支付宝交易号'.$trade_no.'交易金额'.$total_fee.
            '交易账号'.$buyer_id.'支付状态'.$_POST['trade_status']."开始操作".$line,3,$log);
        if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
            echo "success";
            //以下是数据库操作代码
            error_log($out_trade_no."验证支付成功开始查询数据".$line,3,$log);
           
             $sql="select * from cm_paypay where oid='{$out_trade_no}' limit 1";
             $res=$mysqli->query($sql);
             $pay=$res->fetch_assoc();
             if(empty($pay['trade_no'])){
                 error_log($out_trade_no."支付成功开始处理数据".$line,3,$log);
                
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
                     error_log('$uid'.$uid.'$paypayid'.$paypayid.$line,3,$log);
                     $content='支付宝充值，充值订单号'.$out_trade_no.'支付宝交易号'.$trade_no;
                     
                     $sql="insert into cm_pay(uid,money,time,content)
                     values({$uid},{$total_fee},{$time},'{$content}')";
                     $mysqli->query($sql);
                     $payid=$mysqli->insert_id;
                     if($payid>0){
                         error_log('$uid'.$uid.'$$payid'.$payid.$line,3,$log);
                         $sql="select account from cm_users where id={$uid}";
                         $res=$mysqli->query($sql);
                         $account=$res->fetch_assoc();
                         $account_old=$account['account'];
                         $account_new=bcadd($account_old, $total_fee,2);
                         $sql="update cm_users set account={$account_new} where id={$uid}";
                         $mysqli->query($sql);
                         $row=$mysqli->affected_rows;
                         if($row===1){
                             error_log($out_trade_no."支付数据保存成功".$line,3,$log);
                             $mysqli->commit();
                         }else{
                             $mysqli->rollback();
                              error_log($out_trade_no."用户余额保存失败".$line,3,$log);
                         }
                     }else{
                         $mysqli->rollback();
                        
                         error_log($out_trade_no."用户收支记录保存失败".$line,3,$log);
                     }
                     //
                 }else{
                     $mysqli->rollback(); 
                     error_log($out_trade_no."但网站支付记录保存失败".$line,3,$log);
                 }
                 
                 
             }else{
                 error_log($out_trade_no."数据早已存在".$line,3,$log);
                 
             }
        }else{
            error_log($out_trade_no."支付失败".$line,3,$log);
           
        }
    
}
else {
    echo "fail";
    error_log("AlipayNotify-验证失败".$_POST['out_trade_no'].$line,3,$log);
    
}