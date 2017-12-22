<?php
header("content-type:text/html;charset=utf-8");
ini_set('date.timezone','Asia/Shanghai');
 
require_once "lib/WxPay.Api.php";
require_once "lib/WxPay.NativePay.php"; 

$config=(require_once dirname(dirname(__FILE__)).'/data/conf/db.php');
$alipay_config=$config['ALIPAY_CONFIG'];
$mysqli=new mysqli($config['DB_HOST'], $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME'],$config['DB_PORT']);
$mysqli->set_charset('utf8');
  
$log='wx.log';
$line=PHP_EOL;
$time=time();

 
$data = file_get_contents("php://input"); 
$postObj = simplexml_load_string($data, 'SimpleXMLElement', LIBXML_NOCDATA);
$out_trade_no= $postObj->out_trade_no;
error_log(date('Y-m-d H:i:s').'weixin/notify-start-$$out_trade_no'.$out_trade_no."\n",3,'wx.log');
$input = new WxPayOrderQuery();
$input->SetOut_trade_no($out_trade_no);
$result = WxPayApi::orderQuery($input);

if(array_key_exists("return_code", $result)
    && array_key_exists("result_code", $result)
    && $result["return_code"] == "SUCCESS"
    && $result['result_code']=='SUCCESS'
    && $result['trade_state']=='SUCCESS')
{
    error_log($out_trade_no . "验证支付成功开始查询数据" . $line, 3, $log);
    $total_fee=bcdiv($result['total_fee'],100,2);
    $trade_no=$result['transaction_id'];
    $buyer_id=$result['openid'];
    $sql = "select * from cm_paypay where oid='{$out_trade_no}' limit 1";
    $res = $mysqli->query($sql);
    $pay = $res->fetch_assoc();
    if (empty($pay['trade_no'])) {
        error_log($out_trade_no . "支付成功开始处理数据" . $line, 3, $log);
        
        $arr = explode('-', $out_trade_no);
        $uid = $arr[0];
        
        $mysqli->autocommit(FALSE);
        
        $sql = "insert into cm_paypay(uid,oid,money,trade_no,buyer_id,time,type)
        values({$uid},'{$out_trade_no}',{$total_fee},'{$trade_no}','{$buyer_id}',{$time},2)";
        $mysqli->query($sql);
        $paypayid = $mysqli->insert_id;
        if ($paypayid > 0) {
            
            $content = '微信充值，充值订单号' . $out_trade_no . '交易号' . $trade_no;
            error_log('$uid' . $uid . '$paypayid' . $paypayid . $content . $line, 3, $log);
            $sql = "insert into cm_pay(uid,money,time,content)
            values({$uid},{$total_fee},{$time},'{$content}')";
            $mysqli->query($sql);
            
            $payid = $mysqli->insert_id;
            if ($payid > 0 && $payid != $paypayid) {
                error_log('$uid' . $uid . '$$payid' . $payid . $line, 3, $log);
                $sql = "select account from cm_users where id={$uid}";
                $res = $mysqli->query($sql);
                $account = $res->fetch_assoc();
                $account_old = $account['account'];
                $account_new = bcadd($account_old, $total_fee, 2);
                $sql = "update cm_users set account={$account_new} where id={$uid}";
                $mysqli->query($sql);
                $row = $mysqli->affected_rows;
                if ($row === 1) {
                    error_log($out_trade_no . "支付数据保存成功" . $line, 3, $log);
                    $mysqli->commit();
                    exit('<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>');
                    
                } else {
                    $mysqli->rollback();
                    error_log($out_trade_no . "用户余额保存失败" . $line, 3, $log);
                }
            } else {
                $er = $mysqli->error;
                error_log('保存pay错误信息' . $er . $line, 3, $log);
                $mysqli->rollback();
                
                error_log($out_trade_no . "用户收支记录保存失败" . $line, 3, $log);
            }
            //
        } else {
            $er = $mysqli->error;
            error_log('保存paypay错误信息' . $er . $line, 3, $log);
            $mysqli->rollback();
            error_log($out_trade_no . "但网站支付记录保存失败" . $line, 3, $log);
        }
    } else {
        error_log($out_trade_no . "数据早已存在" . $line, 3, $log);
    }
}
error_log($out_trade_no . "支付失败" . $line, 3, $log);
 

